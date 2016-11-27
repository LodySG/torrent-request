<?php 

namespace TorrentRequestBundle\Utils;

use GuzzleHttp\Client;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class T411Manager
{
    private $token;
    private $uid;
    private $auth_uri;
    private $user_info_uri;
    private $torrent_search_uri;
    private $torrent_download_uri;
    private $torrent_details_uri;
    private $url_api;
    private $username;
    private $password;
    private $client;
    private $container;
    
    public function __construct($param_t411, $container)
    {
        $this->url_api = $param_t411["url_api"];
        $this->auth_uri = $param_t411["auth_uri"];
        $this->user_info_uri = $param_t411["user_info_uri"];
        $this->torrent_search_uri = $param_t411["torrent_search_uri"];
        $this->torrent_download_uri = $param_t411["torrent_download_uri"];
        $this->torrent_details_uri = $param_t411["torrent_details_uri"];
        $this->categories_uri = $param_t411["categories_uri"];
        $this->terms_uri = $param_t411["terms_uri"];
        $this->username = $param_t411["username"];
        $this->password = $param_t411["password"];
        $this->container = $container;
        
        $this->client = new Client(array(
            'base_uri' => $this->url_api,
        ));
        
        $this->fetchToken();
    }  

    public function fetchToken()
    {   
        $response = $this->client->request('POST', $this->auth_uri,[
            'form_params' => [
                'username' => $this->username,
                'password' => $this->password
            ]
        ]);
        $ar_user = $this->getJson($response);

        //dump($ar_user);
        //die();
        
        if(isset($ar_user["token"]))
        {
            $this->token = $ar_user["token"];
            $this->uid = $ar_user["uid"];
        }
        else {
            throw new \Exception("Il y a un soucis avec T411 !!!", 1);
        }
        
    }

    public function getRatio()
    {
        $response = $this->client->request('GET', $this->user_info_uri.'/'.$this->uid, $this->getAuthorization());
        $ar_user_info = $this->getJson($response);
        $uploaded_bits = $ar_user_info["uploaded"];
        $downloaded_bits = $ar_user_info["downloaded"];
        $ratio = $uploaded_bits / $downloaded_bits;
        return $ratio;
    }

    public function getDetailsTorrent($id_torrent)
    {
        $response = $this->client->request('GET', $this->torrent_details_uri.'/'.$id_torrent, $this->getAuthorization());
        $ar_details = $this->getJson($response);
        return $ar_details;
    }

    public function getDownloadTorrent($id_torrent)
    {
        //$fs = new Filesystem(); 
        
        $response = $this->client->request('GET', $this->torrent_download_uri.'/'.$id_torrent, $this->getAuthorization());
        $file_stream = base64_encode($response->getBody()->getContents());
        
        $contentDispositionHeader = $response->getHeader('Content-Disposition');
        preg_match('/filename="(.*)"/', $contentDispositionHeader[0], $matches);
        
        $filename = str_replace(".torrent","",$matches[1]);
        
        return array("name" => $filename, "content" => $file_stream);
    }
 
    public function search($str_search)
    {
        $response = $this->client->request('GET', $this->torrent_search_uri.'/'.$str_search, $this->getAuthorization());
        $ar_search = $this->getJson($response);
        return $ar_search["torrents"];
    }
 
    public function searchSerieBySeasonEpisode($title,$season,$episode)
    {
        $serie_cat = 433;
        
        $serie_quality_str = "";
        $serie_quality_str .= "&term[7][]=11"; // TVrip [Rip SD (non HD) depuis Source Tv HD/SD]
        $serie_quality_str .= "&term[7][]=12"; // TVripHD 720 [Rip HD depuis Source Tv HD]
        $serie_quality_str .= "&term[7][]=1174"; // Web-Dl 1080
        $serie_quality_str .= "&term[7][]=1175"; // Web-Dl 720
        
        $serie_lang_str = "";
        $serie_lang_str .= "&term[51][]=1216"; // VOSTFR
        $serie_lang_str .= "&term[51][]=1212"; // Multi (Français inclus)
        
        $season_term_id;
        $episode_term_id;
        
        $terms = $this->getTermsByCategoryId($serie_cat);
        
        $season_terms = $terms[45]["terms"];
        $episode_terms = $terms[46]["terms"];
        
        ksort($season_terms);
        ksort($episode_terms);
        
        $i = 1;
        
        foreach($season_terms as $key_season => $season_label)
        {
           if($i == $season)
           {
               $season_term_id = $key_season;
               break;
           }
           $i++;
        }
        
        $i = 0;
        
        foreach($episode_terms as $key_episode => $episode_label)
        {
           if($i == $episode)
           {
               $episode_term_id = $key_episode;
               break;
           }
           $i++;
        }
        
        $str_search = $title.'?cat='.$serie_cat.'&term[45][]='.$season_term_id.'&term[46][]='.$episode_term_id.$serie_quality_str.$serie_lang_str.'&limit=30';
        
        $ar_series_result = $this->search($str_search);
        
        usort($ar_series_result, array($this, "compareSeeders"));
        
        if($ar_series_result)
            return $ar_series_result[0];
        else
            return null;
    }
    
    public function searchMovie($title)
    {
        $torrent = null;
        
        $film_cat = 631;
        $animation_cat = 455;
        $documentaire_cat = 634;
        $spectacle_cat = 635;
        
        $movie_quality_str = "";
        $movie_quality_str .= "&term[7][]=15"; // HDrip 720 [Rip HD depuis Bluray]
        $movie_quality_str .= "&term[7][]=16"; // HDrip 1080 [Rip HD depuis Bluray]
        $movie_quality_str .= "&term[7][]=1162"; // TVripHD 1080 [Rip HD depuis Source Tv HD]
        $movie_quality_str .= "&term[7][]=1208"; // HDlight 1080 [Rip HD-léger depuis source HD]
        $movie_quality_str .= "&term[7][]=1218"; // HDlight 720 [Rip HD-léger depuis source HD]
        
        $movie_lang_str = "";
        $movie_lang_str .= "&term[17][]=721";
        $movie_lang_str .= "&term[17][]=542";
        
        $str_search = $title.'?cat='.$film_cat.$movie_quality_str.'&limit=200';
        
        $ar_series_result = $this->search($str_search);
        usort($ar_series_result, array($this, "compareSeeders"));
        
        if($ar_series_result)
            return $ar_series_result[0];
        else
            return null;
    }
    
    public function getVideoCategories()
    {
        $response = $this->client->request('GET', $this->categories_uri, $this->getAuthorization());
        $ar_cats = $this->getJson($response);
        
        return $ar_cats[210]["cats"];
    }
    
    public function getTermsByCategoryId($cat_id)
    {
        $response = $this->client->request('GET', $this->terms_uri, $this->getAuthorization());
        $ar_terms = $this->getJson($response);
        
        return $ar_terms[$cat_id];
    }
    
//---------------------------------------------------------------------------
    
    public function getToken()
    {
        return $this->token;
    }
    
    public function getAuthorization()
    {
        return array('headers' => ['Authorization' => $this->token]);
    }
    
    public function getJson($response)
    {
        $body = $response->getBody();
        $stringBody = (string) $body;
        return json_decode($stringBody, true);
    }
    
    function compareSeeders($torrent_a, $torrent_b)
    {   
        return $torrent_a["seeders"] < $torrent_b["seeders"];
    }
}