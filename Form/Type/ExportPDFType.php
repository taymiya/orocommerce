<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Ibnab\Bundle\PmanagerBundle\Processor\ProcessorRegistry;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate as PDFTemplateEntity;
use Ibnab\Bundle\PmanagerBundle\Entity\Repository\PDFTemplateRepository;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;
use Symfony\Component\HttpFoundation\RequestStack;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Ibnab\Bundle\PmanagerBundle\Form\Type\PDFTemplateSelectType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
class ExportPDFType extends AbstractType
{
    const NAME = 'ibnab_pmanager_exportpdf';

    protected $securityContext;
    protected $requestStack;
    /** @var FrontendHelper */
    protected $frontendHelper;
    /**
     * @param ProcessorRegistry $processorRegistry
     */
    public function __construct(TokenStorage $securityContext,RequestStack $requestStack)
    {
       $this->securityContext = $securityContext;
       $this->requestStack = $requestStack;
    }
    /**
     * @param FrontendHelper $frontendHelper
     */
    public function setFrontendHelper(FrontendHelper $frontendHelper)
    {
        $this->frontendHelper = $frontendHelper;
    }
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $request = $this->requestStack->getCurrentRequest();
        $styleinline = array();
        if (true == $this->frontendHelper->isFrontendRequest($request)) {
            $styleinline = array('style' => 'width:100%;');
        }
       $builder->add('entityClass', HiddenType::class, ['required' => true])
       ->add('entityId', HiddenType::class, ['required' => true]);       
        $builder
            ->add(
                'template',
                PDFTemplateSelectType::class,
                [
                    'label' => 'oro.email.template.label',
                    'attr' => $styleinline,
                    'required' => true,
                    'depends_on_parent_field' => 'entityClass',
                    'configs' => [
                        'allowClear' => true
                    ]
                ]
            );
        $process = array_flip([
                        'download' => 'ibnab.pmanager.pdftemplate.exportpdf.download_type',
                        'attach'  => 'ibnab.pmanager.pdftemplate.exportpdf.attach_type'
                    ]);
           if (true != $this->frontendHelper->isFrontendRequest($request)) {
            $builder->add(
                'process',
                ChoiceType::class,
                [
                    'label'      => 'oro.email.type.label',
                    'required'   => true,
                    'data'       => 'download',
                    'choices'  => $process,
                    'expanded'   => true
                ]
            );
            $builder->add(
                'prefixname',
                TextType::class,
                [
                    'label'      => 'ibnab.pmanager.pdftemplate.exportpdf.prefix',
                    'required'   => false
                ]
            );
           }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'initChoicesByEntityName']);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'initChoicesByEntityName']);


    }
/**
     * @param FormEvent $event
     */
    public function initChoicesByEntityName(FormEvent $event)
    {
        
         $valuefrompost = $this->requestStack->getCurrentRequest()->get('ibnab_pmanager_exportpdf');
        if($valuefrompost and isset($valuefrompost['entityClass'])){
        $entityClass = $valuefrompost['entityClass'];
        }
        else{
        $entityClass = $this->requestStack->getCurrentRequest()->get('entityClass');
        $entityClass = trim(str_replace("_","\\",$entityClass));
        }

        //$data = $event->getData();
        if (null === $entityClass){
            return;
        }
        //$entityClass = is_object($data) ? $data->getEntityClass() : $data['entityClass'];
        $form = $event->getForm();
        /** @var UsernamePasswordOrganizationToken $token */
        $token        = $this->securityContext->getToken();
        $organization = $token->getOrganizationContext();
        //var_dump($entityClass);die();
        FormUtils::replaceField(
            $form,
            'template',
            [
                'selectedEntity' => 'Ibnab\\Bundle\\PmanagerBundle\\Entity\\PDFTemplate',
                'query_builder'  =>
                    function (PDFTemplateRepository $templateRepository) use (
                        $entityClass,
                        $organization
                    ) {
                        return $templateRepository->getEntityTemplatesQueryBuilder($entityClass, $organization, true);
                    },
            ],
            ['choices']
        );

     /*   if ($this->securityContext->isGranted('EDIT', 'entity:Oro\Bundle\EmailBundle\Entity\EmailUser')) {
            FormUtils::replaceField(
                $form,
                'contexts',
                [
                    'read_only' => false,
                ]
            );
        }*/
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
        return self::NAME;
    }
}
