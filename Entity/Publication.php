<?php

namespace Ibnab\Bundle\PmanagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
/**
 * @ORM\Entity
 * @ORM\Table(name="ibnab_publication",
 * uniqueConstraints={@ORM\UniqueConstraint(name="UQ_NAME_Publication", columns={"name"})},
 * indexes={@ORM\Index(name="pmanager_publication_name_idx", columns={"name"}),
 *      @ORM\Index(name="pmanager_publication_created_idx", columns={"created_at"}),
 *      @ORM\Index(name="pmanager_publication_updated_idx",columns={"updated_at"})
 * }
 * )
 */
class Publication implements \ArrayAccess
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
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    protected $content;
    
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
    }
    public function setWidth($width)
    {
        $this->width = $width;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function setHeight($height)
    {
        $this->height = $height;
    }
    public function getHeight()
    {
        return $this->height;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
    public function getContent()
    {
        return $this->content;
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
    public function getProductSelect(){
    }
    public function setProductSelect($productSelect){
    }
    public function getProductTemplateSelect(){
    }
    public function setProductTemplateSelect($productTemplateSelect){
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
