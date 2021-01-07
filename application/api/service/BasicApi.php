<?php

namespace app\api\service;

use think\Db;

/**
 * 基础接口类
 * Class BasicApi
 * @package controller
 */
class BasicApi
{

    /**
     * 当前请求对象
     * @var \think\Request
     */
    protected $request;

    /**
     * 构造方法
     * BasicApi constructor.
     */
    public function __construct()
    {
        ToolsService::corsOptionsHandler();
        $this->request = app('request');
    }

    /**
     * 返回成功的操作
     * @param mixed $msg 消息内容
     * @param array $data 返回数据
     * @param integer $code 返回代码
     */
    protected function success($msg, $data = [], $code = 100)
    {
        ToolsService::success($msg, $data, $code);
    }

    /**
     * 返回失败的请求
     * @param mixed $msg 消息内容
     * @param array $data 返回数据
     * @param integer $code 返回代码
     */
    protected function error($msg, $data = [], $code = 200)
    {
        ToolsService::error($msg, $data, $code);
    }

    /**
     * 验证手机
     * @param mixed $msg 消息内容
     * @param array $data 返回数据
     * @param integer $code 返回代码
     */
    function check($data, $rule = NULL, $ext = NULL)
    {
        $data = trim(str_replace(PHP_EOL, '', $data));

        if (empty($data)) {
            return false;
        }
        $validate['require'] = '/.+/';
        $validate['url'] = '/^http(s?):\\/\\/(?:[A-za-z0-9-]+\\.)+[A-za-z]{2,4}(?:[\\/\\?#][\\/=\\?%\\-&~`@[\\]\':+!\\.#\\w]*)?$/';
        $validate['currency'] = '/^\\d+(\\.\\d+)?$/';
        $validate['number'] = '/^\\d+$/';
        $validate['zip'] = '/^\\d{6}$/';
        $validate['cny'] = '/^(([1-9]{1}\\d*)|([0]{1}))(\\.(\\d){1,2})?$/';
        $validate['integer'] = '/^[\\+]?\\d+$/';
        $validate['double'] = '/^[\\+]?\\d+(\\.\\d+)?$/';
        $validate['english'] = '/^[A-Za-z]+$/';
        $validate['idcard'] = '/^([0-9]{15}|[0-9]{17}[0-9a-zA-Z])$/';
        $validate['truename'] = '/^[\\x{4e00}-\\x{9fa5}]{2,20}$/u';
        $validate['username'] = '/^[0-9a-z_]{3,15}$/';
        $validate['email'] = '/^\\w+([-+.]\\w+)*@\\w+([-.]\\w+)*\\.\\w+([-.]\\w+)*$/';
        $validate['mobile'] = '/^(((1[0-9][0-9]{1})|159|153)+\\d{8})$/';
        $validate['password'] = '/^[a-zA-Z0-9]{6,16}$/';
        $validate['xnb'] = '/^[a-zA-Z]$/';

        if (isset($validate[strtolower($rule)])) {
            $rule = $validate[strtolower($rule)];
            return preg_match($rule, $data);
        }
        $Ap = '\\x{4e00}-\\x{9fff}' . '0-9a-zA-Z\\@\\#\\$\\%\\^\\&\\*\\(\\)\\!\\,\\.\\?\\-\\+\\|\\=';
        $Cp = '\\x{4e00}-\\x{9fff}';
        $Dp = '0-9';
        $Wp = 'a-zA-Z';
        $Np = 'a-z';
        $Tp = '@#$%^&*()-+=';
        $_p = '_';
        $pattern = '/^[';
        $OArr = str_split(strtolower($rule));
        in_array('a', $OArr) && ($pattern .= $Ap);
        in_array('c', $OArr) && ($pattern .= $Cp);
        in_array('d', $OArr) && ($pattern .= $Dp);
        in_array('w', $OArr) && ($pattern .= $Wp);
        in_array('n', $OArr) && ($pattern .= $Np);
        in_array('t', $OArr) && ($pattern .= $Tp);
        in_array('_', $OArr) && ($pattern .= $_p);
        isset($ext) && ($pattern .= $ext);
        $pattern .= ']+$/u';
        return preg_match($pattern, $data);
    }

    /**
     * @desc 加密方式
     * @param $string
     * @return string
     */
    function encrypt($string)
    {
        return md5('C-H_t-X' . $string);
    }

    function base64Image($base64_image_content,$upad = 'userimagse')
    {
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            //图片后缀
            $type = $result[2];
            if ($type == 'jpeg')$type = 'jpg';
            $ext = strtolower('png,jpg,rar,doc,icon,mp4,jpeg');
            if (is_string($ext)) {$ext = explode(',', $ext);}
            if (!in_array($type, $ext)) {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '不允许上传的文件类型';
                return $data;
            }
            //保存位置--图片名
            $image_name = date('His') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT) . "." . $type;
            $iamge_dir = 'upload/'.$upad . date('Ymd');
            $image_url = $iamge_dir . $image_name;
            if (!is_dir(dirname('./' . $iamge_dir))) {
                mkdir(dirname('./' . $iamge_dir));
                chmod(dirname('./' . $iamge_dir), 0777);
            }

            //解码
            $decode = base64_decode(str_replace($result[1], '', $base64_image_content));
            if (file_put_contents('./' . $image_url, $decode)) {
                $appRoot = request()->root(true);  // 去掉参数 true 将获得相对地址
                $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
                $uriRoot = in_array($uriRoot, ['/', '\\']) ? '' : $uriRoot;
                $data['code'] = 1;
                $data['imageName'] = $image_name;
                $data['image_url'] = $uriRoot . '/' . $image_url;
                $data['type'] = $type;
                $data['msg'] = '保存成功！';
            } else {
                $data['code'] = 0;
                $data['imgageName'] = '';
                $data['image_url'] = '';
                $data['type'] = '';
                $data['msg'] = '图片保存失败！';
            }
        } else {
            $data['code'] = 0;
            $data['imgageName'] = '';
            $data['image_url'] = '';
            $data['type'] = '';
            $data['msg'] = 'base64图片格式有误！';
        }
        return $data;
    }

    function check_arr($rs)
    {
        foreach ($rs as $v) {
            if (is_array($v)) {
                foreach ($v as $val) {
                    if (!$val) {
                        return false;
                    }
                }
            } else {
                if (!$v) {
                    return false;
                }
            }
        }
        return true;
    }
    
    /* 分润自动发放，不审核模式 */
    function mlog($mid, $type, $status, $money, $content, $extends = '')
    {
        $before = Db::name('StoreMember')->where(['id' => $mid])->value($type);
        $res[] = Db::name('StoreMember')->where(['id' => $mid])->setInc($type, $money);
        $res[] = Db::name('MemberMoneyLog')->insert([
            'mid' => $mid,
            'type' => $type,
            'status' => $status,
            'money' => $money,
            'before' => $before,
            'content' => $content,
            'extends' => $extends,
            'is_show' => 1,
            'check_status' => 1,
            'pay_status' => 1,
        ]);
        return $res;
    }

    /* 分润记录生成，审核模式 */
    function mlogad_shou($mid, $type, $status, $money, $content, $extends = '',$time)
    {
        $before = Db::name('StoreMember')->where(['id' => $mid])->value($type);
        $res[] = Db::name('MemberMoneyLog')->insert([
            'mid' => $mid,
            'type' => $type,
            'status' => $status,
            'money' => $money,
            'before' => $before,
            'content' => $content,
            'extends' => $extends,
            'create_at' => $time,
        ]);
        return $res;
    }

    /* 分润记录生成，审核模式 */
    function mlogad($mid, $type, $status, $money, $content, $extends = '')
    {
        $before = Db::name('StoreMember')->where(['id' => $mid])->value($type);
        $res[] = Db::name('MemberMoneyLog')->insert([
            'mid' => $mid,
            'type' => $type,
            'status' => $status,
            'money' => $money,
            'before' => $before,
            'content' => $content,
            'extends' => $extends,
        ]);
        return $res;
    }
    
    /* 分润发放，审核模式 */
    function mlogup($mid, $type, $id, $money, $content, $extends = '')
    {
        $before = Db::name('StoreMember')->where(['id' => $mid])->value($type);
        $res[] = Db::name('StoreMember')->where(['id' => $mid])->setInc($type, $money);
        $res[] = Db::name('MemberMoneyLog')->update([
            'id' => $id,
            'before' => $before,
            'is_show' => 1,
            'pay_status' => 1,
        ]);
        return $res;
    }

}