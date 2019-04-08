<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplate;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class PDFTemplateRepository extends EntityRepository
{
    /**
     * Gets a template by its name
     * This method can return null if the requested template does not exist
     *
     * @param string $templateName
     * @return PDFTemplate|null
     */
    public function findByName($templateName)
    {
        return $this->findOneBy(array('name' => $templateName));
    }

   /**
     * Load templates by entity name
     *
     * @param              $entityName
     *
     * @return PDFTemplate[]
     */
    public function getTemplateByEntityName($entityName)
    {
        return $this->findBy(array('entityName' => $entityName));
    }



    /**
     * Return templates query builder filtered by entity name
     *
     * @param string       $entityName    entity class
     * @param Organization $organization
     * @param bool         $includeSystem if true - system templates will be included in result set
     *
     * @return QueryBuilder
     */
    public function getEntityTemplatesQueryBuilder($entityName, Organization $organization, $includeSystem = false)
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.entityName = :entityName')
            ->orderBy('e.name', 'ASC')
            ->setParameter('entityName', $entityName);

        if ($includeSystem) {
            $qb->orWhere('e.entityName IS NULL');
        }
        $qb->andWhere("e.organization = :organization")
            ->setParameter('organization', $organization);
      

        return $qb;
    }
    /**
     * Return templates query builder 
     *
     *
     * @return QueryBuilder
     */
    public function getAll()
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.name', 'ASC');

      

        return $qb;
    }
    /**
     * Return a query builder which can be used to get names of entities
     * which have at least one pdf template
     *
     * @return QueryBuilder
     */
    public function getDistinctByEntityNameQueryBuilder()
    {
        return $this->createQueryBuilder('e')
            ->select('e.entityName')
            ->distinct();
    }
}
