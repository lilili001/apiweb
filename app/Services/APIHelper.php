<?php
/**
 * Created by PhpStorm.
 * User: yixin
 * Date: 2018/11/1
 * Time: 9:42
 */

namespace App\Services;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class APIHelper
{

    protected $client;
    /**
     * APIHelper constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    public function get($apiStr)
    {
        try{
            $request = $this->client->get($apiStr);
            //或者如下
            /*$this->client->request('GET',$apiStr,[
                'query' => $data
            ]);*/

            $response = $request->getBody()->getContents();
        }catch (RequestException $e){
            //echo $e->getRequest();
            if ($e->hasResponse()) {
                return $e->getResponse();
            }
        }

        return $response;
    }
    
    public function post($apiStr ,$body )
    {
        try{

            $response = $this->client->request('POST',$apiStr,[
                'form_params' => $body
            ]);
            $response = $response->getBody()->getContents();

        }catch (RequestException $e){

            if ($e->hasResponse()) {
                return $e->getResponse();
            }

        }

        return $response;
    }
}