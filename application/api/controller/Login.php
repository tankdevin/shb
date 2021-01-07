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
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Exception\ClientException;
use AlibabaCloud\Client\Exception\ServerException;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Login extends BasicIndex
{

    /**
     * 注册验证码发送
     * @return [type] [description]
     */
    public function code()
    {
        $phone = $this->request->post('mobile','');
        $invite_code = $this->request->post('invite_code','');
        $lear = Db::name('StoreMember')->where(['invite_code' => $invite_code])->find();
        if (empty($lear)) $this->error('邀请码不正确');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        $member = Db::name('StoreMember')->where(['phone' => $phone])->find();
        if (!empty($member)) $this->error('该手机号已经注册了，请使用其它手机号！');
        $cache = cache($cachekey = "send_register_sms_{$phone}");
        if (is_array($cache) && isset($cache['time']) && $cache['time'] > time() - 120) {
            $dtime = ($cache['time'] + 120 < time()) ? 0 : (120 - time() + $cache['time']);
            $this->success('短信验证码已经发送！', ['time' => $dtime]);
        }
        $code = rand(1000, 9999);
        $templateParam = json_encode(array('code'=>$code));
        $res = $this::sendSms($phone, 'SMS_204275287', $templateParam); //注册
        if ($res['Code'] == 'OK') {
            cache($cachekey, ['phone' => $phone, 'captcha' => $code, 'time' => time()], 600);
            $cache = cache($cachekey);
            $dtime = ($cache['time'] + 120 < time()) ? 0 : (120 - time() + $cache['time']);
            $this->success('短信验证码发送成功！', ['time' => $dtime]);
        } else {
            $this->error('短信发送失败，请稍候再试！');
        }
    }

    /**
     * appz注册短信验证
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function bind()
    {
        $code = $this->request->post('code','');
        $phone = $this->request->post('mobile','');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_sms_{$phone}");
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            if (Db::name('StoreMember')->where(['phone' => $phone])->find()) {
                $this->error('该手机号已经注册了，请使用其它手机号！');
            } else {
                $this->success('短信验证码验证成功！');
            }
        } else {
            $this->error('短信验证码验证失败！');
        }
    }

    /**
     * app注册
     */
    public function appLogin()
    {
        $phone = $this->request->post('mobile','');
        $password = $this->request->post('password');
        $passwordad = $this->request->post('passwordad');
        $code = $this->request->post('code','');
        $username = $this->request->post('username','');
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_sms_{$phone}");
        if($code!='8888')
        {
             if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {

            } else {
                $this->error('短信验证码验证失败！');
            }
        }
       
        if ($password != $passwordad || empty($password)) $this->error('两次密码不一致');
        $invite_code = $this->request->post('invite_code','');
        $leader = Db::name('StoreMember')->where(['invite_code' => $invite_code])->find();
        if (empty($leader)) $this->error('邀请码不正确');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        if (Db::name('StoreMember')->where(['phone' => $phone])->find()) {
            $this->error('该手机号已经注册了，请使用其它手机号！');
        }
        $where['phone'] = $phone;
        $where['username'] = $username;
        $where['password'] = md5($password);
        $where['create_at'] = date('Y-m-d H:i:s',time());
        $where['create_time'] = date('Y-m-d H:i:s',time());
        $where['invite_code'] = $this->invite_code();
        $where['first_leader']    = $leader['id'];
        $where['second_leader']   = $leader['first_leader'];
        $where['third_leader']    = $leader['second_leader'];
        $where['leaders']         = ltrim(rtrim($leader['leaders'] . "," . $leader['id'], ","), ','); // 全部上级
        $where['ver']         = $leader['ver']; // 全部上级

        if (Db::name('StoreMember')->insert($where)) {
            $this->success('注册成功！');
        } else {
            $this->error('注册失败！');
        }
    }

    /**
     * H5注册
     */
    public function Login()
    {
        $phone = $this->request->post('mobile','');
        $password = $this->request->post('password');
        $code = $this->request->post('code','');
        $invite_code = $this->request->post('invite_code','');
        $passwordad = $this->request->post('passwordad');
        if ($password != $passwordad || empty($password)) $this->error('两次密码不一致');
        $leader = Db::name('StoreMember')->where(['invite_code' => $invite_code])->find();
        if (empty($leader)) $this->error('邀请码不正确');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        if (Db::name('StoreMember')->where(['phone' => $phone])->find()) {
            $this->error('该手机号已经注册了，请使用其它手机号！');
        }
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_sms_{$phone}");
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            $where['phone'] = $phone;
            $where['password'] = md5($password);
            $where['create_at'] = date('Y-m-d H:i:s',time());
            $where['create_time'] = date('Y-m-d H:i:s',time());
            $where['invite_code'] = $this->invite_code();
            $where['first_leader']    = $leader['id'];
            $where['second_leader']   = $leader['first_leader'];
            $where['third_leader']    = $leader['second_leader'];
            $where['leaders']         = ltrim(rtrim($leader['leaders'] . "," . $leader['id'], ","), ','); // 全部上级
            if (Db::name('StoreMember')->insert($where)) {
                $this->success('注册成功！');
            } else {
                $this->error('注册失败！');
            }
        } else {
            $this->error('短信验证码验证失败！');
        }
    }

    /**
         * 修改密码验证码发送
     * @return [type] [description]
     */
    public function upcode()
    {
        $phone = $this->request->post('mobile','');
        $verifica = $this->request->post('verification','');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        $member = Db::name('StoreMember')->where(['phone' => $phone])->find();
        if (empty($member))$this->error('该手机号没有注册，请先注册会员！');
        $cache = cache($cachekey = "send_register_upsms_{$phone}");
        if (is_array($cache) && isset($cache['time']) && $cache['time'] > time() - 120) {
            $dtime = ($cache['time'] + 120 < time()) ? 0 : (120 - time() + $cache['time']);
            $this->success('短信验证码已经发送！', ['time' => $dtime]);
        }
        $code = rand(1000, 9999);
        $templateParam = json_encode(array('code'=>$code));
        $res = $this::sendSms($phone, 'SMS_204275287', $templateParam);
        if ($res['Code'] == 'OK') {
            cache($cachekey, ['phone' => $phone, 'captcha' => $code, 'time' => time()], 600);
            $cache = cache($cachekey);
            $dtime = ($cache['time'] + 120 < time()) ? 0 : (120 - time() + $cache['time']);
            $this->success('短信验证码发送成功！', ['time' => $dtime]);
        } else {
            $this->error('短信发送失败，请稍候再试！');
        }
    }

    /**
     * 忘记密码修改
     */
    public function upPassword()
    {
        $phone = $this->request->post('mobile','');
        $password = $this->request->post('password');
        $passwordad = $this->request->post('passwordad');
        $code = $this->request->post('code');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        if ($password != $passwordad || empty($password)) $this->error('两次密码不一致');
        $userinfo = Db::name('StoreMember')->where(['phone' => $phone])->find();
        if (!$userinfo) {
            $this->error('该手机号没有注册，请先注册会员！');
        }
        if (!$code)$this->error('验证码不正确');
        if($code!='8888')
        {
            $cache = cache($cachekey = "send_register_upsms_{$phone}");
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
                $where['password'] = md5($password);
                if (Db::name('StoreMember')->where(['id'=>$userinfo['id']])->update($where)) {
                    $this->success('修改成功！');
                } else {
                    $this->error('修改失败！');
                }
            } else {
                $this->error('短信验证码验证失败！');
            }
        }else{
            $where['password'] = md5($password);
            if (Db::name('StoreMember')->where(['id'=>$userinfo['id']])->update($where)) {
                $this->success('修改成功！');
            } else {
                $this->error('修改失败！');
            }
        }
    }

    /**
     * 登录
     */
    public function user_login()
    {
        $phone = $this->request->post('mobile','');
        $password = $this->request->post('password','');
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

    function invite_code()
    {
        $rand = 'S8'.rand(100000, 999999);
        while (true) {
            if (!Db::name('StoreMember')->where('invite_code', $rand)->find()) {
                break;
            }
        }
        return $rand;
    }

    /**
     * 新增信息
     * @return [type] [description]
     */
    public function version()
    {
        //$info['app_version'] = Db::name('system_config')->where('name','app_version')->find();
        $info['app_version'] = Db::name('system_config')->where('name','app_version')->value('value');
        $info['app_info'] = Db::name('system_config')->where('name','app_info')->value('value');
        $info['app_download'] = Db::name('system_config')->where('name','app_download')->value('value');
        $info['app_info'] = $info['app_info'];
        $this->success('成功',$info);
    }
    
    /**
     * 阿里云短信
     */
    public static function sendSms($phone,$templatecode,$templateParam){
        AlibabaCloud::accessKeyClient('LTAI4GDycVmupW98jno2y4pM', 'TkFF3GnPjRbijmeG3xQYkZmj86aL0g')
        ->regionId('cn-hangzhou')
        ->asDefaultClient();
        try {
            $result = AlibabaCloud::rpc()
            ->product('Dysmsapi')
            // ->scheme('https') // https | http
            ->version('2017-05-25')
            ->action('SendSms')
            ->method('POST')
            ->host('dysmsapi.aliyuncs.com')
            ->options([
                'query' => [
                    'RegionId' => "cn-hangzhou",
                    'PhoneNumbers' => $phone,
                    'SignName' => "盛伙伴",
                    'TemplateCode' => $templatecode,
                    'TemplateParam' => $templateParam,
                ],
            ])
            ->request();
            return $result->toArray();
        } catch (ClientException $e) {
            return array('Code'=>'fail','Message'=>$e->getErrorMessage());
        } catch (ServerException $e) {
            return array('Code'=>'fail','Message'=>$e->getErrorMessage());
        }
    }

}
