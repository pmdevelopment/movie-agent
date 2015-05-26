<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 26.05.15
 * Time: 17:44
 */

namespace PM\ScanBundle\Command;

use DateTime;
use DirectoryIterator;
use PM\ScanBundle\Entity\Folder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class FilesCommand
 *
 * @package PM\ScanBundle\Command
 */
class FilesCommand extends ContainerAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('pm:scan:files')
            ->setDescription('Scan Filesystem')
            ->addOption(
                're-index',
                null,
                InputOption::VALUE_NONE,
                'If set, the last modified date will be ignored'
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
        $folders = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->findBy(array("parent" => null));

        $this->scanFolders($folders, $output, $input->getOption("re-index"));
    }

    /**
     * Scan Folders
     *
     * @param Folder[]        $folders
     * @param OutputInterface $output
     * @param bool            $forceReindex
     */
    private function scanFolders($folders, OutputInterface $output, $forceReindex = false)
    {
        foreach ($folders as $folder) {
            $output->writeln(sprintf("Working Folder: <options=bold>%s</options=bold>", $folder->getPath()));

            if (Folder::TYPE_IGNORE === $folder->getType()) {
                /**
                 * Ignore
                 */
                $output->writeln(" <info>Ignore...</info>");
            } elseif (false === is_dir($folder->getPath())) {
                /**
                 * Delete
                 */
                $output->writeln(" <warning>Removed....</warning>");

                $this->getDoctrine()->getManager()->remove($folder);
                $this->getDoctrine()->getManager()->flush();
            } else {
                /**
                 * Read existing files and folders
                 */
                $directory = new DirectoryIterator($folder->getPath());

                if (null === $folder->getModified() || $folder->getModified()->getTimestamp() !== $directory->getMTime() || true === $forceReindex) {
                    $output->writeln(" !!! Importing !!!");

                    $modified = new DateTime();
                    $modified->setTimestamp($directory->getMTime());

                    $folder->setModified($modified);


                    $this->import($folder, $directory);

                    $output->writeln(" <info>Done...</info>");

                    $this->getDoctrine()->getManager()->persist($folder);
                    $this->getDoctrine()->getManager()->flush();

                    $this->scanFolders($folder->getChildren(), $output, $forceReindex);
                }

                $output->writeln(" <info>Nothing to do...</info>");
            }
        }
    }

    /**
     * Import missing Files and Folder from File System
     *
     * @param Folder            $folder
     * @param DirectoryIterator $directory
     */
    private function import(Folder $folder, DirectoryIterator $directory)
    {
        foreach ($directory as $dirContent) {
            if (true === $dirContent->isFile()) {
                /**
                 * File
                 */
            } else {
                /**
                 * Folder
                 */
                if (true === $dirContent->isDir() && false === $dirContent->isDot() && '.' !== substr($dirContent->getFilename(), 0, 1)) {
                    $fullPath = sprintf("%s/%s", $dirContent->getPath(), $dirContent->getFilename());

                    $children = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->findOneBy(array('path' => $fullPath));
                    if (null === $children) {
                        $children = new Folder();
                        $children
                            ->setType($folder->getType())
                            ->setName($dirContent->getFilename())
                            ->setPath($fullPath)
                            ->setParent($folder);

                        $this->getDoctrine()->getManager()->persist($children);

                        $folder->getChildren()->add($children);
                    }

                }
            }
        }
    }

    /**
     * Get Doctrine
     *
     * @return \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private
    function getDoctrine()
    {
        return $this->getContainer()->get("doctrine");
    }
}