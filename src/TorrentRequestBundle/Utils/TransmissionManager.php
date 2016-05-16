<?php 

namespace TorrentRequestBundle\Utils;

use GuzzleHttp\Client;

class TransmissionManager
{
    
    private $transmission;
    private $api_uri;
    private $container;
    private $client;
    private $xt_id;
    private $auth;
    private $username;
    private $password;
    private $last_tag;
    
    public function __construct($param_transmission,$container)
    {
        $this->container = $container;
        $this->api_uri = $param_transmission["api_uri"];
        $this->client = new Client(array(
            'base_uri' => $param_transmission["url_api"],
        ));
        $this->username = $param_transmission["username"];
        $this->password = $param_transmission["password"];
        
        $this->getAuth();
    }
    
    public function getTorrents()
    {
        $request_ar = array();
        $request_ar["method"] = "torrent-get";
        $request_ar["arguments"] = array("fields" => ["id","name","percentDone"]);
        
        $data = json_encode($request_ar);
        
        $response = $this->client->request('POST', $this->api_uri, [
            "headers" => $this->getHeaders(),
            "body" => $data
        ]);
        
        return json_decode($response->getBody(),TRUE);
    }
    
    public function addTorrent($metainfo)
    {
        $request_ar = array();
        $request_ar["method"] = "torrent-add";
        $request_ar["arguments"] = array("metainfo" => $metainfo);
        
        $data = json_encode($request_ar);
        
        $response = $this->client->request('POST', $this->api_uri, [
            "headers" => $this->getHeaders(),
            "body" => $data
        ]);
        
        return json_decode($response->getBody(),TRUE);
    }
    
    public function removeTorrent($id)
    {
        $request_ar = array();
        $request_ar["method"] = "torrent-remove";
        $request_ar["arguments"] = array("ids" => array($id), "delete-local-data" => "true");
        
        $data = json_encode($request_ar);
        
        $response = $this->client->request('POST', $this->api_uri, [
            "headers" => $this->getHeaders(),
            "body" => $data
        ]);
        
        return json_decode($response->getBody(),TRUE);
    }
    
    public function getAuth()
    {
        try{
            $response = $this->client->request('GET', $this->api_uri, ['auth' => [$this->username, $this->password]]); 
            $this->auth = $response->getHeaderLine("Authorization");
            $this->xt_id = $response->getHeaderLine("X-Transmission-Session-Id");
        }catch(\GuzzleHttp\Exception\ClientException $e){
            $response = $e->getResponse();
            $request = $e->getRequest();
            $this->auth = $request->getHeaderLine("Authorization");
            $this->xt_id = $response->getHeaderLine("X-Transmission-Session-Id");
        }
        
        return $this->xt_id;
    }
    
    public function getHeaders()
    {
        return array("X-Transmission-Session-Id" => $this->xt_id, "authorization" => $this->auth);
    }
    
    /*
    "rpc-authentication-required": true,
    "rpc-bind-address": "0.0.0.0",
    "rpc-enabled": true,
    "rpc-password": "{c51066afceeee5de46dd7d3fae6c48ec9bb3008e/zaWUFmG",
    "rpc-port": 9494,
    "rpc-url": "/transmission/",
    "rpc-username": "lodydody",
    "rpc-whitelist": "127.0.0.1",
    */
}