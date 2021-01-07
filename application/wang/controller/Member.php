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

/**
 * 会员信息管理
 * Class Member
 * @package app\store\controller
 */
class Member extends Controller
{

    function mlog($mid, $type, $status, $money, $content,$time,$extends = '')
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
            'create_at' => $time,
            'is_show' => 1,
            'pay_status' => 1,
            'check_status' => 1,
        ]);
        return $res;
    }

    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreMember';

    /**
     * 会员信息管理
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->title = '会员信息管理';
        $query = $this->_query($this->table)->like('nickname,phone')->equal('vip_level');
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 会员信息管理
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberlist()
    {
        $this->title = '会员信息管理';
        $tuijianname = $this->request->get('tuijianname','');
        $tuijianid = $this->request->get('tuijianid','');
        $first_leader= '';
        if($tuijianname){
            $first_leader = Db::name('store_member')->where('username',$tuijianname)->value('id');
        }
        if($tuijianid){
            $first_leader = Db::name('store_member')->where('invite_code',$tuijianid)->value('id');
        }
        $query = $this->_query($this->table)->like('username,phone,invite_code,ver')->equal('vip_level');
        if($tuijianname || $tuijianid){
            $query = $query->equaladd('first_leader',$first_leader);
        }
        $userlaver = Db::name('store_member_laver')->order('num asd')->select();
        $this->assign('userlaver',$userlaver);
        $info = $query->dateBetween('create_at')->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key =>$val){
            $merinfo[$key]['shiming'] = $val['card_type'] == 1?'是':'否';
            if($val['first_leader']){
                $firmem = Db::name('store_member')->where('id',$val['first_leader'])->find();
                $merinfo[$key]['tuijianname'] = $firmem['username'];
                $merinfo[$key]['tuijianno'] = $firmem['invite_code'];
                if($firmem['ver'] == 1){
                    $merinfo[$key]['tuijianjibie'] = $firmem['vip_manual'];
                }else{
                    $merinfo[$key]['tuijianjibie'] = $firmem['vip_level'];
                }
            }else{
                $merinfo[$key]['tuijianname'] = '';
                $merinfo[$key]['tuijianno'] = '';
                $merinfo[$key]['tuijianjibie'] = '';
            }
            $merinfo[$key]['jihuono'] =  Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>1])->count();
            $merinfo[$key]['chuku'] = Db::name('member_machine_log')->where(['form_mid'=>$val['id'],'type'=>1])->count();//->sum('num');
            
            //$member = Db::name('store_member')->where('first_leader',$val['id'])->field('id')->select();
            //$member = array_column($member,'id');
            
            $merinfo[$key]['jihuois'] = Db::name('member_shanghu')->where(['type'=>1])->where('mid',$val['id'])->count();
            //$merinfo[$key]['jihuono'] = Db::name('member_machine')->where(['type'=>1])->whereIn('mid',$member)->count();
            $merinfo[$key]['ruku'] = $merinfo[$key]['jihuono'] + $merinfo[$key]['chuku'] + $merinfo[$key]['jihuois'];

            $merinfo[$key]['zhuanru'] = Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>7])->count();
            $merinfo[$key]['zhuanchu'] = Db::name('member_machine')->where(['mid'=>$val['id'],'type'=>6])->count();
        }
        $info['list'] = $merinfo;

        return $this->fetch('', $info);

    }

    /**
     * 用户关系
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberguanxi()
    {
        $this->title = '会员信息管理';
        set_time_limit(0);
        $users = Db::name('store_member');
        $id = $_GET['id'];
        $list = Db::name('store_member')->where(['first_leader' => $id])->select();

        foreach ($list as $key => $v) {
            $list[$key]['share'] = Db::name('store_member')->where('first_leader', $v['id'])->select();
            //$list[$key]['team_share'] = get_team_share_num($v['id']);
            //   $list[$key]['children'] = self::handleUser($v['id'], $layer);

        }

        return view('', [
            'id' => $id,
            'list' => $list,
        ]);

    }

    public function memberxiaji($id)
    {
        $list = Db::name('store_member')->where(['first_leader' => $id])->select();
        return $list;

    }

    /**
     * 用户信息
     * @auth true
     * @menu true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberinfo()
    {
        $this->title = '会员信息管理';
        $users = Db::name('store_member');
        $id = $_GET['id'];
        $list = Db::name('store_member')->where(['id' => $id])->find();

        $list['address'] = Db::name('member_address')->where(['mid' => $id])->order('is_default desc')->select();
        if($list['card_type'] == 1){
            $list['shiming'] = Db::name('member_card')->where(['mid' => $id,'card_type'=>1,'card_state'=>1])->find();
        }

        return view('', [
            'id' => $id,
            'list' => $list,
        ]);
    }

    /**
     * 编辑用户金额
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberjine()
    {
        $this->title = '编辑用户金额';
        $id = $this->request->Get('id');
        $user = Db::name('store_member')->where(['id'=>$id])->find();
        if ($this->request->isPost()) {
            $integral = $this->request->post('integral');
            $activity_integral = $this->request->post('activity_integral');
            $activation_prize = $this->request->post('activation_prize');
            $recommend_prize = $this->request->post('recommend_prize');
            $profit_prize = $this->request->post('profit_prize');
            $activity_prize = $this->request->post('activity_prize');
            $standard_prize = $this->request->post('standard_prize');
            $team_prize = $this->request->post('team_prize');
            $payment = $this->request->post('payment');
            $time = $this->request->post('time');

            Db::startTrans();
            $res[] = 1;
            if(!empty($integral)&& is_numeric($integral)){
                if(($user['integral']+$integral)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'integral',3,$integral,'数据更正',$time);
            }
            if(!empty($activity_integral)&& is_numeric($activity_integral)){
                if(($user['activity_integral']+$activity_integral)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'activity_integral',3,$activity_integral,'数据更正',$time);
            }
            if(!empty($activation_prize)&& is_numeric($activation_prize)){
                if(($user['activation_prize']+$activation_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'activation_prize',3,$activation_prize,'数据更正',$time);
            }
            if(!empty($recommend_prize)&& is_numeric($recommend_prize)){
                if(($user['recommend_prize']+$recommend_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'recommend_prize',3,$recommend_prize,'数据更正',$time);
            }
            if(!empty($profit_prize)&& is_numeric($profit_prize)){
                if(($user['profit_prize']+$profit_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'profit_prize',3,$profit_prize,'数据更正',$time);
            }
            if(!empty($activity_prize)&& is_numeric($activity_prize)){
                if(($user['activity_prize']+$activity_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'activity_prize',3,$activity_prize,'数据更正',$time);
            }
            if(!empty($standard_prize)&& is_numeric($standard_prize)){
                if(($user['standard_prize']+$standard_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'standard_prize',3,$standard_prize,'数据更正',$time);
            }
            if(!empty($team_prize)&& is_numeric($team_prize)){
                if(($user['team_prize']+$team_prize)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'team_prize',3,$team_prize,'数据更正',$time);
            }
            if(!empty($payment)&& is_numeric($payment)){
                if(($user['payment']+$payment)<0){
                    Db::rollback();
                    $this->error('失败');
                }
                $res[] = $this->mlog($user['id'],'payment',3,$payment,'数据更正',$time);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                sysoplog('用户管理', '后台管理员修改金额');
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }

        }

        return view('',$user);
    }
    
    /**
     * 设置等级1.0
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberver()
    {
        $this->title = '编辑用户等级';
        $id = $this->request->Get('id');
        $vip = Db::name('StoreManualLaver')->order('id esc')->field('id,name')->select();
        $merber= Db::name('store_member')->where(['id'=>$id])->find();
        if($merber['vip_date_s'] != null){
            $merber['vip_time'] = $merber['vip_date_s'].' - '.$merber['vip_date_e'];
        }else{
            $merber['vip_time'] = '';
        }
        if ($this->request->isPost()) {
            $vip_level = $this->request->post('vip_level');
            $vip_time = $this->request->post('vip_time');
            if(!$vip_level||!$vip_time) $this->error('参数不全');
            $vip_time= explode(' - ', $vip_time);
            $s_time = $vip_time[0];
            $e_time= $vip_time[1];
            if(strtotime($s_time)>strtotime($e_time)) $this->error("结束时间不能小于开始时间");
            if($merber['first_leader'] != 0){
                $leader = Db::name('store_member')->where(['id'=>$merber['first_leader']])->find();
                $endtime = strtotime($leader['vip_date_e'].' 23:59:59');
                if($leader['vip_manual'] == 0 || $endtime<time()) $this->error("该用户的上级分润等级已失效，无法设置");
                if($vip_level>$leader['vip_manual']) $this->error("下级等级不能超过上级的等级");
                if(strtotime($s_time)<strtotime($leader['vip_date_s']) || strtotime($s_time)>strtotime($leader['vip_date_e'])) $this->error("时间范围应在".$leader['vip_date_s']."到".$leader['vip_date_e']."之间");
            }
            
            $result = Db::name('store_member')->where(['id'=>$id])->update(['vip_manual'=>$vip_level,'vip_date_s'=>$s_time,'vip_date_e'=>$e_time]);
            if($result){
                $this->success('成功');
            }else{
                $this->error('失败，请重试');
            }
        }
        return view('',['vip'=>$vip,'user'=>$merber]);
    }
    
    /**
     * 设置等级2.0
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function membertver()
    {
        $this->title = '编辑用户等级';
        $id = $this->request->Get('id');
        $vip = Db::name('StoreMemberLaver')->order('id esc')->field('id,name')->select();
        $merber= Db::name('store_member')->where(['id'=>$id])->find();
        if($merber['vip_date_s'] != null){
            $merber['vip_time'] = $merber['vip_date_s'].' - '.$merber['vip_date_e'];
        }else{
            $merber['vip_time'] = '';
        }
        if ($this->request->isPost()) {
            $vip_level = $this->request->post('vip_level');
            $vip_time = $this->request->post('vip_time');
            if(!$vip_level||!$vip_time) $this->error('参数不全');
            $vip_time= explode(' - ', $vip_time);
            $s_time = $vip_time[0];
            $e_time= $vip_time[1];
            if(strtotime($s_time)>strtotime($e_time)) $this->error("结束时间不能小于开始时间");
            if($merber['first_leader'] != 0 && $merber['first_leader'] != 1){
                $leader = Db::name('store_member')->where(['id'=>$merber['first_leader']])->find();
                $endtime = strtotime($leader['vip_date_e'].' 23:59:59');
                if($leader['vip_manual'] == 0 || $endtime<time()) $this->error("该用户的上级分润等级已失效，无法设置");
                if($vip_level>$leader['vip_manual']) $this->error("下级等级不能超过上级的等级");
                if(strtotime($s_time)<strtotime($leader['vip_date_s']) || strtotime($s_time)>strtotime($leader['vip_date_e'])) $this->error("时间范围应在".$leader['vip_date_s']."到".$leader['vip_date_e']."之间");
            }
            
            $result = Db::name('store_member')->where(['id'=>$id])->update(['vip_manual'=>$vip_level,'vip_date_s'=>$s_time,'vip_date_e'=>$e_time]);
            if($result){
                $this->success('成功');
            }else{
                $this->error('失败，请重试');
            }
        }
        return view('',['vip'=>$vip,'user'=>$merber]);
    }

    /**
     * 编辑用户信息
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberxinxi()
    {
        $this->title = '编辑用户';
        $id = $this->request->Get('id');
        $user = Db::name('store_member')->where(['id'=>$id])->find();
        if ($this->request->isPost()) {
            $nickname = $this->request->post('nickname');
            $phone= $this->request->post('phone');
            $username = $this->request->post('username');
            $vip_level = $this->request->post('vip_level');
            $first_leader = $this->request->post('first_leader');
            $password = $this->request->post('password');
            $passwordad = $this->request->post('passwordad');
            $paypassword = $this->request->post('paypassword');
            $paypasswordad = $this->request->post('paypasswordad');
            $res = [];
            $invite_code = $this->request->post('invite_code');
            if((Db::name('store_member')->where(['invite_code'=>$invite_code])->find())&&$invite_code!=$user['invite_code'])$this->error('邀请码已存在');
            if((Db::name('store_member')->where(['phone'=>$phone])->find())&&$phone!=$user['phone'])$this->error('手机号已存在');

            Db::startTrans();

            $where['nickname'] = $nickname;
            $where['phone'] = $phone;
            $where['username'] = $username;
            $where['vip_level'] = $vip_level;
            if($invite_code!=$user['invite_code']){
                $where['invite_code'] = $invite_code;
                $where['qrcode'] = '';
            }

            if($first_leader){
                $memberinfo =  Db::name('store_member')->where('phone',$first_leader)->find();
                if($memberinfo['id'] != $user['first_leader']){
                    $where['first_leader']    = $memberinfo['id'];
                    $where['second_leader']   = $memberinfo['first_leader'];
                    $where['third_leader']    = $memberinfo['second_leader'];
                    $where['leaders']         = ltrim(rtrim($memberinfo['leaders'] . "," . $memberinfo['id'], ","), ','); // 全部上级
                    $fisdad = Db::name('store_member')->where('find_in_set('.$user['id'].',leaders)')->order('id asc')->select();
                    foreach ($fisdad as $value)
                    {
                        $werda = [];
                        $haystack = $value['leaders'];
                        $pos = strpos($haystack, strval($user['id']));
                        $str=substr_replace($haystack,"",0,$pos);

                        $werda['leaders'] = $where['leaders'].','.$str;
                        if($value['first_leader'] == $user['id']){
                            $werda['second_leader']    = $memberinfo['id'];
                            $werda['third_leader']   = $memberinfo['first_leader'];
                        }
                        if($value['second_leader'] == $user['id']){
                            $werda['third_leader']    = $memberinfo['id'];
                        }
                        $res[] = Db::name('store_member')->where('id',$value['id'])->update($werda);
                    }
                }
            }

            if($password){
                if($password !=$passwordad)$this->success('密码不一样');
                $where['password'] = md5($password);
            }
            if($paypassword){
                if($paypassword !=$paypasswordad)$this->success('密码不一样');
                $where['paypassword'] = md5($paypassword);
            }

            $res[] = Db::name('store_member')->where('id',$user['id'])->update($where);

            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }

        }
        $userlaver = Db::name('store_member_laver')->order('num asd')->select();
        $firmem = Db::name('store_member')->where('id',$user['first_leader'])->find();
        return view('',['userlaver'=>$userlaver,'user'=>$user,'firmem'=>$firmem]);
    }
    
    /**
     * 冻结管理
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberdongjie()
    {
        $this->title = '冻结管理';
        $id = $this->request->Get('id');
        $info = Db::name('store_member')->field('is_activation,is_recommend,is_profit,is_activity,is_standard,is_team')->where(['id'=>$id])->find();
        if ($this->request->isPost()) {
            $update['is_activation'] = $this->request->post('is_activation',1);
            $update['is_recommend']= $this->request->post('is_recommend',1);
            $update['is_profit']= $this->request->post('is_profit',1);
            $update['is_activity']= $this->request->post('is_activity',1);
            $update['is_standard']= $this->request->post('is_standard',1);
            $update['is_team']= $this->request->post('is_team',1);

            $res = Db::name('store_member')->where('id',$id)->update($update);

            if ($res) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }

        }
        return view('',['info'=>$info]);
    }
    
    /**
     * 配置提现
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function membertixian()
    {
        $this->title = '提现配置';
        $id = $this->request->Get('id');
        $info = Db::name('store_member_config')->where(['mid'=>$id])->find();
        if ($this->request->isPost()) {
            $update['activation_xf'] = $this->request->post('activation_xf',0);
            $update['recommend_xf']= $this->request->post('recommend_xf',0);
            $update['profit_xf']= $this->request->post('profit_xf',0);
            $update['activity_xf']= $this->request->post('activity_xf',0);
            $update['team_xf']= $this->request->post('team_xf',0);
            $update['tx_xf']= $this->request->post('tx_xf',0);
            if($info){
                $res = Db::name('store_member_config')->where('mid',$id)->update($update);
            }else{
                $update['mid'] = $id;
                $res = Db::name('store_member_config')->insert($update);
            }

            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }

        }
        return view('',['info'=>$info,'id'=>$id]);
    }
    
    /**
     * 修改认证信息
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function editrenzehng()
    {
        $this->title = '认证信息';
        $id = $this->request->Get('id');
        $info = Db::name('member_card')->where(['id'=>$id,'card_state'=>1])->find();
        if(!$info) $this->error('未通过不能修改');
        if ($this->request->isPost()) {
            $update['bank_name']= $this->request->post('bank_name',0);
            $update['bank_num']= $this->request->post('bank_num',0);
            $res = Db::name('member_card')->where(['id'=>$id,'card_state'=>1])->update($update);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }

        }
        return view('',['info'=>$info]);
    }

    /**
     * 积分明细
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function memberjifen()
    {
        $this->title = '积分明细';
        $id = $this->request->Get('id');
        $user = Db::name('member_money_log')->select();
        return view('',$user);
    }

    /**
     * 认证管理
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function renzheng()
    {
        $this->title = '认证管理';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query('member_card')->equal('card_state');
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 认证
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $id= $this->request->post('id','');
        if(!$id)$this->error('参数错误');
        $creat = Db::name('member_card')->where(['id'=>$id,'card_state'=>0])->find();
        if(!$creat)$this->error('参数错误');

        Db::startTrans();

        $res[] = Db::name('store_member')->where(['id'=>$creat['mid'],'card_type'=>0])->update(['card_type'=>1,'username'=>$creat['card_name']]);
        $res[] = Db::name('member_card')->where(['id'=>$creat['id'],'card_state'=>0])->update(['card_state'=>1,'update_at'=>date('Y-m-d H:i:s')]);

        if ($this->check_arr($res)) {
            Db::commit();
            sysoplog('认证管理', '后台管理员认证通过管理');
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 认证拒绝
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbidad()
    {
        $id= $this->request->post('id','');
        if(!$id)$this->error('参数错误');
        $creat = Db::name('member_card')->where(['id'=>$id,'card_state'=>0])->find();
        if(!$creat)$this->error('参数错误');
         if(Db::name('member_card')->where(['id'=>$creat['id'],'card_state'=>0])->update(['card_state'=>2,'update_at'=>date('Y-m-d H:i:s')])){
             sysoplog('认证管理', '后台管理员认证拒绝');
             $this->success('成功');
         }else{
             $this->error('失败');
         }
    }

     /**
     * 分润补贴开关
     */
    public function memberswith()
    {
        $id = $this->request->get('id','');
        $type = $this->request->get('type','');
        if(!$id || !$type)$this->error('参数错误');
        if($type == 1){
            $file = 'show_butie';
        }elseif($type == 2){
            $file = 'show_feiyun';
        }elseif($type == 4){
            $file = 'show_yajin';
        }elseif($type == 3){
            $file = 'show_butieall';
        }elseif($type == 6){
            $file = 'show_zhanye';
        }else{
            $file = 'show_jifen';
        }
        $swith = Db::name('store_member')->where(['id'=>$id])->value($file);
        if($swith == 1){
            $show =0;
            if($type == 1){
                $date = Db::name('store_member')->where('find_in_set('.$id.',leaders)')->whereOr('id',$id)->update([$file=>0,'xiaoshou_time'=>date('Y-m-d H:i:s')]);
            }elseif ($type == 6){
                $date = Db::name('store_member')->where('find_in_set('.$id.',leaders)')->whereOr('id',$id)->update([$file=>0,'zhanye_time'=>date('Y-m-d H:i:s')]);
            }else{
                $date = Db::name('store_member')->where('find_in_set('.$id.',leaders)')->whereOr('id',$id)->update([$file=>0]);
            }

            if($date){
                $this->success('成功');
            }else{
                $this->error('失败');
            }
        }else{
            $show =1;
            if($type == 6){
                if(Db::name('store_member')->where('find_in_set('.$id.',leaders)')->whereOr('id',$id)->update([$file=>$show])){
                    $this->success('成功');
                }else{
                    $this->error('失败');
                } 
            }else{
               if(Db::name('store_member')->where(['id'=>$id])->update([$file=>$show])){
                    $this->success('成功');
                }else{
                    $this->error('失败');
                } 
            }
        }
    }

}
