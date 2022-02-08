<?php

namespace App\Handlers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use App\Interfaces\ClientHandlerInterface;
use Illuminate\Support\Facades\Config;

class TelnaGuzzleApiHandler implements ClientHandlerInterface{

    private $data = [];
    private $route = "";
    private $client;

    public function __construct($route,array $data=[],object $client){
        $this->route    = $route;
        $this->data     = $data;
        $this->client   = $client;
    }

    public function post(){
        try{
            $response = $this->client->request('POST',$this->route,[
                "body" => json_encode($this->data)
            ]);
            return json_decode($response->getBody());
        } catch (ClientException $e) {
            Log::error('api-call-process',[
                'route'=> $this->route,
                'method'=> 'POST',
                'data'=>$this->data,
                'error' => $e->getMessage()
            ]);
           return false;
        }
    }

    public function put(){
        try{
            $response = $this->client->request('PUT',$this->route,[
                "body" => json_encode($this->data)
            ]);
            return json_decode($response->getBody());
        } catch (ClientException $e) {
            Log::error('api-call-process',[
                'route'=> $this->route,
                'method'=> 'PUT',
                'data'=>$this->data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function get(){
        try{
            $response = $this->client->request('GET',$this->route,
                ['query' => $this->data]
            );
            return json_decode($response->getBody());
        } catch (ClientException $e) {
            Log::error('api-call-process',[
                'route'=> $this->route,
                'method'=> 'GET',
                'data'=>$this->data,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function delete(){
        try{
            $response = $this->client->request('DELETE',$this->route,[
                "json" => $this->data
            ]);
            return json_decode($response->getBody());
        } catch (ClientException $e) {
            Log::error('api-call-process',[
                'route'=> $this->route,
                'method'=> 'DELETE',
                'data'=>$this->data,
                'error' => $e->getMessage()
            ]);
           return false;
        }
    }

}
