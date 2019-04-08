<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation as JMS;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Ibnab\Bundle\PmanagerBundle\Model\ExtendPDFTemplate;
use Symfony\Component\Validator\Constraints as Assert;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Ibnab\Bundle\PmanagerBundle\Model\PDFTemplateInterface;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * PDFTemplate
 *
 * @ORM\Entity
 * @ORM\Table(name="ibnab_pmanager_template",
 *      uniqueConstraints={@ORM\UniqueConstraint(name="UQ_NAME_PDFTemplate", columns={"name", "entityName"})},
 *      indexes={@ORM\Index(name="pmanager_pdftemplate_name_idx", columns={"name"}),
 *          @ORM\Index(name="pmanager_pdftemplate_is_system_idx", columns={"isSystem"}),
 *          @ORM\Index(name="pmanager_pdftemplate_entity_name_idx", columns={"entityName"})})
 * @ORM\Entity(repositoryClass="Ibnab\Bundle\PmanagerBundle\Entity\Repository\PDFTemplateRepository")
 * @Gedmo\TranslationEntity(class="Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplateTranslation")
 * @ORM\HasLifecycleCallbacks
 * @Config(
 *      defaultValues={
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "entity"={
 *              "icon"="icon-phone-sign"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "grouping"={
 *              "groups"={"activity"}
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      },
 *      routeName="pmanager_template_index",
 *      routeView="pmanager_template_view",
 *      routeUpdate="pmanager_template_update"
 * )
 * @JMS\ExclusionPolicy("ALL")
 */
class PDFTemplate extends ExtendPDFTemplate implements PDFTemplateInterface, Translatable
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
     * @var boolean
     *
     * @ORM\Column(name="isSystem", type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    protected $isSystem;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isEditable", type="boolean")
     * @JMS\Type("boolean")
     * @JMS\Expose
     */
    protected $isEditable;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $name;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $description;
    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $css;
    /**
     * @var string
     *
     * @ORM\Column(name="format", type="string", length=4, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $format;
    /**
     * @var string
     *
     * @ORM\Column(name="unit", type="string", length=4, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $unit;
    /**
     * @var string
     *
     * @ORM\Column(name="direction", type="string", length=4, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $direction;
    /**
     * @var string
     *
     * @ORM\Column(name="font", type="string", length=70, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $font;
    /**
     * @var string
     *
     * @ORM\Column(name="orientation", type="string", length=2, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $orientation;
    /**
     * @var string
     *
     * @ORM\Column(name="auteur", type="string", length=30, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $auteur;
    /**
     * @var integer
     *
     * @ORM\Column(name="margintop", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $margintop;
    /**
     * @var integer
     *
     * @ORM\Column(name="marginleft", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $marginleft;
    /**
     * @var integer
     *
     * @ORM\Column(name="marginright", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $marginright;
    /**
     * @var integer
     *
     * @ORM\Column(name="marginbottom", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $marginbottom;
    /**
     * @var integer
     *
     * @ORM\Column(name="autobreak", type="boolean", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $autobreak;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     * @JMS\Type("integer")
     * @JMS\Expose
     */
    protected $parent;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     * @Gedmo\Translatable
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $content;

    /**
     * @var string
     *
     * @ORM\Column(name="entityName", type="string", length=255, nullable=true)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $entityName;
    /**
     * @var boolean
     *
     * @ORM\Column(name="hf", type="boolean", options={"default"=false})
     */
    protected $hf;
    /**
     * Template type:
     *  - html
     *  - text
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20)
     * @JMS\Type("string")
     * @JMS\Expose
     */
    protected $type;

    /**
     * @Gedmo\Locale
     */
    protected $locale;

    /**
     * @ORM\OneToMany(
     *     targetEntity="Ibnab\Bundle\PmanagerBundle\Entity\PDFTemplateTranslation",
     *     mappedBy="object",
     *     cascade={"persist", "remove"}
     * )
     * @Assert\Valid()
     */
    //@Assert\Valid(cascade_validation = true)
    protected $translations;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @param $name
     * @param string $content
     * @param string $type
     * @param bool $isSystem
     * @internal param $entityName
     */
    public function __construct($name = '', $content = '', $type = 'html', $isSystem = false)
    {
        // name can be overridden from pdf template
        $this->name = $name;
        // isSystem can be overridden from pdf template
        $this->isSystem = $isSystem;
        // isEditable can be overridden from pdf template
        $this->isEditable = false;

        $boolParams = array('isSystem', 'isEditable');
        $templateParams = array('name',  'entityName', 'isSystem', 'isEditable');
        foreach ($templateParams as $templateParam) {
            if (preg_match('#@' . $templateParam . '\s?=\s?(.*)\n#i', $content, $match)) {
                $val = trim($match[1]);
                if (isset($boolParams[$templateParam])) {
                    $val = (bool) $val;
                }
                $this->$templateParam = $val;
                $content = trim(str_replace($match[0], '', $content));
            }
        }

        // make sure that user's template is editable
        if (!$this->isSystem && !$this->isEditable) {
            $this->isEditable = true;
        }

        $this->type = $type;
        $this->content = $content;
        $this->translations = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return PDFTemplate
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * Set parent
     *
     * @param integer $parent
     *
     * @return PDFTemplate
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent
     *
     * @return integer
     */
    public function getParent()
    {
        return $this->parent;
    }


    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set entityName
     *
     * @param string $entityName
     * @return PDFTemplate
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
     * Set template description
     *
     * @param string $description
     * @return PDFTemplate
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get template description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set pdf format
     *
     * @param string $format
     * @return PDFTemplate
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get pdf format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

        /**
     * Set pdf font
     *
     * @param string $format
     * @return PDFTemplate
     */
    public function setFont($font)
    {
        $this->font = $font;

        return $this;
    }

    /**
     * Get pdf font
     *
     * @return string
     */
    public function getFont()
    {
        return $this->font;
    }
    /**
     * Set pdf orientation
     *
     * @param string $orientation
     * @return PDFTemplate
     */
    public function setOrientation($orientation)
    {
        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Get pdf orientation
     *
     * @return string
     */
    public function getOrientation()
    {
        return $this->orientation;
    }
    /**
     * Set pdf auteur
     *
     * @param string $auteur
     * @return PDFTemplate
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get pdf auteur
     *
     * @return string
     */
    public function getAuteur()
    {
        return $this->auteur;
    }
    /**
     * Set pdf unit
     *
     * @param string $unit
     * @return PDFTemplate
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get pdf unit
     *
     * @return string
     */
    public function getUnit()
    {
        return $this->unit;
    }
    /**
     * Set pdf direction
     *
     * @param string $direction
     * @return PDFTemplate
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;

        return $this;
    }

    /**
     * Get pdf direction
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
    /**
     * Set pdf margintop
     *
     * @param int $margintop
     * @return PDFTemplate
     */
    public function setMargintop($margintop)
    {
        $this->margintop = $margintop;

        return $this;
    }

    /**
     * Get pdf margintop
     *
     * @return int
     */
    public function getMargintop()
    {
        return $this->margintop;
    }
    /**
     * Set pdf marginleft
     *
     * @param int $marginleft
     * @return PDFTemplate
     */
    public function setMarginleft($marginleft)
    {
        $this->marginleft = $marginleft;

        return $this;
    }

    /**
     * Get pdf marginleft
     *
     * @return int
     */
    public function getMarginleft()
    {
        return $this->marginleft;
    }
    /**
     * Set pdf marginright
     *
     * @param int $marginright
     * @return PDFTemplate
     */
    public function setMarginright($marginright)
    {
        $this->marginright = $marginright;

        return $this;
    }

    /**
     * Get pdf marginright
     *
     * @return int
     */
    public function getMarginright()
    {
        return $this->marginright;
    }
    /**
     * Set pdf marginbottom
     *
     * @param int $marginbottom
     * @return PDFTemplate
     */
    public function setMarginbottom($marginbottom)
    {
        $this->marginbottom = $marginbottom;

        return $this;
    }


    /**
     * Get pdf marginbottom
     *
     * @return int
     */
    public function getMarginbottom()
    {
        return $this->marginbottom;
    }
    /**
     * Set pdf autobreak
     *
     * @param boolen $autobreak
     * @return PDFTemplate
     */
    public function setAutobreak($autobreak)
    {
        $this->autobreak = $autobreak;

        return $this;
    }

    /**
     * Get pdf autobreak
     *
     * @return boolean
     */
    public function getAutobreak()
    {
        return $this->autobreak;
    }
    /**
     * Set a flag indicates whether a template is system or not.
     *
     * @param boolean $isSystem
     * @return PDFTemplate
     */
    public function setIsSystem($isSystem)
    {
        $this->isSystem = $isSystem;

        return $this;
    }

    /**
     * Get a flag indicates whether a template is system or not.
     * System templates cannot be removed or changed.
     *
     * @return boolean
     */
    public function getIsSystem()
    {
        return $this->isSystem;
    }

    /**
     * Get a flag indicates whether a template can be changed.
     *
     * @param boolean $isEditable
     * @return PDFTemplate
     */
    public function setIsEditable($isEditable)
    {
        $this->isEditable = $isEditable;

        return $this;
    }

    /**
     * Get a flag indicates whether a template can be changed.
     * For user's templates this flag has no sense (these templates always have this flag true)
     * But editable system templates can be changed (but cannot be removed or renamed).
     *
     * @return boolean
     */
    public function getIsEditable()
    {
        return $this->isEditable;
    }

    /**
     * Set locale
     *
     * @param mixed $locale
     * @return PDFTemplate
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return mixed
     */
    public function getLocale()
    {
        return $this->locale;
    }
    /**
     * Set template css
     *
     * @param string $css
     * @return PDFTemplate
     */
    public function setCss($css)
    {
        $this->css = $css;

        return $this;
    }

    /**
     * Get template css
     *
     * @return string
     */
    public function getCss()
    {
        return $this->css;
    }
    /**
     * Set template type
     *
     * @param string $type
     * @return PDFTemplate
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get template type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set translations
     *
     * @param ArrayCollection $translations
     * @return PDFTemplate
     */
    public function setTranslations($translations)
    {
        /** @var PDFTemplateTranslation $translation */
        foreach ($translations as $translation) {
            $translation->setObject($this);
        }

        $this->translations = $translations;
        return $this;
    }

    /**
     * Get translations
     *
     * @return ArrayCollection|PDFTemplateTranslation[]
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Clone template
     */
    public function __clone()
    {
        // cloned entity will be child
        $this->parent = $this->id;
        $this->id = null;
        $this->isSystem = false;
        $this->isEditable = true;

        if ($this->getTranslations() instanceof ArrayCollection) {
            $clonedTranslations = new ArrayCollection();
            foreach ($this->getTranslations() as $translation) {
                $clonedTranslations->add(clone $translation);
            }
            $this->setTranslations($clonedTranslations);
        }
    }

    /**
     * Convert entity to string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getName();
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
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    /**
     * Set hf
     *
     * @param string $hf
     * @return PDFTemplate
     */
    public function setHf($hf)
    {
        $this->hf = $hf;

        return $this;
    }

    /**
     * Get hf
     *
     * @return boolean
     */
    public function getHf()
    {
        return $this->hf;
    }
    
    /**
     * Set footer
     *
     * @param string $footer
     * @return PDFTemplate
     */
    public function setFooter($footer)
    {
        $this->footer= $footer;

        return $this;
    }

    /**
     * Get footer
     *
     * @return string
     */
    public function getFooter()
    {
        return $this->footer;
    }
}
