<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Ibnab\Bundle\PmanagerBundle\Entity\Repository\PDFTemplateRepository;

class FrontendPDFTemplateSelectType extends AbstractType
{
    protected $securityContext;
    /**
     * @var Request
     */
    protected $repository;

    /**
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct(SecurityContextInterface $securityContext,PDFTemplateRepository $repository)
    {
       $this->securityContext = $securityContext;
       $this->repository = $repository;
    }



    /**
     * Returns a list of choices
     *
     * @return array
     */
    protected function getChoices($options)
    {
        $token        = $this->securityContext->getToken();
        $organization = $token->getOrganizationContext();
        $orderClass ="Oro\\Bundle\\OrderBundle\\Entity\\Order";
        //$orderClass = $options['invalid_message'];
        if(isset($options['attr']['classPassed'])){
            $orderClass = $options['attr']['classPassed'];
        }
        $templateResult = $this->repository->getEntityTemplatesQueryBuilder($orderClass, $organization)->getQuery()->getResult();  
        $choices = [];
        foreach ($templateResult as $template) {
            $choices[$template->getId()] = $template;
        }
        return $choices;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibnab_pmanager_frontendpdftemplate_list';
    }



    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaultConfigs = [
            'placeholder'             => 'ibnab.pmanager.pdftemplate.frontend.choose_template',
        ];

        $resolver->setDefaults(
            [
                'choices'              => function (Options $options) {
                    return $this->getChoices($options);
                },
                'choice_attr'          => function ($choice) {
                    return array();
                },
                'empty_value'          => '',
                'show_plural'          => false,
                'configs'              => $defaultConfigs,
                'translatable_options' => false,
                'apply_exclusions'     => true,
                'group_by' => function () {
                    return null;
                }
            ]
        );
        $resolver->setNormalizers(
            [
                // this normalizer allows to add/override config options outside
                'configs' => function (Options $options, $configs) use ($defaultConfigs) {
                    return array_merge($defaultConfigs, $configs);
                }
            ]
        );
    }

    
    /**
     * Returns a list of choice attributes for the given entity
     *
     * @param string $entityClass
     *
     * @return array
     */
    /*
    protected function getChoiceAttributes($entityClass)
    {
        $attributes = [];
        foreach ($this->itemsCache[$entityClass] as $key => $val) {
            $attributes['data-' . $key] = $val;
        }

        return $attributes;
    }
*/
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_choice';
    }

}
