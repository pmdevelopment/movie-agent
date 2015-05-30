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
     * @param string|null $codec
     * @param null|string $language
     *
     * @return array
     */
    public function getValidStreams($streams, $codec, $language = null)
    {
        $result = array();

        foreach ($streams as $streamData) {
            $streamLanguage = $this->getLanguageFromStream($streamData);

            if (($streamData['codec_type'] === $codec || $streamData['codec_name'] === $codec) && (null === $language || $streamLanguage === $language || null === $streamLanguage)) {
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
        $videoCodecs = $this->getValidStreams($streams, $this->getVideoCodec());
        if (0 === count($videoCodecs)) {
            $command[] = sprintf("-c:v %s", $this->getVideoCodecLibrary());

            $videoCodec = $this->getValidStreams($streams, 'video');
        } else {
            $command[] = "-c:v copy";
            $command[] = sprintf("-map 0:%s", $videoCodecs[0]['index']);
        }

        /**
         * Audio
         */
        $audioCodecs = $this->getValidStreams($streams, $this->getVideoAudioCodec(), $this->getVideoAudioLanguage());
        $audioCodecsCount = count($audioCodecs);
        if (0 === $audioCodecsCount) {
            $command[] = sprintf("-c:a %s", $this->getVideoAudioCodec());

            $audioCodec = $this->getValidStreams($streams, 'audio', $this->getVideoAudioLanguage());
            $command[] = sprintf("-map 0:%s", $audioCodec[0]['index']);
        } else {
            $command[] = "-c:a copy";
            $command[] = sprintf("-map 0:%s", $audioCodecs[0]['index']);
        }

        if (0 < $this->getThreads()) {
            $command[] = sprintf("-threads %s", $this->getThreads());
        }

        /**
         * Remove Metadata
         */
        $command[] = "-map_metadata -1";

        /**
         * Set Output file
         */
        $command[] = File::getPathWithNewExtension($filePath, $this->getVideoContainer());

        $command[] = "2>&1";

        $command = implode(" ", $command);
        $result = shell_exec($command);

        return array(
            'filePath' => $filePath,
            'output'   => $result,
            'command'  => $command
        );
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