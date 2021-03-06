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
use PM\ScanBundle\Entity\Log;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->setDescription('Transcode files')
            ->addOption(
                'continue',
                null,
                InputOption::VALUE_NONE,
                'If set, it will search for new file after transcoding'
            );


    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /**
         * Check disk space
         */
        $dirMedia = $this->getContainer()->getParameter("dir_media");
        $dirMediaFreeSpace = $this->getContainer()->getParameter("dir_media_minimum_free_space");
        if ($dirMediaFreeSpace > disk_free_space($dirMedia)) {
            $output->writeln("<error>Disk space below limit!</error>");

            return;
        }

        $files = $this->getDoctrine()->getRepository("PMScanBundle:File")->findBy(array('transcodeStatus' => File::TRANSCODE_NONE), array("modified" => "asc"), 1);
        if (1 === count($files)) {
            $file = $files[0];

            $output->writeln(sprintf("Checking file <option=bold>%s</option=bold>", $file->getPath()));

            if (false === $file->isKnownExtension()) {
                $output->writeln(" <error>Unknown extension</error>");

                $this->setTranscodeStatus($file, File::TRANSCODE_FAILED);
            } else {
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

                    $output->writeln(" <info>Done</info>");

                } catch (\Exception $e) {
                    $this->setTranscodeStatus($file, File::TRANSCODE_FAILED);

                    $output->writeln(sprintf(" <error>Failed</error>: %s", $e->getMessage()));
                }
            }


            /**
             * Check new files
             */
            if (true === $input->getOption('continue')) {
                $this->execute($input, $output);
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
        $ffprobe = $this->getContainer()->get("pm_scan.services.stream_service");

        $streams = $ffprobe->get($file->getPath());

        $videoStreams = $ffprobe->getValid($streams, $ffmpeg->getVideoCodec());
        $audioStreams = $ffprobe->getValid($streams, $ffmpeg->getVideoAudioCodec(), $ffmpeg->getVideoAudioLanguage());

        if (true === $ffmpeg->isValidVideoContainer($file->getExtension()) && 0 < count($videoStreams) && 0 < count($audioStreams)) {
            $this->setTranscodeStatus($file, File::TRANSCODE_IGNORED);
        } else {
            $result = $ffmpeg->transcodeVideo($file->getPath());

            /**
             * Move to Backup
             */
            $fileBackup = File::getPathWithNewExtension($file->getPath(), "backup");
            rename($file->getPath(), $fileBackup);

            $backup = clone $file;
            $backup
                ->setPath($fileBackup)
                ->setName(basename($fileBackup))
                ->setExtension('backup')
                ->setTranscodeStatus(File::TRANSCODE_BACKUP)
                ->setFolder($file->getFolder())
                ->setModified(new \DateTime());

            $file
                ->setPath($result['filePath'])
                ->setName(basename($result['filePath']))
                ->setExtension($ffmpeg->getVideoContainer())
                ->setTranscodeStatus(File::TRANSCODE_DONE);

            /**
             * Log
             */
            $log = new Log();
            $log
                ->setCommand($result['command'])
                ->setFile($file)
                ->setResult($result['output'])
                ->setTime(new \DateTime());

            $this->getDoctrine()->getManager()->persist($backup);
            $this->getDoctrine()->getManager()->persist($file);
            $this->getDoctrine()->getManager()->persist($log);

            $this->getDoctrine()->getManager()->flush();
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