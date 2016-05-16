<?php 

namespace TorrentRequestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use TorrentRequestBundle\Utils\T411Manager;

class TestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('torrent-request:test')
            ->setDescription('Test');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $transmission_manager = $this->getContainer()->get('transmission_manager');
        
        $transmission_manager->getAuth();
    }
}