<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\ProductBundle\Model\ProductHolderInterface;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;

class ProductTemplateSelectType extends AbstractType
{
    const NAME = 'ibnab_product_template_select';
    const DATA_PARAMETERS = 'data_parameters';

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var array
     */
    protected $options;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                self::DATA_PARAMETERS => [],
                'autocomplete_alias' => 'ibnab_pmanager_producttemplate',
                'empty_label' => 'oro.product.removed',
                'grid_name' => 'pmanager-producttemplates-grid',
                'configs' => [
                    'placeholder' => 'oro.product.form.choose',
                    
                ],
            ]
        );
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
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->options = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $config = $form->getConfig();
        $view->vars['depends_on_parent_field'] = $config->getOption('depends_on_parent_field');
        $view->vars['data_route'] = $config->getOption('data_route');
        $view->vars['data_route_parameter'] = $config->getOption('data_route_parameter');
    }
}
