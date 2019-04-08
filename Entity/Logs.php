<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation as JMS;

use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * PDFTemplate
 *
 * @ORM\Entity
 * @ORM\Table(name="ibnab_pmanager_logs",
 *      indexes={@ORM\Index(name="pmanager_logs_entity_name_idx", columns={"entityName"}),
 *              @ORM\Index(name="pmanager_logs_created_at_idx", columns={"createdAt"}),
 *              @ORM\Index(name="pmanager_logs_realname_idx", columns={"realname"}),
 *              @ORM\Index(name="pmanager_logs_side_idx", columns={"side"})})
 * @ORM\Entity(repositoryClass="Ibnab\Bundle\PmanagerBundle\Entity\Repository\LogsRepository")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          }
 *      },
 *      routeName="pmanager_logs_index",
 *      routeView="pmanager_logs_view",
 *      routeUpdate="pmanager_logs_update"
 * )
 * @JMS\ExclusionPolicy("ALL")
 */
class Logs
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $id;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="entitytargetId", type="string", length=255 , nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $entitytargetId;

    /**
     * @var string
     *
     * @ORM\Column(name="entityName", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $entityName;

    /**
     * @var string
     *
     * @ORM\Column(name="side", type="string", length=45, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $side;
    
    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=10, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $type;    
    
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="text", nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $filename;
    /**
     * @var string
     *
     * @ORM\Column(name="realname", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $realname;
    
    /**
     * @var string
     *
     * @ORM\Column(name="filepath", type="text", nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $filepath;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="templateId", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $templateId;
 
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    protected $createdAt;
    
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     */
    public function __construct()
    {
    }
    /**
     * Pre persist event handler
     *
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entitytargetId
     *
     * @param int $entitytargetId
     * @return Logs
     */
    public function setEntitytargetId($entitytargetId)
    {
        $this->entitytargetId = $entitytargetId;

        return $this;
    }

    /**
     * Get entitytargetId
     *
     * @return int
     */
    public function getEntitytargetId()
    {
        return $this->entitytargetId;
    }

    /**
     * Gets owning user
     *
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets owning user
     *
     * @param User $owningUser
     *
     * @return PDFTemplate
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setRealname($realname)
    {
        $this->realname = $realname;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRealname()
    {
        return $this->realname;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilepath($filepath)
    {
        $this->filepath = $filepath;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilepath()
    {
        return $this->filepath;
    }
    /**
     * Set entityName
     *
     * @param string $entityName
     * @return Logs
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * Get entityName
     *
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }
 
    /**
     * Set side
     *
     * @param string $side
     * @return Logs
     */
    public function setSide($side)
    {
        $this->side = $side;

        return $this;
    }

    /**
     * Get side
     *
     * @return string
     */
    public function getSide()
    {
        return $this->side;
    }
    
    /**
     * Set side
     *
     * @param string $type
     * @return Logs
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     *
     * @param int $templateId
     * @return $Log
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }
    /**
     * Set organization
     *
     * @param Organization $organization
     * @return PDFTemplate
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }
    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    /**
     * Convert entity to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getRealname();
    }
}
