<?php

namespace Ibnab\Bundle\PmanagerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Util\ClassUtils;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Oro\Bundle\EmbeddedFormBundle\Controller\EmbedFormController as EParent;
use Oro\Bundle\EmbeddedFormBundle\Entity\EmbeddedForm;
use Oro\Bundle\OrganizationBundle\Form\Type\OwnershipType;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbeddedFormManager;
use Oro\Bundle\EmbeddedFormBundle\Manager\EmbedFormLayoutManager;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitAfterEvent;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
class PEmbedFormController extends EParent
{
    /**
     * @Route("/pmanager/submit/{id}", name="pmanager_embedded_form_submit", requirements={"id"="[-\d\w]+"})
     */
    public function formAction(EmbeddedForm $formEntity, Request $request)
    {
                $logger = $this->get('logger');


        $response = new Response();
        $response->setPublic();
        $response->setEtag($formEntity->getId() . $formEntity->getUpdatedAt()->format(\DateTime::ISO8601));
        if ($response->isNotModified($request)) {
            return $response;
        }

        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.entity_manager');
        /** @var EmbeddedFormManager $formManager */
        $formManager = $this->get('oro_embedded_form.manager');
        $form        = $formManager->createForm($formEntity->getFormType(),null,array('csrf_protection' => false));

        if (in_array($request->getMethod(), ['POST', 'PUT'])) {

            $dataClass = $form->getConfig()->getOption('data_class');
            if (isset($dataClass) && class_exists($dataClass)) {
                $ref         = new \ReflectionClass($dataClass);
                $constructor = $ref->getConstructor();
                $data        = $constructor && $constructor->getNumberOfRequiredParameters()
                    ? $ref->newInstanceWithoutConstructor()
                    : $ref->newInstance();

                $form->setData($data);
            } else {
                $data = [];
            }
            $event = new EmbeddedFormSubmitBeforeEvent($data, $formEntity);
            $eventDispatcher = $this->get('event_dispatcher');
            $eventDispatcher->dispatch(EmbeddedFormSubmitBeforeEvent::EVENT_NAME, $event);
            $form->submit($request);
            

                    
            $event = new EmbeddedFormSubmitAfterEvent($data, $formEntity, $form);
            $eventDispatcher->dispatch(EmbeddedFormSubmitAfterEvent::EVENT_NAME, $event);
        }
        $isValid =true;
        foreach ($form->all() as $child) {
            $typeName = $child->getConfig()->getType()->getName();
            $childName = $child->getName();
            $typeName = $child->getConfig()->getType()->getName();
            if (!$form->get($childName)->isValid() && $typeName != "submit") {
                $isValid =  false;
            }
        }
        if (in_array($request->getMethod(), ['POST', 'PUT']) && $isValid) {
            $entity = $form->getData();
            
            /**
             * Set owner ID (current organization) to concrete form entity
             */
            $entityClass      = ClassUtils::getClass($entity);
            
            $config           = $this->get('oro_entity_config.provider.ownership');
            $entityConfig     = $config->getConfig($entityClass);
            $formEntityConfig = $config->getConfig($formEntity);

            if ($entityConfig->get('owner_type') === OwnershipType::OWNER_TYPE_ORGANIZATION) {
                $accessor = PropertyAccess::createPropertyAccessor();
                $accessor->setValue(
                    $entity,
                    $entityConfig->get('owner_field_name'),
                    $accessor->getValue($formEntity, $formEntityConfig->get('owner_field_name'))
                );
            }
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('oro_embedded_form_success', ['id' => $formEntity->getId()]));
        }

        /** @var EmbedFormLayoutManager $layoutManager */
        $layoutManager = $this->get('oro_embedded_form.embed_form_layout_manager');
        $response->setContent($layoutManager->getLayout($formEntity, $form)->render());

        return $response;
    }

    /**
     * @Route("/success/{id}", name="pmanager_embedded_form_success", requirements={"id"="[-\d\w]+"})
     */
    public function formSuccessAction(EmbeddedForm $formEntity,Request $request)
    {
        /** @var EmbedFormLayoutManager $layoutManager */
        $layoutManager = $this->get('oro_embedded_form.embed_form_layout_manager');

        return new Response($layoutManager->getLayout($formEntity)->render());
    }
}
