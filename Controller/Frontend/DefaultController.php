<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller\Frontend;

use Symfony\Component\Security\Core\Util\ClassUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;
use Symfony\Component\Routing\RouterInterface;
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
class DefaultController extends Controller {


    /**
     * @AclAncestor("oro_order_frontend_view")
     * @Route("/pmanager/default/indexfrontendorder/{id}", name="pmanager_default_indexfrontendorder")
     */
    public function indexFrontendOrderAction(Order $order) {
        $info = $this->get('request')->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = "download";
        $responseData = $this->resultPDF("Oro\\Bundle\\OrderBundle\\Entity\\Order",$order->getId(),$responseDataGetway['process']);

        return $this->render(
            "IbnabPmanagerBundle:Default:indexfrontendorder.html.twig",
           $responseData
        );

    }
    /**
     * @AclAncestor("oro_sale_quote_frontend_view")
     * @Route("/pmanager/default/indexfrontendquote/{id}", name="pmanager_default_indexfrontendquote")
     */
    public function indexFrontendQuoteAction(Quote $quote) {
        $info = $this->get('request')->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = "download";
        $responseData = $this->resultPDF("Oro\\Bundle\\SaleBundle\\Entity\\Quote",$quote->getId(),$responseDataGetway['process']);

        return $this->render(
            "IbnabPmanagerBundle:Default:indexfrontendquote.html.twig",
           $responseData
        );

    }
    protected function instancePDF($templateResult) {
        $configProvider = $this->getConfigurationProvider();
        $orientation = $templateResult->getOrientation() ? $templateResult->getOrientation() : 'P';
        $direction = $templateResult->getDirection() ? $templateResult->getDirection() : 'ltr';
        $font = $templateResult->getFont() ? $templateResult->getFont() : 'helvetica';
        $unit = $templateResult->getUnit() ? $templateResult->getUnit() : 'mm';
        $format = $templateResult->getFormat() ? $templateResult->getFormat() : 'A4';
        $right = $templateResult->getMarginright() ? $templateResult->getMarginright() : '2';
        $top = $templateResult->getMargintop() ? $templateResult->getMargintop() : '2';
        $left = $templateResult->getMarginleft() ? $templateResult->getMarginleft() : '2';
        $bottom = $templateResult->getMarginBottom() ? $templateResult->getMarginBottom() : '2';
        /*
        $header = $templateResult->getHeader() ? $templateResult->getHeader() : Null;
        $footer = $templateResult->getFooter() ? $templateResult->getFooter () : Null;
        if(!is_null($header)){
            $resultForPDFHeader = $this->get('ibnab_pmanager.pdftemplate_renderer')
                    ->renderWithDefaultFilters($header->getContent(), null);
        }*/
        if ($templateResult->getAutobreak() == 1) {
            $autobreak = true;
        } else {
            $autobreak = false;
        }

        $pdfObj = $this->get("ibnab_pmanager.tcpdf")->create($orientation, $unit, $format, true, 'UTF-8', false);
        if ($direction == 'rtl'):
            $pdfObj->setRTL(true);
        else:
            $pdfObj->setRTL(false);
        endif;
        if($templateResult->getHf()){
            
            $logo = $configProvider->getLogo();
            $logoSize = $configProvider->getLogoSize();
            $textHeader = $configProvider->getTextHeader();
            $titleHeader = $configProvider->getTitleHeader();
            if($logo != ""){
            $pdfObj->SetHeaderData($this->get('kernel')->getRootDir().'/'.$logo, $logoSize, $titleHeader, $textHeader);
            }

            $marginHeader = $configProvider->getMarginHeader();
            $marginFooter = $configProvider->getMarginFooter();
            
            $pdfObj->SetHeaderMargin($marginHeader);
            $pdfObj->SetFooterMargin($marginFooter);
        }
        $pdfObj->SetFont($font);
        $pdfObj->SetCreator($templateResult->getAuteur());
        $pdfObj->SetAuthor($templateResult->getAuteur());
        $pdfObj->SetMargins($left, $top, $right);
        $pdfObj->SetAutoPageBreak($autobreak, $bottom);
        return $pdfObj;
    }
    /**
     * @Route("/export/downloadorder/{fileName}/{id}", name="oro_importexportfrontendorder_export_download")
     * @AclAncestor("oro_order_frontend_view")
     *
     * @param string $fileName
     *
     * @return Response
     */
    public function downloadExportOrderResultAction($fileName,Order $order)
    {
        return $this->getExportHandler()->handleDownloadExportResult($fileName);
    }
    /**
     * @Route("/export/downloadquote/{fileName}/{id}", name="oro_importexportfrontendquote_export_download")
     * @AclAncestor("oro_sale_quote_frontend_view")
     *
     * @param string $fileName
     *
     * @return Response
     */
    public function downloadExportQuoteResultAction($fileName,Quote $quote)
    {
        return $this->getExportHandler()->handleDownloadExportResult($fileName);
    }
    protected function getExportHandler() {
        return $this->get('oro_importexport.handler.export');
    }
    protected function getFileOpertator() {
        return $this->get('oro_importexport.file.file_system_operator');
    }
    protected function getAttachmentManager() {
        return $this->get('oro_attachment.manager');
    }
    protected function getConfigurationProvider() {
        return $this->get('ibnab_pmanager.provider.configuration');
    }
    protected function resultPDF($entityClass,$entityId,$process = "download",$isFrontend = false,$currentEntityId = 0){
        $responseData = [
            'saved' => false
        ];

        
        //$importForm = $this->createForm('ibnab_pmanager_exportpdf');
        $responseData['entityClass'] = $entityClass;
        $responseData['entityId'] = $entityId;
        $responseData['process'] = $process;
        $pdftemplateEntity = new PDFTemplate();
        if(!$isFrontend){
        $importForm = $this->createForm('ibnab_pmanager_exportpdf');       
        $attachmentManager = $this->get('oro_attachment.manager');
        $responseData['form'] = $importForm->createView();
        if ($templateResult = $this->get('ibnab_pmanager.form.handler.exportpdf')->process()) {
            $entity = $this->getDoctrine()
                    ->getRepository($responseData['entityClass'])
                    ->findOneBy(array('id' => $responseData['entityId']));
            $templateParams = [
                'entity' => $entity
            ];
            $pdfObj = $this->instancePDF($templateResult);
            $pdfObj->setFontSubsetting(false);
            $pdfObj->AddPage();
            $outputFormat = 'pdf';
            $resultForPDF = $this->get('ibnab_pmanager.pdftemplate_renderer')
                    ->renderWithDefaultFilters($templateResult->getContent(), $templateParams);
            $resultForPDF = $templateResult->getCss() . $resultForPDF;

            $responseData['resultForPDF'] = $resultForPDF;

            $pdfObj->writeHTML($responseData['resultForPDF'], true, 0, true, 0);
            if ($responseData['entityClass'] == "Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm"):
              $this->getEmbeddedJs($responseData['entityClass'], $responseData['entityId'], $pdfObj);
            endif;
            $pdfObj->lastPage();

            //substr($info['entityClass'], strrpos($str, '\\') + 1)
            $fileName = $this->getFileOpertator()
                    ->generateTemporaryFileName($responseData['entityId'], $outputFormat);
            $pdfObj->Output($fileName, 'F');
            if($responseData['entityClass'] == "Oro\\Bundle\\OrderBundle\\Entity\\Order"){
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendorder_export_download', ['fileName' => basename($fileName),'id' => $responseData['entityId']]
            );
            }
            if($responseData['entityClass'] == "Oro\\Bundle\\SaleBundle\\Entity\\Quote"){
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendquote_export_download', ['fileName' => basename($fileName),'id' => $responseData['entityId']]
            );
            }
            if ($responseData['process'] == 'attach') {
                $attachment = new Attachment();
                $file = $this->getAttachmentManager()->prepareRemoteFile($fileName);
                $this->getAttachmentManager()->upload($file);
                //$attachment->save()
                $em = $this->getDoctrine()->getManager();
                $em->persist($file);
                $em->flush();
                $attachment->setFile($file);
                $em->persist($attachment);
                $em->flush();
                //var_dump($attachment);die(); 
                $responseData['attachment_id'] = $attachment->getId();
            }

            $responseData['url'] = $url;
            $responseData['saved'] = true;
        }
        }else{
            $templateResult = $this->getDoctrine()
                    ->getRepository("Ibnab\\Bundle\\PmanagerBundle\\Entity\\PDFTemplate")
                    ->findOneBy(array('id' => $responseData['entityId']));
            $entity = $this->getDoctrine()
                    ->getRepository($responseData['entityClass'])
                    ->findOneBy(array('id' => $currentEntityId));
            $templateParams = [
                'entity' => $entity
            ];
            $pdfObj = $this->instancePDF($templateResult);
            $pdfObj->setFontSubsetting(false);
            $pdfObj->AddPage();
            $outputFormat = 'pdf';
            $resultForPDF = $this->get('ibnab_pmanager.pdftemplate_renderer')
                    ->renderWithDefaultFilters($templateResult->getContent(), $templateParams);
            $resultForPDF = $templateResult->getCss() . $resultForPDF;

            $responseData['resultForPDF'] = $resultForPDF;  
            $pdfObj->writeHTML($responseData['resultForPDF'], true, 0, true, 0);
            $pdfObj->lastPage();

            //substr($info['entityClass'], strrpos($str, '\\') + 1)
            $fileName = $this->getFileOpertator()
                    ->generateTemporaryFileName($currentEntityId, $outputFormat);
            $pdfObj->Output($fileName, 'F');
            if($responseData['entityClass'] == "Oro\\Bundle\\OrderBundle\\Entity\\Order"){
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendorder_export_download', ['fileName' => basename($fileName),'id' => $currentEntityId]
            );
            }
            if($responseData['entityClass'] == "Oro\\Bundle\\SaleBundle\\Entity\\Quote"){
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendquote_export_download', ['fileName' => basename($fileName),'id' => $currentEntityId]
            );
            }
            $responseData['url'] = $url;
            $responseData['saved'] = true;
                  
        }

        //return $this->update($pdftemplateEntity);
        return $responseData;        
    }

    /**
     * @AclAncestor("oro_order_frontend_view")
     * @Route("/pmanager/frontend/createorder/{id}", name="pmanager_frontendorder_create")
     * @Template("IbnabPmanagerBundle:Default:getTemplateFrontend.html.twig")
     */
    public function createFrontendOrderAction(Order $order) {
        $configProvider = $this->getConfigurationProvider();
        //echo $order->getId();$this->getUser()->getId();
        $entityClass = $this->get('request')->get('entityClass');
        $entityId = $order->getId();
        $importForm = $this->createForm('ibnab_pmanager_exportpdf');
        if(!$configProvider->getEnableMultiTemplate() == 1){
            if($entityClass == "Oro\\Bundle\\OrderBundle\\Entity\\Order"){
                  if($configProvider->getDefaultOrderTemplate() != null){
                    $currentEntityId = $this->get('request')->get('id');
                    $responseData = $this->resultPDF($entityClass,$configProvider->getDefaultOrderTemplate(),'download',true,$currentEntityId);  
                    return $this->render(
                                "IbnabPmanagerBundle:Default:indexfrontendorder.html.twig", $responseData
                    );
                }
            }

        }
        return $this->render(
            "IbnabPmanagerBundle:Default:getTemplateFrontendOrder.html.twig",
         array(
            'entityClass' => $entityClass,
            'entityId' => $entityId,
            'form' => $importForm->createView()
        )
        );
    }
    /**
     * @AclAncestor("oro_sale_quote_frontend_view")
     * @Route("/pmanager/frontend/createquote/{id}", name="pmanager_frontendquote_create")
     * @Template("IbnabPmanagerBundle:Default:getTemplateFrontend.html.twig")
     */
    public function createFrontendQuoteAction(Quote $quote) {
        $configProvider = $this->getConfigurationProvider();
        //echo $order->getId();$this->getUser()->getId();
        $entityClass = $this->get('request')->get('entityClass');
        $entityId = $quote->getId();
        $importForm = $this->createForm('ibnab_pmanager_exportpdf');
        if(!$configProvider->getEnableMultiTemplate() == 1){
            if($entityClass == "Oro\\Bundle\\SaleBundle\\Entity\\Quote"){
                  if($configProvider->getDefaultQuoteTemplate() != null){
                    $currentEntityId = $this->get('request')->get('id');
                    $responseData = $this->resultPDF($entityClass,$configProvider->getDefaultQuoteTemplate(),'download',true,$currentEntityId);  
                    return $this->render(
                                "IbnabPmanagerBundle:Default:indexfrontendquote.html.twig", $responseData
                    );
                }
            }

        }
        return $this->render(
            "IbnabPmanagerBundle:Default:getTemplateFrontendQuote.html.twig",
         array(
            'entityClass' => $entityClass,
            'entityId' => $entityId,
            'form' => $importForm->createView()
        )
        );
    }
    protected function proccess(PDFTemplate $entity) {
        $responseData = [
            'saved' => false
        ];

        if ($this->get('ibnab_pmanager.form.handler.exportpdf')->process($entity)) {
            $responseData['saved'] = true;
        }
        $responseData['form'] = $this->get('ibnab_pmanager_exportpdf')->createView();

        return $responseData;
    }

}
