<?php
/*********************公众号开发********************************/
namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

define("TOKEN", "weixin");
define('APPID','wxf6e6082a60d63506');
define('APPSECRET','f0935021fa50e3c3d5147ed4afe8b551');

class WechatController extends Controller
{
    public function valid()//验证接口的方法
    {
        $echoStr = $_GET["echostr"];//从微信用户端获取一个随机字符赋予变量echostr
        info('echoStr:'.$echoStr);
        //valid signature , option访问地61行的checkSignature签名验证方法，如果签名一致，输出变量echostr，完整验证配置接口的操作
        if($this->checkSignature()){
            info('checked:');
            echo $echoStr;
            exit;
        }
    }
    //签名验证程序	，checkSignature被18行调用。官方加密、校验流程：将token，timestamp，nonce这三个参数进行字典序排序，然后将这三个参数字符串拼接成一个字符串惊喜shal加密，开发者获得加密后的字符串可以与signature对比，表示该请求来源于微信。
    private function checkSignature()
    {
        $signature = $_GET["signature"];//从用户端获取签名赋予变量signature
        $timestamp = $_GET["timestamp"];//从用户端获取时间戳赋予变量timestamp
        $nonce = $_GET["nonce"];	//从用户端获取随机数赋予变量nonce

        $token = TOKEN;//将常量token赋予变量token
        $tmpArr = array($token, $timestamp, $nonce);//简历数组变量tmpArr
        sort($tmpArr, SORT_STRING);//新建排序
        $tmpStr = implode( $tmpArr );//字典排序
        $tmpStr = sha1( $tmpStr );//shal加密
        //tmpStr与signature值相同，返回真，否则返回假
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}
