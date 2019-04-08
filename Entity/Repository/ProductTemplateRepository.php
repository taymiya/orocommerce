<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Ibnab\Bundle\PmanagerBundle\Entity\ProductTemplate;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class ProductTemplateRepository extends EntityRepository
{


    /**
     * Load templates by  name
     *
     * @param              $name
     *
     * @return ProductTemplate[]
     */
    public function getTemplateByName($name)
    {
        return $this->findBy(array('name' => $name));
    }

}
