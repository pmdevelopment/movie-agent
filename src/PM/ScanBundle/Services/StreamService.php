<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 31.05.15
 * Time: 10:39
 */

namespace PM\ScanBundle\Services;

/**
 * Class StreamService
 *
 * @package PM\ScanBundle\Services
 */
class StreamService
{

    /**
     * Get Streams
     *
     * @param string $filePath
     *
     * @return array
     */
    public function get($filePath)
    {
        $command = sprintf("ffprobe -v quiet -print_format json -show_streams '%s'", $filePath);
        $result = shell_exec($command);

        if (null === $result) {
            throw new \LogicException(sprintf("No result for %s", $command));
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
    public function getValid($streams, $codec, $language = null)
    {
        $result = array();

        foreach ($streams as $streamData) {
            $streamLanguage = $this->getLanguage($streamData);

            if (isset($streamData['codec_name'])) {
                if (($streamData['codec_type'] === $codec || $streamData['codec_name'] === $codec) && (null === $language || $streamLanguage === $language || null === $streamLanguage)) {
                    $result[] = $streamData;
                }
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
    public function getLanguage($stream)
    {
        if (false === isset($stream['tags']) || false === isset($stream['tags']['language'])) {
            return null;
        }

        return $stream['tags']['language'];
    }
}