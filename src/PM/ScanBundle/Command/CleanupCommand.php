<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 31.05.15
 * Time: 10:37
 */

namespace PM\ScanBundle\Command;


use Doctrine\ORM\QueryBuilder;
use PM\ScanBundle\Entity\File;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DebugCommand
 *
 * @package PM\ScanBundle\Command
 */
class CleanupCommand extends ContainerAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('pm:scan:cleanup')
            ->setDescription('Remove backups and missing files')
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'No Waiting time'
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
        /** @var QueryBuilder|File[] $files */
        $files = $this->getDoctrine()->getRepository("PMScanBundle:File")->createQueryBuilder('f');
        $files
            ->where("f.transcodeStatus = :status")
            ->setParameter('status', File::TRANSCODE_BACKUP);

        if (true !== $input->getOption('force')) {
            $modified = new \DateTime(sprintf("-%s days", $this->getContainer()->getParameter('transcode_backup_days')));
            $files
                ->andWhere("f.modified < :modified")
                ->setParameter('modified', $modified->format('Y-m-d H:i:s'));
        }

        $files = $files->getQuery()->getResult();

        foreach ($files as $file) {
            $output->writeln(sprintf("Removing <option=bold>%s</option=bold>", $file->getPath()));

            unlink($file->getPath());

            $this->getDoctrine()->getManager()->remove($file);
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