<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2019 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://demo.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 代码仓库：https://gitee.com/zoujingli/ThinkAdmin
// | github 代码仓库：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\api\controller;

use library\Controller;
use app\api\service\BasicIndex;
use think\db;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Daohang extends BasicIndex
{

    /**
     * banner
     * @return [type] [description]
     */
    public function bannerlist()
    {
        $bannerlist = Db::name('ContentBanner')->where(['status' => 1])->order('sort desc')->field('title,img,url')->select();
        $this->success('成功', $bannerlist);
    }


    public function user_login()
    {
        $phone = $this->request->post('mobile');
        $password = $this->request->post('password');
        $userinfo = Db::name('StoreMember')->where(['phone' => $phone])->find();
        if(!$userinfo)$this->error('账号密码不正确');
        if($userinfo['password']!=md5($password))$this->error('账号密码不正确');
        $token = sha1($this->encrypt($userinfo['phone'] . md5($userinfo['create_at'] . rand(9999, 99999) . $userinfo['last_time'])));

        $res = Db::name('StoreMember')->where(array('id' => $userinfo['id']))->update(array('token'     => $token, 'last_time' => time(),));
        if ($res) {
            $this->success('登录成功', $token);
        } else {
            $this->error('登录失败');
        }
    }













}
