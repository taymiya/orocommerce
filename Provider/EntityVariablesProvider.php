<?php

namespace Ibnab\Bundle\PmanagerBundle\Provider;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\EmailBundle\Provider\EntityVariablesProviderInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\UIBundle\Formatter\FormatterManager;

class EntityVariablesProvider implements EntityVariablesProviderInterface {

    /** @var TranslatorInterface */
    protected $translator;

    /** @var ConfigManager */
    protected $configManager;

    /** @var FormatterManager */
    protected $formatterManager;

    /** @var ManagerRegistry */
    protected $doctrine;
    protected $allAssociations = array();
    protected $currentEntity;

    /**
     * EntityVariablesProvider constructor.
     *
     * @param TranslatorInterface $translator
     * @param ConfigManager       $configManager
     * @param ManagerRegistry     $doctrine
     * @param FormatterManager    $formatterManager
     */
    public function __construct(
    TranslatorInterface $translator, ConfigManager $configManager, ManagerRegistry $doctrine, FormatterManager $formatterManager
    ) {
        $this->translator = $translator;
        $this->configManager = $configManager;
        $this->doctrine = $doctrine;
        $this->formatterManager = $formatterManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableDefinitions($entityClass = null, $entityName = null) {
        if ($entityClass) {
            // process the specified entity only
            return $this->getEntityVariableDefinitions($entityClass, $entityName);
        }

        // process all entities
        $result = [];
        $entityIds = $this->configManager->getProvider('entity')->getIds();

        foreach ($entityIds as $entityId) {
            $className = $entityId->getClassName();

            $entityData = $this->getEntityVariableDefinitions($className, $entityName);
            //$subTemplateData = $this->getEntitySubTemplateDefinitions($className, $entityName);
            if (!empty($entityData)) {
                $result[$className] = $entityData;
            }
        }
        $subTemplateIds = $this->getEntitySubTemplateDefinitions($this->allAssociations);
        if(isset($result[$entityName])):
            $result[$entityName]['subtemplate']= $subTemplateIds;
        endif;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getVariableGetters($entityClass = null) {
        if ($entityClass) {
            // process the specified entity only
            return $this->getEntityVariableGetters($entityClass);
        }

        // process all entities
        $result = [];
        $entityIds = $this->configManager->getProvider('entity')->getIds();
        foreach ($entityIds as $entityId) {
            $className = $entityId->getClassName();
            $entityData = $this->getEntityVariableGetters($className);
            if (!empty($entityData)) {
                $result[$className] = $entityData;
            }
        }
        return $result;
    }

    /**
     * @param string $entityClass
     *
     * @return array
     */
    protected function getEntitySubTemplateDefinitions($allAssociations) {

        $pdfTemplateRepository = $this->doctrine->getRepository('IbnabPmanagerBundle:PDFTemplate');
        $resultSubTemplate = array();
        foreach($allAssociations as $allAssociation):
            $templates = $pdfTemplateRepository->getTemplateByEntityName($allAssociation);
            foreach($templates as $template):
                $resultSubTemplate[$template->getId()] = [
                'type' => "string" ,
                'label' => $template->getName()
            ];
            endforeach;
        endforeach;
        return $resultSubTemplate;
        
    }

    /**
     * @param string $entityClass
     *
     * @return array
     */
    protected function getEntityVariableDefinitions($entityClass, $entityName = null) {

        $entityClass = ClassUtils::getRealClass($entityClass);
        $extendConfigProvider = $this->configManager->getProvider('extend');
        if (!$extendConfigProvider->hasConfig($entityClass)
            || !ExtendHelper::isEntityAccessible($extendConfigProvider->getConfig($entityClass))
        ) {
            return [];
        }

        $result = [];

        $em = $this->doctrine->getManagerForClass($entityClass);
        $metadata = $em->getClassMetadata($entityClass);
        $reflClass = new \ReflectionClass($entityClass);
        $entityConfigProvider = $this->configManager->getProvider('entity');
        $fieldConfigs = $this->configManager->getProvider('pmanager')->getConfigs($entityClass);
        foreach ($fieldConfigs as $fieldConfig) {
            if (!$fieldConfig->is('available_in_template')) {
                continue;
            }

            /** @var FieldConfigId $fieldId */
            $fieldId = $fieldConfig->getId();
            $fieldName = $fieldId->getFieldName();
            list($varName) = $this->getFieldAccessInfo($reflClass, $fieldName);
            if (!$varName) {
                continue;
            }

            $fieldLabel = $entityConfigProvider->getConfig($entityClass, $fieldName)->get('label');

            $var = [
                'type' => $fieldId->getFieldType(),
                'label' => $this->translator->trans($fieldLabel)
            ];

            if ($metadata->hasAssociation($fieldName)) {
                $targetClass = $metadata->getAssociationTargetClass($fieldName);

                if ($entityConfigProvider->hasConfig($targetClass)) {
                    $var['related_entity_name'] = $targetClass;
                }
            }

            $formatters = $this->formatterManager->guessFormatters($fieldId);
            if ($formatters) {
                $var = array_merge($var, $formatters);
            }

            $result[$varName] = $var;
        }
        if (!is_null($entityName)) {
            if (is_null($this->currentEntity)) {
                $emCurrent = $this->doctrine->getManagerForClass($entityName);
                $metadataCurrent = $em->getClassMetadata($entityName);
                $this->currentEntity = $metadataCurrent;
            }
            $allass = array();
            foreach ($this->currentEntity->getAssociationMappings() as $assMap) {

                if (isset($assMap['isOwningSide']) && isset($assMap['targetEntity'])):
                    //var_dump($entityClass." ".$assMap['targetEntity']);die();
                    if (!$assMap['isOwningSide']):
                        $this->allAssociations[] = $assMap['targetEntity'];
                    endif;
                endif;
            }
        }
        return $result;
    }

    /**
     * @param string $entityClass
     *
     * @return array
     */
    protected function getEntityVariableGetters($entityClass) {

        $entityClass = ClassUtils::getRealClass($entityClass);
        $extendConfigProvider = $this->configManager->getProvider('extend');
        if (!$extendConfigProvider->hasConfig($entityClass) || !ExtendHelper::isEntityAccessible($extendConfigProvider->getConfig($entityClass))
        ) {
            return [];
        }

        $result = [];
        $reflClass = new \ReflectionClass($entityClass);
        $fieldConfigs = $this->configManager->getProvider('pmanager')->getConfigs($entityClass);
        foreach ($fieldConfigs as $fieldConfig) {
            if (!$fieldConfig->is('available_in_template')) {
                continue;
            }

            /** @var FieldConfigId $fieldId */
            $fieldId = $fieldConfig->getId();

            list($varName, $getter) = $this->getFieldAccessInfo($reflClass, $fieldId->getFieldName());
            if (!$varName) {
                continue;
            }

            $resultGetter = $getter;
            $formatters = $this->formatterManager->guessFormatters($fieldId);
            if ($formatters && count($formatters)) {
                $resultGetter = array_merge(['property_path' => $getter], $formatters);
            }

            $result[$varName] = $resultGetter;
        }

        return $result;
    }

    /**
     * @param \ReflectionClass $reflClass
     * @param string           $fieldName
     *
     * @return array [variable name, getter method name]
     */
    protected function getFieldAccessInfo(\ReflectionClass $reflClass, $fieldName) {
        $getter = null;
        if ($reflClass->hasProperty($fieldName) && $reflClass->getProperty($fieldName)->isPublic()) {
            return [$fieldName, null];
        }

        $name = Inflector::classify($fieldName);
        $getter = 'get' . $name;
        if ($reflClass->hasMethod($getter) && $reflClass->getMethod($getter)->isPublic()) {
            return [lcfirst($name), $getter];
        }

        $getter = 'is' . $name;
        if ($reflClass->hasMethod($getter) && $reflClass->getMethod($getter)->isPublic()) {
            return [lcfirst($name), $getter];
        }

        return [null, null];
    }

}
