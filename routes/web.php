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

Route::group(['prefix'=>'wechat'],function($router){
    $router->get('wx', 'WechatController@valid' );
    $router->post('wx', 'WechatController@responseMsg' );
    $router->get('createMenu','WechatController@createMenu');
});