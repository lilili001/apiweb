<?php
/***********************小程序开发*****************************************/
namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

define("TOKEN", env('WECHAT_OFFICIAL_ACCOUNT_TOKEN'));
define('APPID', env('WECHAT_OFFICIAL_ACCOUNT_APPID'));
define('APPSECRET', env('WECHAT_OFFICIAL_ACCOUNT_SECRET'));

class WxCbController extends Controller
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
    //公有的responseMsg的方法，是我们回复微信的关键。以后的章节修改代码就是修改这个。
    public function responseMsg(Request $request)
    {
        info('111');
        //info( json_encode($request->all()));
        //get post data, May be due to the different environments
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");

        //$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//将用户端放松的数据保存到变量postStr中，由于微信端发送的都是xml，
        //使用postStr无法解析，故使用$GLOBALS["HTTP_RAW_POST_DATA"]获取
        //info('postStr:'.$postStr);
        //extract post data如果用户端数据不为空，执行30-55否则56-58
        if (!empty($postStr)){
            //将postStr变量进行解析并赋予变量postObj。simplexml_load_string（）函数是php中一个解析XML的函数，SimpleXMLElement为新对象的类，
            //LIBXML_NOCDATA表示将CDATA设置为文本节点，CDATA标签中的文本XML不进行解析
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            info('postObj:'.json_encode($postObj));
            //get openid,最好使用trim方法去除空格
            $openid = trim($postObj->FromUserName);//用户的openid, 将微信用户端的用户名赋予变量FromUserName
            $content = trim($postObj->Content);//获取用户发送的内容

            //  获取acsess_token 这个每天最多请求微信服务器2000次, 过期时间2小时, 所以开发的时候常保存到数据库或文件,
            //  每次请求后台的时候查看acsess_token是否过期, 如果过期则要重新调用微信api获取acsess_token

            $accessToken =  $this->getAccessToken() ;
            info('\n $accessToken 01:'. $accessToken);

            //客服接口-发消息
            $postMsgApi = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$accessToken}";
            info('\n $postMsgApi:'. ($postMsgApi));
            $data = [
                "touser" => $openid,
                "msgtype"=> "text",
                "text"=> [
                    "content"=>"Hello World"
                ]
            ];

            if($content == '价格'){
                $data['text']['content'] = urlencode('价格是100w');
            }else{
                $data['text']['content'] = urlencode('你好,欢迎来到miyaye的部落');
            }

            $str = $this->httpPost(urldecode(json_encode($data)),$postMsgApi);

            info('\n str:'. json_encode($str));

            return $str;

        }else {
            return "回复为空，无意义，调试用";//回复为空，无意义，调试用
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

    
    //微信登陆api
    public function login()
    {
        $appid = config('wx.xiaochengxu.appid');
        $appsecret = config('wx.xiaochengxu.appsecret');
        $jscode = request('js_code');
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appid}&secret={$appsecret}&js_code=$jscode&grant_type=authorization_code";

        $client = new Client();
        $response = $client->request('get',$url);
        $response = $response->getBody()->getContents();
        return response()->json($response);
    }

    public function sendTemplateMsg(Request $request)
    {
        $openid = $request->get('openId');
        $formid = $request->get('formId');
        $name = $request->get('name');
        $address = $request->get('address');
        $price = $request->get('price');
        $date = $request->get('date');
        $templateID = 'I8Dj59WV-cqJKRMDXhox0ST6-KRg0n3bZa24qAcoM_g';//从公众号后台获取

        $data = <<<END
{
  "touser": "{$openid}",
  "template_id": "{$templateID}",
  "page": "index",
  "form_id": "{$formid}",
  "data": {
      "keyword1": {
          "value": "{$address}"
      },
      "keyword2": {
          "value": "{$date}"
      },
      "keyword3": {
          "value": "{$name}"
      } ,
      "keyword4": {
          "value": "{$price}"
      }
  },
  "emphasis_keyword": "keyword3.DATA"
}
END;

        $access_token = $this->getAccessToken();
        $sendTemplateMessageApi = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$access_token}";

        //$res = $this->httpPost($data,$sendTemplateMessageApi);

        $client = new Client();

        $res = $client->request('POST', $sendTemplateMessageApi, [
            'json' => json_decode($data,true)
        ]
         );
        return $res;
    }
    
    private function getAccessToken() {
        $appid = APPID;
        $secret = APPSECRET;
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        info('function:getAccessToken');
        $data = json_decode($this->get_php_file(public_path('access_token.php')));
        info('\n data:'.json_encode($data));
        if ($data->expire_time < time()) {
            info('\n 222');
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
            info('\n url:'.$url);

            $res = $this->httpGet($url);
            info( '\n access_token api res:' . $res );
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
            info('333 access_token:'.$access_token);
        }
        return $access_token;
    }

    private function httpGet($url) {
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

    private function httpPost($data,$url) {
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

    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }
    private function set_php_file($filename, $content) {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
}
