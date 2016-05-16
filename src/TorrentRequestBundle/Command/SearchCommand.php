<?php 

namespace TorrentRequestBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TorrentRequestBundle\Entity\Serie;

class SearchCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('torrent-request:search')
            ->setDescription('Lancer la recherche de torrent');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $t411_manager = $this->getContainer()->get('t411_manager');
        $transmission_manager = $this->getContainer()->get('transmission_manager');
        
        $serie_repository = $em->getRepository('TorrentRequestBundle:Serie');
        $movie_repository = $em->getRepository('TorrentRequestBundle:Movie');
        
        $all_videos_to_find = array();
        $series = $serie_repository->findByStatus(0);
        $movies = $movie_repository->findByStatus(0);
        
        $all_videos_to_find = array_merge($series,$movies);
        $torrents = array();
        
        foreach($all_videos_to_find as $video) {
            $type = $video->getType();
            $torrent = null;
            
            switch ($type){
                case 'movie':
                    // Find THE torrent info
                    $torrent = $t411_manager->searchMovie($video->getName());
                    if ($torrent) {
                        // Get Metainfo
                        $metainfo = $t411_manager->getDownloadTorrent($torrent["id"]);
                        //Launch download
                        $response = $transmission_manager->addTorrent($metainfo["content"]);
                        
                        if($response["result"] == "success"){
                            // Update Status
                            if(isset($response["arguments"]["torrent-added"]))
                            {
                                $video->setStatus(1);
                                $video->setTransmissionId($response["arguments"]["torrent-added"]["id"]);
                                $video->setOriginalFilename($response["arguments"]["torrent-added"]["name"]);
                            }
                            
                            if(isset($response["arguments"]["torrent-duplicate"]))
                            {
                                $video->setStatus(1);
                                $video->setTransmissionId($response["arguments"]["torrent-duplicate"]["id"]);
                                $video->setOriginalFilename($response["arguments"]["torrent-duplicate"]["name"]);
                            }
                            
                            echo date("d-m-Y H:i:s")." ".$video->getOriginalFilename()."\n";
                            
                            $em->merge($video);
                        }
                    }
                    break;
                case 'serie':
                    $torrent = $t411_manager->searchSerieBySeasonEpisode($video->getName(), $video->getSeason(), $video->getEpisode());
                    if ($torrent) {
                        // Get Metainfo
                        $metainfo = $t411_manager->getDownloadTorrent($torrent["id"]);
                        //Launch download
                        $response = $transmission_manager->addTorrent($metainfo["content"]);
                        
                        if($response["result"] == "success"){
                            // Update Status
                            
                            if(isset($response["arguments"]["torrent-added"]))
                            {
                                $video->setStatus(1);
                                $video->setTransmissionId($response["arguments"]["torrent-added"]["id"]);
                                $video->setOriginalFilename($response["arguments"]["torrent-added"]["name"]);
                            }
                            
                            if(isset($response["arguments"]["torrent-duplicate"]))
                            {
                                $video->setStatus(1);
                                $video->setTransmissionId($response["arguments"]["torrent-duplicate"]["id"]);
                                $video->setOriginalFilename($response["arguments"]["torrent-duplicate"]["name"]);
                            }
                            
                            $filename_lower = strtolower($video->getOriginalFilename());
                            $new_episode = new Serie();
                            
                            if(strstr($filename_lower,".final."))
                            {
                                $new_episode->setName($video->getName());
                                $num_season = $video->getSeason();
                                $num_season++;
                                $new_episode->setSeason($num_season);
                                $new_episode->setEpisode(1);
                            }else{
                                $new_episode->setName($video->getName());
                                $new_episode->setSeason($video->getSeason());
                                $num_episode = $video->getEpisode();
                                $num_episode++;
                                $new_episode->setEpisode($num_episode);
                            }
                            
                            echo date("d-m-Y H:i:s")." ".$video->getOriginalFilename()."\n";
                            
                            $em->merge($video);
                            $em->persist($new_episode);
                        }
                    }
                    break;
                default:
                    # code...
                    break;
            }
        }
        $em->flush();
    }
}