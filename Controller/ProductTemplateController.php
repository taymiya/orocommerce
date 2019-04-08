<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate;
use Ibnab\Bundle\PmanagerBundle\Form\Type\ProductTemplateType;


class ProductTemplateController extends Controller
{
    
    /**
     * @Route("/view/{name}", name="pmanager_defaut_view")
     * @Acl(
     *      id="pmanager_producttemplate_view",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="VIEW"
     * )
     * @Template
     */
    public function viewAction($name) {
        $entity = $this->getDoctrine()
                ->getRepository('IbnabPmanagerBundle:ProductTemplate')
                ->getTemplateByEntityName($entityName);

        return array('entity' => $entity);
    }
    
     /**
     * @Route("/pmanager/producttemplate/index", name="pmanager_producttemplate_index")
     * @Template()
     * @Acl(
     *      id="pmanager_producttemplate_index",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="VIEW"
     * )
     */
    public function indexAction()
    {
       return array();
    }
    /**
     * @Route("pmanager/producttemplate/update/{id}", name="pmanager_producttemplate_update", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="pmanager_producttemplate_update",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(ProductTemplate $entity, $isClone = false)
    {
        return $this->update($entity, $isClone);
    }

    /**
     * @Route("pmanager/producttemplate/create", name="pmanager_producttemplate_create")
     * @Acl(
     *      id="pmanager_producttemplate_create",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="CREATE"
     * )
     * @Template("IbnabPmanagerBundle:ProductTemplate:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new ProductTemplate());
    }



    /**
     * @Route("pmanager/producttemplate/preview/{id}" , name="pmanager_producttemplate_preview" , requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="pmanager_producttemplate_preview",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="VIEW"
     * )
     * @Template("IbnabPmanagerBundle:ProductTemplate:preview.html.twig")
     * @param bool|int $id
     * @return array
     */
    public function previewAction($id = false)
    {
        if (!$id) {
            $productTemplate = new ProductTemplate();
        } else {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            $productTemplate = $em->getRepository('Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate')->find($id);
        }

        /** @var FormInterface $form */
        $form = $this->get('ibnab_pmanager.form.producttemplate');
        $form->setData($productTemplate);
        $request = $this->get('request');

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($request);
        }

        $templateRendered = $this->get('ibnab_pmanager.pdftemplate_renderer')
            ->compilePreview($productTemplate);

        return array(
            'content'     => $templateRendered,
            'contentType' => $productTemplate->getType()
        );
    }
    /**
     * @Route("pmanager/producttemplate/load/{id}" , name="pmanager_producttemplate_load" , requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="pmanager_producttemplate_load",
     *      type="entity",
     *      class="IbnabPmanagerBundle:ProductTemplate",
     *      permission="VIEW"
     * )
     * @param bool|int $id
     * @return array
     */
    public function loadAction(ProductTemplate $entity) {
        try {
            $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
            $jsonEntity = $serializer->toArray($entity);
            return new JsonResponse([
                'success' => true,
                'data' => $jsonEntity // Your data here
            ]);
        } catch (\Exception $exception) {

            return new JsonResponse([
                'success' => false,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ]);
        }

    }

    /**
     * @param ProductTemplate $entity
     * @param bool $isClone
     * @return array
     */
    protected function update(ProductTemplate $entity, $isClone = false)
    {
        /*
        if ($this->get('ibnab_pmanager.form.handler.producttemplate')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('ibnab.pmanager.producttemplate.saved.message')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'pmanager_producttemplate_update', 'parameters' => ['id' => $entity->getId()]],
                ['route' => 'pmanager_producttemplate_index'],
                $entity
            );
        }

        return array(
            'entity'  => $entity,
            'form'    => $this->get('ibnab_pmanager.form.producttemplate')->createView(),
            'isClone' => $isClone
        );
         * */
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $entity,
            $this->createForm(ProductTemplateType::class, $entity),
            function (ProductTemplate $entity) {
                return [
                    'route' => 'pmanager_producttemplate_update',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            function (ProductTemplate $entity) {
                return [
                    'route' => 'pmanager_producttemplate_update',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            $this->get('translator')->trans('ibnab.pmanager.producttemplate.saved.message')
        );
    }
}
