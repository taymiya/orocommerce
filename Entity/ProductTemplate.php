<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
/**
 * @ORM\Entity
 * @ORM\Table(name="ibnab_product_template",
 * uniqueConstraints={@ORM\UniqueConstraint(name="UQ_NAME_ProductTemplate", columns={"name"})},
 * indexes={@ORM\Index(name="pmanager_producttemplate_name_idx", columns={"name"}),
 *      @ORM\Index(name="pmanager_producttemplate_created_idx", columns={"created_at"}),
 *      @ORM\Index(name="pmanager_producttemplate_updated_idx",columns={"updated_at"})
 * }
 * )
 * @ORM\Entity(repositoryClass="Ibnab\Bundle\PmanagerBundle\Entity\Repository\ProductTemplateRepository")
 * @Config(
 *      defaultValues={
 *         "form"={
 *             "form_type"="ibnab_pmanager_producttemplate",
 *             "grid_name"="pmanager-producttemplates-grid",
 *         },
 *      }
 * )
 */
class ProductTemplate implements \ArrayAccess
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    
    protected $name;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="round", type="integer", nullable=true)
     */
    protected $round;
    /**
     * @var string
     *
     * @ORM\Column(name="background", type="string", length=7, nullable=true)
     */
    
    protected $background;
    /**
     * @var string
     *
     * @ORM\Column(name="border", type="string", length=7, nullable=true)
     */
    
    protected $border;
    /**
     * @var double
     *
     * @ORM\Column(name="width", type="decimal", scale=3, nullable=true)
     */
    protected $width;
    
    /**
     * @var double
     *
     * @ORM\Column(name="height", type="decimal", scale=3, nullable=true)
     */
    protected $height;


    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;
   
    /**
     * @var string
     *
     * @ORM\Column(name="css", type="text", nullable=true)
     */
    
    protected $css;
 
    /**
     * @var string
     *
     * @ORM\Column(name="layout", type="text", nullable=true)
     */
    
    protected $layout;
    
    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;
    
    /**
     * Template type:
     *  - html
     *  - text
     *
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=20, nullable=true, options={"default" : "html"})
     */
    protected $type;
    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="created_at", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", name="updated_at", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedAt;
        
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
    public function setRound($round)
    {
        $this->round = $round;
        return $this;
    }
    public function getRound()
    {
        return $this->round;
    }
    public function setBackground($background)
    {
        $this->background = $background;
        return $this;
    }
    public function getBackground()
    {
        return $this->background;
    }
    public function setBorder($border)
    {
        $this->border = $border;
        return $this;
    }
    public function getBorder()
    {
        return $this->border;
    }
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }
    public function getHeight()
    {
        return $this->height;
    }
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    public function getContent()
    {
        return $this->content;
    }
    public function setCss($css)
    {
        $this->css = $css;
        return $this;
    }
    public function getCss()
    {
        return $this->css;
    }
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    public function getLayout()
    {
        return $this->layout;
    }
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
    public function getType()
    {
        return $this->type;
    }
    /**
     * Set publication created date/time
     *
     * @param \DateTime $created
     * @return Publication
     */
    public function setCreatedAt($created)
    {   
        if(!$this->getCreatedAt()){
          $this->createdAt = new \DateTime('now', new \DateTimeZone('UTC'));
        }

        return $this;
    }

    /**
     * Get publication created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Get publication last update date/time
     *
     * @param \DateTime $updated
     * @return Publication
     */
    public function setUpdatedAt($updated)
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        return $this;
    }

    /**
     * Get publication last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function offsetExists($offset) {
        
    }

    public function offsetGet($offset) {
        
    }

    public function offsetSet($offset, $value) {
        
    }

    public function offsetUnset($offset) {
        
    }

}
