<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WexinController extends Controller
{
    //微信登陆api
    public function login()
    {
        $appid = config('wx.xiaochengxu.appid');
        $appsecret = request('wx.xiaochengxu.appsecret');
        $jscode = request('js_code');

        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appsecret&js_code=$jscode&grant_type=authorization_code';
        dd($url);
        $client = new Client();
        $response = $client->request('get',$url);
        $response = $response->getBody()->getContents();
        dd($response);
    }
}
