<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller\Api\Rest;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\Authentication\Token\UsernamePasswordOrganizationToken;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

use Ibnab\Bundle\PmanagerBundle\Entity\Repository\LogsRepository;
use Ibnab\Bundle\PmanagerBundle\Entity\Logs;
use Symfony\Component\Filesystem\Filesystem;


/**
 * @RouteResource("logs")
 * @NamePrefix("logsoro_api_")
 */
class LogsController extends RestController
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete logs",
     *      resource=true
     * )
     * @Acl(
     *      id="pmanager_logs_delete",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Logs",
     *      permission="DELETE"
     * )
     * @Delete(requirements={"id"="\d+"})
     *
     * @return Response
     */
    public function deleteAction($id)
    {
        $entity = $this->getManager()->find($id);
        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        $em = $this->getManager()->getObjectManager();
        $em->remove($entity);
        $fs = new Filesystem();
        $fs->remove($entity->getFilepath());            
        $em->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('ibnab_pmanager.logs.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('ibnab_pmanager.form.type.pdftemplate.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('ibnab_pmanager.form.handler.pdftemplate.api');
    }
}
