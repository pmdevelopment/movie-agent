<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 27.05.15
 * Time: 21:33
 */

namespace PM\ScanBundle\Model;

use PM\ScanBundle\Entity\File;

/**
 * Class FileModel
 *
 * @package PM\ScanBundle\Model
 */
class FileModel
{
    const TRANSCODE_NONE = 0;
    const TRANSCODE_WORKING = 1;
    const TRANSCODE_IGNORED = 2;
    const TRANSCODE_DONE = 3;
    const TRANSCODE_BACKUP = 8;
    const TRANSCODE_FAILED = 9;

    /**
     * Get Known Extension
     *
     * @return array
     */
    public static function getKnownExtensions()
    {
        return array(
            'mkv',
            'mp4',
            'm4v',
            'mpg',
            'mp2',
            'mpeg',
            'mpe',
            'mpv',
            'webm',
            'flv',
            'vob',
            'avi',
            'wmv',
            '3gp',
            'mp3',
            'cbr',
            'm3a',
            'aac'
        );
    }

    /**
     * Get Path with new extension
     *
     * @param string $path
     * @param string $extension
     *
     * @return string
     */
    public static function getPathWithNewExtension($path, $extension)
    {
        $path = explode('.', $path);
        unset($path[count($path) - 1]);

        return sprintf("%s.%s", implode(".", $path), $extension);
    }

    /**
     * Is Known Extension?
     *
     * @return bool
     */
    public function isKnownExtension()
    {
        if (!$this instanceof File) {
            throw new \LogicException("Not a file");
        }

        if (true === in_array($this->getExtension(), self::getKnownExtensions())) {
            return true;
        }

        return false;
    }

}