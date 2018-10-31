<?php

namespace App\Http\Controllers;

use function Couchbase\defaultDecoder;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    protected $wechat;

    /**
     * UsersController constructor.
     * @param $wechat
     */
    public function __construct()
    {
        $this->wechat = app('wechat.official_account');
    }

    /*列举素有的 关注我的公众号的用户*/
    public function users()
    {
        $users = $this->wechat->user->list();
        dd( $users  ) ;
    }

    /**************获取某个用户*********************/
    public function user($openid)
    {
        $user = $this->wechat->user->get($openid);
        dd($user);
    }

    public function remark($openid)
    {
        $this->wechat->user->remark($openid, 'miya_good_gal');
        return 'ok';
    }
}
