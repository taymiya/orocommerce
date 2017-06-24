<?php

namespace Ibnab\Bundle\PmanagerBundle\Provider;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Security\Core\Util\ClassUtils;
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
class PDFTemplateRenderer extends \Twig_Environment {

    const VARIABLE_NOT_FOUND = 'oro.email.variable.not.found';

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

    /** @var array */
    private $systemVariables;

    /** @var ManagerRegistry */
    protected $doctrine;

    protected $serializer;
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
    \Twig_LoaderInterface $loader, $options, VariablesProvider $variablesProvider, Cache $cache, $cacheKey, \Twig_Extension_Sandbox $sandbox, TranslatorInterface $translator, LocaleSettings $localeSettings, ManagerRegistry $doctrine
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
        if (array_key_exists('entity', $templateParams)) {
            $content = $this->processDefaultFilters($content, $templateParams);
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
            $result = $match[0];
            $path = $match[1];
            $split = explode('.', $path);
            $subsplit = $split;
            if ($split[0] && 'entity' === $split[0]) {
                unset($split[0]);
                try {
                    $propertyPath = array_pop($split);
                    $value = $entity;

                    if (count($split)) {
                        $value = $that->getValue($entity, implode('.', $split));
                    }

                    // check if value exists
                    $that->getValue($value, $propertyPath);

                    $propertyName = lcfirst(Inflector::classify($propertyPath));
                    if (is_object($value) && array_key_exists('default_formatter', $config)) {
                        $valueClass = ClassUtils::getRealClass($value);
                        $defaultFormatter = $config['default_formatter'];
                        if (array_key_exists($valueClass, $defaultFormatter) && array_key_exists($propertyName, $defaultFormatter[$valueClass]) && !is_null($defaultFormatter[$valueClass][$propertyName])
                        ) {


                            $result = sprintf(
                                    '{{ %s|oro_format(\'%s\') }}', $path, $config['default_formatter'][ClassUtils::getRealClass($value)][$propertyName]
                            );
                        }
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
                        $pdfTemplate = $this->doctrine->getManager()->getRepository('Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate')->find($subsplit[1]);
                        $subEntityName = $pdfTemplate->getEntityName();
                        $subTableName = $em->getClassMetadata($subEntityName)->getTableName();
                        
                        if (!is_null($pdfTemplate)):
                            $fKey = $this->getPDFTempale($pdfTemplate, $parentEntityName);
                            if (!is_null($fKey)):
                                $sql = 'SELECT *'.
                                        ' FROM ' . $subTableName .' as e'.
                                        ' Where ' . $fKey . '=' . $entity->getId();
                                $result = "";
                                $stmt = $em->getConnection()->prepare($sql);
                                $stmt->execute();
                                $fetsheds=  $stmt->fetchAll();
                                
                                foreach ($fetsheds as $fetshed):
                                    if(isset($fetshed['id'])):
                                    $jsonContent = $this->serializer->serialize($fetshed, 'json');
                                    $subTemplateEntity = $this->serializer->deserialize($jsonContent, $subEntityName, 'json');
                                    $subTemplateParams['entity'] = $subTemplateEntity;
                                    $subResult = $this->processDefaultFilters($pdfTemplate->getContent(), $subTemplateParams);
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
            }/*
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
            }*/
            return $result;
        };

        return preg_replace_callback('/{{\s([\w\d\.\_\-]*?)\s}}/u', $callback, $template);
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

}
