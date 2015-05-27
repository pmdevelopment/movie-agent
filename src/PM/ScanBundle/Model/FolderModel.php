<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 26.05.15
 * Time: 18:22
 */

namespace PM\ScanBundle\Model;

use PM\ScanBundle\Entity\Folder;

/**
 * Class FolderModel
 *
 * @package PM\ScanBundle\Model
 */
class FolderModel
{

    const TYPE_IGNORE = 0;
    const TYPE_VIDEO_MOVIE = 1;
    const TYPE_VIDEO_SERIES = 2;
    const TYPE_AUDIO_MUSIC = 3;
    const TYPE_AUDIO_BOOK = 4;
    const TYPE_BOOK_COMIC = 5;

    /**
     * Get Types
     *
     * @return array
     */
    public static function getTypes()
    {
        return array(
            self::TYPE_IGNORE       => 'Ignore',
            self::TYPE_VIDEO_MOVIE  => 'Movies',
            self::TYPE_VIDEO_SERIES => 'Series',
            self::TYPE_AUDIO_MUSIC  => 'Music',
            self::TYPE_AUDIO_BOOK   => 'Audio books',
            self::TYPE_BOOK_COMIC   => 'Comic books'
        );
    }

    /**
     * Get Types Audio
     *
     * @return array
     */
    public static function getTypesAudio()
    {
        return array(
            self::TYPE_AUDIO_BOOK,
            self::TYPE_AUDIO_MUSIC
        );
    }

    /**
     * Get Types Video
     *
     * @return array
     */
    public static function getTypesVideo()
    {
        return array(
            self::TYPE_VIDEO_MOVIE,
            self::TYPE_VIDEO_SERIES
        );
    }

    /**
     * Get Type Id
     *
     * @param string $name
     *
     * @return int
     */
    public static function  getTypeId($name)
    {
        foreach (self::getTypes() as $typeId => $type) {
            if ($type === $name) {
                return $typeId;
            }
        }

        return self::TYPE_IGNORE;
    }

    /**
     * Get Type Text
     *
     * @return string
     */
    public function getTypeText()
    {
        if (!$this instanceof Folder) {
            throw new \LogicException("Not a folder");
        }

        return self::getTypes()[$this->getType()];
    }

    /**
     * Get all Parents
     *
     * @return array|Folder[]
     */
    public function getParents()
    {
        if (!$this instanceof Folder) {
            throw new \LogicException("Not a folder");
        }

        $parents = array($this);

        if (null !== $this->getParent()) {
            $parents = array_merge($parents, $this->getParent()->getParents());
        }

        return $parents;
    }

}