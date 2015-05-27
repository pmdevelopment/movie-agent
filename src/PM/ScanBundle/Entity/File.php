<?php

namespace PM\ScanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use PM\ScanBundle\Model\FileModel;

/**
 * File
 *
 * @ORM\Table(name="scan_file")
 * @ORM\Entity
 */
class File extends FileModel
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
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime")
     */
    private $modified;

    /**
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="PM\ScanBundle\Entity\Folder", inversedBy="files")
     */
    private $folder;

    /**
     * @var int
     *
     * @ORM\Column(name="transcode_status",type="integer", options={"default":0})
     */
    private $transcodeStatus;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", options={"default":0})
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="extension",type="string",length=30)
     */
    private $extension;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setTranscodeStatus(self::TRANSCODE_NONE);
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
     * @return File
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
     * @return File
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
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return File
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param Folder $folder
     *
     * @return File
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return int
     */
    public function getTranscodeStatus()
    {
        return $this->transcodeStatus;
    }

    /**
     * @param int $transcodeStatus
     *
     * @return File
     */
    public function setTranscodeStatus($transcodeStatus)
    {
        $this->transcodeStatus = $transcodeStatus;

        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     *
     * @return File
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param string $extension
     *
     * @return File
     */
    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }


}
