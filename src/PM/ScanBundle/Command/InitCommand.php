<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 26.05.15
 * Time: 17:44
 */

namespace PM\ScanBundle\Command;

use PM\ScanBundle\Entity\Folder;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Class FilesCommand
 *
 * @package PM\ScanBundle\Command
 */
class InitCommand extends ContainerAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('pm:scan:init')
            ->setDescription('Init Filesystem');

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $folderPath = $this->getContainer()->getParameter("dir_media");

        if (true === empty($folderPath) || false === is_dir($folderPath)) {
            $output->writeln("<error>dir_media not valid</error>");
        }


        $doctrine = $this->getContainer()->get("doctrine");
        $helper = $this->getHelper('question');

        $directory = new \DirectoryIterator($folderPath);

        foreach ($directory as $dirContent) {
            if (true === $dirContent->isDir() && false === $dirContent->isDot() && '.' !== substr($dirContent->getFilename(), 0, 1)) {

                $question = new ChoiceQuestion(sprintf("What media is found in '%s'", $dirContent->getFilename()), Folder::getTypes(), Folder::TYPE_IGNORE);

                $type = $helper->ask($input, $output, $question);
                $fullPath = sprintf("%s/%s", $dirContent->getPath(), $dirContent->getFilename());

                $folder = $doctrine->getRepository("PMScanBundle:Folder")->findOneBy(array('path' => $fullPath));
                if (null === $folder) {
                    $folder = new Folder();
                }

                $folder
                    ->setType(Folder::getTypeId($type))
                    ->setName($dirContent->getFilename())
                    ->setPath($fullPath);

                $doctrine->getManager()->persist($folder);
            }
        }

        $doctrine->getManager()->flush();

    }

}