<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\EmailBundle\Entity\Manager\EmailManager;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailAttachment;
use Oro\Bundle\EmailBundle\Form\Model\Email as EmailModel;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\EmailBundle\Form\Model\EmailAttachment as ModelEmailAttachment;
use Oro\Bundle\EmailBundle\Entity\EmailAttachmentContent;
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\EmailBundle\Controller\EmailController as BaseController;
/**
 * Class EmailController
 *
 * @package Ibnab\Bundle\PmanagerBundle\Controller
 *
 */
class EmailController extends BaseController
{
    /**
     * @Route("/createpdf", name="ibnab_pmanger_email_createpdf")
     * @AclAncestor("ibnab_pmanger_email_createpdf")
     * @Template("OroEmailBundle:Email:update.html.twig")
     */
    public function createPDFAction(Request $requestParam)
    {
        
       $emailModel = $this->get('oro_email.email.model.builder')->createEmailModel();
       $request = $requestParam->query;
       $attachmentId = $request->get('attachmentId');
       $attachment = $this->getDoctrine()
            ->getRepository('OroAttachmentBundle:Attachment')
            ->findOneBy(array('id' => $attachmentId));
        $emailAttachment = new EmailAttachment();
        $modelEmailAttachment = new ModelEmailAttachment();
        $emailAttachment->setFile($attachment->getFile());
        $emailAttachment->setFileName($attachment->getFile()->getFileName());
        $emailAttachmentContent = new EmailAttachmentContent();
        $emailAttachment->setContentType($attachment->getFile()->getMimeType());
        $emailAttachment->setContent($emailAttachmentContent);
        $modelEmailAttachment->setType(ModelEmailAttachment::TYPE_ATTACHMENT);
        $modelEmailAttachment->setFileSize($attachment->getFile()->getFileSize());
        $modelEmailAttachment->setModified($attachment->getFile()->getUpdatedAt());
        $modelEmailAttachment->setId($attachment->getId());
        $modelEmailAttachment->setEmailAttachment($emailAttachment);
        //$emailModel->addAttachment($modelEmailAttachment);
        $emailModel->setAttachmentsAvailable([$modelEmailAttachment]);
        return $this->process($emailModel);
    }
}
