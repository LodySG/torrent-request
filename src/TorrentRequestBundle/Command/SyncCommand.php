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

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('torrent-request:sync')
            ->setDescription('Lancer la synchronysation du dossier de telechargement');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fs = new Filesystem();
        $files_path = $this->getContainer()->getParameter('files_path');
        $lock_path = $files_path['lock_path'];
        $lock_file = $lock_path.$this->getName();
        
        if(false == $fs->exists($lock_file))
        {
            $fs->touch($lock_file);
            try
            {    
                $fs->touch($lock_file);
                
                $em = $this->getContainer()->get('doctrine')->getEntityManager();
                
                $mount_directory = $files_path["mount_dir"];
                $destination_directory = $files_path["destination_dir"];
                
                $serie_repository = $em->getRepository('TorrentRequestBundle:Serie');
                $movie_repository = $em->getRepository('TorrentRequestBundle:Movie');
                $transmission_manager = $this->getContainer()->get('transmission_manager');
                
                $torrents = $transmission_manager->getTorrents();
                
                $all_videos_to_move = array();
                $series = $serie_repository->findByStatus(1);
                $movies = $movie_repository->findByStatus(1);
                
                $all_videos_to_move = array_merge($series,$movies);       
                
                foreach ($all_videos_to_move as $video) {
                    
                    $file_path = $mount_directory.$video->getOriginalFilename();
                    $dest_path = $destination_directory.$video->getOriginalFilename();
                    
                    if($fs->exists($file_path)){
                        $commandlinesync = 'rsync -rvz --remove-source-files "'.$file_path.'" '.$destination_directory;
                        $process = new Process($commandlinesync);
                        $process->setTimeout(7200);
                        $process->run(function ($type, $buffer){
                            if (Process::ERR === $type) {
                                echo date("d-m-Y H:i:s").' ERR > '.$buffer;
                            } else {
                                echo date("d-m-Y H:i:s").' '.$buffer;
                            }
                        });
                        if($process->isSuccessful()){
                            $commandlineremove = 'rm -rf "'.$file_path.'"';
                            $process = new Process($commandlineremove);
                            $process->setTimeout(3600);
                            
                            $video->setStatus(2);
                            $em->merge($video);
                            $em->flush();
                        }
                    }
                }
                $fs->remove($lock_file);
            }
            finally
            {
                $fs->remove($lock_file);
            }
              
        }
    }
}