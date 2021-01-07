<?php
namespace app\wang\controller;

use library\Controller;
use think\db;

/**
 * 内容管理
 * Class ContentBanner
 * @package app\wang\controller
 */
class Machine extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'MemberMachine';

    /**
     * 机具管理
     * @auth true
     * @menu true
     */
    public function jijulist()
    {
        $this->title = '机具管理';

        $name = $this->request->get('name','');
        $id = $this->request->get('id','');
        $phone = $this->request->get('phone','');
        $first_leader = '';
        if($name){
            $first_leader = Db::name('store_member')->where('username',$name)->value('id');
        }
        if($id){
            $first_leader = Db::name('store_member')->where('id',$id)->value('id');
        }
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
        }
        $query = $this->_query($this->table)->equal('machine_no,type');
        if($name || $id ||$phone){
            $query = $query->equaladd('mid',$first_leader);
        }
        //$query = $this->_query($this->table)->equal('machine_no,type');
        $query->whereIn('type','1,3,7')->order('id desc')->page();
    }

    /**
     * 机具删除
     */
    public function jijudel()
    {
        $id = $this->request->get('id','');
        if(!$id)$this->error('失败');
        $masd = Db::name('member_machine')->where(['id'=>$id,'type'=>1])->find();
        if(!$masd)$this->error('失败');

        Db::startTrans();
        $res[] =  Db::name('member_machine')->where(['id'=>$id,'type'=>1])->update(['type'=>0,'update_time'=>date('Y-m-d H:i:s')]);

        $indewher['mid'] = 1;
        $indewher['form_mid'] = $masd['mid'];
        $indewher['machine_no'] = $masd['machine_no'];
        $indewher['type'] = 2;
        $indewher['num'] = 1;
        $indewher['yuanyin'] = '后台机具删除';
        $res[] = Db::name('member_machine_log')->insert($indewher);
        if ($this->check_arr($res)) {
            Db::commit();
            $tishi = '成功';
            $this->success($tishi);
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 机具添加
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function shanghulistadd()
    {
        $this->title = '添加机具';
        if ($this->request->isPost()) {
            $connet = $this->request->post('connet');
            $bianhaoinfo = explode(',',$connet);
            $num = count($bianhaoinfo);
            foreach ($bianhaoinfo as $val){
                $jijuinfo = Db::name('member_machine')->where(['machine_no'=>$val])->find();
                if($jijuinfo){
                    $num = $num-1;
                    continue;
                }

                $where = [];
                $where['mid'] = 1;
                $where['machine_no'] = $val;
                $where['type'] = 1;
                Db::name('member_machine')->insert($where);
            }
            $indewher['mid'] = 1;
            $indewher['form_mid'] = '';
            $indewher['machine_no'] = $connet;
            $indewher['type'] = 1;
            $indewher['num'] = $num;
            $indewher['yuanyin'] = '后台管理员入库';
            Db::name('member_machine_log')->insert($indewher);
        }
        $this->_form($this->table, 'shanghulistadd');
    }

    /**
     * 机具转移
     */
    public function jijuchuku()
    {
        $this->title = '添加机具';
        $id = $this->request->get('id');
        $machae = Db::name('member_machine')->where(['id'=>$id,'type'=>3])->find();
        $shanghu = Db::name('member_shanghu')->where(['machine_no'=>$machae['machine_no']])->find();
        if ($this->request->isPost()) {
            $id = $this->request->get('id','');
            if(!$id)$this->error('参数错误.');

            $jijuinfo = Db::name('MemberMachine')->where(['type'=>3,'id'=>$id])->find();
            if(!$jijuinfo)$this->error('机具错误.');

            $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'machine_no'=>$jijuinfo['machine_no']])->find();
            if(!$shanghuinfo)$this->error('商户错误.');

            $member = Db::name('store_member')->where('phone',$shanghuinfo['shanghu_phone'])->find();
            if(!$member)$this->error('无此用户.');

            $where['mid'] = $member['id'];
            $where['form_mid'] = $jijuinfo['mid'];
            $where['machine_no'] = $jijuinfo['machine_no'];
            $where['type'] = 7;

            Db::startTrans();
            $res[] = Db::name('MemberShanghu')->where(['id'=>$shanghuinfo['id'],'type'=>1])->update(['mid'=>$member['id'],'update_time'=>date('Y-m-d H:i:s')]);
            $res[] = Db::name('MemberMachineLog')->insert($where);
            $res[] = Db::name('MemberMachine')->where(['id'=>$jijuinfo['id'],'type'=>3])->update(['type'=>6,'update_time'=>date('Y-m-d H:i:s')]);
            unset($where['form_mid']);
            $res[] = Db::name('MemberMachine')->insert($where);
            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }

            /*$phone = $this->request->post('phone');
            $id = $this->request->post('id');
            $merinfo = Db::name('store_member')->where(['phone'=>$phone])->find();
            if(!$merinfo)$this->error('手机号不正确');
            $macinfo = Db::name('member_machine')->where(['id'=>$id,'type'=>1])->find();
            if(!$macinfo)$this->error('机具有误');

            $where['mid'] = $merinfo['id'];
            $where['form_mid'] = $macinfo['mid'];
            $where['type'] = 1;
            $where['machine_no'] = $macinfo['machine_no'];

            Db::startTrans();

            $res[] = Db::name('MemberMachine')->where(['id'=>$id,'type'=>1])->update(['type'=>2,'update_time'=>date('Y-m-d H:i:s')]);
            $res[] = Db::name('MemberMachine')->insert($where);

            $wheread['mid'] = $merinfo['id'];
            $wheread['form_mid'] = $macinfo['mid'];
            $wheread['type'] = 1;
            $wheread['machine_no'] = $macinfo['machine_no'];
            $wheread['type'] = 2;
            $wheread['num'] = 1;
            $wheread['yuanyin'] = '后台管理员出库';
            $res[] = Db::name('MemberMachineLog')->insert($wheread);
            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }*/
        }
        $this->fetch('', ['shanghu'=>$shanghu]);
        //$this->_form($this->table, 'jijuchuku',$shanghu);
    }

    /**
     * 机具转移
     */
    public function jijuchukuad()
    {
        $this->title = '机具转移';
        $id = $this->request->post('id');
        if(!$id)$this->error('参数错误.');
        $machae = Db::name('member_machine')->where(['type'=>3])->whereIn('id',$id)->select();
        foreach ($machae as $val){
            $shanghuinfo = Db::name('member_shanghu')->where(['type'=>1,'machine_no'=>$val['machine_no']])->find();
            if(!$shanghuinfo)$this->error('商户错误.');

            $member = Db::name('store_member')->where('phone',$shanghuinfo['shanghu_phone'])->find();
            if(!$member)$this->error('无此用户.');

            $where['mid'] = $member['id'];
            $where['form_mid'] = $val['mid'];
            $where['machine_no'] = $val['machine_no'];
            $where['type'] = 7;
            Db::startTrans();
            $res[] = Db::name('MemberShanghu')->where(['id'=>$shanghuinfo['id'],'type'=>1])->update(['mid'=>$member['id'],'update_time'=>date('Y-m-d H:i:s')]);
            $res[] = Db::name('MemberMachineLog')->insert($where);
            $res[] = Db::name('MemberMachine')->where(['id'=>$val['id'],'type'=>3])->update(['type'=>6,'update_time'=>date('Y-m-d H:i:s')]);
            unset($where['form_mid']);
            $res[] = Db::name('MemberMachine')->insert($where);
            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
            }
        }
        $this->success('成功');
    }

    /**
     * SN库存
     * @auth true
     * @menu true
     */
    public function snlist()
    {
        $this->title = 'SN库存';

        $query = $this->_query($this->table)->equal('machine_no,is_huabo');
        //$query = $this->_query($this->table)->equal('machine_no,type');
        $query->whereIn('type',1)->order('id desc')->page();
    }
    
    
    public function orderlist(){
        $this->title = '流水管理';
        $query = $this->_query("member_shanghu_order")->equal('*');
        $zero = ' 00:00:00';
        $zero_end = ' 23:59:59';
        if(array_key_exists("create_at",$_GET) && array_key_exists("machine_no",$_GET)){
            if($_GET['create_at'] && $_GET['machine_no']==""){
                $get_time = $_GET['create_at'];
                $start = substr($get_time,0,10).$zero;
                $end = substr($get_time,13,10).$zero_end;
                $query->whereBetweenTime('create_time',$start,$end)->where('is_chuli',1)->order('id desc')->page();
            }elseif ($_GET['machine_no'] && $_GET['create_at']==""){
                $query->where('snNo',$_GET['machine_no'])->where('is_chuli',1)->order('id desc')->page();
            }elseif ($_GET['machine_no'] && $_GET['create_at']){
                $get_time = $_GET['create_at'];
                $start = substr($get_time,0,10).$zero;
                $end = substr($get_time,13,10).$zero_end;
                $query->whereBetweenTime('create_time',$start,$end)->where('is_chuli',1)->where('snNo',$_GET['machine_no'])->order('id desc')->page();
            }elseif ($_GET['machine_no']=="" && $_GET['create_at']==""){
                $query->order('id desc')->where('is_chuli',1)->page();
            }
        }else{
            $query->order('id desc')->where('is_chuli',1)->page();
        }
        if(array_key_exists("create_at",$_GET)){
            $get_time = $_GET['create_at'];
            $start = substr($get_time,0,10).$zero;
                $end = substr($get_time,13,10).$zero_end;
            $query->whereBetweenTime('create_time',$start,$end)->where('is_chuli',1)->order('id desc')->page();
        }
        if(array_key_exists("machine_no",$_GET)){
            $query->where('snNo',$_GET['machine_no'])->where('is_chuli',1)->order('id desc')->page();
            
        }else{
            $query->where('is_chuli',1)->order('id desc')->page();
        }

    }

    /**
     * sn码后台添加
     */
    public function snadd()
    {
        $this->title = 'sn码后台添加';
        if ($this->request->isPost()) {
            $snguding = $this->request->post('snguding');
            $snstatus = $this->request->post('snstatus');
            $snend = $this->request->post('snend');
            $ver = $this->request->post('ver');
            $class = $this->request->post('class');
            if(!$snend || !$snstatus || !$ver || !$class)$this->error('参数错误');
            $num = $snend-$snstatus+1;

            Db::startTrans();

            $machine_nonum = '';
            $machinetishi = '';
            for ($i=$snstatus;$i<=$snend;$i++){
                $machine_no = $snguding.$i;
                $jijuinfo = Db::name('member_machine')->where(['machine_no'=>$machine_no])->whereIn('type','1,3,4,6,7')->find();
                if($jijuinfo){
                    $num = $num-1;
                    $machinetishi = $machine_no.','.$machinetishi;
                    continue;
                }
                if($machine_nonum)
                {
                    $machine_nonum = $machine_nonum.','.$machine_no;
                }else{
                    $machine_nonum = $machine_no;
                }
                $where = [];
                $where['machine_no'] = $machine_no;
                $where['type'] = 1;
                $where['ver'] = $ver;
                $where['class'] = $class;
                $res[] = Db::name('member_machine')->insert($where);
            }

            $indewher['mid'] = '';
            $indewher['form_mid'] = '';
            $indewher['machine_no'] = $machine_nonum;
            $indewher['type'] = 1;
            $indewher['num'] = $num;
            $indewher['yuanyin'] = '后台SN码入库';
            $res[] = Db::name('member_machine_log')->insert($indewher);
            if ($this->check_arr($res)) {
                Db::commit();
                if($machinetishi){
                    $tishi = $machinetishi.'已存在，其余已导入成功';
                }else{
                    $tishi = '成功';
                }
                $this->success($tishi);
            } else {
                Db::rollback();
                $this->error('失败');
            }

        }
        $this->_form($this->table, 'snadd');
    }

    /**
     * sn删除
     */
    public function sndelad()
    {
        $id = $this->request->get('id','');
        if(!$id)$this->error('失败');
        $masd = Db::name('member_machine')->where(['type'=>1,'is_huabo'=>1])->where('id',$id)->find();
        if(!$masd)$this->error('失败');

        Db::startTrans();
        $res[] =  Db::name('member_machine')->where(['id'=>$masd['id'],'type'=>1])->update(['type'=>0,'update_time'=>date('Y-m-d H:i:s')]);
        $indewher['machine_no'] = $masd['machine_no'];
        $indewher['type'] = 0;
        $indewher['num'] = 1;
        $indewher['yuanyin'] = '后台SN码删除';
        $res[] = Db::name('member_machine_log')->insert($indewher);
        if ($this->check_arr($res)) {
            Db::commit();
            $tishi = '成功';
            $this->success($tishi);
        } else {
            Db::rollback();
            $this->error('失败');
        }

    }

    /**
     * 机具删除
     */
    public function jijudelad()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('失败');
        $id = explode(',',$id);
        $masd = Db::name('member_machine')->where(['type'=>1])->whereIn('id',$id)->select();
        if(!$masd)$this->error('失败');

        Db::startTrans();
        foreach ($masd as $val){
            $res[] =  Db::name('member_machine')->where(['id'=>$val['id'],'type'=>1])->update(['type'=>0,'update_time'=>date('Y-m-d H:i:s')]);
        }
        $macsdasdname = implode(',',array_column($masd,'machine_no'));
        //$indewher['mid'] = 1;
        //$indewher['form_mid'] = $masd['mid'];
        $indewher['machine_no'] = $macsdasdname;
        $indewher['type'] = 0;
        $indewher['num'] = count($masd);
        $indewher['yuanyin'] = '后台SN码删除';
        $res[] = Db::name('member_machine_log')->insert($indewher);
        if ($this->check_arr($res)) {
            Db::commit();
            $tishi = '成功';
            $this->success($tishi);
        } else {
            Db::rollback();
            $this->error('失败');
        }

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
    public function snrukulist()
    {
        $this->title = '机具管理';

        $name = $this->request->get('name','');
        $id = $this->request->get('id','');
        $phone = $this->request->get('phone','');
        $paixu = $this->request->get('paixu','asc');
        $first_leader = '';
        if($name){
            $first_leader = Db::name('store_member')->where('username',$name)->value('id');
        }
        if($id){
            $first_leader = Db::name('store_member')->where('id',$id)->value('id');
        }
        if($phone){
            $first_leader = Db::name('store_member')->where('phone',$phone)->value('id');
        }
        $query = $this->_query($this->table)->equal('machine_no,is_huabo');
        if($name || $id ||$phone){
            $query = $query->equaladd('mid',$first_leader);
        }
        //$query = $this->_query($this->table)->equal('machine_no,type');
        $query->whereIn('type','1')->where('is_huabo',0)->order('machine_no '.$paixu)->page();
    }

    /**
     * 机具划拨
     */
    public function jijuadd()
    {
        $this->title = '划拨机具';
        if ($this->request->isPost()) {
            $snguding = $this->request->post('snguding');
            $snstatus = $this->request->post('snstatus');
            $snend = $this->request->post('snend');
            $phone = $this->request->post('phone');
            if(!$phone|| !$snend || !$snstatus )$this->error('参数错误');
            $num = $snend-$snstatus+1;

            $memberinfo = Db::name('store_member')->where('phone',$phone)->find();
            if(!$memberinfo)$this->error('参数错误.');

            $jijuinfo = Db::name('member_machine')->where(['is_huabo'=>1,'type'=>1])->whereBetween('machine_no',[$snguding.$snstatus,$snguding.$snend])->select();
            if(count($jijuinfo)<1)$this->error('参数错误..');

            $mleadsaver = Db::name('member_machine')->where(['mid'=>$memberinfo['id']])->whereIn('type','1,3,6')->count();//伙伴机具数量
            $mlefgdfgdfver = Db::name('store_member_laver')->where('num','<=',$mleadsaver+count($jijuinfo))->order('num desc')->find();

            Db::startTrans();

            // if($mlefgdfgdfver['id']>$memberinfo['vip_level']){
            //     if($memberinfo['vip_level'] == 1){
            //         $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id'],'create_at'=>date('Y-m-d H:i:s')]);
            //     }else{
            //         $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id']]);
            //     }
            // }
	    $res[] = 1;
            foreach ($jijuinfo as $val){
                if($memberinfo['ver'] != $val['ver']) continue;
                
                $res[] =  Db::name('member_machine')->where(['is_huabo'=>1,'type'=>1,'id'=>$val['id']])->update(['is_huabo'=>0,'mid'=>$memberinfo['id'],'update_time'=>date('Y-m-d H:i:s')]);
                $indewher['mid'] = $memberinfo['id'];
                $indewher['form_mid'] = '';
                $indewher['machine_no'] = $val['machine_no'];
                $indewher['type'] = 1;
                $indewher['num'] = 1;
                $indewher['yuanyin'] = '【'.$memberinfo['username'].'】收到【系统】划拨机具，SN号【'.$val['machine_no'].'】';
                $res[] = Db::name('member_machine_log')->insert($indewher);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'jijuadd');
    }
    /**
     * 单个机具划拨
     */
    public function jijuoneadd()
    {
        $this->title = '划拨机具';
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            $phone = $this->request->post('phone');
            if (!$phone || !$id) $this->error('参数错误');

            $memberinfo = Db::name('store_member')->where('phone', $phone)->find();
            if (!$memberinfo) $this->error('参数错误.');

            $jijuinfo = Db::name('member_machine')->where(['is_huabo' => 1, 'type' => 1, 'id' => $id])->find();
            if (!$jijuinfo) $this->error('参数错误..');

            $mleadsaver = Db::name('member_machine')->where(['mid' => $memberinfo['id']])->whereIn('type', '1,3,6')->count();//伙伴机具数量
            $mlefgdfgdfver = Db::name('store_member_laver')->where('num', '<=', $mleadsaver + 1)->order('num desc')->find();

            Db::startTrans();

            if ($mlefgdfgdfver['id'] > $memberinfo['vip_level']) {
                if($memberinfo['vip_level'] == 1){
                    $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id'],'create_at'=>date('Y-m-d H:i:s')]);
                }else{
                    $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id']]);
                }
            }

            $res[] = Db::name('member_machine')->where(['is_huabo' => 1, 'type' => 1, 'id' => $jijuinfo['id']])->update(['is_huabo' => 0, 'mid' => $memberinfo['id'], 'update_time' => date('Y-m-d H:i:s')]);
            $indewher['mid'] = $memberinfo['id'];
            $indewher['form_mid'] = '';
            $indewher['machine_no'] = $jijuinfo['machine_no'];
            $indewher['type'] = 1;
            $indewher['num'] = 1;
            $indewher['yuanyin'] = '【' . $memberinfo['username'] . '】收到【系统】划拨机具，SN号【' . $jijuinfo['machine_no'] . '】';
            $res[] = Db::name('member_machine_log')->insert($indewher);


            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'jijuoneadd');
    }

    /**
     * 机具回收
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function jijuhuishou()
    {
        $id = $this->request->get('id','');
        if(!$id)$this->error('失败');
        $masd = Db::name('member_machine')->where(['id'=>$id,'type'=>1])->find();
        if(!$masd)$this->error('失败');

        $memberinfo = Db::name('store_member')->where('id',$masd['mid'])->find();

        Db::startTrans();
        $res[] =  Db::name('member_machine')->where(['id'=>$id,'type'=>1])->update(['mid'=>'','is_huabo'=>1,'update_time'=>date('Y-m-d H:i:s')]);

        $indewher['mid'] = '';
        $indewher['form_mid'] = $masd['mid'];
        $indewher['machine_no'] = $masd['machine_no'];
        $indewher['type'] = 2;
        $indewher['num'] = 1;
        $indewher['yuanyin'] = '【'.$memberinfo['username'].'】被【系统】收回划拨机具，SN号【'.$masd['machine_no'].'】';
        $res[] = Db::name('member_machine_log')->insert($indewher);
        if ($this->check_arr($res)) {
            Db::commit();
            $tishi = '成功';
            $this->success($tishi);
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 机具回收
     */
    public function jijuhuishouad()
    {

        $id = $this->request->post('id','');
        if(!$id)$this->error('失败');
        $id = explode(',',$id);
        $masd = Db::name('member_machine')->where(['type'=>1])->whereIn('id',$id)->select();
        if(!$masd)$this->error('失败');

        Db::startTrans();
        foreach ($masd as $val){
            $memberinfo = Db::name('store_member')->where('id',$val['mid'])->find();
            $res[] =  Db::name('member_machine')->where(['id'=>$val['id'],'type'=>1])->update(['mid'=>'','is_huabo'=>1,'update_time'=>date('Y-m-d H:i:s')]);
            $indewher['mid'] = '';
            $indewher['form_mid'] = $val['mid'];
            $indewher['machine_no'] = $val['machine_no'];
            $indewher['type'] = 2;
            $indewher['num'] = 1;
            $indewher['yuanyin'] = '【'.$memberinfo['username'].'】被【系统】收回划拨机具，SN号【'.$val['machine_no'].'】';
            $res[] = Db::name('member_machine_log')->insert($indewher);
        }
        if ($this->check_arr($res)) {
            Db::commit();
            $tishi = '成功';
            $this->success($tishi);
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 机具转移
     */
    public function jijuzhuanyi()
    {
        $this->title = '机具转移';
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            $phone = $this->request->post('phone');
            if (!$id||!$phone) $this->error('参数错误.');

            $machae = Db::name('member_machine')->where(['type' =>1,'is_huabo'=>0,'id'=>$id])->find();
            if (!$machae) $this->error('机具错误.');

            $member = Db::name('store_member')->where('phone', $phone)->find();
            $memberinfo = Db::name('store_member')->where('id', $machae['mid'])->find();
            if (!$member || $member['id']== $memberinfo['id']) $this->error('手机有误.');
            
            $mleadsaver = Db::name('member_machine')->where(['mid'=>$member['id']])->whereIn('type','1,3,6')->count();//伙伴机具数量
            $mlefgdfgdfver = Db::name('store_member_laver')->where('num','<=',$mleadsaver+1)->order('num desc')->find();
            
            //var_dump($mlefgdfgdfver);die;

            Db::startTrans();
            
            if($member['ver']!=$memberinfo['ver']){
                $this->error('版本不同不能转移.');
            }

            $res[] = Db::name('member_machine')->where(['id' => $id, 'type' => 1,'is_huabo'=>0])->update(['mid' => $member['id'], 'update_time' => date('Y-m-d H:i:s')]);

            $indewher['mid'] = '';
            $indewher['form_mid'] = $machae['mid'];
            $indewher['machine_no'] = $machae['machine_no'];
            $indewher['type'] = 2;
            $indewher['num'] = 1;
            $indewher['yuanyin'] = '【'.$memberinfo['username'].'】被【系统】收回划拨机具，SN号【'.$machae['machine_no'].'】';
            $res[] = Db::name('member_machine_log')->insert($indewher);

            $inde['mid'] = $member['id'];
            $inde['form_mid'] = '';
            $inde['machine_no'] = $machae['machine_no'];
            $inde['type'] = 1;
            $inde['num'] = 1;
            $inde['yuanyin'] = '【' . $member['username'] . '】收到【系统】划拨机具，SN号【' . $machae['machine_no'] . '】';
            $res[] = Db::name('member_machine_log')->insert($inde);


            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
            }

            $this->success('成功');
        }
        $this->_form($this->table, 'jijuoneadd');
    }

    /**
     * 机具转移多个
     */
    public function jijuzhuanyitwo()
    {
        $this->title = '机具转移';
        $id = $this->request->get('id');
        halt($id);
        if ($this->request->isPost()) {
            $id = $this->request->post('id');
            $phone = $this->request->post('phone');
            if (!$id||!$phone) $this->error('参数错误.');

            $machae = Db::name('member_machine')->where(['type' =>1,'is_huabo'=>0])->whereIn('id',$id)->select();
            if (!$machae) $this->error('机具错误.');

            $member = Db::name('store_member')->where('phone', $phone)->find();
            if (!$member) $this->error('手机有误.');

            Db::startTrans();

            foreach ($machae as $val){
                $memberinfo = Db::name('store_member')->where('id', $val['mid'])->find();
                if ($member['id']== $memberinfo['id'])continue;

                $res[] = Db::name('member_machine')->where(['id' => $val['id'], 'type' => 1,'is_huabo'=>0])->update(['mid' => $member['id'], 'update_time' => date('Y-m-d H:i:s')]);
                $indewher['mid'] = '';
                $indewher['form_mid'] = $val['mid'];
                $indewher['machine_no'] = $val['machine_no'];
                $indewher['type'] = 2;
                $indewher['num'] = 1;
                $indewher['yuanyin'] = '【'.$memberinfo['username'].'】被【系统】收回划拨机具，SN号【'.$val['machine_no'].'】';
                $res[] = Db::name('member_machine_log')->insert($indewher);

                $inde['mid'] = $member['id'];
                $inde['form_mid'] = '';
                $inde['machine_no'] = $val['machine_no'];
                $inde['type'] = 1;
                $inde['num'] = 1;
                $inde['yuanyin'] = '【' . $member['username'] . '】收到【系统】划拨机具，SN号【' . $val['machine_no'] . '】';
                $res[] = Db::name('member_machine_log')->insert($inde);
            }

            if ($this->check_arr($res)) {
                Db::commit();
            } else {
                Db::rollback();
            }

            $this->success('成功');
        }
        $this->_form($this->table, 'jijuoneadd');
    }

    /**
     * 划拨记录
     * @auth true
     * @menu true
     */
    public function snlog()
    {
        $this->title = '划拨记录';
        $query = $this->_query('member_machine_log')->like('machine_no')->equal('type');
        $query->where('type',1)->order('id desc')->page();
    }


}
