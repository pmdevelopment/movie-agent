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
use PM\ScanBundle\Entity\File;
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
                'reset',
                null,
                InputOption::VALUE_NONE,
                'If set, existing files will removed from database'
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
        $start = microtime(true);

        if (true === $input->getOption('reset')) {
            $this->reset();
        }

        $folders = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->findBy(array("parent" => null));

        $this->scanFolders($folders, $output);

        $end = microtime(true);
        $output->writeln(sprintf("Scanned within %s seconds", round($end - $start, 2)));
    }

    /**
     * Scan Folders
     *
     * @param Folder[]        $folders
     * @param OutputInterface $output
     */
    private function scanFolders($folders, OutputInterface $output)
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


                $modified = new DateTime();
                $modified->setTimestamp($directory->getMTime());

                $folder->setModified($modified);


                $this->import($folder, $directory);

                $output->writeln(" <info>Done...</info>");

                $this->getDoctrine()->getManager()->persist($folder);
                $this->getDoctrine()->getManager()->flush();

                $this->scanFolders($folder->getChildren(), $output);
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
            $fullPath = sprintf("%s/%s", $dirContent->getPath(), $dirContent->getFilename());

            if (true === $dirContent->isFile()) {
                /**
                 * File
                 */
                $children = $this->getDoctrine()->getRepository("PMScanBundle:File")->findOneBy(array('path' => $fullPath));
                if (null === $children) {
                    $children = new File();
                    $children
                        ->setName($dirContent->getFilename())
                        ->setPath($fullPath)
                        ->setFolder($folder)
                        ->setSize($dirContent->getSize())
                        ->setExtension($dirContent->getExtension());
                }

                $modified = new DateTime();
                $modified->setTimestamp($dirContent->getMTime());

                $children->setModified($modified);

                $this->getDoctrine()->getManager()->persist($children);

            } else {
                /**
                 * Folder
                 */
                if (true === $dirContent->isDir() && false === $dirContent->isDot() && '.' !== substr($dirContent->getFilename(), 0, 1)) {

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
     * Reset
     */
    private function reset()
    {
        $folders = $this->getDoctrine()->getRepository("PMScanBundle:Folder")->findAll();

        foreach ($folders as $folder) {
            if (null !== $folder->getParent()) {
                $this->getDoctrine()->getManager()->remove($folder);
            }
        }

        $this->getDoctrine()->getManager()->flush();
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
}