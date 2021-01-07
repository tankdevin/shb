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
class MoneyLognew extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
        protected $table = 'MemberMoneyLog';

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
        $info = $query->dateBetween('create_at')->where('type','integral')->order('id desc')->pagenew();

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
     * 激活
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mejihuo()
    {
        $this->title = '激活';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','activation_prize')->order('id desc')->pagenew();

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
     * 活动
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mezhanye()
    {
        $this->title = '活动';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','activity_prize')->order('id desc')->pagenew();

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
     * 推荐
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mexiaoshou()
    {
        $this->title = '推荐';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','recommend_prize')->order('id desc')->pagenew();

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
     * 分润
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mefenrun()
    {
        $this->title = '分润';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','profit_prize')->order('id desc')->pagenew();

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
     * 达标奖
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function meguanli()
    {
        $this->title = '达标奖';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','standard_prize')->order('id desc')->pagenew();

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
     * 团队奖
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function mehehuoren()
    {
        $this->title = '团队奖';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','team_prize')->order('id desc')->pagenew();

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
        $this->title = '活动积分';
        $phone = $this->request->get('phone','');
        $mid = Db::name('store_member')->where(['phone'=>$phone])->value('id');
        $query = $this->_query($this->table);
        if(!empty($phone)){
            $query = $query->equaladd('mid',$mid);
        }
        $info = $query->dateBetween('create_at')->where('type','activity_integral')->order('id desc')->pagenew();

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

}
