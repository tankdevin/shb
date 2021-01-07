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
 * 会员金额管理
 * Class Member
 * @package app\store\controller
 */
class MoneyLog extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
        protected $table = 'MemberMoneyLog';

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
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $query->dateBetween('create_at')->where('type','integral')->order('id desc')->page();
    }

    /**
     * 金额明细
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function membermoney()
    {
        $this->title = '金额明细';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table)->equal('status,type');
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $query->dateBetween('create_at')->where('type','money')->order('id desc')->page();
    }


    /**
     * 提现
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function tixianlist()
    {
        $this->title = '提现';
        $phone = $this->request->get('phone','');
        $status = $this->request->get('status','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query('member_tixian')->equal('status,type');

        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $this->assign('status',$status);
        $query->dateBetween('create_at')->order('id desc')->page();
    }

    /**
     * 提现状态修改
     */
    public function tixianup()
    {
        $id = $this->request->get('id','');
        $typead = $this->request->get('typead','1');
        if($typead!=1 ||!$id)$this->error('失败');
        $masd = Db::name('member_tixian')->where(['id'=>$id])->find();
        if(!$masd)$this->error('失败');
        if($masd['status'] != 0 && $masd['status'] != 2)$this->error('违规操作');
        $money = bcsub(bcsub(bcsub($masd['money'],$masd['ptpoint'],2),$masd['formalities'],2),$masd['payment'],2);
        $data = array(
            'batchNo' => date('YmdHis'),
            'callbackUrl' => 'http://shenghuoban.web368.cn/api/plan/bank_notify',
            'totalAmount' => $money,
            'customerNo' => '30407876',
            'charset' => 'utf-8',
            'signType' => 'MD5',
            'remark' => '伙伴提现',
            'details'=> [array(
                'id' => $masd['id'],
                'bankName' => $masd['bank_name'],
                'accountType' => 'C(个人)',
                'bankUserName' => $masd['bank_username'],
                'bankAccount' => $masd['bank_num'],
                'amount' => $money,
                'remark' => '伙伴提现',
            )]
        );
        $url = 'https://bapi.shengpay.com/statement/api/batchPayment/transBatchBank';
        $signstr = $data['charset'].$data['signType'].$data['customerNo'].$data['batchNo'].$data['callbackUrl'].$data['totalAmount'].
        $data['details'][0]['id'].$data['details'][0]['province'].$data['details'][0]['city'].$data['details'][0]['branchName'].$data['details'][0]['bankName'].
        $data['details'][0]['accountType'].$data['details'][0]['bankUserName'].
        $data['details'][0]['bankAccount'].$data['details'][0]['amount'].$data['details'][0]['remark'].'QC7FEHCDmzr9xrIF';
        $data['sign'] = strtoupper(md5($signstr));
        $result = http_post($url, json_encode($data),array('headers'=>array('Content-Type:application/json')));
        $result = json_decode($result,true);
        if(isset($result['resultCode']) && $result['resultCode'] == '00'){
            
            $res[] =  Db::name('member_tixian')->where(['id'=>$id])->update(['status'=>1,'update_at'=>date('Y-m-d H:i:s')]);
            $this->success('成功');
        }else{
            $this->error('【代付返回信息】'.$result['resultMessage']);
        }
    }

    /**
     * 提现状态拒绝
     */
    public function tixiandel()
    {
        $id = $this->request->get('id','');
        if(!$id)$this->error('失败');
        $masd = Db::name('member_tixian')->where(['id'=>$id])->find();
        if(!$masd)$this->error('失败');
        if($masd['status'] != 0 && $masd['status'] != 2)$this->error('违规操作');
        Db::startTrans();
        $res[] =  Db::name('member_tixian')->where(['id'=>$id])->update(['status'=>4,'update_at'=>date('Y-m-d H:i:s')]);

//         if($masd['type'] == 1 || $masd['type'] == 2){
//             if($masd['type'] == 1){
//                 $type = 'money';
//             }
//             if($masd['type'] == 2){
//                 $type = 'tongji_money';
//             }
//         }

        if($masd['type'] == 'standard_prize' && !empty($masd['shanghu_id'])){
            $res[] = Db::name('member_shanghu')->where(['id'=>$masd['shanghu_id'],'is_yajin'=>3])->update(['is_yajin'=>5,'yajintime'=>time()]);
        }else{
            $res[] =Db::name('store_member')->where('id',$masd['mid'])->setInc($masd['type'],$masd['money']);
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
     * 编辑账户信息
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function tixianedit()
    {
        $this->title = '修改账户';
        $id = $this->request->Get('id');
        $user = Db::name('member_tixian')->where(['id'=>$id])->find();
        if ($this->request->isPost()) {
            $update['bank_name'] = $this->request->post('bank_name');
            $update['bank_username'] = $this->request->post('bank_username');
            $update['bank_num'] = $this->request->post('bank_num');
            $res = Db::name('member_tixian')->where(['id'=>$id,'status'=>2])->update($update);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
            
        }
        return view('',['user'=>$user]);
    }
    
    /**
     * 我的积分
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mejifen()
    {
        $this->title = '我的积分';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','integral')->where('status',4)->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['id'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
            $member = Db::name('member_shanghu')->where('machine_no',$val['extends'])->find();
            $merinfo[$key]['shanghu_name'] = $member['shanghu_name'];
            $merinfo[$key]['shanghu_phone'] = $member['shanghu_phone'];
        }
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 激活津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mejihuo()
    {
        $this->title = '激活津贴';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','money')->where('status',4)->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['id'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
            $member = Db::name('member_shanghu')->where('machine_no',$val['extends'])->find();
            $merinfo[$key]['shanghu_name'] = $member['shanghu_name'];
            $merinfo[$key]['shanghu_phone'] = $member['shanghu_phone'];
        }
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 展业津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mezhanye()
    {
        $this->title = '展业津贴';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','money')->where('status',6)->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['id'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
        }
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 销售津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mexiaoshou()
    {
        $this->title = '销售津贴';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','money')->where('status',5)->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['id'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
            $merinfo[$key]['vip_level'] = $member['vip_level'];
            $memberad = Db::name('store_member')->where('id',$val['extends'])->find();
            $merinfo[$key]['shanghu_name'] = $memberad['username'];
            $merinfo[$key]['shanghu_phone'] = $memberad['phone'];
            $merinfo[$key]['did'] = $memberad['invite_code'];
            $merinfo[$key]['pvip_level'] = $memberad['vip_level'];
        }
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 分润津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mefenrun()
    {
        $this->title = '分润津贴';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','tongji_money')->where('status',7)->where('content','<>','数据更正')->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['id'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
            $merinfo[$key]['vip_level'] = $member['vip_level'];
            $memberad = Db::name('store_member')->where('id',$val['extends'])->find();
            $merinfo[$key]['shanghu_name'] = $memberad['username'];
            $merinfo[$key]['shanghu_phone'] = $memberad['phone'];
            $merinfo[$key]['did'] = $memberad['id'];
            $merinfo[$key]['pvip_level'] = $memberad['vip_level'];
        }
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 审核分润
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function check()
    {
        $type = $this->request->get('type','');
        $id = $this->request->post('id','');
        if(!$type || !$id) $this->error('失败');
        if(!in_array($type, array(1,2))) $this->error('失败');
        $query = $this->_query($this->table);
        $result = $query->whereIn('id',$id)->where('check_status',0)->update(['check_status'=>$type]);
        if($result){
            $this->success('审核成功');
        }else{
            $this->error('失败');
        }
    }

    /**
     * 管理津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function meguanli()
    {
        $this->title = '管理津贴';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','tongji_money')->where('status',8)->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['invite_code'];
            $merinfo[$key]['pinvite_code'] = $member['invite_code'];
            $merinfo[$key]['vip_level'] = $member['vip_level'];
            $memberad = Db::name('store_member')->whereIn('id',$val['extends'])->select();
            $merinfo[$key]['memberad'] = $memberad;
        }
        $info['list'] = $merinfo ;
        return $this->fetch('',$info);
    }

    /**
     * 伙伴收益
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mehehuoren()
    {
        $this->title = '伙伴收益';
        $query = $this->_query('store_member')->equal('phone');
        //$query =$query->dateBetween('create_at');
        $info = $query->order('id desc')->pagenew();
        $create = $this->request->get('create_at','');
        if($create){
            $create_at = explode(" - ",$create);
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $where['mid'] = $val['id'];

            $merinfo[$key]['jihuo'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',4)->where('type','money')->where('money','>',0)->sum('money');
            $merinfo[$key]['zhanye'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',6)->where('type','money')->where('money','>',0)->sum('money');
            $merinfo[$key]['xiaoshou'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',5)->where('type','money')->where('money','>',0)->sum('money');
            $merinfo[$key]['fenrun'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',7)->where('type','tongji_money')->where('money','>',0)->sum('money');
            $merinfo[$key]['guanli'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('status',8)->where('type','tongji_money')->where('money','>',0)->sum('money');
            $merinfo[$key]['mejifen'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','integral')->where('money','>',0)->sum('money');
        } //halt($merinfo);
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);

    }

    /**
     * 个人流水明细
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function oneliushui()
    {
        $this->title = '个人流水明细';
        $id = $this->request->get('id','');
        $member = Db::name('store_member')->where('id',$id)->find();
        $snNo = $this->request->get('snNo','');
        $where = [];

        $timead = $this->request->get('timead','');
        if($timead){
            $addtime = date('Y-m-01 0:0:1',strtotime($timead));
            $endtime = date('Y-m-d 23:23:59',strtotime('+1 month -1 day',strtotime($timead)));
        }else{
            $addtime = date('Y-m-01 0:0:1',time());
            $endtime = date('Y-m-d 23:23:59',strtotime('+1 month -1 day',time()));
        }
        if($snNo)$where['machine_no'] = $snNo;
        $machine_no = Db::name('member_shanghu')->where(['mid'=>$id])->where($where)->field('machine_no')->select();
        $machine_no = array_column($machine_no,'machine_no');

        $query = $this->_query('member_shanghu_order_member')->equal('txnCd');
        /*if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }*/
        $info = $query->whereBetweenTime('create_at',$addtime,$endtime)->innew('snNo',$machine_no)->order('id desc')->pagenew();
        $info['member'] = $member;
        return $this->fetch('',$info);

    }

    /**
     * 伙伴流水
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function twoliushui()
    {
        $this->title = '伙伴流水';
        $id = $this->request->get('id','');
        $list = Db::name('store_member')->where('first_leader',$id)->select();

        $timead = $this->request->get('timead','');
        if($timead){
            $addtime = date('Y-m-01 0:0:1',strtotime($timead));
            $endtime = date('Y-m-d 23:23:59',strtotime('+1 month -1 day',strtotime($timead)));
        }else{
            $addtime = date('Y-m-01 0:0:1',time());
            $endtime = date('Y-m-d 23:23:59',strtotime('+1 month -1 day',time()));
        }

        foreach ($list as $key=>$val)
        {
            $list[$key]['oneyeji'] = Db::name('member_shanghu_order_log')->where('mid',$val['id'])->whereBetweenTime('create_time',$addtime,$endtime)->sum('oneyeji');
            $list[$key]['teamyeji'] = Db::name('member_shanghu_order_log')->where('mid',$val['id'])->whereBetweenTime('create_time',$addtime,$endtime)->sum('teamyeji');
        }
        //halt($list);

        return view('',['list'=>$list]);

    }

    /**
     * 押金
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function meyajin()
    {
        $this->title = '押金';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query('member_shanghu');
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('dabiao_time')->where('type','1')->where('type',1)->whereIn('is_yajin','1,3,4,5')->order('id desc')->pagenew();

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $member = Db::name('store_member')->where('id',$val['mid'])->find();
            $merinfo[$key]['username'] = $member['username'];
            $merinfo[$key]['phone'] = $member['phone'];
            $merinfo[$key]['pid'] = $member['invite_code'];
            $merinfo[$key]['vip_level'] = $member['vip_level'];
            $merinfo[$key]['zfb_name'] = $member['zfb_name'];
            $merinfo[$key]['zfb_mobile'] = $member['zfb_mobile'];
            switch ($val['is_yajin'])
            {
                case 1:
                    $merinfo[$key]['is_yajin']= '已达标';
                    break;
                case 3:
                    $merinfo[$key]['is_yajin']= '已申请';
                    break;
                case 4:
                    $merinfo[$key]['is_yajin']= '已通过';
                    break;
                case 5:
                    $merinfo[$key]['is_yajin']= '已拒绝';
                    break;
            }
        }
        $info['list'] = $merinfo ;
        return $this->fetch('',$info);

    }

    /**
     * 分润津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function meshouyitx()
    {
        $this->title = '收益与提现明细';
        $query = $this->_query('store_member')->equal('phone');
        //$query =$query->dateBetween('create_at');
        $info = $query->order('id desc')->pagenew();
        $create = $this->request->get('create_at','');
        if($create){
            $create_at = explode(" - ",$create);
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }

        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $where['mid'] = $val['id'];

            $merinfo[$key]['butie'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','money')->whereIn('status','4,5,6')->sum('money');
            $merinfo[$key]['butietixian'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','money')->where('status',2)->where('money','>',0)->sum('money');
            $merinfo[$key]['butieyue'] = $merinfo[$key]['butie'] - $merinfo[$key]['butietixian'];

            $merinfo[$key]['fenrong'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','tongji_money')->whereIn('status','7,8')->sum('money');
            $merinfo[$key]['fenrongtixina'] = Db::name('member_money_log')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('type','tongji_money')->where('status',2)->where('money','>',0)->sum('money');
            $merinfo[$key]['fenrongyue'] = $merinfo[$key]['fenrong'] - $merinfo[$key]['fenrongtixina'];


        } //halt($merinfo);
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);
    }

    /**
     * 分润津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function jiaoyilist()
    {
        $this->title = '交易数据';
        $query = $this->_query('store_member')->equal('phone,invite_code,vip_level');
        //$query =$query->dateBetween('create_at');
        $info = $query->order('id desc')->pagenew();
        $create = $this->request->get('create_at','');
        if($create){
            $create_at = explode(" - ",$create);
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }
//halt($create_at);
        $merinfo = $info['list'];
        foreach ($merinfo as $key=>$val)
        {
            $where['mid'] = $val['id'];

            $merinfo[$key]['pos'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','01')->sum('txnAmt');
            $merinfo[$key]['posad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','01')->count();
            $merinfo[$key]['shaoma'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','10')->sum('txnAmt');
            $merinfo[$key]['shaomaad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','10')->count();
            $merinfo[$key]['shanfu'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','12')->sum('txnAmt');
            $merinfo[$key]['shanfuad'] = Db::name('member_shanghu_order_member')->where($where)->whereBetweenTime('create_at',$create_at[0],$create_at[1])->where('txnCd','12')->count();

            $merinfo[$key]['sum'] = $merinfo[$key]['pos'] + $merinfo[$key]['shaoma']+ $merinfo[$key]['shanfu'];


        } //halt($merinfo);
        $userlaver = Db::name('store_member_laver')->order('num asd')->select();
        $this->assign('userlaver',$userlaver);
        $info['list'] = $merinfo ;

        return $this->fetch('',$info);
    }

}
