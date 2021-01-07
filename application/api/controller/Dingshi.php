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
class Dingshi extends BasicIndex
{
    /**
     * 激活返现信息入库
     * @return [type] [description]
     */
    public function jihuo_reflow()
    {
        set_time_limit(0);
        $jihuolist = Db::name('member_shanghu_jihuo')->where(['is_chuli'=>0])->select();

        foreach ($jihuolist as $key=>$val){
            $asdfd = Db::name('member_machine')->where(['machine_no'=>$val['machine_no'],'type'=>1])->where('mid','<>',0)->find();
            if($asdfd){
                $where['mid'] = $asdfd['mid'];
                $where['machine_no'] = $asdfd['machine_no'];
                $where['type'] = 3;

                $banding['mid'] = $asdfd['mid'];
                $banding['machine_no'] = $asdfd['machine_no'];
                $banding['type'] = 1;
                $banding['jihuo_time'] = $val['ordTim'];

                Db::startTrans();
                $res[] = Db::name('MemberMachine')->where(['type'=>1,'id'=>$asdfd['id']])->update(['type'=>3,'update_time'=>date('Y-m-d H:i:s')]);
                $res[] = Db::name('MemberMachineLog')->insert($where);
                $res[] = Db::name('MemberShanghu')->insert($banding);
                $res[] = Db::name('member_shanghu_jihuo')->where(['id'=>$val['id']])->update(['is_chuli'=>1,'update_time'=>time()]);

                //激活奖
                $meminfo = Db::name('store_member')->where(['id'=>$asdfd['mid']])->find();
                $res[] = $this->mlog($asdfd['mid'],'integral',1,'200','激活机具SN:'.$asdfd['machine_no'],$asdfd['machine_no']);
                $res[] = $this->mlog($asdfd['mid'],'activation_prize',1,'150','激活奖励（150元）',$asdfd['machine_no']);
                if($meminfo['recommend_two'] == 0){
                    $ma_num = Db::name('MemberMachine')->whereIn('type',[1,3,7])->where('mid',$meminfo['first_leader'])->count();
                    if($ma_num >= 5){
                        $res[] = $this->mlog($meminfo['first_leader'],'recommend_prize',1,'50','伙伴首次激活奖励（50元）',$asdfd['mid']);
                    }
                    $res[] = Db::name('store_member')->where(['id'=>$asdfd['mid']])->update(['recommend_two'=>1]);
                }
                if ($this->check_arr($res)) {
                    Db::commit();

                } else {
                    Db::rollback();

                }
            }else{
                Db::name('member_shanghu_jihuo')->where(['id'=>$val['id']])->update(['is_chuli'=>2,'update_time'=>time()]);
                return '成功';
            }
        }
        return 6;
    }


    /**
     * 商户交易本机状态修改
     * @return [type] [description]
     */
    public function activate_update()
    {
        set_time_limit(0);
        $laver = Db::name('store_member_laver')->column('pro','id');
        $laver_ma = Db::name('store_manual_laver')->column('pro','id');
        $orderlist =  Db::name('member_shanghu_order')->where(['is_chuli'=>0])->where('ttxnSts','00')->select();
        foreach ($orderlist as $key => $val)
        {
            $where = [];
            $res = [];
            $member_shanghu =  Db::name('member_shanghu')->where(['machine_no'=>$val['snNo']])->find();
            $memberinfo =  Db::name('store_member')->where(['id'=>$member_shanghu['mid']])->find();


            if($member_shanghu){
                if($val['ttxnSts'] == '00'){
                    $member_shanghu_order_log = Db::name('member_shanghu_order_log')->where(['mid'=>$member_shanghu['mid'],'montime'=>date('Y-m',strtotime($val['txnTm']))])->find();
                    Db::startTrans();
                    if($memberinfo['leaders']){
                        $all_leaders = array_reverse(explode(',', $memberinfo['leaders']));//反转数组
                        foreach ($all_leaders as $v)
                        {
                            $where = [];
                            $asdaf= Db::name('member_shanghu_order_log')->where(['mid'=>$v,'montime'=>date('Y-m',strtotime($val['txnTm']))])->find();
                            if(!$asdaf){
                                $where['mid'] = $v;
                                $where['montime'] = date('Y-m',strtotime($val['txnTm']));
                                Db::name('member_shanghu_order_log')->insert($where);
                            }
                        }
                        $res[] = Db::name('member_shanghu_order_log')->where('montime',date('Y-m',strtotime($val['txnTm'])))->whereIn('mid',$memberinfo['leaders'])->setInc('teamyeji',$val['txnAmt']);
                        $res[] = Db::name('member_shanghu_order_log')->where('montime',date('Y-m',strtotime($val['txnTm'])))->whereIn('mid',$memberinfo['leaders'])->setInc('numyeji',$val['txnAmt']);
                    }

                    if($member_shanghu_order_log){
                        $where = [];
                        if($val['crdTyp'] == 'CUP'){
                            $where['post'] = $val['txnAmt']+$member_shanghu_order_log['post'];
//                             if(($member_shanghu['num_money']+$val['txnAmt'])>=100000 &&$member_shanghu['is_yajin']==0&&strtotime('+ 90 day',strtotime($member_shanghu['jihuo_time']))>time()){
//                                 $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->update(['dabiao_time'=>date('Y-m-d H:i:s',strtotime($val['txnTm'])),'is_yajin'=>1]);
//                             }
                            $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setInc('num_money',$val['txnAmt']);
                        }
                        //扫码
                        if($val['crdTyp'] != 'CUP'){
                            $where['yinlian'] = $val['txnAmt']+$member_shanghu_order_log['yinlian'];
                        }
//                         //闪付
//                         if($val['txnCd'] == '10' ||$val['txnCd'] == '12' ){
//                             $where['yunsfu'] = $val['txnAmt']+$member_shanghu_order_log['yunsfu'];
//                         }
                        $where['oneyeji'] = $val['txnAmt']+$member_shanghu_order_log['oneyeji'];
                        $where['numyeji'] = $val['txnAmt']+$member_shanghu_order_log['numyeji'];
                        $res[] = Db::name('member_shanghu_order_log')->where('id',$member_shanghu_order_log['id'])->update($where);

                    }else{
                        $where['mid'] = $member_shanghu['mid'];
                        $where['montime'] = date('Y-m',strtotime($val['txnTm']));
                        if($val['crdTyp'] == 'CUP'){
                            $where['post'] = $val['txnAmt'];
//                             if(($member_shanghu['num_money']+$val['txnAmt'])>=100000 &&$member_shanghu['is_yajin']==0&&strtotime('+ 90 day',strtotime($member_shanghu['jihuo_time']))>time()){
//                                 $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->update(['dabiao_time'=>date('Y-m-d H:i:s',strtotime($val['txnTm'])),'is_yajin'=>1]);
//                             }
                            $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setInc('num_money',$val['txnAmt']);
                        }
                        if($val['crdTyp'] != 'CUP'){
                            $where['yinlian'] = $val['txnAmt'];
                        }
//                         if($val['txnCd'] == '10' ||$val['txnCd'] == '12' ){
//                             $where['yunsfu'] = $val['txnAmt'];
//                         }
                        $where['oneyeji'] = $val['txnAmt'];
                        $where['numyeji'] = $val['txnAmt'];
                        $res[] = Db::name('member_shanghu_order_log')->insert($where);
                    }

                    $wherelogs['mid'] = $member_shanghu['mid'];
                    $wherelogs['snNo'] = $val['snNo'];
                    $wherelogs['txnCd'] = $val['txnCd'];
                    $wherelogs['crdTyp'] = $val['crdTyp'];
                    $wherelogs['txnBusTyp'] = 0;
                    $wherelogs['txnAmt'] = $val['txnAmt'];
                    $wherelogs['create_at'] = $val['txnTm'];
                    $wherelogs['yue_time'] = date('Y-m',strtotime($val['txnTm']));

                    $res[] = Db::name('member_shanghu_order_member')->insert($wherelogs);
                    if($val['crdTyp'] == 'CUP'){
                        $res[] = $this->profit($val,$memberinfo,$laver,$laver_ma);
                    }
                    
                }
                $res[] = Db::name('member_shanghu_order')->where(['id'=>$val['id']])->update(['is_chuli'=>1,'update_time'=>time()]);

            }
            if ($this->check_arr($res)) {
                Db::commit();
                echo 1;
            } else {
                Db::rollback();
                echo 2;
            }
        }
        return '成功';
    }
    
    /**
     * 当月会员等级处理
     */
    public function vip_update()
    {
        set_time_limit(0);
        $list = Db::name('member_shanghu_order_log')->where('montime',date('Y-m'))->select();
        $laver = Db::name('store_member_laver')->order('num','desc')->select();
        foreach ($list as $key => $val)
        {
            $vip = $this->num_vip($val['numyeji'],$laver);
            $member = Db::name('store_member')->where(['id'=>$val['mid']])->where(function ($query) use ($vip) {
                $query->whereOr([
                    \think\Db::raw('vip_level <> '.$vip),
                    \think\Db::raw('vip_level IS NULL'),
                ]);
            })->find();
            
            if($member){
                $result = Db::name('store_member')->where(['id'=>$val['mid']])->update(['vip_level'=>$vip]);
                if (!$result) {
                    echo $val['mid'].'失败。';
                }
            }
        }
        return '成功';
    }
    
    /**
     * 更新押金达标
     * @return string
     */
    public function update_dabiao()
    {
        $member_shanghu =  Db::name('member_shanghu')->where(['is_yajin'=>0])->select();
        foreach ($member_shanghu as $vl){
            if($vl['jihuo_time'] == null) continue;
            $i = $vl['month_yajin'] + 1;
            $start = date('YmdHis',strtotime('+' .$vl['month_yajin'].' month',strtotime($vl['jihuo_time'])));
            $end = date('YmdHis',strtotime('+' .$i.' month',strtotime($vl['jihuo_time'])));
            if(time()>strtotime('+' .$i.' month',strtotime($vl['jihuo_time']))){
                $money =  Db::name('member_shanghu_order')->where(['snNo'=>$vl['machine_no'],'is_chuli'=>1,'ttxnSts'=>'00','crdTyp'=>'CUP'])->where('txnTm', 'between', [$start, $end])->sum('txnAmt');
                if($money>=20000){
                    $update['month_yajin'] = $i;
                    if($i>=6) $update['is_yajin'] = 1;//满足6个月达标
                    Db::name('member_shanghu')->where('id',$vl['id'])->update($update);
                }else{
                    $update['is_yajin'] = 7;//断刷失效
                    Db::name('member_shanghu')->where('id',$vl['id'])->update($update);
                }
            }
            if($i == 6){
                $money =  Db::name('member_shanghu_order')->where(['snNo'=>$vl['machine_no'],'is_chuli'=>1,'ttxnSts'=>'00','crdTyp'=>'CUP'])->where('txnTm', 'between', [$start, $end])->sum('txnAmt');
                if($money>=20000){
                    $update['month_yajin'] = $i;
                    if($i>=6) $update['is_yajin'] = 1;//满足6个月达标
                    Db::name('member_shanghu')->where('id',$vl['id'])->update($update);
                }
            }
        }
        return '成功';
    }

    /**
     * 商户刷满1万返推荐奖
     *@return [type] [description]
     */
    public function zhanye_yeji()
    {
        $membershanghuinfo = Db::name('member_shanghu')->where(['type'=>1,'is_zhanye'=>0])->where('num_money','>=',10000)->select();

        foreach ($membershanghuinfo as $key=>$val){
            Db::startTrans();
            $res[] = Db::name('member_shanghu')->where('id',$val['id'])->update(['is_zhanye'=>1]);
            $res[] = $this->mlog($val['mid'],'activation_prize',1,50,'商户刷满一万奖',$val['machine_no']);
            if ($this->check_arr($res)) {
                Db::commit();
                return 1;
            } else {
                Db::rollback();
                return 2;
            }
        }
        return '成功';
    }
    
    
    /**
     * 团队奖发放
     * @return [type] [description]
     */
    public function team_yeji()
    {
        set_time_limit(0);
        $day = date('d');
        $honder = date('H');
        if($day == 1 && $honder<6) {
            $lastmonth_start = date("Y-m", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));//上月时间
            //$lastmonth_start = date("Y-m");//上月时间
            $memberlist = Db::name('store_member')->order('id asc')->select();
            
            foreach ($memberlist as $key => $val) {
                $monlog = Db::name('member_money_log')->where(['mid' => $val['id'], 'status' => 1, 'type'=>'team_prize'])->whereLike('create_at', date('Y-m'). '%')->find();
                if ($monlog) continue;
                
                $xiajimever = Db::name('store_member')->alias('a')
                ->leftJoin('member_shanghu_order_log b', 'a.id = b.mid')
                ->where(['a.first_leader' => $val['id']])
                ->where('b.montime',$lastmonth_start)
                ->where('b.numyeji', '>=', 20000000)
                ->field('a.id')
                ->select();
                if (count($xiajimever) >= 12) {
                    $memad = bcmul(count($xiajimever), 2000, 0);
                }elseif(count($xiajimever) >= 9){
                    $memad = bcmul(count($xiajimever), 1500, 0);
                }elseif(count($xiajimever) >= 6){
                    $memad = bcmul(count($xiajimever), 1000, 0);
                }elseif(count($xiajimever) >= 3){
                    $memad = bcmul(count($xiajimever), 500, 0);
                }else{
                    $memad = 0;
                }
                if ($memad > 0) {
                    $asdaf = implode(',',array_column($xiajimever,'id'));
                    Db::startTrans();
                    $res[] = $this->mlog($val['id'], 'team_prize', 1, $memad, '团队奖',$asdaf);
                    if ($this->check_arr($res)) {
                        Db::commit();
                    } else {
                        Db::rollback();
                    }
                }
            }
            return '成功';
        }
        return '时间不到';
    }
    
    public function jiage($qianshu){
        if($qianshu>=1000000000){
            $num = 9;
        }elseif ($qianshu>=500000000){
            $num = 8;
        }elseif ($qianshu>=200000000){
            $num = 7;
        }elseif ($qianshu>=100000000){
            $num = 6;
        }elseif ($qianshu>=30000000){
            $num = 5;
        }elseif ($qianshu>=10000000){
            $num = 4;
        }elseif ($qianshu>=3000000){
            $num = 3;
        }else{
            $num = 0;
        }
        return $num;
    }

    /**
     * 处理分润
     * @param unknown $money
     * @param unknown $memberinfo
     * @param unknown $lavey
     * @return number|\think\db\true
     */
    private function profit($order,$memberinfo,$lavey,$laver_ma){
        $money = $order['txnAmt'];
        $res[] = 1;
        if($memberinfo['ver'] == 2){
            $time = strtotime($order['txnTm']);
            if($time>strtotime($memberinfo['vip_date_s'].' 00:00:00') && $time<strtotime($memberinfo['vip_date_e'].' 23:59:59')&& $memberinfo['vip_manual'] > $memberinfo['vip_level']){
                $pro = $lavey[$memberinfo['vip_manual']];
                $profit = bcdiv(bcmul($money, $pro, 4), 10000, 2);//用户金额
                $res[] = $this->mlog($memberinfo['id'], 'profit_prize', 1, $profit, '分润奖');
            }else{
                $pro = $lavey[$memberinfo['vip_level']];
                $profit = bcdiv(bcmul($money, $pro, 4), 10000, 2);//用户金额
                $res[] = $this->mlog($memberinfo['id'], 'profit_prize', 1, $profit, '分润奖');
            }
            if($memberinfo['leaders']){
                $all_leaders = array_reverse(explode(',', $memberinfo['leaders']));//反转数组
                $count = count($all_leaders);
                foreach ($all_leaders as $k=>$v)
                {
                    $my = Db::name('store_member')->where(['id' => $v])->find();
                    if(($k-1)<0){
                        $next = $memberinfo;
                    }else{
                        $next = Db::name('store_member')->where(['id' => $all_leaders[$k-1]])->find();
                    }
                    
                    if($time>strtotime($my['vip_date_s'].' 00:00:00') && $time<strtotime($my['vip_date_e'].' 23:59:59')&& $my['vip_manual'] > $my['vip_level']){
                        $pro_my = $lavey[$my['vip_manual']];
                    }else{
                        $pro_my = $lavey[$my['vip_level']];
                    }
                    if($time>strtotime($next['vip_date_s'].' 00:00:00') && $time<strtotime($next['vip_date_e'].' 23:59:59')&& $next['vip_manual'] > $next['vip_level']){
                        $pro_next = $lavey[$next['vip_manual']];
                    }else{
                        $pro_next= $lavey[$next['vip_level']];
                    }
                    
                    $pro = $pro_my-$pro_next;
                    if($pro>0){
                        $profit = bcdiv(bcmul($money, $pro, 4), 10000, 2);//用户金额
                        $res[] = $this->mlog($my['id'], 'profit_prize', 1, $profit, '分润奖');
                    }
                }
            }
        }else{
            $time = strtotime($order['txnTm']);
            if($time>strtotime($memberinfo['vip_date_s'].' 00:00:00') && $time<strtotime($memberinfo['vip_date_e'].' 23:59:59')&& $memberinfo['vip_manual'] != 0){
                $pro = $laver_ma[$memberinfo['vip_manual']];
                $profit = bcdiv(bcmul($money, $pro, 4), 10000, 2);//用户金额
                $res[] = $this->mlog($memberinfo['id'], 'profit_prize', 1, $profit, '分润奖');
            }
            if($memberinfo['leaders']){
                $all_leaders = array_reverse(explode(',', $memberinfo['leaders']));//反转数组
                $count = count($all_leaders);
                foreach ($all_leaders as $k=>$v)
                {
                    $my = Db::name('store_member')->where(['id' => $v])->find();
                    if(($k-1)<0){
                        $next = $memberinfo;
                    }else{
                        $next = Db::name('store_member')->where(['id' => $all_leaders[$k-1]])->find();
                    }
                    if($time>strtotime($my['vip_date_s'].' 00:00:00')&& $time<strtotime($my['vip_date_e'].' 23:59:59')&& $my['vip_manual'] != 0){
                        $pro_my = $laver_ma[$my['vip_manual']];
                    }else{
                        $pro_my = 0;
                    }
                    if($time>strtotime($next['vip_date_s'].' 00:00:00')&& $time<strtotime($next['vip_date_e'].' 23:59:59')&& $next['vip_manual'] != 0){
                        $pro_next = $laver_ma[$next['vip_manual']];
                    }else{
                        $pro_next = 0;
                    }
                    $pro = $pro_my-$pro_next;
                    if($pro>0){
                        $profit = bcdiv(bcmul($money, $pro, 4), 10000, 2);//用户金额
                        $res[] = $this->mlog($my['id'], 'profit_prize', 1, $profit, '分润奖');
                    }
                }
            }
        }
        return $res;
    }
    
    /**
     * 获取vip等级
     * @param unknown $qianshu
     */
    private function num_vip($qianshu,$laver)
    {
        foreach ($laver as $vl){
            if($qianshu>=($vl['num']*10000)) return $vl['id'];
        }
    }

}
