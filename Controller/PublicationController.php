<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Ibnab\Bundle\PmanagerBundle\Form\Type\PublicationType;
use Ibnab\Bundle\PmanagerBundle\Entity\Publication;

class PublicationController extends Controller
{
     /**
     * @Route("/pmanager/publication/index", name="pmanager_publication_index")
     * @Template()
     * @Acl(
     *      id="pmanager_publication_index",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Publication",
     *      permission="VIEW"
     * )
     */
    public function indexAction()
    {
       return array();
    }
    /**
     * @Route("pmanager/publication/update/{id}", name="pmanager_publication_update", requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="pmanager_publication_update",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Publication",
     *      permission="EDIT"
     * )
     * @Template()
     */
    public function updateAction(Publication $entity, $isClone = false)
    {
        return $this->update($entity, $isClone);
    }

    /**
     * @Route("pmanager/publication/create", name="pmanager_publication_create")
     * @Acl(
     *      id="pmanager_publication_create",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Publication",
     *      permission="CREATE"
     * )
     * @Template("IbnabPmanagerBundle:Publication:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Publication());
    }



    /**
     * @Route("pmanager/publication/preview/{id}" , name="pmanager_publication_preview" , requirements={"id"="\d+"}, defaults={"id"=0}))
     * @Acl(
     *      id="pmanager_publication_preview",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Publication",
     *      permission="VIEW"
     * )
     * @Template("IbnabPmanagerBundle:Publication:preview.html.twig")
     * @param bool|int $id
     * @return array
     */
    public function previewAction($id = false)
    {
        if (!$id) {
            $publicationTemplate = new Publication();
        } else {
            /** @var EntityManager $em */
            $em = $this->get('doctrine.orm.entity_manager');
            $publicationTemplate = $em->getRepository('Ibnab\Bundle\PmanagerBundle\Entity\Publication')->find($id);
        }

        /** @var FormInterface $form */
        $form = $this->get('ibnab_pmanager.form.publication');
        $form->setData($publicationTemplate);
        $request = $this->get('request');

        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $form->submit($request);
        }

        $templateRendered = $this->get('ibnab_pmanager.pdftemplate_renderer')
            ->compilePreview($publicationTemplate);

        return array(
            'content'     => $templateRendered,
            'contentType' => "html"
        );
    }

    /**
     * @param Publication $entity
     * @param bool $isClone
     * @return array
     */
    protected function update(Publication $entity, $isClone = false)
    {
        /*if ($this->get('ibnab_pmanager.form.handler.publication')->process($entity)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('ibnab.pmanager.publication.saved.message')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'pmanager_publication_update', 'parameters' => ['id' => $entity->getId()]],
                ['route' => 'pmanager_publication_index'],
                $entity
            );
        }

        return array(
            'entity'  => $entity,
            'form'    => $this->get('ibnab_pmanager.form.publication')->createView(),
            'isClone' => $isClone
        );*/
        return $this->get('oro_form.model.update_handler')->handleUpdate(
            $entity,
            $this->createForm(PublicationType::class, $entity),
            function (Publication $entity) {
                return [
                    'route' => 'pmanager_publication_update',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            function (Publication $entity) {
                return [
                    'route' => 'pmanager_publication_update',
                    'parameters' => ['id' => $entity->getId()]
                ];
            },
            $this->get('translator')->trans('ibnab.pmanager.publication.saved.message')
        );
    }
}
