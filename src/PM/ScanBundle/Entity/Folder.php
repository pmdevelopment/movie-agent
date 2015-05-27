<?php

namespace PM\ScanBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use PM\ScanBundle\Model\FolderModel;

/**
 * Folder
 *
 * @ORM\Table(name="scan_folder")
 * @ORM\Entity
 */
class Folder extends FolderModel
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=true)
     */
    private $modified;

    /**
     * @var Folder|null
     *
     * @ORM\ManyToOne(targetEntity="PM\ScanBundle\Entity\Folder", inversedBy="children")
     */
    private $parent;

    /**
     * @var Folder[]|Collection
     *
     * @ORM\OneToMany(targetEntity="PM\ScanBundle\Entity\Folder", mappedBy="parent")
     */
    private $children;

    /**
     * @var File[]|Collection
     *
     * @ORM\OneToMany(targetEntity="PM\ScanBundle\Entity\File", mappedBy="folder")
     */
    private $files;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setChildren(new ArrayCollection());
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
     *
     * @return Folder
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
     * Set path
     *
     * @param string $path
     *
     * @return Folder
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Folder
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return null|Folder
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param null|Folder $parent
     *
     * @return Folder
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|Folder[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param Collection|Folder[] $children
     *
     * @return Folder
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param DateTime $modified
     *
     * @return Folder
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * @return Collection|File[]
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param Collection|File[] $files
     *
     * @return Folder
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

}
