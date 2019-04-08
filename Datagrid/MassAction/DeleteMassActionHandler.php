<?php

namespace Ibnab\Bundle\PmanagerBundle\Datagrid\MassAction;

use Doctrine\ORM\EntityManager;
use Ibnab\Bundle\PmanagerBundle\Manager\Logs\DeleteManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\DeleteMassActionHandler as ParentHandler;

class DeleteMassActionHandler extends ParentHandler
{
    /**
     * @var DeleteManager
     */
    protected $deleteManager;

    /**
     * @param DeleteManager $deleteManager
     */
    public function setDeleteManager($deleteManager)
    {
        $this->deleteManager = $deleteManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function processDelete($entity, EntityManager $manager)
    {
        $this->deleteManager->deleteOrCancel($entity, true);

        return $this;
    }
}


