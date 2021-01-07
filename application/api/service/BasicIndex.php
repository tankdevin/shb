<?php

namespace app\api\service;

use think\Db;

/**
 * 基础接口类
 * Class BasicApi
 * @package controller
 */
class BasicIndex extends BasicApi
{
    protected $uid;

    protected $user;

    protected $number = 10;

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
//        if ($this->request->isGet())
//
//            error('404 Not Found');
        $not_allow_controller = [
            "login",
            "plan",
            "dingshi",
            "index",
            "bank",
            "alipay",
        ];
        if (!in_array(strtolower($this->request->controller()), $not_allow_controller))
            if (empty($token = $this->request->post('token', request()->header('token', '')))) {
                $this->error('请重新登录', [], 999);
            } else {
                $user = Db::name('StoreMember')->where(['token' => $token])->find();
                if (!$user)  $this->error("请重新登陆", [], 999);
                if ($user['status'] == 99) {
                    $this->error("该账号已被封号,请联系申请解封", [], 999);
                }
                $this->uid = $user['id'];
                $this->user = $user;

                // listen level
               // upgrade_level($this->id);
            }
    }
}