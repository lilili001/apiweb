<?php

namespace App\Http\Controllers;

use EasyWeChat\Kernel\Messages\Article;
use EasyWeChat\Kernel\Messages\Image;
use EasyWeChat\Kernel\Messages\Media;
use EasyWeChat\Kernel\Messages\Message;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MaterialController extends Controller
{
    protected $wechat;

    /**
     * MaterialController constructor.
     * @param $wechat
     */
    public function __construct()
    {
        $this->wechat = app('wechat.official_account');
    }

    //上传图片素材 注意个人订阅号可能没有权限
    public function image()
    {
       return $this->wechat->material->uploadImage(public_path('images/fx.jpg') );

       //返回如下
        //{"media_id":"6N2Wu2qHBkGBqpruD0ZI9wBW1mCBYC8jSvbB6e4k_18","url":"http:\/\/mmbiz.qpic.cn\/mmbiz_jpg\/5qQscl5o9sm1GsKALBQyEr5RcX2KuvwOcbTrfqSXia2XjGKrjyAiaDKVYWI1qTYvJssV5PxA3S7ibxoJ6TOr9ZlTA\/0?wx_fmt=jpeg"}
    }

    public function uploadNews()
    {
        // 上传单篇图文
        $article = new Article([
            'title' => '平语”近人——坚定不移贯彻新发展理念，习近平这些话意义深远',
            "content" => '创新
　　坚持创新发展，是我们分析近代以来世界发展历程特别是总结我国改革开放成功实践得出的结论，是我们应对发展环境变化、增强发展动力、把握发展主动权，更好引领新常态的根本之策。
　　——2016年1月18日，在省部级主要领导干部学习贯彻十八届五中全会精神专题研讨班开班式上的讲话
　　中华民族奋斗的基点是自力更生，攀登世界科技高峰的必由之路是自主创新，所有企业都要朝这个方向努力奋斗。实现中华民族伟大复兴宏伟目标时不我待，要有志气和骨气加快增强自主创新能力和实力，努力实现关键核心技术自主可控，把创新发展主动权牢牢掌握在自己手中。',
            'thumb_media_id' => '6N2Wu2qHBkGBqpruD0ZI9wBW1mCBYC8jSvbB6e4k_18',
            'show_cover' => 1,
            'digest' => '【编前语】2015年10月29日，党的十八届五中全会公报发表',
            'source_url' => 'https://www.easywechat.com',
            'author' => 'miyaye', // 作者
        ]);
        return $this->wechat->material->uploadArticle($article);
    }

    /*******************获取所有的图片素材列表**************************************/
    /*******************经过上面的 image方法上传完图片后 此处在这个列表中可以看到********************************/
    public function materialList()
    {
        $list = $this->wechat->material->list('images');
        dd($list);
    }

    /*******************获取某个素材***************************/
    public function material($mediaid)
    {
        $stream = $this->wechat->material->get($mediaid);

        dd($stream);
        //file_put_contents('a.jpg',$stream);
        /*或者如下*/
       /* if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            // 以内容 md5 为文件名
            $stream->save(public_path('sq.jpg'));

            // 自定义文件名，不需要带后缀
            //$stream->saveAs('保存目录', '文件名');
        }*/


    }

    public function test()
    {
        $accessToken = $this->wechat->access_token;
        $token = $accessToken->getToken()['access_token'];
    }


    /***************消息群发***********************/
    public function message()
    {
        $wechat = app('wechat.official_account');
        $items = [
            new NewsItem([
                'title'       => "平语”近人——坚定不移贯彻新发展理念，习近平这些话意义深远",
                'description' => "编前语】2015年10月29日，党的十八届五中全会公报发表，会议提出了创新、协调、绿色、开放、共享的新发展理念。对于这五大发展理念，习近平总书记在不同场合作出了深入阐释",
                'url'         => "http://www.xinhuanet.com/politics/xxjxs/2018-10/29/c_1123630541.htm",
                'image'       => "http://news.xinhuanet.com/politics/2016-09/18/129284700_14741694437441n.jpg",
            ])
        ];
        $news = new News($items);

        $text = new Text('您好！overtrue。');
        //$image = new Image("6N2Wu2qHBkGBqpruD0ZI9573QfWgtKw2B-rga6qYtH8");

        //return $wechat->broadcasting->sendMessage( $news );

        //测试号不支持消息类型
        return $wechat->broadcasting->sendNews('6N2Wu2qHBkGBqpruD0ZI9-wXvO835A0j636cH8AIK8M');
    }

    public function menulist()
    {
         $list = $this->wechat->menu->list();

        $current = $this->wechat->menu->current();
        dd($current);
    }

    public function create_menu()
    {
        $buttons = [
            [
                "type" => "click",
                "name" => "今日歌曲",
                "key"  => "V1001_TODAY_MUSIC"
            ],
            [
                "name"       => "菜单",
                "sub_button" => [
                    [
                        "type" => "view",
                        "name" => "搜索",
                        "url"  => "http://www.soso.com/"
                    ],
                    [
                        "type" => "view",
                        "name" => "视频",
                        "url"  => "http://v.qq.com/"
                    ],
                    [
                        "type" => "click",
                        "name" => "赞一下我们",
                        "key" => "V1001_GOOD"
                    ],
                ],
            ],
        ];
        return $this->wechat->menu->create($buttons);
    }
}
