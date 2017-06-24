<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate as PDFTemplateEntity;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
class PDFTemplateType extends AbstractType
{
    const NAME = 'ibnab_pmanager_pdftemplate';
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
            'text',
            array(
                'label'    => 'oro.email.emailtemplate.name.label',
                'required' => true
            )
        );
        $builder->add(
            'description',
            'textarea',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.description.label',
                'required' => false
            )
        );
        $builder->add(
            'css',
            'textarea',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.css.label',
                'required' => false
            )
        );
        $builder->add(
            'auteur',
            'text',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.auteur.label',
                'required' => false
            )
        );
        $builder->add(
            'unit',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.unit.label',
                'multiple' => false,
                'choices'  => array(
                    'mm'  => 'ibnab.pmanager.pdftemplate.unit.millimeter_label',
                    'pt' => 'ibnab.pmanager.pdftemplate.unit.point_label',
                    'cm'  => 'ibnab.pmanager.pdftemplate.unit.centimeter_label',
                    'inch'  => 'ibnab.pmanager.pdftemplate.unit.inch_label',
                ),
                'required' => true
            )
        );
        $builder->add(
            'direction',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.direction.label',
                'multiple' => false,
                'choices'  => array(
                    'ltr'  => 'ibnab.pmanager.pdftemplate.direction.ltr_label',
                    'rtl' => 'ibnab.pmanager.pdftemplate.direction.rtl_label'
                ),
                'required' => true
            )
        );
        $builder->add(
            'font',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.font.label',
                'multiple' => false,
                'choices'  => array(
                    'helvetica' => 'Helvetica or Arial',                   
                    'helveticaB'  => 'Helvetica Bold',
                    'helveticaBI' => 'Helvetica Bold Italic',
                    'helveticaI' => 'Helvetica Italic',
                    'courier'  => 'Courier (fixed-width)',
                    'courierBI' => 'Courier Bold',
                    'courierI'  => 'Courier Bold Italic',
                    'symbol'  => 'Symbol (Symbolic)',
                    'times' => 'Times New Roman (Serif)',                   
                    'timesB'  => 'Times New Roman Bold',
                    'timesBI' => 'Times New Roman Bold Italic',
                    'timesI'  => 'Times New Roman Italic',
                    'zapfdingbats' => 'Zapf Dingbats',  
                    'cid0cs' => 'cid0cs (Chinese)',
                    'cid0jp' => 'cid0jp (Japan)',
                    'cid0kr' => 'cid0kr (Korea)',
                    'aealarabiya' => 'Ae alarabiya (Arabic)', 
                    'aefurat' => 'Ae furat (Arabic)',
                    'kozminproregular' => 'Kozmin pro regular (Asian characters)',                   
                    'kozminpromedium'  => 'Kozmin pro medium (Asian characters)',
                    'msungstdlight' => 'Msung std light (Asian characters)',
                    'arialunicid0'  => 'Arial unicid0 (Asian characters)',
                    'hysmyeongjostmedium' => 'Hysmyeong jost medium (Asian characters)',
                    'stsongstdlight' => 'St song std light (All Asian characters)',
                ),
                'required' => true
            )
        );

        $builder->add(
            'format',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.format.label',
                'multiple' => false,
                'choices'  => array(
                    'A4' => 'A4',
                    'A3' => 'A3',
                ),
                'required' => true
            )
        );
        $builder->add(
            'orientation',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.orientation.label',
                'multiple' => false,
                'choices'  => array(
                    'P' => 'ibnab.pmanager.pdftemplate.orientation.portrait_label',
                    'L'  => 'ibnab.pmanager.pdftemplate.orientation.landscape_label',
                ),
                'required' => true
            )
        );
        $builder->add(
            'margintop',
            'text',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.margintop.label',
                'required' => false
            )
        );
        $builder->add(
            'marginleft',
            'text',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.marginleft.label',
                'required' => false
            )
        );
        $builder->add(
            'marginright',
            'text',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.marginright.label',
                'required' => false
            )
        );
        $builder->add(
            'marginbottom',
            'text',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.marginbottom.label',
                'required' => false
            )
        );
        $builder->add(
            'autobreak',
            'choice',
            array(
                'label'    => 'ibnab.pmanager.pdftemplate.autobreak.label',
                'multiple' => false,
                'choices'  => array(
                    '1'  => 'ibnab.pmanager.pdftemplate.autobreak.yes_label',
                    '0' => 'ibnab.pmanager.pdftemplate.autobreak.no_label',
                 ),
                'required' => true
            )
        );
        $builder->add(
            'type',
            'choice',
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
            'entityName',
            'oro_entity_choice',
            array(
                'label'    => 'oro.email.emailtemplate.entity_name.label',
                'tooltip'  => 'oro.email.emailtemplate.entity_name.tooltip',
                'required' => false,
                'configs'  => [
                    'allowClear' => true
                ]
            )
        );
        /*
        $builder->add(
            'header',
            'genemu_jqueryselect2_entity',
            [
                'required' => false,
                'label'    => 'ibnab.pmanager.pdftemplate.header.label',
                'class'    => 'Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate',
                'configs'  => ['placeholder' => 'pmanager.template.form.choose'],
                'property' => 'name', 
            ]
        );
        $builder->add(
            'footer',
            'genemu_jqueryselect2_entity',
            [
                'required' => false,
                'label'    => 'ibnab.pmanager.pdftemplate.header.label',
                'class'    => 'Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate',
                'configs'  => ['placeholder' => 'pmanager.template.form.choose'],
                'property' => 'name', 
            ]
        );
        */
        /*
        $builder->add(
            'header',
            'oro_jqueryselect2_hidden',
            array(
                'autocomplete_alias' => 'users',

                // Default values
                'configs' => array(
                    'component'               => 'autocomplete',
                    'placeholder'             => 'ibnab.pmanager.pdftemplate.footer.label',
                    'allowClear'              => true,
                    'minimumInputLength'      => 1,
                    'route_name'              => 'oro_form_autocomplete_search',
                    'allowCreateNew'          => true,
                    'renderedPropertyName'    => 'name'
                )
            )
        );
        $builder->add(
            'footer',
            'oro_jqueryselect2_hidden',
            array(
                'autocomplete_alias' => 'users',

                // Default values
                'configs' => array(
                    'component'               => 'autocomplete',
                    'placeholder'             => 'ibnab.pmanager.pdftemplate.footer.label',
                    'allowClear'              => true,
                    'minimumInputLength'      => 1,
                    'route_name'              => 'oro_form_autocomplete_search',
                    'allowCreateNew'          => true,
                    'renderedPropertyName'    => 'name'
                )
            )
        );
        */
        $builder->add('hf', 'checkbox', ['required' => false,'label'    => 'ibnab.pmanager.pdftemplate.hf.label']);
        /*
        $builder
            ->add(
                'header',
                'ibnab_pmanager_pdftemplate_update_list',
                [
                    'label' => 'ibnab.pmanager.pdftemplate.footer.label',
                     'configs'  => [
                         'allowClear' => true,
                         'placeholder'             => 'ibnab.pmanager.pdftemplate.header.choose.label',
                      ]
                ]
            );
        $builder
            ->add(
                'footer',
                'ibnab_pmanager_pdftemplate_update_list',
                [
                    'label' => 'ibnab.pmanager.pdftemplate.header.label',
                     'configs'  => [
                         'allowClear' => true,
                         'placeholder'             => 'ibnab.pmanager.pdftemplate.footer.choose.label',
                      ]
                ]
            );
         * */
         
        $lang              = $this->localeSettings->getLanguage();
        $notificationLangs = $this->userConfig->get('oro_locale.languages');
        $notificationLangs = array_merge($notificationLangs, [$lang]);
        $localeLabels      = $this->localeSettings->getLocalesByCodes($notificationLangs, $lang);
        $builder->add(
            'translations',
            'ibnab_pmanager_pdftemplate_translatation',
            array(
                'label'    => 'oro.email.emailtemplate.translations.label',
                'required' => false,
                'locales'  => $notificationLangs,
                'labels'   => $localeLabels,

            )
        );
        $builder->add(
            'translation',
            'hidden',
            [
                'mapped' => false,
                'attr' => ['class' => 'translation']
            ]
        );
        $builder->add(
            'parentTemplate',
            'hidden',
            array(
                'label'         => 'oro.email.emailtemplate.parent.label',
                'property_path' => 'parent'
            )
        );

        // disable some fields for non editable email template
        $setDisabled = function (&$options) {
            if (isset($options['auto_initialize'])) {
                $options['auto_initialize'] = false;
            }
            $options['disabled'] = true;
        };
        $factory     = $builder->getFormFactory();
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory, $setDisabled) {
                $data = $event->getData();
               // var_dump($form->setData('header',1));die();
                if ($data && $data->getId() && $data->getIsSystem()) {
                    $form = $event->getForm();
                    // entityName field
                    $options = $form->get('entityName')->getConfig()->getOptions();
                    $setDisabled($options);
                    $form->add($factory->createNamed('entityName', 'oro_entity_choice', null, $options));
                    // name field
                    $options = $form->get('name')->getConfig()->getOptions();
                    $setDisabled($options);
                    $form->add($factory->createNamed('name', 'text', null, $options));
                    if (!$data->getIsEditable()) {
                        // name field
                        $options = $form->get('type')->getConfig()->getOptions();
                        $setDisabled($options);
                        $form->add($factory->createNamed('type', 'choice', null, $options));
                    }
                }
        //$entityClass = is_object($data) ? $data->getEntityClass() : $data['entityClass'];



            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate',
                'intention'            => 'pdftemplate',
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
    protected function getLanguages()
    {
        $languages = $this->userConfig->get('oro_locale.languages');

        return array_unique(array_merge($languages, [$this->localeSettings->getLanguage()]));
    }

    /**
     * @return array
     */
    protected function getLocaleLabels()
    {
        return $this->localeSettings->getLocalesByCodes($this->getLanguages(), $this->localeSettings->getLanguage());
    }
}
