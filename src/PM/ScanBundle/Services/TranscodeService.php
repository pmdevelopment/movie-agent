<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 27.05.15
 * Time: 22:30
 */

namespace PM\ScanBundle\Services;

use PM\ScanBundle\Entity\File;

/**
 * Class TranscodeService
 *
 * @package PM\ScanBundle\Services
 */
class TranscodeService
{

    /**
     * @var int
     */
    private $threads;

    /**
     * @var string
     */
    private $videoCodec;

    /**
     * @var string
     */
    private $videoAudioCodec;

    /**
     * @var string
     */
    private $videoAudioLanguage;

    /**
     * @var string
     */
    private $videoContainer;


    /**
     * @param int    $threads
     * @param string $videoCodec
     * @param string $videoAudioCodec
     * @param string $videoAudioLanguage
     * @param string $videoContainer
     */
    public function __construct($threads, $videoCodec, $videoAudioCodec, $videoAudioLanguage, $videoContainer)
    {
        $this->threads = $threads;

        $this->videoCodec = $videoCodec;
        $this->videoAudioCodec = $videoAudioCodec;
        $this->videoContainer = $videoContainer;
        $this->videoAudioLanguage = $videoAudioLanguage;
    }

    /**
     * @return string
     */
    public function getVideoCodec()
    {
        return $this->videoCodec;
    }

    /**
     * @return string
     */
    public function getVideoAudioCodec()
    {
        return $this->videoAudioCodec;
    }

    /**
     * @return string
     */
    public function getVideoAudioLanguage()
    {
        return $this->videoAudioLanguage;
    }

    /**
     * @return string
     */
    public function getVideoContainer()
    {
        return $this->videoContainer;
    }

    /**
     * @return int
     */
    public function getThreads()
    {
        return $this->threads;
    }


    /**
     * Get Streams
     *
     * @param string $filePath
     *
     * @return array
     */
    public function getStreams($filePath)
    {
        $result = shell_exec(sprintf("ffprobe -v quiet -print_format json -show_streams %s", escapeshellcmd($filePath)));

        if (null === $result) {
            throw new \LogicException("No result");
        }

        $result = json_decode($result, true);

        if (false === isset($result['streams']) || false === is_array($result['streams']) || 0 === count($result['streams'])) {
            throw new \LogicException("Result not readable");
        }

        return $result['streams'];
    }

    /**
     * Get Valid Streams
     *
     * @param array       $streams
     * @param string      $codec
     * @param null|string $language
     *
     * @return array
     */
    public function getValidStreams($streams, $codec, $language = null)
    {
        $result = array();

        foreach ($streams as $streamData) {
            $streamLanguage = $this->getLanguageFromStream($streamData);

            if ($streamData['codec_name'] === $codec && (null === $language || $streamLanguage === $language || null === $streamLanguage)) {
                $result[] = $streamData;
            }
        }

        return $result;
    }

    /**
     * Get Language from Stream
     *
     * @param array $stream
     *
     * @return null|string
     */
    private function getLanguageFromStream($stream)
    {
        if (false === isset($stream['tags']) || false === isset($stream['tags']['language'])) {
            return null;
        }

        return $stream['tags']['language'];
    }

    /**
     * Is Valid Container?
     *
     * @param string $fileExtension
     *
     * @return bool
     */
    public function isValidVideoContainer($fileExtension)
    {
        if ($fileExtension === $this->videoContainer) {
            return true;
        }

        return false;
    }

    /**
     * Transcode Video
     *
     * @param string     $filePath
     * @param null|array $streams
     *
     * @return bool
     */
    public function transcodeVideo($filePath, $streams = null)
    {
        if (null === $streams) {
            $streams = $this->getStreams($filePath);
        }

        $command = array(
            'ffmpeg',
            sprintf('-i %s', $filePath)
        );

        /**
         * Video
         */
        if (0 === count($this->getValidStreams($streams, $this->getVideoCodec()))) {
            $command[] = sprintf("-c:v %s", $this->getVideoCodecLibrary());
        } else {
            $command[] = "-c:v copy";
        }

        /**
         * Audio
         */
        $audioCodecs = $this->getValidStreams($streams, $this->getVideoAudioCodec(), $this->getVideoAudioLanguage());
        $audioCodecsCount = count($audioCodecs);
        if (0 === $audioCodecsCount) {
            $command[] = sprintf("-c:a %s", $this->getVideoAudioCodec());
        } else {
            $command[] = "-c:a copy";
        }

        /**
         * Todo: Mapping!
         */

        if (0 < $this->getThreads()) {
            $command[] = sprintf("-threads %s", $this->getThreads());
        }

        $command[] = File::getPathWithNewExtension($filePath, $this->getVideoContainer());

        dump($command);


        //ffmpeg -i tvp-americandad-s09e16-1080p.mkv -map 0:0 -map 0:1 -c:v copy -c:a copy  s10e16.mp4

        return true;
    }

    /**
     * Get Video Codec as Library for FFMPEG
     *
     * @return string
     */
    private function getVideoCodecLibrary()
    {
        if ('h264' === $this->getVideoCodec()) {
            return "libx264";
        }

        return $this->getVideoCodec();
    }
}