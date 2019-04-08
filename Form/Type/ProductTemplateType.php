<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate;
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Oro\Bundle\FormBundle\Form\Type\OroSimpleColorPickerType;
class ProductTemplateType extends AbstractType
{
    const NAME = 'ibnab_pmanager_producttemplate';
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
            'round',
            TextType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.round.label',
                'required' => false
            )
        );
        $builder->add(
            'background',
            OroSimpleColorPickerType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.background.label',
                'required' => false,
                'allow_custom_color' => true,
                'allow_empty_color'  => true,
            )
        );
        $builder->add(
            'border',
            OroSimpleColorPickerType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.border.label',
                'required' => false,
                'allow_custom_color' => true,
                'allow_empty_color'  => true,
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
            'css',
            TextareaType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.css.label',
                'required' => false
            )
        );
        $builder->add(
            'layout',
            TextareaType::class,
            array(
                'label'    => 'ibnab.pmanager.producttemplate.layout.label',
                'required' => false
            )
        );
        $builder->add(
            'type',
            ChoiceType::class,
            array(
                'label'    => 'oro.email.emailtemplate.type.label',
                'multiple' => false,
                'expanded' => true,
                'choices'  => array(
                    'html' => 'oro.email.datagrid.emailtemplate.filter.type.html',
                    'txt'  => 'oro.email.datagrid.emailtemplate.filter.type.txt'
                ),
                'required' => true
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
                'data_class'           => 'Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate',
                'intention'            => 'producttemplate',
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
