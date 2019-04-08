<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ibnab\Bundle\PmanagerBundle\Entity\Publication;
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Ibnab\Bundle\PmanagerBundle\Form\Type\ProductTemplateSelectType;
class PublicationType extends AbstractType
{
    const NAME = 'ibnab_pmanager_publication';
    /**
     * @var UserConfigManager
     */
    private $userConfig;

    /**
     * @var LocaleSettings
     */
    private $localeSettings;

    /**
     * @param UserConfigManager $userConfig
     * @param LocaleSettings    $localeSettings
     */
    public function __construct(ConfigManager $userConfig, LocaleSettings $localeSettings)
    {
        $this->userConfig     = $userConfig;
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.name.label',
                'required' => true
            )
        );
        
        $builder->add(
            'description',
            TextareaType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.description.label',
                'required' => false
            )
        );

        $builder->add(
            'width',
            TextType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.width.label',
                'required' => false
            )
        );
        $builder->add(
            'height',
            TextType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.height.label',
                'required' => false
            )
        );
        $builder->add(
            'content',
            OroRichTextType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.content.label',
                'required' => false ,
                'wysiwyg_options' => [
                        'statusbar' => true,
                        'resize' => true,
                ]
            )
        );
        $builder->add(
            'productSelect',
            ProductSelectType::class,
            array(
                    'required' => false,
                    'label' => 'oro.product.entity_label',
                    'create_enabled' => false,
            )
        );
        $builder->add(
            'productTemplateSelect',
            ProductTemplateSelectType::class,
            array(
                    'required' => false,
                    'label' => 'ibnab.pmanager.producttemplate.entity_label',
                    'create_enabled' => false,
            )
        );
         
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'preSubmit']);
  

    }
    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        /** @var Customer $entity */
        $entity = $event->getForm()->getData();

        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getCreatedAt()) {
            $entity->setCreatedAt($date);
        }


        $entity->setUpdatedAt($date);
    }
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'Ibnab\Bundle\PmanagerBundle\Entity\Publication',
                'intention'            => 'publication',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'cascade_validation'   => true,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

}
