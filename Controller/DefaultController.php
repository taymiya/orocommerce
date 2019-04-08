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
use Oro\Bundle\AttachmentBundle\Entity\Attachment;;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Ibnab\Bundle\PmanagerBundle\Form\Type\ExportPDFType;
use Ibnab\Bundle\PmanagerBundle\Entity\Logs;
use Oro\Bundle\ImportExportBundle\File\FileManager;

class DefaultController extends Controller {

    const CONTACT_ENTITY_NAME = 'OroCRM\Bundle\ContactBundle\Entity\Contact';
    const ORDER_ENTITY_NAME = 'OroCRM\Bundle\MagentoBundle\Entity\Order';

    /**
     * @Acl(
     *      id="pmanager_default_index",
     *      type="entity",
     *      class="IbnabPmanagerBundle:PDFTemplate",
     *      permission="EDIT"
     * )
     * @Route("/pmanager/default/index", name="pmanager_default_index", options= {"expose"= true})
     * @Template("IbnabPmanagerBundle:Default:index.html.twig")
     */
    public function indexAction(Request $requestParam) {
        $request = $requestParam->request;
        $info = $request->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = $info['process'] ? $info['process'] : "download";
        $responseDataGetway['prefixname'] = $info['prefixname'] ? $info['prefixname'] : "";
        $responseData = $this->resultPDF($info['entityClass'], $info['entityId'], $responseDataGetway);

        //return $this->update($pdftemplateEntity);
        return $responseData;
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
          } */
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

    protected function getExportHandler() {
        return $this->get('oro_importexport.handler.export');
    }

    /*
      protected function getFileOpertator() {
      return $this->get('oro_importexport.file.file_system_operator');
      } */

    protected function getEmbeddedJs($entityClass, $entityId, &$pdf) {

        $em = $this->getDoctrine()->getManager();
        $subTableName = $em->getClassMetadata($entityClass)->getTableName();
        $sql = 'SELECT *' .
                ' FROM ' . $subTableName . ' as e' .
                ' Where id=\'' . $entityId . '\'';
        $formEntity = null;
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $fetsheds = $stmt->fetchAll();
        foreach ($fetsheds as $fetshed) {
            $formEntity = $fetshed;
        }
        $url = "http://" . $this->getRequest()->getHost() . '' . $this->get('router')->generate(
                        'pmanager_embedded_form_submit', ['id' => $entityId]
        );
        $form = $this->getEmbeddedFromManager()->createForm(isset($formEntity['form_type']) ? $formEntity['form_type'] : 'orocrm_contact_us.embedded_form');
        /*
          unset($formEntity['created_at']);
          unset($formEntity['updated_at']);
          $encoders = array(new XmlEncoder(), new JsonEncoder());
          $normalizers = array(new ObjectNormalizer());
          $serializer = new Serializer($normalizers, $encoders);
          $jsonContent = $serializer->serialize($formEntity, 'json');
          $formEntityResult = $serializer->deserialize($jsonContent, $entityClass, 'json');
         * 
         */
        $pdf->setFormDefaultProp(array('lineWidth' => 1, 'borderStyle' => 'solid', 'fillColor' => array(255, 255, 255), 'strokeColor' => array(204, 204, 204)));
        $pdf->Cell(0, 5, $formEntity['title'], 0, 1, 'C');
        $pdf->Ln(10);
        $formName = $form->getName();
        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->getToken($formName);
        $insideJS = '';
        $fieldToSended = "";
        $pdf->writeHTMLCell(100, 5, null, null, '<form name="' . $formName . '" method="post" action="' . $url . '" >');
        $pdf->Ln(1);
        foreach ($form->all() as $child) {
            $typeName = $child->getConfig()->getType()->getName();
            $required = $child->getConfig()->getOption("required");
            $label = $this->get('translator')->trans($child->getConfig()->getOption("label"));
            $childName = $child->getName();
            $isEmail = false;
            if (strpos($childName, 'email') !== false) {
                $isEmail = true;
            } else {
                
            }
            if ($required):
                if (!$isEmail) {
                    $insideJS .= "if(!CheckField('" . $formName . '[' . $childName . ']' . "','" . $label . " " . $this->get('translator')->trans('This value should not be blank.') . "')) {return;} ";
                } else {
                    //$insideJS .= "if(!CheckField('".$formName.'['.$childName.']'."','".$label." ".$this->get('translator')->trans('This value should not be blank.')."')) {return;} ";  
                    $insideJS .= "if(!CheckEmailField('" . $formName . '[' . $childName . ']' . "','" . $label . " " . $this->get('translator')->trans('Invalid email address - Please try again.') . "')) {return;} ";
                }
                $label .= "*";
            endif;


            if ($typeName == "text"):
                $pdf->Cell(35, 8, $label . ':');
                //$pdf->writeHTMLCell(100, 5, 0, 0, "<input type='text' name='".$formName.'['.$childName.']'."' />");
                $pdf->TextField($formName . '[' . $childName . ']', 100, 5);
                $fieldToSended.= "\"" . $formName . '[' . $childName . ']' . "\"";
                $pdf->Ln(6);
            elseif ($typeName == "textarea"):
                $pdf->Cell(35, 8, $label . ':');
                $pdf->TextField($formName . '[' . $childName . ']', 120, 18, array('multiline' => true, 'lineWidth' => 0, 'borderStyle' => 'none'));
                $pdf->Ln(19);
                $fieldToSended.= "\"" . $formName . '[' . $childName . ']' . "\"";
            elseif ($typeName == "submit"):
                $pdf->SetX(50);
                $pdf->writeHTMLCell(100, 5, null, null, '<input type="hidden" name="' . $formName . '[_token]' . '" value="' . $token . '" />');
                $fieldToSended.= "\"" . $formName . "[_token]\"";
                //array('S'=>'SubmitForm', 'F'=> $url, 'Flags'=>array('Post'))
                $pdf->Button('submit', 30, 10, 'Submit', 'Submit()', array('lineWidth' => 2, 'borderStyle' => 'beveled', 'fillColor' => array(128, 196, 255), 'strokeColor' => array(64, 64, 64)));
                $pdf->Ln(6);
            endif;
        }
        $pdf->writeHTMLCell(100, 5, null, null, "</form>");
        $js = "
        function CheckField(name,message) {
          var f = getField(name);
          if(f.value == '') {
            app.alert(message);
            f.setFocus();
            return false;
          }
          return true;
        }
        function CheckEmailField(name,message) {
          var f = getField(name);
          if (f.value != '')
          {									
           if (! eMailValidate(f.value)) {
            app.alert(message);
            f.setFocus();
            return false;		
           }
          }
          else{
            app.alert(message);
            f.setFocus();
            return false;
          }
          return true;
        }
          function Submit() {
            " . $insideJS . "
           // var aSubmitFields = new Array( \"orocrm_contactus_contact_request[firstName]\" , \"orocrm_contactus_contact_request[lastname]\" , \"orocrm_contactus_contact_request[email]\" , \"orocrm_contactus_contact_request[comment]\" , \"orocrm_contactus_contact_request[_token]\" );
            this.submitForm({
            cURL: \"" . $url . "\",
            aFields: null,
            cSubmitAs: \"HTML\" 
           });
          }
          ";
        $js = <<<EOD
$js
EOD;
// Add Javascript code
        $pdf->IncludeJS($js);
    }

    protected function getEmbeddedFromManager() {
        return $this->get('oro_embedded_form.manager');
    }

    protected function getEmbeddedLayoutManager() {
        return $this->get('oro_embedded_form.embed_form_layout_manager');
    }

    protected function getFileAttachmentManager() {
        return $this->get('oro_attachment.file_manager');
    }

    protected function getAttachmentManager() {
        return $this->get('oro_attachment.manager');
    }

    protected function getConfigurationProvider() {
        return $this->get('ibnab_pmanager.provider.configuration');
    }

    protected function resultPDF($entityClass, $entityId, $process, $isFrontend = false, $currentEntityId = 0) {
        $responseData = [
            'saved' => false
        ];
        //$importForm = $this->createForm('ibnab_pmanager_exportpdf');
        $responseData['entityClass'] = $entityClass;
        $responseData['entityId'] = $entityId;
        $responseData['process'] = $process['process'];
        $responseData['prefixname'] = $process['prefixname'];
        $pdftemplateEntity = new PDFTemplate();
        $importForm = $this->createForm(ExportPDFType::class);
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
            if (!empty($responseData['prefixname'])) {
                $fileNameVirtual = FileManager::generateFileName($responseData['prefixname'], $outputFormat);
            } else {
                $fileNameVirtual = FileManager::generateFileName($responseData['entityId'], $outputFormat);
            }
            $fileName = FileManager::generateTmpFilePath($fileNameVirtual);
            $pdfObj->Output($fileName, 'F');
            $url = $this->get('router')->generate(
                    'oro_importexport_export_download', ['fileName' => basename($fileName)]
            );
            $fs = new Filesystem();
            $fs->copy($fileName, $this->get('kernel')->getProjectDir() . '/var/import_export/' . basename($fileName), true);
            if ($responseData['process'] == 'attach') {
                $attachment = new Attachment();
                $fileAttachmentManager = $this->getFileAttachmentManager();
                $file = $fileAttachmentManager->createFileEntity($fileName);
                $fileAttachmentManager->preUpload($file);
                $fileAttachmentManager->upload($file);
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
        //return $this->update($pdftemplateEntity);
        if ($responseData['saved'] == true) {
            $log = new Logs();
            $realnameArray = explode("/", $responseData['url']);
            $realname = $realnameArray[count($realnameArray) - 1];
            $log->setEntityName($responseData['entityClass']);
            $log->setTemplateId($templateResult->getId());
            $log->setType("unqiue");
            $log->setEntitytargetId($responseData['entityId']);
            $log->setFilename($responseData['url']);
            $log->setRealname($realname);
            $log->setSide('backend');
            $log->setFilepath($this->get('kernel')->getProjectDir() . '/var/import_export/' . basename($fileName));
            $templateResult = $this->getDoctrine()
                    ->getRepository("Ibnab\\Bundle\\PmanagerBundle\\Entity\\Logs")
                    ->save($log);
        }
        return $responseData;
    }
    /**
     * @Acl(
     *      id="pmanager_defaut_create",
     *      type="entity",
     *      class="IbnabPmanagerBundle:PDFTemplate",
     *      permission="EDIT"
     * )
     * @Route("/pmanager/default/create/{id}", name="pmanager_default_create")
     * @Template("IbnabPmanagerBundle:Default:getTemplate.html.twig")
     */
    public function createAction(Request $requestParam) {
        $request = $requestParam->query;
        $entityClass = $request->get('entityClass');
        $entityId = $requestParam->get('id');
        $importForm = $this->createForm(ExportPDFType::class);
        //echo $entityName;die();
        return array(
            'entityClass' => $entityClass,
            'entityId' => $entityId,
            'form' => $importForm->createView()
        );
    }

    /**
     * @Acl(
     *      id="pmanager_defaut_createview",
     *      type="entity",
     *      class="IbnabPmanagerBundle:PDFTemplate",
     *      permission="EDIT"
     * )
     * @Route("/pmanager/default/createview", name="pmanager_default_createview")
     * @Template("IbnabPmanagerBundle:Default:getTemplate.html.twig")
     */
    public function createviewAction(Request $requestParam) {
        $request = $requestParam->query;
        $entityClass = $request->get('entityClass');
        $entityClass = trim(str_replace("_", "\\", $entityClass));
        $entityId = $request->get('entityId');
        $importForm = $this->createForm(ExportPDFType::class);
        //echo $entityName;die();
        return array(
            'entityClass' => $entityClass,
            'entityId' => $entityId,
            'form' => $importForm->createView()
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

    protected function zip($filename, $allFilename) {
        $zip = new \ZipArchive();
        $res = $zip->open($filename, \ZipArchive::CREATE);

        $zipErrors = [
            \ZipArchive::ER_EXISTS => 'File already exists.',
            \ZipArchive::ER_INCONS => 'Zip archive inconsistent.',
            \ZipArchive::ER_INVAL => 'Invalid argument.',
            \ZipArchive::ER_MEMORY => 'Malloc failure.',
            \ZipArchive::ER_NOENT => 'No such file.',
            \ZipArchive::ER_NOZIP => 'Not a zip archive.',
            \ZipArchive::ER_OPEN => 'Can\'t open file.',
            \ZipArchive::ER_READ => 'Read error.',
            \ZipArchive::ER_SEEK => 'Seek error.',
            \ZipArchive::ER_WRITE => 'Write Error',
        ];

        if ($res !== true) {
            throw new \RuntimeException($zipErrors[$res], $res);
        }

        foreach ($allFilename as $currentFielname) {
            $zip->addFile($currentFielname);
        }

        $isClosed = $zip->close();
        if (!$isClosed) {
            throw new \RuntimeException(sprintf('Pack %s can\'t be closed', $file));
        }

        return true;
    }

    /**
     * @Route("/view/{entityName}", name="pmanager_defaut_view")
     * @Acl(
     *      id="pmanager_defaut_view",
     *      type="entity",
     *      class="IbnabPmanagerBundle:PDFTemplate",
     *      permission="VIEW"
     * )
     * @Template
     */
    public function viewAction($entityName) {
        $entity = $this->getDoctrine()
                ->getRepository('IbnabPmanagerBundle:PDFTemplate')
                ->getTemplateByEntityName($entityName);

        return array('entity' => $entity);
    }

}
