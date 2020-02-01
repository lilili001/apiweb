<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CustomerServiceController extends Controller
{
    protected $wechat;
    protected $cs;
    /**
     * MaterialController constructor.
     * @param $wechat
     */
    public function __construct()
    {
        $this->wechat = app('wechat.official_account');
        $this->cs = $this->wechat->customer_service;
    }

    public function customerServiceList()
    {
        dd($this->cs->list());
    }

    public function add()
    {
        $res = $this->cs->create('foo@test', '客服1');
        dd($res);
    }
}
