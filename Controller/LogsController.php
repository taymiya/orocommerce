<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller;

use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Request;

class LogsController extends Controller {


     /**
     * @Route("/pmanager/logs/index", name="pmanager_logs_index")
     * @Template()
     * @Acl(
     *      id="pmanager_logs_index",
     *      type="entity",
     *      class="IbnabPmanagerBundle:Logs",
     *      permission="VIEW"
     * )
     */
    public function indexAction()
    {
       return array();
    }
}
