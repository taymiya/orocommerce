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
use Oro\Bundle\AttachmentBundle\Entity\Attachment;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Filesystem\Filesystem;

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
     * @Route("/pmanager/default/index", name="pmanager_default_index")
     * @Template("IbnabPmanagerBundle:Default:index.html.twig")
     */
    public function indexAction() {
        $info = $this->get('request')->get('ibnab_pmanager_exportpdf');
        $responseDataGetway['process'] = $info['process'] ? $info['process'] : "download";
        $responseData = $this->resultPDF($info['entityClass'],$info['entityId'],$responseDataGetway['process']);

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

    protected function getExportHandler() {
        return $this->get('oro_importexport.handler.export');
    }
    protected function getFileOpertator() {
        return $this->get('oro_importexport.file.file_system_operator');
    }

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
        $formName =  $form->getName();
        $csrf = $this->get('security.csrf.token_manager');
        $token = $csrf->getToken($formName);
        $insideJS = '';
        $fieldToSended = "";
        $pdf->writeHTMLCell(100, 5, null, null, '<form name="'.$formName.'" method="post" action="'.$url.'" >');
        $pdf->Ln(1);
        foreach ($form->all() as $child) {
            $typeName = $child->getConfig()->getType()->getName();
            $required = $child->getConfig()->getOption("required");
            $label = $this->get('translator')->trans($child->getConfig()->getOption("label"));
            $childName = $child->getName();
            $isEmail = false;
            if (strpos($childName, 'email') !== false) {
                $isEmail= true;
            } 
            else{
                
            }
            if($required):
                if(!$isEmail){
                $insideJS .= "if(!CheckField('".$formName.'['.$childName.']'."','".$label." ".$this->get('translator')->trans('This value should not be blank.')."')) {return;} ";
                }else{
                //$insideJS .= "if(!CheckField('".$formName.'['.$childName.']'."','".$label." ".$this->get('translator')->trans('This value should not be blank.')."')) {return;} ";  
                $insideJS .= "if(!CheckEmailField('".$formName.'['.$childName.']'."','".$label." ".$this->get('translator')->trans('Invalid email address - Please try again.')."')) {return;} ";

                }
                $label .= "*";
            endif;
            
            
            if ($typeName == "text"):
                $pdf->Cell(35, 8, $label . ':');
                //$pdf->writeHTMLCell(100, 5, 0, 0, "<input type='text' name='".$formName.'['.$childName.']'."' />");
                $pdf->TextField($formName.'['.$childName.']', 100, 5);
                $fieldToSended.= "\"".$formName.'['.$childName.']'."\"";
                $pdf->Ln(6);
            elseif ($typeName == "textarea"):
                $pdf->Cell(35, 8, $label . ':');
                $pdf->TextField($formName.'['.$childName.']', 120, 18, array('multiline' => true, 'lineWidth' => 0, 'borderStyle' => 'none'));
                $pdf->Ln(19);
                $fieldToSended.= "\"".$formName.'['.$childName.']'."\"";
            elseif ($typeName == "submit"):
                $pdf->SetX(50);
                $pdf->writeHTMLCell(100, 5, null, null, '<input type="hidden" name="'.$formName.'[_token]'.'" value="'.$token.'" />');
                $fieldToSended.= "\"".$formName."[_token]\"";
                //array('S'=>'SubmitForm', 'F'=> $url, 'Flags'=>array('Post'))
                $pdf->Button('submit', 30, 10, 'Submit', 'Submit()', array('lineWidth' => 2, 'borderStyle' => 'beveled', 'fillColor' => array(128, 196, 255), 'strokeColor' => array(64, 64, 64)));
                $pdf->Ln(6);
            endif;
        }
       $pdf->writeHTMLCell(100, 5, null, null, "</form>");
        $js ="
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
            cURL: \"".$url."\",
            aFields: null,
            cSubmitAs: \"HTML\" 
           });
          }
          ";
$js=<<<EOD
$js
EOD;
// Add Javascript code
         $pdf->IncludeJS($js);
        /*
          $url = "http://" . $this->getRequest()->getHost() . '' . $this->get('router')->generate(
          'oro_embedded_form_submit', ['id' => $entityId]
          );
          $url = str_replace('_dev', '', $url);
          $formConetent = file_get_contents($url);
          $matchesBody = '';
          $matchesStyle = '';
          preg_match("/<body[^>]*>(.*?)<\/body>/is", $formConetent, $matchesBody);
          preg_match("/<style[^>]*>(.*?)<\/style>/is", $formConetent, $matchesStyle);
          var_dump($matchesStyle);
          die();
          if (isset($matchesBody[1])):
          if (isset($matchesStyle[1])):
          if (isset($matchesStyle[2])):
          return "<style>" . $matchesStyle[1] . $matchesStyle[2] . "</style>" . $matchesBody[1];
          else:
          return "<style>" . $matchesStyle[1] . "</style>" . $matchesBody[1];
          endif;

          else:
          return $matchesBody[1];
          endif;
          endif;
       
        $formEntityResult->set('id',$formEntity['id']);echo $formEntityResult->getId();die();
        $formConetent = $this->getEmbeddedLayoutManager()->getLayout($formEntityResult ,$form)->render();
        return $formConetent; */
    }

    protected function getEmbeddedFromManager() {
        return $this->get('oro_embedded_form.manager');
    }
    protected function getEmbeddedLayoutManager() {
        return $this->get('oro_embedded_form.embed_form_layout_manager');
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
            //echo $this->getFileOpertator()->getTemporaryDirectory();die();

            $pdfObj->Output($fileName, 'F');            
            $url = $this->get('router')->generate(
                    'oro_importexport_export_download', ['fileName' => basename($fileName)]
            );
            $fs = new Filesystem();
            $fs->copy($fileName, $this->get('kernel')->getRootDir().'/import_export/'.basename($fileName), true);
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
                    ->generateTemporaryFileName($responseData['entityId'], $outputFormat);
            $pdfObj->Output($fileName, 'F');
            $url = $this->get('router')->generate(
                    'oro_importexport_export_download', ['fileName' => basename($fileName)]
            );
            $fs = new Filesystem();
            $fs->copy($fileName, $this->get('kernel')->getRootDir().'/import_export/'.basename($fileName), true);
            $responseData['url'] = $url;
            $responseData['saved'] = true;
                  
        }

        //return $this->update($pdftemplateEntity);
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
    public function createAction() {
        $entityClass = $this->get('request')->get('entityClass');
        $entityId = $this->get('request')->get('id');
        $importForm = $this->createForm('ibnab_pmanager_exportpdf');
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
    public function createviewAction() {
        $entityClass = $this->get('request')->get('entityClass');
        $entityClass = trim(str_replace("_", "\\", $entityClass));
        $entityId = $this->get('request')->get('entityId');

        $importForm = $this->createForm('ibnab_pmanager_exportpdf');
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
