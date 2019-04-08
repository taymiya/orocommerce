<?php

namespace Ibnab\Bundle\PmanagerBundle\Provider;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
//use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Component\Security\Acl\Util\ClassUtils;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Util\Inflector;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;
use Ibnab\Bundle\PmanagerBundle\Model\PDFTemplateInterface;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Bundle\EmailBundle\Processor\VariableProcessorRegistry;
use Doctrine\Common\Util\ClassUtils as DoctrineClassUtils;

class PDFTemplateRenderer extends \Twig_Environment {

    const VARIABLE_NOT_FOUND = 'oro.email.variable.not.found';

    public static $defaultVariableFilters = ['system.userSignature' => 'oro_html_sanitize'];

    /** @var VariablesProvider */
    protected $variablesProvider;

    /** @var  Cache|null */
    protected $sandBoxConfigCache;

    /** @var  string */
    protected $cacheKey;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var PropertyAccessor */
    protected $accessor;

    /** @var VariableProcessorRegistry */
    protected $variableProcessorRegistry;

    /** @var array */
    private $systemVariables;

    /** @var ManagerRegistry */
    protected $doctrine;
    protected $doctrineManager;
    protected $serializer;
    protected $localizationHelper;

    /** @var ContainerInterface */
    protected $container;

    /**
     * @param \Twig_LoaderInterface   $loader
     * @param array                   $options
     * @param VariablesProvider       $variablesProvider
     * @param Cache                   $cache
     * @param                         $cacheKey
     * @param \Twig_Extension_Sandbox $sandbox
     * @param TranslatorInterface     $translator
     */
    public function __construct(
    \Twig_LoaderInterface $loader, $options, VariablesProvider $variablesProvider, Cache $cache, $cacheKey, \Twig_Extension_Sandbox $sandbox, TranslatorInterface $translator, VariableProcessorRegistry $variableProcessorRegistry, LocaleSettings $localeSettings, ManagerRegistry $doctrine, LocalizationHelper $localizationHelper, ContainerInterface $container
    ) {
        parent::__construct($loader, $options);

        $this->variablesProvider = $variablesProvider;
        $this->sandBoxConfigCache = $cache;
        $this->cacheKey = $cacheKey;
        $this->addExtension($sandbox);
        $this->configureSandbox();
        $this->localeSettings = $localeSettings;
        $this->translator = $translator;
        $this->doctrine = $doctrine;
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $this->serializer = new Serializer($normalizers, $encoders);
        $this->doctrineManager = $this->doctrine->getManager();
        $this->localizationHelper = $localizationHelper;
        $this->container = $container;
        $this->variableProcessorRegistry = $variableProcessorRegistry;
    }

    /**
     * Configure sandbox form config data
     *
     */
    protected function configureSandbox() {
        $allowedData = $this->getConfiguration();
        /** @var \Twig_Extension_Sandbox $sandbox */
        $sandbox = $this->getExtension('sandbox');
        /** @var \Twig_Sandbox_SecurityPolicy $security */
        $security = $sandbox->getSecurityPolicy();
        $security->setAllowedProperties($allowedData['properties']);
        $security->setAllowedMethods($allowedData['methods']);
    }

    /**
     * @return array
     */
    protected function getConfiguration() {
        $allowedData = $this->sandBoxConfigCache->fetch($this->cacheKey);
        if (false === $allowedData) {
            $allowedData = $this->prepareConfiguration();
            $this->sandBoxConfigCache->save($this->cacheKey, serialize($allowedData));
        } else {
            $allowedData = unserialize($allowedData);
        }

        return $allowedData;
    }

    /**
     * Prepare configuration from entity config
     *
     * @return array
     */
    private function prepareConfiguration() {
        $configuration = [];
        $configuration['formatters'] = [];
        $allGetters = $this->variablesProvider->getEntityVariableGetters();
        foreach ($allGetters as $className => $getters) {
            $properties = [];
            $methods = [];
            $formatters = [];
            $defaultFormatters = [];
            foreach ($getters as $varName => $getter) {
                if (empty($getter)) {
                    $properties[] = $varName;
                } else {
                    if (!is_array($getter)) {
                        $methods[] = $getter;
                    } else {
                        $methods[] = $getter['property_path'];
                        $formatters[$varName] = $getter['formatters'];
                        $defaultFormatters[$varName] = $getter['default_formatter'];
                    }
                }
            }
            $configuration['properties'][$className] = $properties;
            $configuration['methods'][$className] = $methods;

            $configuration['formatters'][$className] = $formatters;
            $configuration['default_formatter'][$className] = $defaultFormatters;
        }

        return $configuration;
    }

    protected function addDefaultVariableFilters($template) {
        foreach (self::$defaultVariableFilters as $var => $filter) {
            $template = preg_replace('/{{\s' . $var . '\s}}/u', sprintf('{{ %s|%s }}', $var, $filter), $template);
        }

        return $template;
    }

    /**
     * Compile email message
     *
     * @param EmailTemplateInterface $template
     * @param array                  $templateParams
     *
     * @return array first element is email subject, second - message
     */
    public function compileMessage(PDFTemplateInterface $template, array $templateParams = []) {
        $subject = $template->getSubject();
        $content = $template->getContent();
        $templateRendered = $this->renderWithDefaultFilters($content, $templateParams);
        $subjectRendered = $this->renderWithDefaultFilters($subject, $templateParams);

        return [$subjectRendered, $templateRendered];
    }

    /**
     * Renders content with default filters
     *
     * @param string $content
     * @param array  $templateParams
     *
     * @return string
     */
    public function renderWithDefaultFilters($content, array $templateParams = []) {
        $templateParams['system'] = $this->getSystemVariableValues();
        $content = $this->addDefaultVariableFilters($content);
        if (array_key_exists('entity', $templateParams)) {
            $content = $this->TagsCleaner($content);
            $content = $this->processVariables($content, $templateParams);
            $content = $this->processDefaultFilters($content, $templateParams);
            //$content = $this->processDefaultFilters($content, $templateParams['entity']);
        }
        return $this->render($content, $templateParams);
    }

    /**
     * Compile preview content
     *
     * @param EmailTemplate $entity
     * @param null|string   $locale
     *
     * @return string
     */
    public function compilePreview(PDFTemplate $entity, $locale = null) {
        $content = $entity->getContent();
        if ($locale) {
            foreach ($entity->getTranslations() as $translation) {
                /** @var EmailTemplateTranslation $translation */
                if ($translation->getLocale() === $locale && $translation->getField() === 'content') {
                    $content = $translation->getContent();
                }
            }
        }

        return $this->render('{% verbatim %}' . $content . '{% endverbatim %}', []);
    }

    /**
     * Process entity variables what have default filters, for example, datetime form type field
     *
     * Note:
     *  - all tags that do not start with `entity` will be ignored
     *
     * @param string $template
     * @param object $entity
     *
     * @return EmailTemplate
     */
    protected function processDefaultFilters($template, $templateParams) {
        $that = $this;
        $config = $that->getConfiguration();

        $entity = $templateParams['entity'];

        $callback = function ($match) use ($entity, $that, $config, $templateParams) {
            $docReader = new AnnotationReader();
            $reflect = new \ReflectionClass($entity);

            $numberFormatter = new NumberFormatter($this->localeSettings);
            $productAdditionalAttribute = ['names', 'descriptions', 'shortDescriptions', 'image'];
            $paymentTransaction = $this->doctrineManager->getRepository('Oro\Bundle\PaymentBundle\Entity\PaymentTransaction');
            $result = $match[0];
            $path = $match[1];
            $split = explode('.', $path);
            $subsplit = $split;
            $currentParam = "";

            if (isset($split[0]) && 'entity' === $split[0]) {
                if (isset($split[1])) {
                    $currentParam = $split[1];
                }
                unset($split[0]);
                try {
                    $propertyPath = array_pop($split);
                    $value = $entity;
                    $currentEntityClass = get_class($entity);
                    if ($currentParam == 'paymentMethod' && $currentEntityClass == "Oro\\Bundle\\OrderBundle\\Entity\\Order") {
                        $entityIdForPayemnt = $entity->getId();
                        $paymentMethodResults = $paymentTransaction->getPaymentMethods($currentEntityClass, [$entityIdForPayemnt]);
                        if (isset($paymentMethodResults[$entityIdForPayemnt])) {
                            foreach ($paymentMethodResults as $paymentMethodResult) {
                                $result = implode(", ", $paymentMethodResult);
                            }
                        }
                    } elseif (in_array($currentParam, $productAdditionalAttribute) && $currentEntityClass == "Oro\\Bundle\\ProductBundle\\Entity\\Product") {
                        if ($currentParam == 'image') {
                            $images = $entity->getImagesByType('main')->toArray();
                            foreach ($images as $image) {
                                if (!is_null($image->getImage())) {
                                    $result = '<img src="' . $this->container->get('kernel')->getProjectDir() . '/var/attachment/' . $image->getImage()->getFilename() . '" />';
                                    break;
                                }
                            }
                        } else {
                            $getFunction = 'get' . ucfirst($currentParam);
                            $result = $this->localizationHelper->getLocalizedValue($entity->$getFunction());
                        }
                    } else {

                        // check if value exists
                        $valueToRender = $that->getValue($value, $propertyPath);

                        $propertyName = lcfirst(Inflector::classify($propertyPath));
                        if (is_object($value)) {
                            if (array_key_exists('default_formatter', $config)) {
                                $valueClass = ClassUtils::getRealClass($value);
                                $defaultFormatter = $config['default_formatter'];
                                if (array_key_exists($valueClass, $defaultFormatter) && array_key_exists($propertyName, $defaultFormatter[$valueClass]) && !is_null($defaultFormatter[$valueClass][$propertyName])
                                ) {
                                    return sprintf(
                                            '{{ %s|oro_format(\'%s\') }}', $path, $config['default_formatter'][ClassUtils::getRealClass($value)][$propertyName]
                                    );
                                }
                            }
                        }

                        if (is_object($valueToRender)) {
                            return sprintf(sprintf('{{ %s|oro_format_name }}', $path));
                        }

                        return sprintf('{{ %s|oro_html_sanitize }}', $path);
                    }
                } catch (\Exception $e) {
                    $result = $that->translator->trans(self::VARIABLE_NOT_FOUND);
                }
            } elseif ($subsplit[0] && 'subtemplate' === $subsplit[0]) {
                //unset($subsplit[0]);
                $result = "";
                try {
                    if (isset($subsplit[1]) && is_numeric($subsplit[1])):
                        $em = $this->doctrine->getManager();
                        $parentEntityName = $em->getClassMetadata(get_class($entity))->getName();
                        $pdfTemplate = $this->doctrineManager->getRepository('Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate')->find($subsplit[1]);
                        $subEntityName = $pdfTemplate->getEntityName();
                        $subTableName = $em->getClassMetadata($subEntityName)->getTableName();

                        if (!is_null($pdfTemplate)):

                            $fKey = $this->getPDFTempale($pdfTemplate, $parentEntityName);

                            if (!is_null($fKey)):
                                $sql = 'SELECT *' .
                                        ' FROM ' . $subTableName . ' as e' .
                                        ' Where ' . $fKey . '=' . $entity->getId();
                                //$result = "";
                                $stmt = $em->getConnection()->prepare($sql);
                                $stmt->execute();
                                $fetsheds = $stmt->fetchAll();

                                foreach ($fetsheds as $fetshed):
                                    if (isset($fetshed['id'])):
                                        //$jsonContent = $this->serializer->serialize($fetshed, 'json');
                                        //echo $subEntityName;die();
                                        //$subTemplateEntity = $this->serializer->deserialize($jsonContent, $subEntityName, 'json');
                                        $subTemplateEntity = $this->doctrineManager->getRepository($subEntityName)->find($fetshed['id']);
                                        $subTemplateParams['entity'] = $subTemplateEntity;
                                        $content = $this->TagsCleaner($pdfTemplate->getContent());
                                        $subResult = $this->processDefaultFilters($content, $subTemplateParams);
                                        $subResult = $this->render($subResult, $subTemplateParams);
                                        $result .= $subResult;
                                    endif;
                                endforeach;
                            endif;
                        else:

                        endif;

                    else:

                    endif;
                } catch (\Exception $e) {
                    $result = $that->translator->trans(self::VARIABLE_NOT_FOUND);
                }
            }
            /*

              if ('subtemplate' !== $subsplit[0]) {
              $currency = $this->localeSettings->getCurrency();
              $localizedCurrencySymbol = $this->localeSettings->getCurrencySymbolByCurrency($currency);
              $currencySymbol = $numberFormatter->getSymbol(\NumberFormatter::CURRENCY_SYMBOL, \NumberFormatter::CURRENCY);
              $currencyIntlSymbol = $numberFormatter->getSymbol(\NumberFormatter::INTL_CURRENCY_SYMBOL, \NumberFormatter::CURRENCY);
              if ($reflect->hasProperty($propertyName)) {
              $docInfos = $docReader->getPropertyAnnotations($reflect->getProperty($propertyName));

              if ($docInfos[0]->type === 'money') {

              $result = str_replace(
              array($currency, $currencySymbol, $currencyIntlSymbol), $localizedCurrencySymbol, $numberFormatter->format(trim($this->render($result, $templateParams)), 2)
              );
              }
              }
              } */
            return $result;
        };

        return preg_replace_callback('/{{\s([\w\d\.\_\-]*?)\s}}/u', $callback, $template);
    }

    protected function TagsCleaner($string) {
        $tags = array("html", "body", "head");
        foreach ($tags as $tag) {
            $string = preg_replace("/<\\/?" . $tag . "(.|\\s)*?>/", "", $string);
        }
        return $string ;
    }

    protected function getPDFTempale($pdfTemplate, $parentEntityName = null) {

        if (!is_null($pdfTemplate)):
            $entityName = $pdfTemplate->getEntityName();
            if (!is_null($entityName)) {
                $currentEntity = null;
                $fKey = null;
                $emCurrent = $this->doctrine->getManagerForClass($entityName);
                $metadataCurrent = $this->doctrine->getManager()->getClassMetadata($entityName);
                $currentEntity = $metadataCurrent;

                $allass = array();
                $asso = $currentEntity->getAssociationMappings();
                foreach ($asso as $assMap) {

                    if (isset($assMap['isOwningSide']) && isset($assMap['targetEntity'])):
                        //var_dump($entityClass." ".$assMap['targetEntity']);die();
                        if ($assMap['isOwningSide'] && $assMap['targetEntity'] == $parentEntityName):
                            $allAssociations = isset($assMap['joinColumnFieldNames']) ? $assMap['joinColumnFieldNames'] : null;
                            if (is_array($allAssociations)):
                                foreach ($allAssociations as $allAssociation):
                                    $fKey = $allAssociation;
                                endforeach;
                            endif;
                        endif;
                    endif;
                }
            }
        endif;
        return $fKey;
    }

    /**
     * @param Object $entity
     * @param string $path
     *
     * @return mixed
     */
    protected function getValue($entity, $path) {
        $propertyAccess = $this->getPropertyAccess();

        return $propertyAccess->getValue($entity, $path);
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccess() {
        if (!$this->accessor instanceof PropertyAccessor) {
            $this->accessor = PropertyAccess::createPropertyAccessor();
        }

        return $this->accessor;
    }

    /**
     * @return array
     */
    protected function getSystemVariableValues() {
        if (null === $this->systemVariables) {
            $this->systemVariables = $this->variablesProvider->getSystemVariableValues();
        }

        return $this->systemVariables;
    }

    /**
     * @param string $content
     * @param array $templateParams
     *
     * @return string
     */
    protected function processVariables($content, array $templateParams) {
        $variableDefinitions = (array) $this->variablesProvider->getEntityVariableDefinitions(
                        DoctrineClassUtils::getClass($templateParams['entity'])
        );

        foreach ($variableDefinitions as $key => $variableDefinition) {
            unset($variableDefinitions[$key]);
            $variableDefinitions['entity.' . $key] = $variableDefinition;
        }

        return preg_replace_callback(
                '/{{\s([\w\.\_\-]*?)\s}}/u', function ($match) use ($templateParams, $variableDefinitions) {
            list($result, $path) = $match;
            if (isset($variableDefinitions[$path], $variableDefinitions[$path]['processor'])) {
                if ($this->variableProcessorRegistry->has($variableDefinitions[$path]['processor'])) {
                    $processor = $this->variableProcessorRegistry->get($variableDefinitions[$path]['processor']);

                    return $processor->process(
                                    $path, $variableDefinitions[$path], $templateParams
                    );
                }
            }

            return $result;
        }, $content
        );
    }

}
