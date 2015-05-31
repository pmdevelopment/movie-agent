<?php
/**
 * Created by PhpStorm.
 * User: sjoder
 * Date: 31.05.15
 * Time: 10:37
 */

namespace PM\ScanBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DebugCommand
 *
 * @package PM\ScanBundle\Command
 */
class DebugCommand extends ContainerAwareCommand
{
    /**
     * Configuration
     */
    protected function configure()
    {
        $this
            ->setName('pm:scan:debug')
            ->setDescription('Transcode files')
            ->addArgument(
                'file',
                null,
                InputOption::VALUE_REQUIRED,
                'Some debug information'
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
        $file = $input->getArgument("file");

        $output->writeln(print_r($this->getContainer()->get("pm_scan.services.stream_service")->get($file), true));
    }
}