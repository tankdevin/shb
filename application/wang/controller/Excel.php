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

namespace app\wang\controller;

use library\Controller;
use think\Db;

use library\tools\Csv;

/**
 * 会员信息管理
 * Class Member
 * @package app\store\controller
 */
class Excel extends Controller
{
    
    
    function memberlist()
    {
        set_time_limit(0);
        $name = '伙伴信息_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴编号','伙伴级别','注册时间','手机号','是否实名','推荐人姓名','推荐人编号','推荐人级别','入库（台数）','出库（台数）','已激活机具数','未激活机具数','商户转入','商户转出','我的积分');
        $hea = array('username','userno','jibie','zhuchesjian','phone','shiming','tuijianname','tuijianno','tuijianjibie','ruku','chuku','jihuois','jihuono','zhuanru','zhuanchu','mejifen');
        
        $tuijianname = $this->request->get('tuijianname','');
        $tuijianid = $this->request->get('tuijianid','');
        $vip_level = $this->request->get('vip_level','');
        $create_at = $this->request->get('create_at','');
        
        $first_leader= '';
        if($tuijianname){
            $first_leader = Db::name('store_member')->where('username',$tuijianname)->value('id');
        }
        if($tuijianid){
            $first_leader = Db::name('store_member')->where('invite_code',$tuijianid)->value('id');
        }
        $query = Db::name('store_member');
        if($tuijianname || $tuijianid){
            $query = $query->where('first_leader',$first_leader);
        }
        if($vip_level){
            $query = $query->where('vip_level',$vip_level);
        }
        if($create_at){
            $create_at = explode(' - ', $create_at);
            $create_at[1] = $create_at[1].' 23:59:59';
            $query = $query->where('create_at', 'between time', [$create_at[0], $create_at[1]]);
        }
        $list = $query->order('id asd')->select();
        $merinfo = [];
        if($list){
            foreach ($list as $key =>$val){
                $merinfo[$key]['username'] = $val['username'];
                $merinfo[$key]['userno'] = $val['invite_code'];
                $merinfo[$key]['jibie'] = Db::name('store_member_laver')->where('id',$val['vip_level'])->value('name');
                $merinfo[$key]['zhuchesjian'] = $val['create_at'];
                $merinfo[$key]['phone'] = $val['phone'];
                $merinfo[$key]['shiming'] = $val['card_type'] == 1?'是':'否';
                
                if($val['first_leader']){
                    $firmem = Db::name('store_member')->where('id',$val['first_leader'])->find();
                    $merinfo[$key]['tuijianname'] = $firmem['username'];
                    $merinfo[$key]['tuijianno'] = $firmem['invite_code'];
                    $merinfo[$key]['tuijianjibie'] = Db::name('store_member_laver')->where('id',$firmem['vip_level'])->value('name');
                }else{
                    $merinfo[$key]['tuijianname'] = '';
                    $merinfo[$key]['tuijianno'] = '';
                    $merinfo[$key]['tuijianjibie'] = '';
                }
                
                $merinfo[$key]['chuku'] = Db::name('member_machine_log')->where(['form_mid'=>$val['id'],'type'=>1])->count();
                $merinfo[$key]['jihuois'] = Db::name('member_shanghu')->where(['type'=>1])->where('mid',$val['id'])->count();
                $merinfo[$key]['jihuono'] = Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>1])->count();
//                 $merinfo[$key]['ruku'] = Db::name('member_machine_log')->where(['mid'=>$val['id'],'type'=>2])->sum('num');
                $merinfo[$key]['ruku'] = $merinfo[$key]['jihuono'] + $merinfo[$key]['chuku'] + $merinfo[$key]['jihuois'];
                
                $merinfo[$key]['zhuanru'] = Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>7])->count();
                $merinfo[$key]['zhuanchu'] = Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>6])->count();
                
                $merinfo[$key]['mejifen'] = $val['integral'];
            }
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$merinfo,$hea);
    }
    
    //机具导出
    function jijulist()
    {
        set_time_limit(0);
        $name = '激活机具_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('机具sn号','商户名称','商户手机号','伙伴姓名','伙伴编号','伙伴手机号','是否达标','达标时间','post刷卡金额','激活时间');
        $hea = array('machine_no','shanghu_name','shanghu_phone','username','pid','phone','dabiao','update_time','num_money','jihuo_time');
        
        $machine_no = $this->request->get('machine_no','');
        $userid = $this->request->get('userid');
        $userphone = $this->request->get('userphone');
        $dabiao = $this->request->get('dabiao');
        $where = [];
        if($userid){
            $where['mid'] = Db::name('store_member')->where('invite_code',$userid)->value('id');
        }
        if($userphone){
            $where['mid'] = Db::name('store_member')->where('phone',$userphone)->value('id');
        }
        
        if($machine_no){
            $where['machine_no'] = $machine_no;
        }
        $query = Db::name('member_shanghu')->where($where)->where('type',1);
        if($dabiao == 1){
            $query->where('num_money','>=',5000);
        }
        if($dabiao == 2){
            $query->where('num_money','<',5000);
        }
        
        $list = $query->order('id desc')->select();
        foreach ($list as $key =>$val){
            
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $list[$key]['dabiao'] = $val['num_money']>=5000?'达标':'不达标';
            
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //我的积分具导出
    function jifenlist()
    {
        set_time_limit(0);
        $type = $this->request->get('type','');
        switch ($type){
            case 'integral':
                $name = '通用积分_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'activity_integral':
                $name = '活动积分_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'activation_prize':
                $name = '激活奖_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'recommend_prize':
                $name = '推荐奖_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'profit_prize':
                $name = '分润奖_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'activity_prize':
                $name = '活动奖_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'standard_prize':
                $name = '达标奖_'.date('Y-m-d H:i:s').'.csv';
                break;
            case 'team_prize':
                $name = '团队奖_'.date('Y-m-d H:i:s').'.csv';
                break;
        }
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','金额','机具SN','商户名称','详情','时间');
        $hea = array('username','phone','pid','money','extends','shanghu_name','content','create_at');
        
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        $list = Db::name('member_money_log')
        ->where('type',$type)->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $member = Db::name('member_shanghu')->where('machine_no',$val['extends'])->find();
            $list[$key]['shanghu_name'] = $member['shanghu_name'];
            $list[$key]['shanghu_phone'] = $member['shanghu_phone'];
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //激活津贴导出
    function jihuolist()
    {
        set_time_limit(0);
        $name = '激活津贴_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','奖励','机具SN','商户名称','详情','时间');
        $hea = array('username','phone','pid','money','extends','shanghu_name','content','create_at');
        
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        
        $list = Db::name('member_money_log')->where('type','money')->where('status',4)
        ->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $member = Db::name('member_shanghu')->where('machine_no',$val['extends'])->find();
            $list[$key]['shanghu_name'] = $member['shanghu_name'];
            $list[$key]['shanghu_phone'] = $member['shanghu_phone'];
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //展业津贴导出
    function zhanyelist()
    {
        set_time_limit(0);
        $name = '展业津贴_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','达标商户','奖励金额','达标时间');
        $hea = array('username','phone','pid','extends','money','create_at');
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        $list = Db::name('member_money_log')->where('type','money')->where('status',6)
        ->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //销售津贴导出
    function xiaoshoulist()
    {
        set_time_limit(0);
        $name = '销售津贴_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','伙伴级别','奖励金额','奖励时间','新注册伙伴姓名','新注册伙伴编号','新注册伙伴手机号','新注册伙伴级别');
        $hea = array('username','phone','pid','vip_level','money','create_at','shanghu_name','did','shanghu_phone','pvip_level');
        
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        
        $list = Db::name('member_money_log')->where('type','money')->where('status',5)
        ->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $list[$key]['vip_level'] = Db::name('store_member_laver')->where('id',$member['vip_level'])->value('name');
            $memberad = Db::name('store_member')->where('id',$val['extends'])->find();
            $list[$key]['shanghu_name'] = $memberad['username'];
            $list[$key]['shanghu_phone'] = $memberad['phone'];
            $list[$key]['did'] = $memberad['invite_code'];
            $list[$key]['pvip_level'] = Db::name('store_member_laver')->where('id',$memberad['vip_level'])->value('name');
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //分润津贴导出
    function fenrunlist()
    {
        set_time_limit(0);
        $name = '分润津贴_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','伙伴级别','分润收益','奖励时间');
        $hea = array('username','phone','pid','vip_level','money','create');
        
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        
        $list = Db::name('member_money_log')->where('type','tongji_money')->where('status',7)
        ->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $list[$key]['vip_level'] = Db::name('store_member_laver')->where('id',$member['vip_level'])->value('name');
            $list[$key]['create'] = date('Y-m',strtotime($val['create_at']));
        }//halt($list);
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //管理津贴导出
    function guanlilist()
    {
        set_time_limit(0);
        $name = '管理津贴_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','伙伴级别','管理津贴','达标','时间');
        $hea = array('username','phone','pid','vip_level','money','dabiao','create');
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        $where = [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($create){
            $create_at = explode(' - ',$create);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at[0] = date('Y-m-d H:i:s',strtotime('-50 year'));
            $create_at[1] = date('Y-m-d H:i:s',time());
        }
        $list = Db::name('member_money_log')->where('type','tongji_money')->where('status',8)
        ->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])
        ->order('id desc')->select();
        foreach ($list as $key =>$val){
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $list[$key]['vip_level'] = Db::name('store_member_laver')->where('id',$member['vip_level'])->value('name');
            $list[$key]['create'] = date('Y-m',strtotime($val['create_at']));
            
            $memberad = Db::name('store_member')->whereIn('id',$val['extends'])->select();
            $dabiao = '';
            foreach ($memberad as $v){
                $dabiao = $v['username'].'-'.$v['phone'].'|'.$dabiao;
            }
            $list[$key]['dabiao'] = $dabiao;
            
        }//halt($list);
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //押金导出
    function yajinlist()
    {
        set_time_limit(0);
        $name = '押金_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('SN号','商户名称','商户手机','激活时间','当前交易量','目标交易量','达标退还押金','伙伴姓名','伙伴手机','伙伴编号','伙伴级别','支付宝姓名','支付宝账号','状态');
        
        $hea = array('machine_no','shanghu_name','shanghu_phone','jihuo_time','num_money','jiaoyil','dabiao','username','phone','pid','vip_level','zfb_name','zfb_mobile','is_yajin');
        $phone = $this->request->get('phone','');
        $dabiao_time = $this->request->get('dabiao_time','');
        $where= [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        $list = Db::name('member_shanghu')->where($where);
        if($dabiao_time){
            $sdad = explode(' - ',$dabiao_time);
            $sdad[1] = $sdad[1].' 23:59:59';
            $list->whereBetweenTime('dabiao_time',$sdad[0],$sdad[1]);
        }
        $list = $list->where('type',1)->whereIn('is_yajin','1,3,4,5')->order('id desc')->select();
        foreach ($list as $key =>$val){
            
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] = $member['username'];
            $list[$key]['phone'] = $member['phone'];
            $list[$key]['pid'] = $member['invite_code'];
            $list[$key]['vip_level'] = Db::name('store_member_laver')->where('id',$member['vip_level'])->value('name');
            $list[$key]['zfb_name'] = $member['zfb_name'];
            $list[$key]['zfb_mobile'] = $member['zfb_mobile'];
            $list[$key]['jiaoyil'] = 100000;
            $list[$key]['dabiao'] = 196;
            switch ($val['is_yajin'])
            {
                case 1:
                    $list[$key]['is_yajin']= '已达标';
                    break;
                case 3:
                    $list[$key]['is_yajin']= '已申请';
                    break;
                case 4:
                    $list[$key]['is_yajin']= '已通过';
                    break;
                case 5:
                    $list[$key]['is_yajin']= '已拒绝';
                    break;
            }
        }//halt($list);
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    function tixianlist()
    {
        set_time_limit(0);
        $name = '提现信息_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴编号','伙伴级别','提现金额','平台服务费','实到金额','支付宝信息','类型','提现时间','审批时间','状态');
        $hea = array('username','userno','jibie','money','ptpoint','shidao','zfb','leixing','create_at','update_at','zhuangtao');
        
        $phone = $this->request->get('phone','');
        $type = $this->request->get('type','');
        $create_at = $this->request->get('create_at','');
        $status = $this->request->get('status','');
        
        $where= [];
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
            $where['mid'] = $first_leader;
        }
        if($type){
            $where['type'] = $type;
        }
        if($status != ''){
            $where['status'] = $status;
        }
        $list = Db::name('member_tixian')->where($where);
        
        $wheread = [];
        if($create_at){
            $sdad = explode(' - ',$create_at);
            $sdad[1] = $sdad[1].' 23:59:59';
            
            $list->whereBetweenTime('create_at',$sdad[0],$sdad[1]);
        }
        $list = $list ->order('id desc')->select();
        if($list){
            foreach ($list as $key =>$val){
                $member = Db::name('store_member')->where('id',$val['mid'])->find();
                $list[$key]['username'] = $member['username'];
                $list[$key]['userno'] = $member['invite_code'];
                $list[$key]['jibie'] = Db::name('store_member_laver')->where('id',$member['vip_level'])->value('name');
                $list[$key]['shidao'] = $val['money'] - $val['ptpoint'];
                $list[$key]['zfb'] = '支付宝姓名：'.$val['zfb_name'].'/ 支付宝账号：'.$val['zfb_mobile'];
                $list[$key]['leixing']= '';
                $list[$key]['zhuangtao'] = '';
                if($val['type'] == 1){
                    $list[$key]['leixing'] = '补贴收益';
                }
                if($val['type'] == 2){
                    $list[$key]['leixing'] = '分润收益';
                }
                if($val['type'] == 3){
                    $list[$key]['leixing'] = '押金提现';
                }
                
                if($val['status'] == 0){
                    $list[$key]['zhuangtao'] = '未审核';
                }
                if($val['status'] == 1){
                    $list[$key]['zhuangtao'] = '手动';
                }
                if($val['status'] == 2){
                    $list[$key]['zhuangtao'] = '支付宝';
                }
                if($val['status'] == 3){
                    $list[$key]['zhuangtao'] = '银行';
                }
                if($val['status'] == 4){
                    $list[$key]['zhuangtao'] = '拒绝';
                }
            }
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //伙伴收益
    public function hehuorenlist(){
        set_time_limit(0);
        $name = '伙伴收益_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','伙伴级别','激活津贴','展业津贴','销售津贴','分润津贴','管理津贴','积分');
        $hea = array('username','phone','invite_code','vip_level','jihuo','zhanye','xiaoshou','fenrun','guanli','mejifen');
        $phone = $this->request->get('phone','');
        $create_at = $this->request->get('create_at','');
        
        $whereme = [];
        if($phone){
            $whereme['phone'] = $phone;
        }
        $list = Db::name('store_member')->where($whereme)->order('id','desc')->select();
        if($create_at){
            $create_at = explode(" - ",$create_at);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }
        
        foreach ($list as $key=>$val)
        {
            $where['mid'] = $val['id'];
            $list[$key]['vip_level'] = memberlaver($val['vip_level']);
            $list[$key]['jihuo'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',4)->where('type','money')->where('money','>',0)->sum('money');
            $list[$key]['zhanye'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',6)->where('type','money')->where('money','>',0)->sum('money');
            $list[$key]['xiaoshou'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',5)->where('type','money')->where('money','>',0)->sum('money');
            $list[$key]['fenrun'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',7)->where('type','tongji_money')->where('money','>',0)->sum('money');
            $list[$key]['guanli'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',8)->where('type','tongji_money')->where('money','>',0)->sum('money');
            $list[$key]['mejifen'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','integral')->where('money','>',0)->sum('money');
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //收益提现
    public function shouyitxlist(){
        set_time_limit(0);
        $name = '收益提现明细_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','补贴收益','补贴提现','补贴余额','分润收益','分润提现','分润余额','补贴&分润合计','补贴&分润提现合计','补贴&分润余额合计');
        $hea = array('username','phone','invite_code','butie','butietixian','butieyue','fenrong','fenrongtixina','fenrongyue','total','txtotal','yuetoal');
        $phone = $this->request->get('phone','');
        $create_at = $this->request->get('create_at','');
        
        $whereme = [];
        if($phone){
            $whereme['phone'] = $phone;
        }
        $list = Db::name('store_member')->where($whereme)->order('id','desc')->select();
        if($create_at){
            $create_at = explode(" - ",$create_at);
            $create_at[1] = $create_at[1] .' 23:59:59';
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }
        
        foreach ($list as $key=>$val)
        {
            $where['mid'] = $val['id'];
            $list[$key]['butie'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','money')->whereIn('status','4,5,6')->sum('money');
            $list[$key]['butietixian'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','money')->where('status',2)->where('money','>',0)->sum('money');
            $list[$key]['butieyue'] = $list[$key]['butie'] - $list[$key]['butietixian'];
            
            $list[$key]['fenrong'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','tongji_money')->whereIn('status','7,8')->sum('money');
            $list[$key]['fenrongtixina'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','tongji_money')->where('status',2)->where('money','>',0)->sum('money');
            $list[$key]['fenrongyue'] = $list[$key]['fenrong'] - $list[$key]['fenrongtixina'];
            
            $list[$key]['total'] = $list[$key]['fenrong'] + $list[$key]['butie'];
            $list[$key]['txtotal'] = $list[$key]['butietixian'] + $list[$key]['fenrongtixina'];
            $list[$key]['yuetoal'] = $list[$key]['butieyue'] + $list[$key]['fenrongyue'];
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //交易明细
    public function jiaoyilist(){
        set_time_limit(0);
        $name = '交易明细_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('伙伴姓名','伙伴手机','伙伴编号','伙伴级别','总交易额','POS机交易额','POS机刷卡笔数','扫码交易额','扫码刷卡笔数','闪付交易额','闪付刷卡笔数');
        $hea = array('username','phone','invite_code','vip_level','sum','pos','posad','shaoma','shaomaad','shanfu','shanfuad');
        $phone = $this->request->get('phone','');
        $create_at = $this->request->get('create_at','');
        $invite_code= $this->request->get('invite_code','');
        $level= $this->request->get('level','');
        
        $whereme = [];
        if($phone){
            $whereme['phone'] = $phone;
        }
        if($invite_code){
            $whereme['invite_code'] = $invite_code;
        }
        if($level){
            $whereme['vip_level'] = $level;
        }
        $list = Db::name('store_member')->where($whereme)->order('id','desc')->select();
        if($create_at){
            $create_at = explode(" - ",$create_at);
            $create_at[1] = $create_at[1].' 23:59:59';
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }
        
        foreach ($list as $key=>$val)
        {
            $where['mid'] = $val['id'];
            $list[$key]['vip_level'] = memberlaver($val['vip_level']);
            $list[$key]['pos'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','01')->sum('txnAmt');
            $list[$key]['posad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','01')->count();
            $list[$key]['shaoma'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','10')->sum('txnAmt');
            $list[$key]['shaomaad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','10')->count();
            $list[$key]['shanfu'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','12')->sum('txnAmt');
            $list[$key]['shanfuad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','12')->count();
            $list[$key]['sum'] = $list[$key]['pos'] + $list[$key]['shaoma']+ $list[$key]['shanfu'];
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //入库记录
    public function snrukulist(){
        set_time_limit(0);
        $name = '入库记录_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('持有手机号','编号','SN码','机具类型','机具状态','入网时间','操作时间');
        $hea = array('shoujihao','id','machine_no','zhuangtai','is_huabo','create_time','update_time');
        $username = $this->request->get('name','');
        $phone = $this->request->get('phone','');
        $paixu = $this->request->get('paixu','asc');
        $first_leader = '';
        if($username){
            $first_leader = Db::name('store_member')->where('username',$username)->value('id');
        }
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
        }
        $query = Db::name('MemberMachine');
        if($username|| $phone){
            $query = $query->where('mid',$first_leader);
        }
        $list = $query->whereIn('type','1')->where('is_huabo',0)->order('machine_no '.$paixu)->select();
        foreach ($list as &$vl){
            $vl['shoujihao'] = shoujihao($vl['mid']);
            $vl['zhuangtai'] = zhuangtai($vl['type']);
            $vl['is_huabo'] = $vl['is_huabo'] == 1 ? '未划拨' : '已划拨';
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
    //sn库存
    public function snlist(){
        set_time_limit(0);
        $name = 'SN库存_'.date('Y-m-d H:i:s').'.csv';
        $headers = array('编号','SN码','类型','入网时间');
        $hea = array('id','machine_no','is_huabo','create_time');
        $machine_no = $this->request->get('machine_no','');
        $query = Db::name('MemberMachine');
        if($machine_no){
            $query = $query->where('machine_no',$machine_no);
        }
        $list = $query->order('id desc')->select();
        foreach ($list as &$vl){
            $vl['is_huabo'] = $vl['is_huabo'] == 1 ? '未划拨' : '已划拨';
        }
        $csv = (new Csv());
        $csv->body($name,$headers,$list,$hea);
    }
    
}
