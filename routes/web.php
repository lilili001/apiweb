<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    $oauth = app('wechat.official_account')->oauth;
    $user = $oauth->user();
    dd($user);

    return view('welcome');
});

Route::get('wxlogin','WxCbController@login');
Route::get('wx','WxCbController@valid');
Route::post('wx','WxCbController@responseMsg');
Route::group(['prefix'=>'wxApi'],function($router){
    $router->post('sendTemplateMsg','WxCbController@sendTemplateMsg');
});

//用于和微信交互的方法
Route::any('wechat',"WechatController@serve");

//下面这些可以直接在浏览器中访问
Route::group(['middleware'=>'web'],function (){
    Route::get('users','UsersController@users');//获取所有的用户
    Route::get('user/{openid}','UsersController@user');//获取用户信息
    Route::get('remark/{openid}','UsersController@remark');//修改用户备注

    //素材管理
    Route::get('materialList',"MaterialController@materialList");
    Route::get('image',"MaterialController@image"); //上传图片素材
    Route::get('uploadNews',"MaterialController@uploadNews"); // 上传单篇图文
    Route::get('voice',"MaterialController@audio");//上传音频
    Route::get('material/{mediaid}',"MaterialController@material");

    //测试获取token
    Route::get('accessToken',"MaterialController@accessToken");

    //消息群发
    Route::get('message',"MaterialController@message");

    //菜单
    Route::get('menulist',"MaterialController@menulist");
    Route::get('create_menu',"MaterialController@create_menu");

    //消息模板
    Route::get('templateList',"MaterialController@templateList");
});


Route::group(['middleware' => ['web', 'wechat.oauth']], function ($router) {
    $router->get('/getuser', function () {
        $user = session('wechat.oauth_user.default'); // 拿到授权用户资料

        return app('wechat.official_account')->oauth->scopes(['snsapi_userinfo'])
            ->redirect();

        return view('wuser',compact('user'));
    });
});

Route::get('qr',function (){
    $wechat = app('wechat.official_account');
    $qrcode = $wechat->qrcode;
    $result = $wechat->qrcode->temporary('foo', 6 * 24 * 3600);
    return view('qr', compact('result','qrcode') );
});

/*Route::group(['prefix'=>'wechat'],function($router){
    $router->get('wx', 'WechatController@valid' );
    $router->post('wx', 'WechatController@responseMsg' );
    $router->get('createMenu','WechatController@createMenu');
});*/


Route::get('mail',function (){
    \Illuminate\Support\Facades\Mail::to("2861166132@qq.com")->send(new \App\Mail\welcomeToMiya());
});


Route::get("test1",function(){
    return redirect("test2");
});

Route::get("jssdk", "JSSDKController@jssdk");