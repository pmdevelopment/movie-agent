<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 26.05.15
 * Time: 17:44
 */

namespace PM\ScanBundle\Command;

use PM\ScanBundle\Entity\File;
use PM\ScanBundle\Entity\Folder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class TranscodeCommand
 *
 * @package PM\ScanBundle\Command
 */
class TranscodeCommand extends ContainerAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('pm:scan:transcode')
            ->setDescription('Transcode files');

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $files = $this->getDoctrine()->getRepository("PMScanBundle:File")->findBy(array('transcodeStatus' => File::TRANSCODE_NONE), array("modified" => "asc"), 1);
        if (1 === count($files)) {
            $file = $files[0];

            if (false === $file->isKnownExtension()) {
                $output->writeln(" <error>Unknown extension</error>");

                $this->setTranscodeStatus($file, File::TRANSCODE_FAILED);
            } else {
                $output->writeln(sprintf("Checking file <option=bold>%s</option=bold>", $file->getPath()));

                /**
                 * Check if streams have to be converted
                 */
                $this->setTranscodeStatus($file, File::TRANSCODE_WORKING);

                try {
                    if (true === in_array($file->getFolder()->getType(), Folder::getTypesVideo())) {
                        $this->transcodeVideo($file);
                    } elseif (true === in_array($file->getFolder()->getType(), Folder::getTypesAudio())) {
                        $this->transcodeAudio($file);
                    } else {
                        throw new \LogicException("Unknown Type");
                    }

                } catch (\Exception $e) {
                    $this->setTranscodeStatus($file, File::TRANSCODE_FAILED);

                    $output->writeln(" <error>Failed</error>");
                }
            }
        } else {
            $output->writeln("<info>Nothing to do...</info>");
        }
    }


    /**
     * Get Codec Status
     *
     * @param File $file
     *
     * @return bool
     * @throws \Exception
     */
    private function transcodeVideo($file)
    {
        $ffmpeg = $this->getContainer()->get("pm_scan.services.transcode_service");

        $streams = $ffmpeg->getStreams($file->getPath());

        $videoStreams = $ffmpeg->getValidStreams($streams, $ffmpeg->getVideoCodec());
        $audioStreams = $ffmpeg->getValidStreams($streams, $ffmpeg->getVideoAudioCodec(), $ffmpeg->getVideoAudioLanguage());

        if (true === $ffmpeg->isValidVideoContainer($file->getExtension()) && 0 < count($videoStreams) && 0 < count($audioStreams)) {
            $this->setTranscodeStatus($file, File::TRANSCODE_IGNORED);
        } else {
            $result = $ffmpeg->transcodeVideo($file->getPath());

            if (true === $result) {
                /*
                 * TODO: Save
                 */
                $this->setTranscodeStatus($file, File::TRANSCODE_NONE);
                //$this->setTranscodeStatus($file, File::TRANSCODE_DONE);
            } else {
                throw new \Exception("Transcoding failed");
            }
        }
    }

    /**
     * Get Codec Status
     *
     * @param File $file
     *
     * @return bool
     */
    private function transcodeAudio($file)
    {

    }

    /**
     * Get Doctrine
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private function getDoctrine()
    {
        return $this->getContainer()->get("doctrine");
    }

    /**
     * Set Transcode Status
     *
     * @param File $file
     * @param int  $status
     */
    private function setTranscodeStatus($file, $status)
    {
        $file->setTranscodeStatus($status);

        $this->getDoctrine()->getManager()->persist($file);
        $this->getDoctrine()->getManager()->flush();
    }


}