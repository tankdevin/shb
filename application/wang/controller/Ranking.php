<?php

namespace app\wang\controller;

use library\Controller;
use think\Db;

/**
 * 会员金额管理
 * Class Member
 * @package app\store\controller
 */
class Ranking extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
        protected $table = 'MemberMoneyLog';

    /**
     * 分润津贴
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function tuijianlist()
    {
        $this->title = '推荐伙伴排行榜';
        $create_at = $this->request->get('create_at','');
        $query = $this->_query('store_member')->where('first_leader','>',0);
        if($create_at){
            $create_at = explode(' - ',$create_at);
            $query->whereBetweenTime('create_at',$create_at[0],$create_at[1]);

        }
        $info = $query->group('first_leader')->field('count(*) as num,first_leader')->pagenew();
        $list = $info['list'];
        foreach ($list as $key=>$val){
            $merberinfo  = Db::name('store_member')->where('id',$val['first_leader'])->find();
            $list[$key]['username'] =$merberinfo['username'];
            $list[$key]['phone'] =$merberinfo['phone'];
            $list[$key]['vip_level'] =$merberinfo['vip_level'];
            $list[$key]['ver'] =$merberinfo['ver'];
            $list[$key]['vip_manual'] =$merberinfo['vip_manual'];
            $list[$key]['id'] =$merberinfo['invite_code'];
        }
        $info['list'] = $list;
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
    public function jijulist()
    {
        $this->title = '激活机具数排行榜';
        $create_at = $this->request->get('create_at','');
        $query = $this->_query('member_shanghu')->where('type',1);
        if($create_at){
            $create_at = explode(' - ',$create_at);
            $query->whereBetweenTime('create_at',$create_at[0],$create_at[1]);

        }
        $info = $query->group('mid')->field('count(*) as num,mid')->pagenew();
        $list = $info['list'];
        foreach ($list as $key=>$val){
            $merberinfo  = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] =$merberinfo['username'];
            $list[$key]['phone'] =$merberinfo['phone'];
            $list[$key]['vip_level'] =$merberinfo['vip_level'];
            $list[$key]['ver'] =$merberinfo['ver'];
            $list[$key]['vip_manual'] =$merberinfo['vip_manual'];
            $list[$key]['id'] =$merberinfo['invite_code'];
        }
        $info['list'] = $list;
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
    public function shanghulist()
    {
        $this->title = '商户交易额排行榜';
        $create_at = $this->request->get('create_at','');
        $query = $this->_query('member_shanghu_order_member');
        if($create_at){
            $create_at = explode(' - ',$create_at);
            $query->whereBetweenTime('create_at',$create_at[0],$create_at[1]);

        }
        $info = $query->group('mid')->field('sum(txnAmt) as num,mid')->pagenew();
        $list = $info['list'];
        foreach ($list as $key=>$val){
            $merberinfo  = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] =$merberinfo['username'];
            $list[$key]['phone'] =$merberinfo['phone'];
            $list[$key]['ver'] =$merberinfo['ver'];
            $list[$key]['vip_manual'] =$merberinfo['vip_manual'];
            $list[$key]['vip_level'] =$merberinfo['vip_level'];
            $list[$key]['id'] =$merberinfo['invite_code'];
        }
        $info['list'] = $list;
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
    public function poslist()
    {
        $this->title = 'POS机刷卡交易笔数排行榜';
        $create_at = $this->request->get('create_at','');
        $query = $this->_query('member_shanghu_order_member')->where('crdTyp','CUP');
        if($create_at){
            $create_at = explode(' - ',$create_at);
            $query->whereBetweenTime('create_at',$create_at[0],$create_at[1]);

        }
        $info = $query->group('mid')->field('count(txnAmt) as num,mid')->pagenew();
        $list = $info['list'];
        foreach ($list as $key=>$val){
            $merberinfo  = Db::name('store_member')->where('id',$val['mid'])->find();
            $list[$key]['username'] =$merberinfo['username'];
            $list[$key]['phone'] =$merberinfo['phone'];
            $list[$key]['ver'] =$merberinfo['ver'];
            $list[$key]['vip_manual'] =$merberinfo['vip_manual'];
            $list[$key]['vip_level'] =$merberinfo['vip_level'];
            $list[$key]['id'] =$merberinfo['invite_code'];
        }
        $info['list'] = $list;
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
    public function mingxi()
    {
        $this->title = '明细查询';
        $phone = $this->request->get('phone','');
        $create = $this->request->get('create_at','');
        if($create){
            $create_at = explode(" - ",$create);
        }else{
            $create_at = [];
            $create_at[] = date('Y-m-d H:i:s',strtotime('-60 year'));
            $create_at[] = date('Y-m-d H:i:s');
        }
        $member = Db::name('store_member')->where('phone',$phone)->find();

        if($member){
            //$memlist =  Db::name('store_member')->where('first_leader',$member['id'])->whereBetweenTime('create_at',$create_at[0],$create_at[1])->order('id desc')->select();
            $memlist =  Db::name('store_member')->where('find_in_set('.$member['id'].',leaders)')->order('id desc')->field('id')->select();
            $memidlist = array_column($memlist,'id');
            $member['shanxia1'] =Db::name('member_shanghu')->whereIn('mid',$memidlist)->whereBetweenTime('create_time',$create_at[0],$create_at[1])->count();
            $member['shanxia2'] =Db::name('member_shanghu')->whereIn('mid',$memidlist)->whereBetweenTime('create_time',$create_at[0],$create_at[1])->where('num_money','>=',5000)->count();
            $member['shanxia3'] =  Db::name('store_member')->where('find_in_set('.$member['id'].',leaders)')->whereBetweenTime('create_at',$create_at[0],$create_at[1])->count();
            $member['shanxia4'] =Db::name('member_shanghu')->whereIn('mid',$memidlist)->whereBetweenTime('create_time',$create_at[0],$create_at[1])->where('num_money','>=',5000)->group('mid')->count();
        }

        return $this->fetch('',['member'=>$member]);
    }



}
