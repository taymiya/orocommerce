<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ibnab\Bundle\PmanagerBundle\Entity\Logs;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
 

class LogsRepository extends EntityRepository {

    public function save(Logs $log) {
        $em = $this->getEntityManager();
        $em->persist($log);
        $em->flush();
        return true;
    }

}
