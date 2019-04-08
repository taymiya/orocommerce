<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Ibnab\Bundle\PmanagerBundle\Entity\Repository\PDFTemplateRepository;
use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;

class ExportPDFHandler
{
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Request
     */
    protected $repository;

    /**
     * @param Request       $request
     */
    public function __construct(RequestStack $request,PDFTemplateRepository $repository)
    {
        $this->request    = $request;
        $this->repository = $repository;
    }

    /**
     * Process form
     * @return bool True on successful processing, false otherwise
     */
    public function process()
    {
        //$this->form->setData($entity);
        $request = $this->request->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
          $params = $request->get('ibnab_pmanager_exportpdf');
          if(isset($params['template']) and isset($params['entityClass']) and isset($params['entityId']))
          {

             //$template = $params['template'];
             $resultTemplate = $this->repository->findOneBy(array('id' => $params['template']));
            if ($resultTemplate) {
              //echo $resultTemplate->getEntityName()." ".$params['entityClass'];
              if($resultTemplate->getEntityName()==$params['entityClass'])
              {
               return $resultTemplate;
              }
              else
              {
                return false;
              }
            }
            else
            {
              return false;
            }
             
          }       

        }

        return false;
    }
}
