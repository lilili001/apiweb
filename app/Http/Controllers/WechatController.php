<?php
/*********************公众号开发********************************/

namespace App\Http\Controllers;

use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Voice;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WechatController extends Controller
{
    protected $wechat;
    /**
     * WechatController constructor.
     */
    public function __construct()
    {
        $this->wechat = app('wechat.official_account');
    }

    public function serve()
    {
        info( date('y-m-d h:i:s',time()) );
        $postStr = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input");
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        info('postObj:'.json_encode($postObj));

        //step1: 用户发消息或关注 回复一条消息给用户
        $wechat = app('wechat.official_account');
        //获取用户
        $userApi = $wechat->user;

        $wechat->server->push(function ($message) use($userApi , $wechat) {
            info('message');
            info($message);
            switch ($message['MsgType']) {
                case 'event':

                    if( $message['Event'] == 'subscribe' ){
                        try{
                            $res = $wechat->template_message->sendSubscription([
                                'touser' => $message['FromUserName'],
                                'template_id' => 'sW3tC35gfCfHYk2SH-0FxcvTnSrA1YlBh4QbLPckd3U',
                                'url' => 'https://easywechat.org',
                                'scene' => 1000,
                                'data' => [
                                    'name' => '东瀛',
                                    'address' => '南极',
                                ]
                            ]);

                            info($res);

                        }catch (\Exception $e){
                            info($e->getMessage());
                        }

                    }

                    return '收到事件消息,并推送一条事件给你,你好 欢迎关注。。。';
                    break;
                case 'text':
                    $openId = $message['FromUserName'];//用户的openId
                    info('user info');
                    info( $userApi->get( $openId ) );
                    $user = $userApi->get( $openId ); //拿到用户信息
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
                    $voice = new Volice(['mediaId'=>'6N2Wu2qHBkGBqpruD0ZI96waWxwVpho7CkmkAtGmdvk']);
                    //作为客服给用户发送音频消息
                    return $wechat->staff->message($voice)->to($message['FromUserName'])->send();
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
