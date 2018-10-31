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
    return view('welcome');
});

Route::get('wxlogin','WxCbController@login');
Route::get('wx','WxCbController@valid');
Route::post('wx','WxCbController@responseMsg');
Route::group(['prefix'=>'wxApi'],function($router){
    $router->post('sendTemplateMsg','WxCbController@sendTemplateMsg');
});

Route::group(['middleware'=>'web'],function (){
    Route::get('users','UsersController@users');
    Route::get('user/{openid}','UsersController@user');
    Route::get('remark/{openid}','UsersController@remark');

    //素材管理
    Route::get('materialList',"MaterialController@materialList");
    Route::get('image',"MaterialController@image");
    Route::get('uploadNews',"MaterialController@uploadNews");
    Route::get('audio',"MaterialController@audio");
    Route::get('material/{mediaid}',"MaterialController@material");

    Route::get('test',"MaterialController@test");

    //消息群发
    Route::get('message',"MaterialController@message");

    //菜单
    Route::get('menulist',"MaterialController@menulist");
    Route::get('create_menu',"MaterialController@create_menu");
});


Route::any('wechat',"WechatController@serve");

/*Route::group(['prefix'=>'wechat'],function($router){
    $router->get('wx', 'WechatController@valid' );
    $router->post('wx', 'WechatController@responseMsg' );
    $router->get('createMenu','WechatController@createMenu');
});*/