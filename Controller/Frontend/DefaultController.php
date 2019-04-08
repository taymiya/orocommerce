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
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\SaleBundle\Entity\Quote;
use Oro\Bundle\RFPBundle\Entity\Request As RFQ;
use Symfony\Component\HttpFoundation\Request;
use Ibnab\Bundle\PmanagerBundle\Form\Type\ExportPDFType;
use Symfony\Component\Filesystem\Filesystem;
use Ibnab\Bundle\PmanagerBundle\Entity\Logs;
use Oro\Bundle\ImportExportBundle\File\FileManager;

class DefaultController extends Controller {

    /**
     * @AclAncestor("oro_order_frontend_view")
     * @Route("/pmanager/default/indexfrontendorder/{id}", name="pmanager_default_indexfrontendorder")
     */
    public function indexFrontendOrderAction(Request $requestParam, Order $order) {
        $request = $requestParam->request;
        $info = $request->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = "download";
        $responseData = $this->resultPDF("Oro\\Bundle\\OrderBundle\\Entity\\Order", $info['template'], $responseDataGetway['process'], true, $order->getId());

        return $this->render(
                        "IbnabPmanagerBundle:Default:indexfrontendorder.html.twig", $responseData
        );
    }

    /**
     * @AclAncestor("oro_sale_quote_frontend_view")
     * @Route("/pmanager/default/indexfrontendquote/{id}", name="pmanager_default_indexfrontendquote")
     */
    public function indexFrontendQuoteAction(Request $requestParam, Quote $quote) {
        $request = $requestParam->request;
        $info = $request->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = "download";
        $responseData = $this->resultPDF("Oro\\Bundle\\SaleBundle\\Entity\\Quote", $info['template'], $responseDataGetway['process'], true, $quote->getId());

        return $this->render(
                        "IbnabPmanagerBundle:Default:indexfrontendquote.html.twig", $responseData
        );
    }
    /**
     * @AclAncestor("oro_rfp_frontend_request_view")
     * @Route("/pmanager/default/indexfrontendrfq/{id}", name="pmanager_default_indexfrontendrfq")
     */
    public function indexFrontendRFQAction(Request $requestParam, RFQ $rfq) {
        $request = $requestParam->request;
        $info = $request->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = "download";
        $responseData = $this->resultPDF("Oro\\Bundle\\RFPBundle\\Entity\\Request", $info['template'], $responseDataGetway['process'], true, $rfq->getId());

        return $this->render(
                        "IbnabPmanagerBundle:Default:indexfrontendrfq.html.twig", $responseData
        );
    }
    /**
     * @AclAncestor("oro_rfp_frontend_request_view")
     * @Route("/pmanager/frontend/createrfq/{id}", name="pmanager_frontendrfq_create")
     * @Template("IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig")
     */
    public function createFrontendRFQAction(Request $requestParam, RFQ $rfq) {
        $request = $requestParam->query;
        $configProvider = $this->getConfigurationProvider();
        $entityClass = $request->get('entityClass');
        $entityId = $rfq->getId();
        $importForm = $this->createForm(ExportPDFType::class);
        
        if (!$configProvider->getEnableMultiTemplate() == 1) {
            if ($entityClass == "Oro\\Bundle\\RFPBundle\\Entity\\Request") {
                
                if ($configProvider->getDefaultRFQTemplate() != null) {
                    $currentEntityId = $entityId;
                    $responseData = $this->resultPDF($entityClass, $configProvider->getDefaultRFQTemplate(), 'download', true, $currentEntityId);
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:indexfrontendrfq.html.twig", $responseData
                    );
                } else {
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig"
                    );
                }
            }
        }
        if ($configProvider->getEnableMultiTemplate() == 1) {
            return $this->render(
                            "IbnabPmanagerBundle:Default:getTemplateFrontendRFQ.html.twig", array(
                        'entityClass' => $entityClass,
                        'entityId' => $entityId,
                        'form' => $importForm->createView()
                            )
            );
        }
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
        if ($templateResult->getHf()) {

            $logo = $configProvider->getLogo();
            $logoSize = $configProvider->getLogoSize();
            $textHeader = $configProvider->getTextHeader();
            $titleHeader = $configProvider->getTitleHeader();
            if ($logo != "") {
                $pdfObj->SetHeaderData($this->get('kernel')->getProjectDir() . '/var/' . $logo, $logoSize, $titleHeader, $textHeader);
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
    public function downloadExportOrderResultAction($fileName, Order $order) {
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
    public function downloadExportQuoteResultAction($fileName, Quote $quote) {
        return $this->getExportHandler()->handleDownloadExportResult($fileName);
    }
    /**
     * @Route("/export/downloadrfq/{fileName}/{id}", name="oro_importexportfrontendrfq_export_download")
     * @AclAncestor("oro_sale_quote_frontend_view")
     *
     * @param string $fileName
     *
     * @return Response
     */
    public function downloadExportRFQResultAction($fileName, RFQ $rfq) {
        return $this->getExportHandler()->handleDownloadExportResult($fileName);
    }
    protected function getExportHandler() {
        return $this->get('oro_importexport.handler.export');
    }

    protected function getConfigurationProvider() {
        return $this->get('ibnab_pmanager.provider.configuration');
    }

    protected function resultPDF($entityClass, $entityId, $process = "download", $isFrontend = false, $currentEntityId = 0) {
        $responseData = [
            'saved' => false
        ];
        $responseData['entityClass'] = $entityClass;
        $responseData['entityId'] = $entityId;
        $responseData['process'] = $process;
        $pdftemplateEntity = new PDFTemplate();
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
        $fileNameVirtual = FileManager::generateFileName($responseData['entityId'], $outputFormat);
        $fileName = FileManager::generateTmpFilePath($fileNameVirtual);
        $pdfObj->Output($fileName, 'F');
        if ($responseData['entityClass'] == "Oro\\Bundle\\OrderBundle\\Entity\\Order") {
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendorder_export_download', ['fileName' => basename($fileName), 'id' => $currentEntityId]
            );
        }
        if ($responseData['entityClass'] == "Oro\\Bundle\\SaleBundle\\Entity\\Quote") {
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendquote_export_download', ['fileName' => basename($fileName), 'id' => $currentEntityId]
            );
        }
        if ($responseData['entityClass'] == "Oro\\Bundle\\RFPBundle\\Entity\\Request") {
            $url = $this->get('router')->generate(
                    'oro_importexportfrontendrfq_export_download', ['fileName' => basename($fileName), 'id' => $currentEntityId]
            );
        }
        $fs = new Filesystem();
        $fs->copy($fileName, $this->get('kernel')->getProjectDir() . '/var/import_export/' . basename($fileName), true);
        $fs->remove($fileName);
        $responseData['url'] = $url;
        $responseData['saved'] = true;

        if ($responseData['saved'] == true) {
            $log = new Logs();
            $realnameArray = explode("/", $responseData['url']);
            $realname = $realnameArray[count($realnameArray) - 1];
            $log->setEntityName($responseData['entityClass']);
            $log->setTemplateId($templateResult->getId());
            $log->setType("unqiue");
            $log->setEntitytargetId($responseData['entityId']);
            $log->setFilename($responseData['url']);
            $log->setRealname(basename($fileName));
            $log->setSide('frontend');
            $log->setFilepath($this->get('kernel')->getProjectDir() . '/var/import_export/' . basename($fileName));
            $templateResult = $this->getDoctrine()
                    ->getRepository("Ibnab\\Bundle\\PmanagerBundle\\Entity\\Logs")
                    ->save($log);
        }
        return $responseData;
    }

    /**
     * @AclAncestor("oro_order_frontend_view")
     * @Route("/pmanager/frontend/createorder/{id}", name="pmanager_frontendorder_create")
     * @Template("IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig")
     */
    public function createFrontendOrderAction(Request $requestParam, Order $order) {
        $request = $requestParam->query;
        $configProvider = $this->getConfigurationProvider();
        //echo $order->getId();$this->getUser()->getId();
        $entityClass = $request->get('entityClass');
        $entityId = $order->getId();
        $importForm = $this->createForm(ExportPDFType::class);

        if (!$configProvider->getEnableMultiTemplate() == 1) {
            if ($entityClass == "Oro\\Bundle\\OrderBundle\\Entity\\Order") {
                if ($configProvider->getDefaultOrderTemplate() != null) {
                    $currentEntityId = $entityId;
                    $responseData = $this->resultPDF($entityClass, $configProvider->getDefaultOrderTemplate(), 'download', true, $currentEntityId);
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:indexfrontendorder.html.twig", $responseData
                    );
                } else {
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig"
                    );
                }
            }
        }

        if ($configProvider->getEnableMultiTemplate() == 1) {
            return $this->render(
                            "IbnabPmanagerBundle:Default:getTemplateFrontendOrder.html.twig", array(
                        'entityClass' => $entityClass,
                        'entityId' => $entityId,
                        'form' => $importForm->createView()
                            )
            );
        }
    }

    /**
     * @AclAncestor("oro_sale_quote_frontend_view")
     * @Route("/pmanager/frontend/createquote/{id}", name="pmanager_frontendquote_create")
     * @Template("IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig")
     */
    public function createFrontendQuoteAction(Request $requestParam, Quote $quote) {
        $request = $requestParam->query;
        $configProvider = $this->getConfigurationProvider();
        $entityClass = $request->get('entityClass');
        $entityId = $quote->getId();
        $importForm = $this->createForm(ExportPDFType::class);
        if (!$configProvider->getEnableMultiTemplate() == 1) {
            if ($entityClass == "Oro\\Bundle\\SaleBundle\\Entity\\Quote") {
                if ($configProvider->getDefaultQuoteTemplate() != null) {
                    $currentEntityId = $entityId;
                    $responseData = $this->resultPDF($entityClass, $configProvider->getDefaultQuoteTemplate(), 'download', true, $currentEntityId);
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:indexfrontendquote.html.twig", $responseData
                    );
                } else {
                    return $this->render(
                                    "IbnabPmanagerBundle:Default:noTemplateFrontend.html.twig"
                    );
                }
            }
        }
        if ($configProvider->getEnableMultiTemplate() == 1) {
            return $this->render(
                            "IbnabPmanagerBundle:Default:getTemplateFrontendQuote.html.twig", array(
                        'entityClass' => $entityClass,
                        'entityId' => $entityId,
                        'form' => $importForm->createView()
                            )
            );
        }
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
