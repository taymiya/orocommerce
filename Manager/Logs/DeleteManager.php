<?php

namespace Ibnab\Bundle\PmanagerBundle\Manager\Logs;

use Doctrine\Common\Persistence\ManagerRegistry;
use Ibnab\Bundle\PmanagerBundle\Entity\Logs;
use Symfony\Component\Filesystem\Filesystem;
class DeleteManager
{
    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @param ManagerRegistry $doctrine
     */
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function deleteOrCancel(Logs $logs, $allowCancel)
    {
            $this->doDelete($logs);
        
    }

    protected function doDelete(Logs $logs)
    {
        $manager = $this->doctrine->getManager();
        $fs = new Filesystem();
        $filePath = $logs->getFilepath();   
        $manager->remove($logs);
        $fs->remove($filePath); 

    }

}
