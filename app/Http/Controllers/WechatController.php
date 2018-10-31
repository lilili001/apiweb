<?php
/*********************公众号开发********************************/

namespace App\Http\Controllers;

use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
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
            info('message');
            info($message);
            switch ($message['MsgType']) {
                case 'event':
                    return '收到事件消息';
                    break;
                case 'text':
                    $openId = $message['FromUserName'];
                    info('user info');
                    info( $userApi->get( $openId ) );
                    $user = $userApi->get( $openId );
                    //return '收到文字消息,你好:' . $user['nickname'] ;

                    $media = new Media('6N2Wu2qHBkGBqpruD0ZI9-wXvO835A0j636cH8AIK8M', 'mpnews');

                    $items = [
                        new NewsItem([
                            'title'       => "平语”近人——坚定不移贯彻新发展理念，习近平这些话意义深远",
                            'description' => "编前语】2015年10月29日，党的十八届五中全会公报发表，会议提出了创新、协调、绿色、开放、共享的新发展理念。对于这五大发展理念，习近平总书记在不同场合作出了深入阐释",
                            'url'         => "http://www.xinhuanet.com/politics/xxjxs/2018-10/29/c_1123630541.htm",
                            'image'       => "http://news.xinhuanet.com/politics/2016-09/18/129284700_14741694437441n.jpg",
                        ]),
                    ];
                    $news = new News($items);

                    //return '收到您的消息, 返回一条消息给你, 哈哈哈';
                    return new News($items);
                    break;
                case 'image':
                    $image = new Image("6N2Wu2qHBkGBqpruD0ZI9573QfWgtKw2B-rga6qYtH8");

                    return $image;
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
