<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\TranslationBundle\Form\Type\GedmoTranslationsType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\FormBundle\Form\Type\OroRichTextType;

class PDFTemplateTranslationType extends AbstractType
{
  
    protected $configManager;
    /** @var string */
    protected $parentClass;
    /**
     * @param ConfigManager $configManager
     */
    public function __construct(ConfigManager $configManager, string $parentClass)
    {
        $this->configManager = $configManager;
        $this->parentClass = $parentClass;
    }
  /**
     * Set labels for translation widget tabs
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['labels'] = $options['labels'];                   
    }
    /** @var ConfigManager */

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    $isWysiwygEnabled = $this->configManager->get('oro_form.wysiwyg_enabled');

        $resolver->setDefaults(
            [
                'translatable_class'   => 'Ibnab\\Bundle\\PmanagerBundle\\Entity\\PDFTemplate',
                'csrf_token_id'            => 'pdftemplate_translation',
                'labels'               => [],
                'content_options'      => [],
                'fields'               => function (Options $options) use ($isWysiwygEnabled) {
                    return [
                        'content' => array_merge(
                            [
                                'field_type'      => OroRichTextType::class,
                                'attr'            => [
                                    'class'                => 'template-editor',
                                    'data-wysiwyg-enabled' => $isWysiwygEnabled,
                                ],
                                'wysiwyg_options' => [
                                      'height'     => '250px'
                                ]
                            ],
                            $options['content_options']
                        )
                    ];
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parentClass;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ibnab_pmanager_pdftemplate_translatation';
    }
    
}
