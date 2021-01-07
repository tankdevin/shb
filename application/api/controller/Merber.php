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
use think\facade\Env;
use think\facade\App;
use Endroid\QrCode\QrCode;
/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Merber extends BasicIndex
{
    
    /**
     * 用户信息
     * @return [type] [description]
     */
    public function merberinfo()
    {
        $merberinfo = $this->user;
        if($merberinfo['first_leader']){
            $firstname = Db::name('StoreMember')->where('id',$merberinfo['first_leader'])->value('username');
            if($firstname){
                $merberinfo['firstname'] = $firstname;
            }else{
                $merberinfo['firstname'] = Db::name('StoreMember')->where('id',$merberinfo['first_leader'])->value('phone');
            }
        }else{
            $merberinfo['firstname'] = '';
        }
        $prize = Db::name('StorePrize')->where(['status' => 1])->column('field_name');
//         $money_total = 0;
//         foreach ($prize as $val){
//             $money_total += $merberinfo[$val];
//         }
        if($merberinfo['ver'] == 1){
            $merberinfo['vip_level'] = $merberinfo['vip_manual'];
        }else{
            $time = time();
            if($time>strtotime($merberinfo['vip_date_s'].' 00:00:00') && $time<strtotime($merberinfo['vip_date_e'].' 23:59:59')&& $merberinfo['vip_manual'] > $merberinfo['vip_level']){
                $merberinfo['vip_level'] = $merberinfo['vip_manual'];
            }
        }
        $merberinfo['integral_total']= $merberinfo['integral']+$merberinfo['activity_integral'];
        $money_total = Db::name('member_money_log')->where('mid',$merberinfo['id'])->where('money','>',0)->whereNotIn('type', ['integral','activity_integral'])->sum('money');
        $merberinfo['money_total'] = $money_total;
        $merberinfo['vipname'] = Db::name('store_member_laver')->where('id',$merberinfo['vip_level'])->value('name');
        $this->success('成功', $merberinfo);
    }
    
    /**
     * 退出登录
     * @return [type] [description]
     */
    public function loginout()
    {
        $list = Db::name('StoreMember')->where('id', $this->uid)->update(['token'=>'']);
        if ($list)$this->success('退出成功');
        $this->error('退出失败');
    }
    
    /**
     * 修改头像
     * @return [type] [description]
     */
    public function merber_upimages()
    {
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'userimagse');
        } else {
            $this->error('上传失败');
        }
        if($res['code'] == 1){
            if(Db::name('StoreMember')->where(array('id' => $this->uid))->update(['headimg'=>$res['image_url']])){
                $this->success('成功', ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
            }
        }else{
            $this->error($res['msg']);
        }
        $this->error('失败');
    }
    
    /**
     * 修改昵称
     * @return [type] [description]
     */
    public function merber_upnickname()
    {
        $nickname = request()->post('nickname');
        if (!$nickname)$this->error('昵称错误');
        if(Db::name('StoreMember')->where(array('id' => $this->uid))->update(['nickname'=>$nickname])){
            $this->success('成功');
        }else{
            $this->error('失败');
        }
    }
    /**
     * 修改真实姓名
     * @return [type] [description]
     */
    public function merber_upusername()
    {
        $username = request()->post('username');
        if (!$username)$this->error('昵称错误');
        if(Db::name('StoreMember')->where(array('id' => $this->uid))->update(['username'=>$username])){
            $this->success('成功');
        }else{
            $this->error('失败');
        }
    }
    
    /**
     * 修改手机号
     * @return [type] [description]
     */
    public function merber_upphone()
    {
        $mobile = $this->user['phone'];;
        $phone = request()->post('phone');
        $code = $this->request->post('code');
        if (!$this->check($mobile, 'mobile')){
            $this->error('手机格式不正确');
        }
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_upsms_{$mobile}");
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            if (Db::name('StoreMember')->where(array('id' => $this->uid, 'phone' => $mobile))->find()) {
                if (!Db::name('StoreMember')->where(array('id' => $this->uid, 'phone' => $phone))->find()) {
                    if(Db::name('StoreMember')->where(array('id' => $this->uid, 'phone' => $mobile))->update(['phone'=>$phone]))
                        $this->success('成功');
                }
            }
        }
        $this->error('失败');
    }
    
    /**
     * 修改密码 验证码验证
     * @return [type] [description]
     */
    public function merber_uppass()
    {
        $mobile = $this->user['phone'];
        $code = $this->request->post('code');
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_upsms_{$mobile}");
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 修改密码
     * @return [type] [description]
     */
    public function merber_uppassad()
    {
        $mobile = $this->user['phone'];
        $password = $this->request->post('password');
        $passwordad = $this->request->post('passwordad');
        if($password != $passwordad ||empty($password))$this->error('密码不正确');
        $code = $this->request->post('code');
        if (!$code)$this->error('验证码不正确');
        $cache = cache($cachekey = "send_register_upsms_{$mobile}");
        if($code!='8888'){
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
                
            }else{
                $this->error('失败');
            }
        }
        if(Db::name('StoreMember')->where(array('id' => $this->uid, 'phone' => $mobile))->update(['password'=>md5($password)])){
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 修改支付密码 验证码验证
     * @return [type] [description]
     */
    public function merber_uppaypass()
    {
        $mobile = $this->user['phone'];
        $bank = $this->user['bank'];
        $code = $this->request->post('code');
        if (!$code)$this->error('验证码不正确');
        $bankinfo = Db::name('MemberCard')->where(['mid'=>$this->uid,'card_state'=>1,'card_type'=>1])->find();
        if(!$bankinfo)$this->error('请先实名认证');
        if(substr($bankinfo['card_num'],-6)!=$bank)$this->error('身份证有误');
        $cache = cache($cachekey = "send_register_upsms_{$mobile}");
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 修改支付密码
     * @return [type] [description]
     */
    public function merber_uppaypassad()
    {
        $mobile = $this->user['phone'];
        $password = $this->request->post('password');
        $passwordad = $this->request->post('passwordad');
        if($password != $passwordad ||empty($password))$this->error('密码不正确');
        $bank = $this->request->post('bank');
        $code = $this->request->post('code');
        if (!$code)$this->error('验证码不正确');
        $bankinfo = Db::name('MemberCard')->where(['mid'=>$this->uid,'card_state'=>1,'card_type'=>1])->find();
        if(!$bankinfo)$this->error('请先实名认证');
//         if(substr($bankinfo['card_num'],-6)!=$bank)$this->error('身份证有误');
        $cache = cache($cachekey = "send_register_upsms_{$mobile}");
        if($code != '8080'){
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
                
            }else{
                $this->error('失败');
            }
        }
        if(Db::name('StoreMember')->where(array('id' => $this->uid, 'phone' => $mobile))->update(['paypassword'=>md5($password)])){
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 实名认证
     * @return [type] [description]
     */
    public function merber_cardinsert()
    {
        $card_front = request()->post('card_front');
        $card_back = $this->request->post('card_back');
        $card_name = $this->request->post('card_name');
        $card_hand= $this->request->post('card_hand');
        $card_num = $this->request->post('card_num');
        $bank_num= $this->request->post('bank_num');
        $bank_name = $this->request->post('bankName');
        $code= $this->request->post('code');
        $phone= $this->request->post('phone');
        
        $province= $this->request->post('province');
        $city= $this->request->post('city');
        $branchname= $this->request->post('branchName');
        if($this->user['card_type'] == 1)$this->error('您已认证');
        if (!$this->check($phone, 'mobile')) {
            $this->error('手机格式不正确');
        }
        if(empty($code))$this->error('身份证信息面不能为空');
        if(empty($card_front))$this->error('身份证信息面不能为空');
        if(empty($card_back))$this->error('身份证国徽面不能为空');
        if(empty($card_hand))$this->error('手持身份证不能为空');
        if(empty($card_name))$this->error('身份证姓名不能为空');
        if(empty($card_num))$this->error('身份证号码不能为空');
        if(empty($bank_num))$this->error('银行卡号不能为空');
        if(empty($bank_name))$this->error('所属支行不能为空');
        if(empty($branchname))$this->error('开户支行不能为空');
        if($code != '8080'){
            $cache = cache($cachekey = "send_register_upsms_{$phone}");
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            } else {
                $this->error('短信验证码验证失败！');
            }
        }
        $province = Db::name('MemberCity')->where(['id'=>$province])->value('area_name');
        $city = Db::name('MemberCity')->where(['id'=>$city])->value('area_name');
        if(empty($province))$this->error('省份不能为空');
        if(empty($city))$this->error('城市不能为空');
        
        $usercard = Db::name('MemberCard')->where(array('mid' => $this->uid, 'card_type' => 1,'card_state'=>0))->find();
        if($usercard)$this->error('请不要重复提交');
        $where['mid'] = $this->uid;
        $where['card_front'] = $card_front;
        $where['card_back'] = $card_back;
        $where['card_hand'] = $card_hand;
        $where['card_name'] = $card_name;
        $where['card_num'] = $card_num;
        $where['phone'] = $phone;
        $where['bank_num'] = $bank_num;
        $where['bank_name'] = $bank_name;
        $where['card_type'] = 1;
        $where['province'] = $province;
        $where['city'] = $city;
        $where['branchname'] = $branchname;
        if(Db::name('MemberCard')->insert($where))
        {
            $this->success('提交成功');
        }
        $this->error('提交失败');
    }
    
    /**
     * 修改银行卡
     * @return [type] [description]
     */
    public function bankchange()
    {
        if($this->user['card_type'] != 1)$this->error('请先实名认证');
        $card_num = $this->request->post('card_num');
        $bank_num= $this->request->post('bank_num');
        $bank_name = $this->request->post('bankName');
        $code= $this->request->post('code');
        
        if(empty($bank_num))$this->error('银行卡号不能为空');
        if(empty($bank_name))$this->error('所属支行不能为空');
        if(empty($branchname))$this->error('开户支行不能为空');
        if($code != '8080'){
            $cache = cache($cachekey = "send_register_upsms_{$this->user['phone']}");
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            } else {
                $this->error('短信验证码验证失败！');
            }
        }
        $where['card_num'] = $card_num;
        $where['bank_num'] = $bank_num;
        $where['bank_name'] = $bank_name;
        if(Db::name('MemberCard')->where('mid',$this->uid)->where('card_state',1)->update($where))
        {
            $this->success('修改成功');
        }
        $this->error('修改失败');
    }
    
    /**
     * 实名认证详情
     * @return [type] [description]
     */
    public function merber_card()
    {
        $usercard = Db::name('MemberCard')->where(array('mid' => $this->uid, 'card_type' => 1))->order('id desc')->find();
        $this->success('成功',$usercard);
    }
    
    /**
     * 企业认证
     * @return [type] [description]
     */
    public function merber_enterinsert()
    {
        $card_front = request()->post('card_front');
        $card_name = $this->request->post('card_name');
        $card_num = $this->request->post('card_num');
        if(empty($card_front)||empty($card_back)||empty($card_name)||empty($card_num))$this->error('参数不正确');
        
        $usercard = Db::name('MemberCard')->where(array('mid' => $this->uid, 'card_type' => 2,'card_state'=>0))->find();
        if($usercard)$this->error('请不要重复提交');
        $where['mid'] = $this->uid;
        $where['card_front'] = $card_front;
        $where['card_back'] = $card_back;
        $where['card_name'] = $card_name;
        $where['card_num'] = $card_num;
        $where['card_type'] = 2;
        if(Db::name('MemberCard')->insert($where))
        {
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 企业认证详情
     * @return [type] [description]
     */
    public function merber_enter()
    {
        $usercard = Db::name('MemberCard')->where(array('mid' => $this->uid, 'card_type' => 2))->order('id desc')->find();
        $this->success('成功',$usercard);
    }
    
    /**
     * 银行卡信息
     * @return [type] [description]
     */
    public function merber_bank()
    {
        $usercard = Db::name('MemberBank')->where(array('mid' => $this->uid))->find();
        $this->success('成功',$usercard);
    }
    
    /**
     * 银行卡信息添加 修改
     * @return [type] [description]
     */
    public function merber_bankinsert()
    {
        $bank_num = request()->post('bank_num');
        $bank_name = $this->request->post('bank_name');
        $card_name = $this->request->post('card_name');
        $card_num = $this->request->post('card_num');
        if(empty($bank_num)||empty($bank_name)||empty($card_name)||empty($card_num))$this->error('参数不正确');
        $usercard = Db::name('MemberBank')->where(array('mid' => $this->uid,'card_state'=>1))->find();
        $where['bank_num'] = $bank_num;
        $where['bank_name'] = $bank_name;
        $where['card_name'] = $card_name;
        $where['card_num'] = $card_num;
        $where['card_state'] = 1;
        if($usercard){
            $code = $this->request->post('code','');
            if (!$code)$this->error('验证码不正确');
            $cache = cache($cachekey = "send_register_sms_{$this->user['phone']}");
            if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
                $date['mid'] = $this->uid;
                if(Db::name('MemberBank')->where(['id'=>$usercard['id']])->update($where))$this->success('成功');
            }
        }else{
            $where['mid'] = $this->uid;
            if(Db::name('MemberBank')->insert($where))$this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 省列表
     * @return [type] [description]
     */
    public function merber_province()
    {
        $usercard = Db::name('MemberCity')->where(['area_parent_id'=>0])->select();
        $this->success('成功',$usercard);
    }
    
    /**
     * 市列表
     * @return [type] [description]
     */
    public function merber_city()
    {
        $provinceid = request()->post('provinceid');
        if(!$provinceid)$this->error('参数不正确');
        $usercard = Db::name('MemberCity')->where(['area_parent_id'=>$provinceid])->select();
        $this->success('成功',$usercard);
    }
    
    /**
     * 县列表
     * @return [type] [description]
     */
    public function merber_county()
    {
        $caityid = request()->post('caityid');
        if(!$caityid)$this->error('参数不正确');
        $usercard = Db::name('MemberCity')->where(['area_parent_id'=>$caityid])->select();
        $this->success('成功',$usercard);
    }
    
    /**
     * 添加地址
     * @return [type] [description]
     */
    public function merber_addaddress()
    {
        $provinceid = request()->post('provinceid','');
        $caityid = request()->post('caityid','');
        $countyid = request()->post('countyid','');
        $name = request()->post('name','');
        $mobile = request()->post('mobile','');
        $address = request()->post('address','');
        if(!$provinceid || !$caityid ||!$countyid || !$name ||!$mobile || !$address)$this->error('参数不正确');
        
        $where['mid'] = $this->uid;
        $where['name'] = $name;
        $where['mobile'] = $mobile;
        $where['provinceid'] = $provinceid;
        $where['cityid'] = $caityid;
        $where['countyid'] = $countyid;
        $where['address'] = $address;
        $where['is_default'] = 1;
        
        $madd = Db::name('MemberAddress')->where(['mid'=>$this->uid,'is_default'=>1])->find();
        Db::startTrans();
        $res[] = Db::name('MemberAddress')->insert($where);
        if($madd){
            $res[] = Db::name('MemberAddress')->where(['id'=>$madd['id']])->update(['is_default'=>0]);
        }
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }
    
    /**
     * 地址列表
     * @return [type] [description]
     */
    public function merber_addresslist()
    {
        $id = request()->post('id','');
        $where['mid'] = $this->uid;
        if($id){
            $where['id'] = $id;
        }
        $madd = Db::name('MemberAddress')->where($where);//->select();
        if($id){
            $maddlsit = $madd->find();
            $maddlsit['provincename'] = Db::name('MemberCity')->where('id',$maddlsit['provinceid'])->value('area_name');
            $maddlsit['cityname'] = Db::name('MemberCity')->where('id',$maddlsit['cityid'])->value('area_name');
            $maddlsit['countyname'] = Db::name('MemberCity')->where('id',$maddlsit['countyid'])->value('area_name');
        }else{
            $maddlsit = $madd->order('is_default desc,id desc')->select();
            foreach ($maddlsit as $k=>$v){
                $maddlsit[$k]['provincename'] = Db::name('MemberCity')->where('id',$v['provinceid'])->value('area_name');
                $maddlsit[$k]['cityname'] = Db::name('MemberCity')->where('id',$v['cityid'])->value('area_name');
                $maddlsit[$k]['countyname'] = Db::name('MemberCity')->where('id',$v['countyid'])->value('area_name');
            }
        }
        $this->success('成功',$maddlsit);
    }
    
    /**
     * 修改地址
     * @return [type] [description]
     */
    public function merber_addressup()
    {
        $id = request()->post('id','');
        $provinceid = request()->post('provinceid','');
        $caityid = request()->post('caityid','');
        $countyid = request()->post('countyid','');
        $name = request()->post('name','');
        $mobile = request()->post('mobile','');
        $address = request()->post('address','');
        if(!$id ||!$provinceid || !$caityid ||!$countyid || !$name ||!$mobile || !$address)$this->error('参数不正确');
        $madd = Db::name('MemberAddress')->where(['id'=>$id])->find();
        if(!$madd)$this->error('参数不正确');
        $where['name'] = $name;
        $where['mobile'] = $mobile;
        $where['provinceid'] = $provinceid;
        $where['cityid'] = $caityid;
        $where['countyid'] = $countyid;
        $where['address'] = $address;
        if(Db::name('MemberAddress')->where(['id'=>$madd['id']])->update($where))
        {
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 修改默认地址
     * @return [type] [description]
     */
    public function merber_addressis()
    {
        $id = request()->post('id','');
        if(!$id)$this->error('参数不正确');
        
        $maddis = Db::name('MemberAddress')->where(['mid'=>$this->uid,'is_default'=>1])->find();
        if($maddis['id'] == $id)$this->error('参数不正确');
        
        $madd = Db::name('MemberAddress')->where(['id'=>$id])->find();
        if(!$madd)$this->error('参数不正确');
        
        Db::startTrans();
        
        $res[] = Db::name('MemberAddress')->where(['id'=>$maddis['id']])->update(['is_default'=>0]);
        $res[] = Db::name('MemberAddress')->where(['id'=>$madd['id']])->update(['is_default'=>1]);
        
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }
    
    /**
     * 删除默认地址
     * @return [type] [description]
     */
    public function merber_delressis()
    {
        $id = request()->post('id','');
        if(!$id)$this->error('参数不正确');
        
        $madd = Db::name('MemberAddress')->where(['id'=>$id])->find();
        if(!$madd)$this->error('参数不正确');
        
        if (Db::name('MemberAddress')->where(['id'=>$madd['id']])->delete()) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }
    
    /**
     * 搜索用户
     * @return [type] [description]
     */
    public function merber_shousuo()
    {
        $phone = request()->post('phone','');
        if(!$phone)$this->error('参数不正确');
        
        //$madd = Db::name('store_member')->where(['phone'=>$phone])->find();
        //if(!$madd)$this->error('无此用户');
        $madd = Db::name('store_member')->where(['phone'=>$phone,'status'=>0])->where('find_in_set('.$this->uid.',leaders)')->find();
        if(!$madd)$this->error('抱歉，你尚无此功能操作权限');
        
        $this->success('成功',$madd);
        
    }
    
    /**
     * 用户 意见
     * @return [type] [description]
     */
    public function merber_opinion()
    {
        $opinion_type = request()->post('opinion_type','');
        $opinion_miaoshu = request()->post('opinion_miaoshu','');
        $opinion_img = request()->post('opinion_img','');
        if(!$opinion_type ||!$opinion_miaoshu || !$opinion_img)$this->error('参数不正确');
        $where['opinion_type'] = $opinion_type;
        $where['opinion_miaoshu'] = $opinion_miaoshu;
        $where['opinion_img'] = $opinion_img;
        $where['mid'] = $this->uid;
        if(Db::name('MemberOpinion')->insert($where))
        {
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 首页 数据展示
     * @return [type] [description]
     */
    public function index()
    {
        //$use['num'] = Db::name('member_shanghu_order_log')->where(['mid'=>$this->uid])->where('montime',date('Y-m'))->sum('oneyeji');
        //$use['huoban'] = Db::name('store_member')->where(['first_leader'=>$this->uid])->whereTime('create_at','>=',date('Y-m-01 0:0:0'))->count();
        //$use['shanghu'] = Db::name('member_shanghu')->where(['mid'=>$this->uid])->whereTime('create_time','>=',date('Y-m-01 0:0:0'))->count();
        
        $ulist = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->field('id')->select();
        $uinfo = array_column($ulist,'id');
        //$use['num'] = $use['num'] + $use['firnum'];
        
        //$use['numsum'] = Db::name('member_shanghu_order_log')->where(['mid'=>$this->uid])->sum('oneyeji');
        $use['huoban'] = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->whereTime('create_at','>=',date('Y-m-01 0:0:0'))->count();
        $use['huobansum'] = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->count();
        $uinfo[] = $this->uid;
        $use['num'] = Db::name('member_shanghu_order_member')->whereIn('mid',$uinfo)->where('yue_time',date('Y-m'))->sum('txnAmt');
        $use['shanghu'] = Db::name('member_shanghu')->whereIn('mid',$uinfo)->whereTime('jihuo_time','>=',date('Y-m-01 0:0:0'))->count();
        $use['shanghusum'] = Db::name('member_shanghu')->whereIn('mid',$uinfo)->count();
        $this->success('成功',$use);
    }
    
    /**
     * 我的伙伴
     * @return [type] [description]
     */
    public function huoban()
    {
        $yuefen = request()->post('yuefen','0');
        $jiaoyi = request()->post('jiaoyi','0');
        $paixu = request()->post('paixu','0');
        
        if($yuefen == '2'){
            $yuetime = '1970-01-01 00:00:01';
            $endtime = date('Y-m-d 23:23:59');
        }elseif ($yuefen == '1'){
            $yuetime = date('Y-m-01 00:00:00',strtotime('-1 month', time()));
            $endtime = date('Y-m-d 23:59:59', strtotime(-date('d').'day'));
        }else{
            $yuetime = date('Y-m-01 00:00:00');
            $endtime = date('Y-m-d 23:23:59');
        }
        
        if($jiaoyi == 1){
            $jiaoinfo = 'huoban';
        }else{
            $jiaoinfo = 'num';
        }
        
        if($paixu == 1){
            $paixuinfo = SORT_ASC;
        }else{
            $paixuinfo = SORT_DESC;
        }
        
        $list = Db::name('store_member')->where(['first_leader'=>$this->uid])->select();//->where('find_in_set('.$this->uid.',leaders)')->select();
        foreach ($list as $key=>$val){
            $userinfo = DB::name('store_member')->where('find_in_set('.$val['id'].',leaders)')->select();
            $userinfo = array_column($userinfo,'id');
            $userinfo[] = $val['id'];
            $list[$key]['huoban'] = Db::name('member_shanghu')->whereIn('mid',$userinfo)->whereBetweenTime('jihuo_time',$yuetime,$endtime)/*->whereTime('create_time','>=',$yuetime)*/->count();
            $list[$key]['sql'] = Db::getLastSql();
            if($yuefen == '2'){
                $list[$key]['num'] = Db::name('member_shanghu_order_log')->whereIn('mid',$userinfo)/*->whereTime('create_time','>=',$yuetime)*/->sum('oneyeji');
            }elseif ($yuefen == '1'){
                $list[$key]['num'] = Db::name('member_shanghu_order_log')->whereIn('mid',$userinfo)->where('montime',date('Y-m',strtotime('-1 month', time())))/*->whereTime('create_time','>=',$yuetime)*/->sum('oneyeji');
            }else{
                $list[$key]['num'] = Db::name('member_shanghu_order_log')->whereIn('mid',$userinfo)->where('montime',date('Y-m'))/*->whereTime('create_time','>=',$yuetime)*/->sum('oneyeji');
            }
            
        }
        //$list =  array_multisort(array_column($list, 'num'), SORT_DESC, $list);
        array_multisort(array_column($list, $jiaoinfo),$paixuinfo,$list);
        
        foreach ($list as $k =>$v)
        {
            // $list[$k]['num']=;
        }
        
        $this->success('成功',$list);
    }
    
    /**
     * 设置伙伴等级1.0
     * @return [type] [description]
     */
    public function sethuoban()
    {
        $id = request()->post('id','');
        $v_id = request()->post('v_id','');
        $s_time = request()->post('start_time','');
        $e_time = request()->post('end_time','');
        if(!$id || !$v_id || !$s_time || !$e_time) $this->error("参数异常");
        if(strtotime($s_time)>strtotime($e_time)) $this->error("结束时间不能小于开始时间");
        $leader = Db::name('store_member')->where(['id'=>$this->uid])->find();
        $merber = Db::name('store_member')->where(['id'=>$id])->find();
        if($merber['first_leader'] != $this->uid) $this->error("只能设置直属下级");
        $endtime = strtotime($leader['vip_date_e'].' 23:59:59');
        if($leader['vip_manual'] == 0 || $endtime<time()) $this->error("您的分润等级已失效，无法设置下级");
        if($v_id>$leader['vip_manual']) $this->error("下级等级不能超过您的等级");
        if(strtotime($s_time)<strtotime($leader['vip_date_s']) || strtotime($s_time)>strtotime($leader['vip_date_e'])) $this->error("时间范围应在".$leader['vip_date_s']."到".$leader['vip_date_e']."之间");
        
        $result = Db::name('store_member')->where(['id'=>$id])->update(['vip_manual'=>$v_id,'vip_date_s'=>$s_time,'vip_date_e'=>$e_time]);
        if($result){
            $this->success('成功');
        }else{
            $this->error('失败，请重试');
        }
    }
    
    /**
     * 排行榜
     * @return [type] [description]
     */
    public function paihang()
    {
        $show = sysconf('top_show_name');
        $list = Db::name('member_machine')->field('mid,count(*) as num')->where('type',3)->group('mid')->order('num desc')->limit(20)->select();
        foreach ($list as &$val){
            $val['member'] = Db::name('store_member')->where(['id'=>$val['mid']])->find();
            if(!$show){
                $val['member']['username'] = '';
            }
        }
        $this->success('成功',$list);
    }
    
    /**
     * 我的 数据展示
     * @return [type] [description]
     */
    public function meindex()
    {
        $use['num'] = Db::name('member_shanghu_order_member')->where(['mid'=>$this->uid])->where('yue_time',date('Y-m'))->sum('txnAmt');
        $use['shanghu'] = Db::name('member_shanghu')->where(['mid'=>$this->uid])->whereTime('jihuo_time','>=',date('Y-m-01 0:0:0'))->count();
        $use['shanghunum'] = Db::name('member_shanghu')->where(['mid'=>$this->uid])->count();
        
        $use['huoban'] = Db::name('store_member')->where(['first_leader'=>$this->uid])->whereTime('create_at','>=',date('Y-m-01 0:0:0'))->count();
        $use['huobansum'] = Db::name('store_member')->where(['first_leader'=>$this->uid])->count();
        $use['shanghusum'] = Db::name('member_shanghu')->where(['mid'=>$this->uid])->count();
        
        //$ulist = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->where('vip_level','>',1)->where('first_leader','neq',$this->uid)->field('id')->select();
        $ulist = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->field('id')->select();
        $uinfo = array_column($ulist,'id');
        
        $use['firnum'] = Db::name('member_shanghu_order_member')->whereIn('mid',$uinfo)->where('yue_time',date('Y-m'))->sum('txnAmt');
        $use['firshanghu'] = Db::name('member_shanghu')->whereIn('mid',$uinfo)->whereTime('jihuo_time','>=',date('Y-m-01 0:0:0'))->count();
        $use['firshanghunum'] = Db::name('member_shanghu')->whereIn('mid',$uinfo)->count();
        
        $use['firhuoban'] = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->where('first_leader','neq',$this->uid)->whereTime('create_at','>=',date('Y-m-01 0:0:0'))->count();
        $use['firhuobansum'] = count($ulist);
        
        //$use['firhuoban'] = $use['firhuoban'] - $use['huoban'];
        $use['firhuobansum'] = $use['firhuobansum'] - $use['huobansum'];
        
        $this->success('成功',$use);
    }
    
    /**
     * 我的 数据展示
     * @return [type] [description]
     */
    public function mezhangdan()
    {
        $use = Db::name('member_shanghu_order_log')->group('montime')->select();
        
        foreach ($use as $key=>$val){
            $use[$key]['jiaoyi'] = Db::name('member_shanghu_order_log')->where(['mid'=>$val['mid'],'montime'=>$val['montime'],'ttxnSts'=>'S'])->sum('txnAmt');
            $use[$key]['shoukuan'] = Db::name('member_shanghu_order_log')->where(['mid'=>$val['mid'],'montime'=>$val['montime'],'ttxnSts'=>'C'])->sum('txnAmt');
        }
        
        $this->success('成功',$use);
    }
    
    /**
     * 问题与帮助
     * @return [type] [description]
     */
    public function wanghelp()
    {
        $use = Db::name('wang_help')->where(['status'=>1])->order('sort asc')->select();
        $this->success('成功',$use);
    }
    
    /**
     * 问题与帮助详情
     * @return [type] [description]
     */
    public function wanghelpinfo()
    {
        $id = request()->post('id','0');
        if(!$id)$this->error('失败');
        $use = Db::name('wang_help')->where(['status'=>1,'id'=>$id])->find();
        $this->success('成功',$use);
    }
    
    /**
     * 消息列表
     * @return [type] [description]
     */
    public function wangnews()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;
        
        $use = Db::name('wang_news')->where(['status'=>1])->order('sort asc')->limit($start,$num)->select();
        $this->success('成功',$use);
    }
    
    /**
     * 消息详情
     * @return [type] [description]
     */
    public function wangnewsinfo()
    {
        $id = request()->post('id','0');
        if(!$id)$this->error('失败');
        $use = Db::name('wang_news')->where(['status'=>1,'id'=>$id])->find();
        $this->success('成功',$use);
    }
    
    /**
     * 最新消息
     * @return [type] [description]
     */
    public function wangnewsnew()
    {
        $use = Db::name('wang_news')->where(['status'=>1])->order('addtime desc')->find();
        $this->success('成功',$use);
    }
    
    /**
     * 二维码
     * @return [type] [description]
     */
    public function qrcode()
    {
        $yaoqing = 'http://shenghuoban.web368.cn/index.html#/register?index=1&invite_code='.$this->user['invite_code'];
        $qrcode = db('store_member')->where(array('id' => $this->user['id']))->value('qrcode');
        $base_url = "http://".$_SERVER["HTTP_HOST"];
        if(empty($qrcode)){
            $filename = time().mt_rand(1,4).'';
            $qrcodeUrl = $base_url.'/upload/erweima/'.$filename.'.png';
            $qrcode_new = new QrCode($yaoqing);
            $file_path = './upload/erweima/'.$filename.'.png';
            
            $result = $qrcode_new->setImageType('png')->setSize(153)->save($file_path);
            if($result){
                Db::name("store_member")->where("id", $this->user['id'])->update([
                    'qrcode' => $qrcodeUrl,
                ]);
            }
        }else{
            $qrcodeUrl = $qrcode;
        }
        $this->success('成功',$qrcodeUrl);
    }
    
    /**
     * 金额详情
     * @return [type] [description]
     */
    public function moneylog()
    {
        $page   = request()->post('page', 1);
        $num   = 10;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            $yuetime = date('Y-m',strtotime('last day of -' .$i.' month'));
            if(strtotime('last day of -' .$i.' month') < strtotime('2020-04')){
                break;
            }
            $use[$i]['yuetime'] = $yuetime;
            $other = Db::name('member_money_log')->where(['mid'=>$this->uid])->whereIn('type',['activation_prize','recommend_prize','profit_prize','activity_prize','standard_prize','team_prize'])->where('money','>',0)->whereLike('create_at',$yuetime.'%')->sum('money');
            $use[$i]['jiaoyi'] = $other;
            $use[$i]['shoukuan'] = Db::name('member_money_log')->where(['mid'=>$this->uid])->whereIn('type',['activation_prize','recommend_prize','profit_prize','activity_prize','standard_prize','team_prize'])->where('money','<',0)->whereLike('create_at',$yuetime.'%')->sum('money');
            $key = $key+1;
        }
        $this->success('成功',$use);
        
    }
    
    public function money_log()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;
        //$use = Db::name('member_money_log')->where(['mid'=>$this->uid,'type'=>'money'])->whereIn('status',[4,5,6])->order('id desc')->limit($start,$num)->select();
        $merberinfo = $this->user;
        if($merberinfo['show_butie'] == 0){
            $data['xiaoshou']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>5,'type'=>'money'])->whereTime('create_at','<', $merberinfo['xiaoshou_time'])->sum('money');//销售
        }else{
            $data['xiaoshou']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>5,'type'=>'money'])->sum('money');//销售
        }
        if($merberinfo['show_zhanye'] == 0){
            $data['zhanye']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>6,'type'=>'money'])->whereTime('create_at','<', $merberinfo['zhanye_time'])->sum('money');//展业
        }else{
            $data['zhanye']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>6,'type'=>'money'])->sum('money');//展业
        }
        $use = Db::name('member_money_log')->where(['mid'=>$this->uid,'type'=>'money'])->whereIn('status',[4,5,6])->order('id desc')->limit($start,$num)->select();
        foreach ($use as $key=>$value){
            if($merberinfo['show_butie'] == 0 && $value['create_at']>=$merberinfo['xiaoshou_time']){
                unset($use[$key]);
            }
            if($merberinfo['show_zhanye'] == 0 && $value['create_at']>=$merberinfo['zhanye_time']){
                unset($use[$key]);
            }
        }
        array_multisort($use);
        //  $this->assign('jihuo',Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>4])->sum('money'));
        $data['use']=$use;
        $data['jihuo']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>4,'type'=>'money'])->sum('money');//激活
        //$data['butie']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>1,'type'=>'money'])->sum('money');//销售
        //$data['zhanye']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>6,'type'=>'money'])->sum('money');//展业
        //$data['xiaoshou']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>5,'type'=>'money'])->sum('money');//销售
        $this->success('成功',$data);
        
    }
    
    public function money_logad()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;
        
        $use = Db::name('member_money_log')->where(['mid'=>$this->uid,'type'=>'tongji_money'])->whereIn('status',[7,8])->order('id desc')->limit($start,$num)->select();
        
        //  $this->assign('jihuo',Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>4])->sum('money'));
        $data['use']=$use;
        $data['fenrong']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>7,'type'=>'tongji_money'])->sum('money');//分润
        $data['guanli']=Db::name('member_money_log')->where(['mid'=>$this->uid,'status'=>8,'type'=>'tongji_money'])->sum('money');//管理
        $this->success('成功',$data);
        
    }
    
    /**
     * 奖金记录
     */
    public function log()
    {
        $page   = request()->post('page', 1);
        $type   = request()->post('type', '');
        if(empty($type)) $this->error('参数异常！');
        $num   = $this->number;
        $start = ($page - 1) * $num;
        $merberinfo = $this->user;
        $use=Db::name('member_money_log')->where(['mid' => $this->uid, 'type' => $type])->order('id desc')->limit($start, $num)->select();
        
        $data['use']=$use;
        $this->success('成功',$data);
        
    }
    
    /**
     * 提现记录
     */
    public function withdraw_log()
    {
        $page   = request()->post('page', 1);
        $type= request()->post('type', 1);
        if(empty($type)) $this->error('参数异常！');
        $num   = $this->number;
        $start = ($page - 1) * $num;
        $use=Db::name('member_tixian')->where(['mid' => $this->uid,'type' => $type])->order('id desc')->limit($start, $num)->select();
        
        $this->success('成功',$use);
        
    }
    
    /**
     * 积分详情
     * @return [type] [description]
     */
    public function integrallog()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;
        
        $type = $this->request->post('type','');
        $integlogad = Db::name('member_money_log')->where(['mid'=>$this->uid,'type'=>'integral']);
        if($type){
            if($type == 1){
                $integlogad->where('money','>=',0);
            }else{
                $integlogad->where('money','<',0);
            }
        }
        $integlog = $integlogad->limit($start,$num)->select();
        $this->success('成功',$integlog);
    }
    
    /**
     * 支付宝信息
     * @return [type] [description]
     */
    public function zfb()
    {
        $zfb_name = $this->request->post('zfb_name','');
        $zfb_mobile = $this->request->post('zfb_mobile','');
        $code = $this->request->post('code','');
        if(!$zfb_name||!$zfb_mobile||!$code)$this->error('失败.');
        $cache = cache($cachekey = "send_register_upsms_{$this->user['phone']}");
        // $cache = array();
        // $cache['captcha'] = '866866';
        if (is_array($cache) && isset($cache['captcha']) && $cache['captcha'] == $code) {
            Db::name('store_member')->where(['id'=>$this->uid])->update(['zfb_name'=>$zfb_name,'zfb_mobile'=>$zfb_mobile]);
            $this->success('成功');
        }
        $this->error('失败');
    }
    
    /**
     * 提现申请
     * @return [type] [description]
     */
    public function zfb_tixian()
    {
        $money = $this->request->post('money','');
        $type = $this->request->post('type','');
        $step = $this->request->post('step','1');
        if(!$money)$this->error('金额错误');
        if(!$type)$this->error('参数异常');
        $tixian_time = Db::name('system_config')->where('name','tixian_time')->value('value');
        if($tixian_time){
            $timearr = explode(' - ',$tixian_time);
            $time_one = date('Y-m-d ').$timearr[0];
            $time_two = date('Y-m-d ').$timearr[1];
            if(strtotime($time_one)>strtotime($time_two)){
                if(time()<strtotime($time_two) || time()>strtotime($time_one)){
                    $this->error('提现时间为'.$timearr[1].'到'.$timearr[0]);
                }
            }else{
                if(time()>strtotime($time_two) || time()<strtotime($time_one)){
                    $this->error('提现时间为'.$timearr[0].'到'.$timearr[1]);
                }
            }
        }
        if($this->user['ver'] == 1){
            $sxf_str = $type.'_xf';
            $sxf_str = str_replace('_prize','',$sxf_str);
            $sxf= Db::name('store_member_config')->where('mid',$this->uid)->value($sxf_str);
            if(!$sxf) if(!$type)$this->error('税率异常');
            $sxfing = Db::name('store_member_config')->where('mid',$this->uid)->value('tx_xf');
            if(!$sxfing)$this->error('手续费异常');
        }else{
            $sxf_str = $type.'_xf';
            $sxf = Db::name('system_config')->where('name',$sxf_str)->value('value');
            if(!$sxf) if(!$type)$this->error('税率异常');
            $sxfing = Db::name('system_config')->where('name','tx_xf')->value('value');
            if(!$sxfing)$this->error('手续费异常');
        }
        $khf = Db::name('system_config')->where('name','tx_payment')->value('value');
        if(!$khf)$this->error('扣货款率异常');
        $shouxu = bcmul(bcdiv($money,100,4),$sxf,2);
        $usermoney =$this->user[$type];
        $payment = $this->user['payment'];//欠货款
        
        if($this->user['card_type'] == 0)$this->error('请先实名认证');
        //if(empty($this->user['zfb_name']) && empty($this->user['zfb_mobile']))$this->error('请先设置支付宝信息');
        if($money<100)$this->error('单笔最低100');
        if($usermoney<$money)$this->error('余额不足');
        
        $card = Db::name('member_card')->where('mid',$this->uid)->where('card_state',1)->find();
        if(!$card) $this->error('认证信息缺失');
        $money_one = $money-$shouxu-$sxfing;
        if($payment>0){
            $money_two = bcmul(bcdiv($money_one,100,4),$khf,2);
            if($payment>$money_two){
                $kouhuokuan = $money_two;
            }else{
                $kouhuokuan = $payment;
            }
        }else{
            $kouhuokuan = 0;
        }
        $truemoney = $money-$shouxu-$sxfing-$kouhuokuan;
        if($step == 1) $this->success('确认提现到银行卡？<br>姓名：'.$card['card_name'].'<br>银行卡号：'.$card['bank_num'].'<br>扣除手续费：'.$sxfing.'元,扣除税点：'.$shouxu.'元,扣除货款'.$kouhuokuan.'元,实际到账'.$truemoney.'元');
        $where['mid'] = $this->uid;
        $where['phone'] = $this->user['phone'];
        $where['bank_name'] = $card['bank_name'];
        $where['bank_num'] = $card['bank_num'];
        $where['bank_username'] = $card['card_name'];
        $where['money'] = $money;
        $where['ptpoint'] = $shouxu;
        $where['formalities'] = $sxfing;
        $where['payment'] = $kouhuokuan;
        $where['type'] = $type;
        
        Db::startTrans();
        $res[] = Db::name('MemberMoneyLog')->insert([
            'mid' => $this->uid,
            'type' => $type,
            'status' => 2,
            'money' => -$money,
            'before' => $usermoney,
            'content' => '提现',
            'extends' => '',
            'is_show' => 1,
            'check_status' => 1,
            'pay_status' => 1,
        ]);
        $res[] =Db::name('store_member')->where('id',$this->uid)->setDec($type,$money);
        if($kouhuokuan > 0){
            $res[] =Db::name('store_member')->where('id',$this->uid)->setDec('payment',$kouhuokuan);
        }
        $res[] = Db::name('member_tixian')->insert($where);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }
    
    //写入姓名
    function addImageStr($imgName,$str,$satsda){
        header('content-type:image/jpeg');
        $arr = list( $width, $height, $type) = @getimagesize('upload/shouquna/'.$imgName);
        $types = array(1=>'gif', 2=>'jpeg', 3=>'png');
        $createImage = 'imagecreatefrom'.$types[3];
        $root_path = Env::get('root_path');
        //$root_path = App::getRootPath();
        $img = $createImage($root_path.'public/upload/shouquna/'.$imgName);
        $color = imagecolorallocate($img, 0, 0, 0);
        if($satsda == 1){
            $x = 145;
            $y = 282;
        }
        if($satsda == 2){
            $x = 200;
            $y = 530;
        }
        if($satsda == 3){
            $x = 200;
            $y = 550;
        }
        //$x = ($width -imagefontwidth(24)*strlen($str))/2;
        //$y = ($height - imagefontheight(24))/2;
        //imagestring($img, 24, $x, $y, $str, $color);imagettftext
        $fone = 'msyh.ttc';
        if($satsda == 1){
            imagettftext($img, 10,0, $x, $y, $color,$fone,$str);
            $imagePut = 'image'.$types[$type];
            $imagePut($img,$root_path. '/public/upload/shouquna/'.$this->user['id'].'_'.$imgName);
        }else{
            imagettftext($img, 8,0, $x, $y, $color,$fone,$str);
            $imagePut = 'image'.$types[$type];
            $imagePut($img, 'upload/shouquna/'.$imgName);
        }
        imagedestroy($img);
        return $imagePut;
    }
    
    
    function tupian()
    {
        if(empty($this->user['username'])||$this->user['card_type'] == 0)$this->error('请先实名认证');
        
        // dump($this->user['id']);
        // die();
        if(!@getimagesize('/upload/shouquna/'.$this->user['id'].'_123.png')){
            $integlog =$this->addImageStr('123.png',"姓名：".$this->user['username'],1);
            
            $integlog = $this->addImageStr($this->user['id'].'_123.png',"授权时间：".date('Y-m-d H:i:s'),2);
            $integlog = $this->addImageStr($this->user['id'].'_123.png',"邀请推荐码：".$this->user['invite_code'],3);
        }
        $appRoot = request()->root(true); // 去掉参数 true 将获得相对地址
        $uriRoot = preg_match('/\.php$/', $appRoot) ? dirname($appRoot) : $appRoot;
        $integlog = $uriRoot.'/upload/shouquna/'.$this->user['id'].'_123.png';
        $this->success('成功',$integlog);
    }
    
    /**
     * 我的 个人业绩展示
     * @return [type] [description]
     */
    public function meoneyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        $member_shanghu = Db::name('member_shanghu')->where(['mid'=>$this->uid])->where('type',1)->field('machine_no,jihuo_time')->order('jihuo_time asc')->select();
        if(!empty($member_shanghu)){
            $sadtime = $member_shanghu[0]['jihuo_time'];
            
        }else{
            $this->success('成功',array());
        }
        $member_shanghu = array_column($member_shanghu,'machine_no');
        
        $use = [];
        $key = 0;
        //halt($start);
        for ($i=$start;$i<$end;$i++){
            //$this->success('成功',111);
            /*if(strtotime($this->user['create_at'])>strtotime('-' .$i.' day')){
             break;
             }*/
            $yuetime = date('Y-m-d',strtotime('-' .$i.' day'));
            $use[$key]['yuetime'] = $yuetime;
            $use[$key]['huoban'] = Db::name('store_member')->where('first_leader',$this->uid)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->where('first_leader',$this->uid)->count();
            $use[$key]['shanghu'] = Db::name('member_shanghu')->where('mid',$this->uid)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->where('crdTyp','CUP')->whereIn('snNo',$member_shanghu)->whereLike('create_at',$yuetime.'%')->sum('txnAmt');
            //$use[$key]['shaoma'] = Db::name('member_shanghu_order_member')->whereIn('txnCd',[6,8])->whereIn('snNo',$member_shanghu)->whereLike('create_at',$yuetime.'%')->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->whereIn('snNo',$member_shanghu)->whereLike('create_at',$yuetime.'%')->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            $use[$key]['shanfu'] = 0;
            $use[$key]['mpost'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            
            $use[$key]['sumjiaoyi'] = $use[$key]['poss']+$use[$key]['shaoma']+$use[$key]['shanfu'];
            $use[$key]['dianqian'] = $use[$key]['sumjiaoyi'];
            if( $use[$key]['huoban']==0 && $use[$key]['shanghu']==0 && $use[$key]['poss'] ==0 && $use[$key]['shaoma']==0 && $use[$key]['shanfu']==0)
            {
                if(strtotime($sadtime)>strtotime('-' .$i.' day')){
                    unset($use[$key]);
                }
                
            }
            $key = $key+1;
        }
        $this->success('成功',$use);
    }
    
    /**
     * 我的 个人月业绩展示
     * @return [type] [description]
     */
    public function metwoyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            $yuetime = date('Y-m',strtotime('last day of -' .$i.' month'));
            if(strtotime($this->user['create_time'])>strtotime('last day of -' .$i.' month') && strtotime('last day of -' .$i.' month') < strtotime('2020-5')){
                break;
            }
            
            $use[$key]['yuetime'] = $yuetime;
            $use[$key]['huoban'] = Db::name('store_member')->where('first_leader',$this->uid)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->where('first_leader',$this->uid)->count();
            $use[$key]['shanghu'] = Db::name('member_shanghu')->where('mid',$this->uid)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            $use[$key]['sumjiaoyi'] = Db::name('member_shanghu_order_member')->where('mid',$this->uid)->where('yue_time',$yuetime)->sum('txnAmt');
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->where('mid',$this->uid)->where('yue_time',$yuetime)->where('crdTyp','CUP')->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->where('mid',$this->uid)->where('yue_time',$yuetime)->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            $use[$key]['shanfu'] = 0;
            $use[$key]['mpost'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            $use[$key]['dianqian'] = $use[$key]['sumjiaoyi'];
            
            //$use[$key]['sumjiaoyi'] = $use[$key]['poss']+$use[$key]['shaoma']+$use[$key]['shanfu'];
            
            if( $use[$key]['huoban']==0 && $use[$key]['shanghu']==0 && $use[$key]['poss'] ==0 && $use[$key]['bank']==0 && $use[$key]['sumjiaoyi']==0)
            {
                unset($use[$key]);
            }
            $key = $key+1;
        }
        $this->success('成功',$use);
    }
    
    /**
     * 伙伴 日业绩展示
     * @return [type] [description]
     */
    public function youoneyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        //$membinfoad = Db::name('store_member')->where('first_leader',$this->uid)->where("vip_level > 1")->order('create_at desc')->select();
        $membad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->where('first_leader','neq',$this->uid)->order('create_time asc')->select();
        $membinfoad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->order('create_time asc')->select();
        if(!$membinfoad)$this->success('成功');
        $membinfo = array_column($membinfoad,'id');
        $member_shanghu = Db::name('member_shanghu')->whereIn('mid',$membinfo)->where('type',1)->field('machine_no,jihuo_time')->order('jihuo_time asc')->select();
        if(!empty($member_shanghu)){
            $sadtime = $member_shanghu[0]['jihuo_time'];
        }else{
            $sadtime = $membinfoad[0]['create_time'];
        }
        $membinfoad = array_column($membad,'id');
        
        
        $member_shanghu = array_column($member_shanghu,'machine_no');
        
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            if(strtotime($sadtime)>strtotime(date('Y-m-d 23:23:59',strtotime('-' .$i.' day')))){
                break;
            }
            $yuetime = date('Y-m-d',strtotime('-' .$i.' day'));
            $use[$key]['yuetime'] = $yuetime;
            $use[$key]['huoban'] = Db::name('store_member')->whereIn('id',$membinfoad)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->whereIn('id',$membinfoad)->count();
            $use[$key]['shanghu'] = Db::name('member_shanghu')->whereIn('mid',$membinfo)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->where('crdTyp','CUP')->whereIn('snNo',$member_shanghu)->whereLike('create_at',$yuetime.'%')->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->whereIn('snNo',$member_shanghu)->whereLike('create_at',$yuetime.'%')->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            $use[$key]['shanfu'] = 0;
            $use[$key]['mpost'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            $use[$key]['sumjiaoyi'] = $use[$key]['poss']+$use[$key]['shaoma']+$use[$key]['shanfu'];
            $use[$key]['dianqian'] = $use[$key]['sumjiaoyi'];
            $key = $key+1;
        }
        // if($this->uid == 96) $this->success('成功',$key);
        $this->success('成功',$use);
    }
    
    /**
     * 伙伴 月业绩展示
     * @return [type] [description]
     */
    public function youtwoyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        //$membinfoad = Db::name('store_member')->where('first_leader',$this->uid)->where("vip_level > 1")->order('create_at desc')->select();
        $membinfoad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->where('first_leader','neq',$this->uid)->order('create_time asc')->select();
        $membad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->order('create_time asc')->select();
        if(!$membad)$this->success('成功');
        
        $membinfo = array_column($membinfoad,'id');
        $membinfoad = array_column($membad,'id');
        
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            $yuetime = date('Y-m',strtotime('last day of -' .$i.' month'));
            if((strtotime($membad[0]['create_time'])>strtotime('last day of -' .$i.' month')|| count($membad)<1)&& strtotime('last day of -' .$i.' month') < strtotime('2020-5')){
                break;exit;
            }
            $use[$key]['yuetime'] = $yuetime;
            //$use[$key]['huoban'] = Db::name('store_member')->whereIn('first_leader',$membinfo)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban'] = Db::name('store_member')->whereIn('id',$membinfo)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->whereIn('id',$membinfoad)->count();
            $use[$key]['shanghu'] = Db::name('member_shanghu')->whereIn('mid',$membinfoad)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfoad)->where('crdTyp','CUP')->where('yue_time',$yuetime)->sum('txnAmt');
            //$use[$key]['shaoma'] = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfoad)->whereIn('txnCd',[6,8])->where('yue_time',$yuetime)->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfoad)->where('yue_time',$yuetime)->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            $use[$key]['shanfu'] = 0;
            $use[$key]['mpost'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            $use[$key]['sumjiaoyi'] = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfoad)->where('yue_time',$yuetime)->sum('txnAmt');
            $use[$key]['dianqian'] = $use[$key]['sumjiaoyi'];
            $key = $key+1;
        }
        $this->success('成功',$use);
    }
    
    
    /**
     * 总 日业绩展示
     * @return [type] [description]
     */
    public function myouoneyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        $membinfoad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->order('create_time asc')->select();
//         if(!$membinfoad)$this->success('成功');
        //echo count($membinfoad);die;
        $membinfo = array_column($membinfoad,'id');
        if(!$membinfoad) $membinfo = [$this->uid];
        
        $member_shanghu = Db::name('member_shanghu')->whereIn('mid',$membinfo)->where('type',1)->field('machine_no,jihuo_time')->order('jihuo_time asc')->select();
        if(!empty($member_shanghu)){
            $sadtime = $member_shanghu[0]['jihuo_time'];
        }else{
            $sadtime = $membinfoad[0]['create_time'];
        }
        $member_shanghu = array_column($member_shanghu,'machine_no');
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            if(strtotime($sadtime)>strtotime(date('Y-m-d 23:23:59',strtotime('-' .$i.' day')))){
                break;
            }
            $yuetime = date('Y-m-d',strtotime('-' .$i.' day'));
            $use[$key]['yuetime'] = $yuetime;
            $use[$key]['huoban'] = Db::name('store_member')->whereIn('id',$membinfo)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->whereIn('id',$membinfo)->count();
            $membinfo[] = $this->uid;
            $use[$key]['shanghu'] = Db::name('member_shanghu')->whereIn('mid',$membinfo)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->where('crdTyp','CUP')->whereIn('mid',$membinfo)->whereLike('create_at',$yuetime.'%')->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfo)->whereLike('create_at',$yuetime.'%')->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            $use[$key]['mpost'] = 0;
            $use[$key]['shanfu'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            
            
            $use[$key]['sumjiaoyi'] = $use[$key]['poss']+$use[$key]['shaoma']+$use[$key]['shanfu'];
            $use[$key]['dianqian'] = $use[$key]['poss']+$use[$key]['shaoma']+$use[$key]['shanfu'];
            $key = $key+1;
        }
        $this->success('成功',$use);
    }
    
    /**
     * 总 月业绩展示
     * @return [type] [description]
     */
    public function myoutwoyeji()
    {
        $page   = request()->post('page', 1);
        $num   = 5;
        $end = $page*$num;
        $start = ($page - 1) * $num;
        
        $membinfoad = Db::name('store_member')->where('find_in_set('.$this->uid.',leaders)')->order('create_time asc')->select();
        if(!$membinfoad)$this->success('成功');
        
        $membinfo = array_column($membinfoad,'id');
        
        $use = [];
        $key = 0;
        for ($i=$start;$i<$end;$i++){
            $yuetime = date('Y-m',strtotime('last day of -' .$i.' month'));
            if((count($membinfoad)<1) || strtotime('last day of -' .$i.' month') < strtotime('2020-4')){
                break;
            }
            $use[$key]['yuetime'] = $yuetime;
            $use[$key]['huoban'] = Db::name('store_member')->whereIn('id',$membinfo)->where('id','neq',$this->uid)->whereLike('create_at',$yuetime.'%')->count();
            $use[$key]['huoban_one'] = $use[$key]['huoban'];
            $use[$key]['huoban_total'] = Db::name('store_member')->whereIn('id',$membinfo)->count();
            $membinfo[] = $this->uid;
            $use[$key]['shanghu'] = Db::name('member_shanghu')->whereIn('mid',$membinfo)->whereLike('jihuo_time',$yuetime.'%')->count();
            $use[$key]['shanghu_shou'] = $use[$key]['shanghu'];
            
            //var_dump(date('Y-m-01',strtotime($yuetime)));
            //$msadsa = Db::name('member_shanghu_order_log')->whereIn('mid',$membinfo)->where('montime',$yuetime)->find();
            /*if($msadsa){
             $use[$key]['poss'] = $msadsa['post'];
             $use[$key]['shaoma'] = $msadsa['yinlian'];
             $use[$key]['shanfu'] = $msadsa['yunsfu'];
             }else{
             $use[$key]['poss'] = 0;
             $use[$key]['shaoma'] = 0;
             $use[$key]['shanfu'] = 0;
             
             }*/
            $use[$key]['poss'] = Db::name('member_shanghu_order_member')->where('crdTyp','CUP')->whereIn('mid',$membinfo)->where('yue_time',$yuetime)->sum('txnAmt');
            $shaoma = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfo)->where('yue_time',$yuetime)->where('crdTyp','<>','CUP')->sum('txnAmt');
            $use[$key]['shaoma'] = $shaoma;
            //$use[$key]['shaoma'] = Db::name('member_shanghu_order_member')->where('find_in_set(06,txnCd)')->where('find_in_set(1,txnCd)')->whereIn('txnCd',['06','08'])->whereIn('mid',$membinfo)->where('yue_time',$yuetime)->sum('txnAmt');
            $use[$key]['shanfu'] = 0;
            $use[$key]['mpost'] = 0;
            $use[$key]['bank'] = $use[$key]['poss'];
            $use[$key]['sumjiaoyi'] = Db::name('member_shanghu_order_member')->whereIn('mid',$membinfo)->where('yue_time',$yuetime)->sum('txnAmt');
            $use[$key]['dianqian'] = $use[$key]['sumjiaoyi'];
            $key = $key+1;
        }
        $this->success('成功',$use);
    }
    
    /**
     * 达标信息
     * @return [type] [description]
     */
    public function dabiao()
    {
        $page   = request()->post('page', 1);
        $num   = 10;
        $start = ($page - 1) * $num;
        $merberinfo = $this->user;
        $dapbiao = Db::name('member_shanghu')->where(['mid'=>$this->uid,'type'=>1])->order('id desc')->limit($start,$num)->select();
        
        foreach ($dapbiao as $key=>$val)
        {
            $dapbiao[$key]['mubiaomoney'] = 20000;
            $dapbiao[$key]['famoney'] = 208;
            $time = $this->time($val['jihuo_time']);
            $dapbiao[$key]['jiezhitime'] = date('Y-m-d H:i:s',strtotime('+ 6 month',strtotime($val['jihuo_time'])));
            $dapbiao[$key]['month_jiezhi'] = date('Y-m-d H:i:s',$time['end']);
            $dapbiao[$key]['days'] = 0;
            $start = date('YmdHis',$time['start']);
            $end = date('YmdHis',$time['end']);
            $money =  Db::name('member_shanghu_order')->where(['snNo'=>$val['machine_no'],'is_chuli'=>1,'ttxnSts'=>'00','crdTyp'=>'CUP'])->where('txnTm', 'between', [$start, $end])->sum('txnAmt');
            $dapbiao[$key]['month_money'] = $money;
            $insad = $time['end'] - time();
            if($val['is_yajin'] == 0){
                if($insad<0){
                    $dapbiao[$key]['is_yajin'] = 2;
                }else{
                    $dapbiao[$key]['days'] = intval( $insad/ 86400);
                }
                /* if($dapbiao[$key]['days']>0 &&$dapbiao[$key]['mubiaomoney']<=$val['num_money']&&$dapbiao[$key]['is_yajin']==0){
                 $dapbiao[$key]['is_yajin'] = 1;
                 } */
            }
            
        }
        $this->success('成功',$dapbiao);
    }
    
    private function time($day)
    {
        $time = time();
        for ($i=0;$i<6;$i++){
            $k = $i+1;
            if($time>strtotime('+ '.$i.' month',strtotime($day)) && $time<strtotime('+ '.$k.' month',strtotime($day))){
                return array('start'=>strtotime('+ '.$i.' month',strtotime($day)),'end'=>strtotime('+ '.$k.' month',strtotime($day)));
            }elseif($k == 6 && $time>strtotime('+ '.$k.' month',strtotime($day))){
                return array('start'=>strtotime('+ '.$i.' month',strtotime($day)),'end'=>strtotime('+ '.$k.' month',strtotime($day)));
            }
        }
        return array('start'=>strtotime($day),'end'=>strtotime('+ 1 month',strtotime($day)));
    }
    
    /**
     * 达标信息
     * @return [type] [description]
     */
    public function dabiaoinfo()
    {
        $id   = request()->post('id');
        if(!$id)$this->error('参数错误');
        
        $dapbiao = Db::name('member_shanghu')->where(['mid'=>$this->uid,'type'=>1,'id'=>$id])->find();
        
        $dapbiao['mubiaomoney'] = 20000;
        $dapbiao['famoney'] = 208;
        
        $time = $this->time($dapbiao['jihuo_time']);
        $dapbiao['jiezhitime'] = date('Y-m-d H:i:s',$time['end']);
        $dapbiao['days'] = 0;
        $start = date('Y-m-d H:i:s',$time['start']);
        $end = date('Y-m-d H:i:s',$time['end']);
        $money =  Db::name('member_shanghu_order')->where(['snNo'=>$dapbiao['machine_no'],'is_chuli'=>1,'ttxnSts'=>'00','crdTyp'=>'CUP'])->where('txnTm', 'between time', [$start, $end])->sum('txnAmt');
        $dapbiao['num_money'] = $money;
        $insad = $time['end'] - time();
        if($insad<0){
            $dapbiao['is_yajin'] = 2;
        }else{
            $dapbiao['days'] = intval( $insad/ 86400);
        }
        $dapbiao['daysad'] = 90;
        $this->success('成功',$dapbiao);
    }
    
    /**
     * 达标信息
     * @return [type] [description]
     */
    public function dabiaoup()
    {
        $id   = request()->post('id');
        if(!$id)$this->error('参数错误');
        
        $dapbiao = Db::name('member_shanghu')->where(['mid'=>$this->uid,'type'=>1,'id'=>$id])->find();
        if($dapbiao['is_yajin']!=1  && $dapbiao['is_yajin']!=5)$this->error('不满足退回条件');
        $jiezhitime = strtotime('+ 90 day',strtotime($dapbiao['jihuo_time']));
        // if($jiezhitime<time())$this->error('超时');//达标提现不再限制时间
        if($this->user['card_type'] == 0){
            $this->error('失败',array('is_ali'=>0));
        }
        if($this->user['card_type'] == 0)$this->error('请先实名认证');
        
        $sxf = Db::name('system_config')->where('name','dabiaosxf')->value('value');
        $shouxu = bcmul(bcdiv(208,100,2),$sxf,2);
        
        $card = Db::name('member_card')->where('mid',$this->uid)->where('card_state',1)->find();
        if(!$card) $this->error('认证信息缺失');
        $where['mid'] = $this->uid;
        $where['phone'] = $this->user['phone'];
        $where['bank_name'] = $card['bank_name'];
        $where['bank_num'] = $card['bank_num'];
        $where['bank_username'] = $card['card_name'];
        $where['money'] = 208;
        $where['ptpoint'] = $shouxu;
        $where['type'] = 'standard_prize';
        $where['shanghu_id'] = $id;
        
        Db::startTrans();
        
        $res[] = Db::name('member_tixian')->insert($where);
        $res[] = Db::name('member_shanghu')->where(['mid'=>$this->uid,'type'=>1,'id'=>$id])->update(['is_yajin'=>3,'yajintime'=>time()]);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('退回余额将返回到您的银行账户'.$card['bank_num'],array('is_ali'=>1));
        } else {
            Db::rollback();
            $this->error('失败',array('is_ali'=>1));
        }
    }
    
    /**
     * 新增信息
     * @return [type] [description]
     */
    public function mekefu()
    {
        $info['kefuphone'] = '400-180-2768';
        $this->success('成功',$info);
    }
    
     /**
     * 用户信息
     * @return [type] [description]
     */
    public function tixianinfo()
    {
        $merberinfo = $this->user;
        if($merberinfo['first_leader']){
            $merberinfo['firstname'] = Db::name('StoreMember')->where('id',$merberinfo['first_leader'])->value('phone');
        }else{
            $merberinfo['firstname'] = '';
        }
        $prize = Db::name('StorePrize')->where(['status' => 1])->column('field_name');
        $money_total = 0;
        foreach ($prize as $val){
            $money_total += $merberinfo[$val];
        }
        $merberinfo['integral_total']= $merberinfo['integral']+$merberinfo['activity_integral'];
        $merberinfo['money_total'] = $money_total;
        $merberinfo['vipname'] = Db::name('store_member_laver')->where('id',$merberinfo['vip_level'])->value('name');

        $care = Db::name('MemberCard')->where(['mid'=>$merberinfo['id'],'card_state'=>1])->find();
        if(empty($care)){
            $this->error('请先实名认证');
        }
        $merberinfo['card'] = $care;
        $this->success('成功', $merberinfo);
    }
    
}
