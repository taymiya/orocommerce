<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Ibnab\Bundle\PmanagerBundle\Entity\Repository\PDFTemplateRepository;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;

class FrontendPDFTemplateSelectType extends AbstractType
{
    protected $tokenStorage;
    /**
     * @var Request
     */
    protected $repository;

    /**
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct(TokenStorageInterface $tokenStorage,PDFTemplateRepository $repository)
    {
       $this->tokenStorage = $tokenStorage;
       $this->repository = $repository;
    }



    /**
     * Returns a list of choices
     *
     * @return array
     */
    protected function getChoices($options)
    {
        $token        = $this->tokenStorage->getToken();
        $organization = $token->getOrganizationContext();
        $orderClass ="Oro\\Bundle\\OrderBundle\\Entity\\Order";
        //$orderClass = $options['invalid_message'];
        if(isset($options['attr']['classPassed'])){
            $orderClass = $options['attr']['classPassed'];
        }
        $templateResult = $this->repository->getEntityTemplatesQueryBuilder($orderClass, $organization)->getQuery()->getResult();  
        $choices = [];
        
        foreach ($templateResult as $template){
            $choices[$template->getName()] = $template->getId();
        }
        //var_dump($choices);die();
        return $choices;
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
    public function getBlockPrefix()
    {
        return 'ibnab_pmanager_frontendpdftemplate_list';
    }


    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $defaultConfigs = [
            'placeholder'             => 'ibnab.pmanager.pdftemplate.frontend.choose_template',
        ];

        $resolver->setDefaults(
            [
                'choices'              => function (Options $options) {
                    return $this->getChoices($options);
                },
                'choice_attr'          => [],
            ]
        );
        $resolver->setNormalizer(
            
                // this normalizer allows to add/override config options outside
                'configs' , function (Options $options, $configs) use ($defaultConfigs) {
                    return array_merge($defaultConfigs, $configs);
                }
            
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
        return Select2ChoiceType::class;
    }

}
