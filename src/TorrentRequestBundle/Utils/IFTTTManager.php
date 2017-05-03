<?php 

namespace TorrentRequestBundle\Utils;

use GuzzleHttp\Client;

class IFTTTManager
{
    private $url_api;
    private $client;
    private $key;
    private $container;
    
    public function __construct($ifttt, $ifttt_key, $container)
    {
        //dump($ifttt_key);
        
        $this->url_api = $ifttt["url"];
        $this->key = $ifttt[$ifttt_key];
        $this->container = $container;
        
        $this->client = new Client(array(
            'base_uri' => $this->url_api,
        ));
    }

    public function sendNotification($titre)
    {
        $response = $this->client->request('POST', $this->key, [
            "form_params" => [
                    'value1' => $titre
                ]
        ]);

        return $response;
    }
}