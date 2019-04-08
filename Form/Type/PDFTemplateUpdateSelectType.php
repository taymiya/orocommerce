<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
class PDFTemplateUpdateSelectType extends AbstractType
{
        const NAME = 'ibnab_pmanager_pdftemplate_update_list';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'pmanager_template_autocomplete',
                'grid_name' => 'pmanager-pdftemplates-grid',
                'data_class' => null,
                'configs' => [
                    'placeholder' => 'pmanager.template.form.choose',
                ],
                'data_route_parameter'    => 'name'
            ]
        );
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
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::NAME;
    }
}
