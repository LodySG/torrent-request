<?php

namespace TorrentRequestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use TorrentRequestBundle\Entity\Serie;
use TorrentRequestBundle\Form\SerieType;
use TorrentRequestBundle\Form\MovieType;
use TorrentRequestBundle\Entity\Movie;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="torrent_request_homepage")
     */
    public function indexAction(Request $request)
    {
        $ar_view = array();
        
        if($request->query->get('success'))
        {
            $ar_view['success'] = $request->query->get('success');
        }
           
        $em = $this->getDoctrine()->getManager();
        
        $serie_repository = $em->getRepository('TorrentRequestBundle:Serie');
        $movie_repository = $em->getRepository('TorrentRequestBundle:Movie');
        
        $series = $serie_repository->findAll();
        $movies = $movie_repository->findAll();
        
        usort($series, array($this, "cmp"));
        usort($movies, array($this, "cmp"));
        
        $series_tidy = array();
        
        foreach($series as $serie)
        {
            $key = $serie->getName();
            
            if(!array_key_exists($key,$series_tidy))
                $series_tidy[$key] = array();
                
            $series_tidy[$key][] = $serie;
        }
        
        
        $ar_view["series"] = $series_tidy;
        $ar_view["movies"] = $movies;
        
        //\Doctrine\Common\Util\Debug::dump($series);
        //die();
        
        // liste et liens formulaires
        //$transmission_manager = $this->get('transmission_manager');
        //$t411_manager = $this->get('t411_manager');
        
        //$file = $t411_manager->getDownloadTorrent(5462872);
        
        //$torrent = $transmission_manager->addTorrent($file);
        
        return $this->render('TorrentRequestBundle:Default:index.html.twig', $ar_view);
    }
    
    /**
     * @Route("/serie/{id}", name="torrent_request_serie", defaults={"id" = 0}, requirements={"id"="\d+"})
     */
    public function serieAction($id, Request $request)
    {
        $ar_view = array();
        $em = $this->getDoctrine()->getManager();
        $serie_repository = $em->getRepository('TorrentRequestBundle:Serie');
        
        $ar_view["themoviedb"]  = $this->getParameter("themoviedb");


        if($id != null)
        {
            $serie = $serie_repository->find($id);
        }
        else
        {
            $serie = new Serie();
        }
        
        $form = $this->createForm(SerieType::class, $serie);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // ... perform some action, such as saving the task to the database
            if($serie->getId() != null)
            {
                $em->merge($serie);
            }else
            {
                $em->persist($serie);
            }
            
            $em->flush();
            return $this->redirectToRoute('torrent_request_homepage');
        }   
        
        $ar_view["form"] = $form->createView();
        return $this->render('TorrentRequestBundle:Default:form.html.twig',$ar_view);
    }
    
    /**
     * @Route("/movie/{id}", name="torrent_request_movie", defaults={"id" = 0}, requirements={"id"="\d+"})
     */
    public function movieAction($id, Request $request)
    {
        $ar_view = array();
        $em = $this->getDoctrine()->getManager();
        $movie_repository = $em->getRepository('TorrentRequestBundle:Movie');
        
        $ar_view["themoviedb"]  = $this->getParameter("themoviedb");

        if($id != null)
        {
            $movie = $movie_repository->find($id);
        }
        else
        {
            $movie = new Movie();
        }
        
        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // ... perform some action, such as saving the task to the database
            if($movie->getId() != null)
            {
                $em->merge($movie);
            }else
            {
                $em->persist($movie);
            }
            $em->flush();
            return $this->redirectToRoute('torrent_request_homepage');
        }
        
        $ar_view["form"] = $form->createView();
        return $this->render('TorrentRequestBundle:Default:form.html.twig',$ar_view);
    }
    
    /**
     * @Route("/delete/{type}/{id}", name="torrent_request_delete", requirements={"id"="\d+", "type"="serie|movie"})
     */
    public function deleteAction($type, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $fs = new Filesystem();
        $transmission_manager = $this->get("transmission_manager");
        $files_path = $this->getParameter('files_path');
        $mount_dir = $files_path["mount_dir"];
        $dest_dir = $files_path["destination_dir"];
        
        switch($type)
        {
            case "movie":
                $movie_repository = $em->getRepository('TorrentRequestBundle:Movie');
                $movie = $movie_repository->find($id);
                
                if ($movie->getStatus() == 2) {
                    $fs->remove($dest_dir.$movie->getOriginalFilename());
                    //$transmission_manager->removeTorrent($movie->getTransmissionId());
                }elseif ($movie->getStatus() == 1) {
                    //$fs->remove($mount_dir.$movie->getOriginalFilename());
                    $transmission_manager->removeTorrent($movie->getTransmissionId());
                }
                
                $em->remove($movie);
                break;
            case "serie":
                $serie_repository = $em->getRepository('TorrentRequestBundle:Serie');
                $serie = $serie_repository->find($id);
                
                if ($serie->getStatus() == 2) {
                    $fs->remove($dest_dir.$serie->getOriginalFilename());
                    //$transmission_manager->removeTorrent($serie->getTransmissionId());
                }elseif ($serie->getStatus() == 1) {
                    //$fs->remove($mount_dir.$serie->getOriginalFilename());
                    $transmission_manager->removeTorrent($serie->getTransmissionId());
                }
                
                //$transmission_manager->removeTorrent($serie->getTransmissionId());
                $em->remove($serie);
                break;
            default:
                break;
        }
        
        $em->flush();
        
        return $this->redirectToRoute('torrent_request_homepage');
    }
    
    function cmp($a, $b)
    {
        return strcmp($a, $b);
    }
    
}
