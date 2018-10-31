<?php
/*********************公众号开发********************************/

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
 
define("TOKEN", "weixin");
define('APPID', 'wxf6e6082a60d63506');
define('APPSECRET', 'f0935021fa50e3c3d5147ed4afe8b551');

class WechatController extends Controller
{
    public function valid()//验证接口的方法
    {
        $echoStr = $_GET["echostr"];//从微信用户端获取一个随机字符赋予变量echostr
        info('echoStr:' . $echoStr);
        //valid signature , option访问地61行的checkSignature签名验证方法，如果签名一致，输出变量echostr，完整验证配置接口的操作
        if ($this->checkSignature()) {
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
        $nonce = $_GET["nonce"];    //从用户端获取随机数赋予变量nonce

        $token = TOKEN;//将常量token赋予变量token
        $tmpArr = array($token, $timestamp, $nonce);//简历数组变量tmpArr
        sort($tmpArr, SORT_STRING);//新建排序
        $tmpStr = implode($tmpArr);//字典排序
        $tmpStr = sha1($tmpStr);//shal加密
        //tmpStr与signature值相同，返回真，否则返回假
        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    private function getAccessToken()
    {
        $appid = APPID;
        $secret = APPSECRET;
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        info('function:getAccessToken');
        $data = json_decode($this->get_php_file(public_path('access_token.php')));
        info('\n data:' . json_encode($data));
        if ($data->expire_time < time()) {
            info('\n 222');
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
            info('\n url:' . $url);

            $res = $this->httpGet($url);
            info('\n access_token api res:' . $res);
            $res = json_decode($res);

            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file("access_token.php", json_encode($data));
            }
        } else {
            info('333');
            $access_token = $data->access_token;
            info('333 access_token:' . $access_token);
        }
        return $access_token;
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function httpPost($data, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }

    public function serve()
    {
        //step1: 用户发消息或关注 回复一条消息给用户
        $wechat = app('wechat.official_account');
        //获取用户
        $userApi = $wechat->user;

        $wechat->server->push(function ($message) use($userApi) {
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    $openId = $message['FromUserName'];
                    info('user info');
                    info( $userApi->get( $openId ) );
                    $user = $userApi->get( $openId );
                    return '收到文字消息,你好:' . $user['nickname'] ;
                    break;
                case 'image':
                    return '收到图片消息';
                    break;
                case 'voice':
                    return '收到语音消息';
                    break;
                case 'video':
                    return '收到视频消息';
                    break;
                case 'location':
                    return '收到坐标消息';
                    break;
                case 'link':
                    return '收到链接消息';
                    break;
                case 'file':
                    return '收到文件消息';
                // ... 其它消息
                default:
                    return '收到其它消息';
                    break;
            }
        });


       /* $wechat->server->push(function($message){
            return "欢迎关注 overtrue！";
        });*/

        return $wechat->server->serve();
    }

}
