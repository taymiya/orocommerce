<?php

namespace Ibnab\Bundle\PmanagerBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate;

class ProductTemplateHandler
{
    use RequestHandlerTrait;
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var ObjectManager
     */
    protected $translator;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     * @param Translator    $translator
     */
    public function __construct(FormInterface $form, RequestStack $request, ObjectManager $manager, TranslatorInterface $translator)
    {
        $this->form       = $form;
        $this->request    = $request;
        $this->manager    = $manager;
        $this->translator = $translator;
    }

    /**
     * Process form
     *
     * @param  PDFTemplate $entity
     * @return bool True on successful processing, false otherwise
     */
    public function process(ProductTemplate $entity)
    {
        $this->form->setData($entity);
        $request = $this->request->getCurrentRequest();
        if (in_array($request->getMethod(), array('POST', 'PUT'))) {
            $this->submitPostPutRequest($this->form, $request);
            if ($this->form->isValid()) {
                if ($entity->getType() == null) {
                        $entity->setType('html');
                }
                $this->manager->persist($entity);
                $this->manager->flush();

                return true;
            }
        }

        return false;
    }
}
