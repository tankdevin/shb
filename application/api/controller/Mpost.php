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
class Mpost extends BasicIndex
{

    /**
     * 我的机具总列表
     * @return [type] [description]
     *
     */
    public function machine()
    {
        $machine= Db::name('MemberMachine')->field('count(*) as num, class')->whereIn('type',[1,3,7])->where('mid',$this->uid)->group('class')->select();
        $this->success('成功', $machine);
    }
    
    /**
     * 我的机具
     * @return [type] [description]
     * 
     */
    public function machinelist()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $type = $this->request->post('type','');
        $class = $this->request->post('class','');
        $paixu = $this->request->post('paixu','asc');
        
        //FIXME: 2020.7.27 客户提出排序对调，目前默认的是DESC倒序排列，客户要求正序排列，前端调整的话，需重新打包APP，故后端更改，前端传DESC，后端处理成ASC.
        //以下为处理过程：
        if($paixu == 'desc'){
          $paixu = 'asc';  
        } else {
          $paixu = 'desc';     
        }
        
        
        if(!$type)$this->error('参数错误.');
        if(!$class)$this->error('参数错误.');
        $sql = Db::name('MemberMachine');
        if($type == 3){
            $sql->whereIn('type',[3,7]);
            //$where['type'] = ['in','3,7'];
        }else{
            $sql->where('type',$type);
        }
        $where['mid'] = $this->uid;
        $where['class'] = $class;
        $machine = $sql->where($where)->order('machine_no '.$paixu)->limit($start,$num)->select();
        foreach ($machine as $key=>$val){
            $machine[$key]['machname'] = Db::name('MemberShanghu')->where('machine_no',$val['machine_no'])->value('shanghu_name');
        }
        $this->success('成功', $machine);
    }

    /**
     * 我的机具数量
     * @return [type] [description]
     */
    public function machinenum()
    {
        $class = $this->request->post('class','');
        if(!$class)$this->error('参数错误.');
        $machine['nojihuo'] = Db::name('MemberMachine')->where('type',1)->where('class',$class)->where('mid',$this->uid)->count();
        $machine['yesjihuo'] = Db::name('member_shanghu')->alias('s')
                                ->leftJoin('member_machine m', 's.machine_no = m.machine_no')
                                ->where('s.type',1)
                                ->whereIn('m.type',[1,3,7])
                                ->where('m.class',$class)
                                ->where('s.mid',$this->uid)
                                ->where('m.mid',$this->uid)
                                ->count('s.id');
        $this->success('成功', $machine);
    }

    /**
     * 我的未入库机具
     * @return [type] [description]
     */
    public function machine_rukulist()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $type = $this->request->post('type','1');
        if(!$type)$this->error('参数错误.');
        $where['type'] = 1;
        $where['mid'] = $this->uid;
        $machine = Db::name('MemberMachine')->where($where)->order('create_time desc')->limit($start,$num)->select();
        $this->success('成功', $machine);
    }

    /**
     * 我的机具--搜索
     * @return [type] [description]
     */
    public function machine_sousuone()
    {
        $machiceno = $this->request->post('machiceno','');
        if(!$machiceno)$this->error('参数错误.');
        $where['machine_no'] = $machiceno;
        $where['mid'] = $this->uid;
        $machine = Db::name('MemberMachine')->where($where)->find();
        $this->success('成功', $machine);
    }

    /**
     * 我的机具--多搜索
     * @return [type] [description]
     */
    public function machine_sousu()
    {
        $machiceno = $this->request->post('machiceno','');
        $machicenoad = $this->request->post('machicenoad','');
        if(!$machiceno || !$machicenoad)$this->error('参数错误.');
        if($machiceno>$machicenoad)$this->error('参数错误.');
        $where['mid'] = $this->uid;
        $machine = Db::name('MemberMachine')->where($where)->whereBetween('machine_no',$machiceno.",".$machicenoad)->select();
        $this->success('成功', $machine);
    }

    /**
     * 机具下发
     * @return [type] [description]
     */
    public function machine_xiafa()
    {
        $machineno = $this->request->post('machineno','');
        $phone = $this->request->post('phone','');
        //$machineno = json_decode($machineno,true);
        if(!$phone)$this->error('参数错误.');
        if(count($machineno)<5 || count($machineno)%5 !=0)$this->error('机具必须大于5台，并且是5的倍数.');

        $memberinfo = DB::name('StoreMember')->where(['phone'=>$phone,'status'=>0])->where('find_in_set('.$this->uid.',leaders)')->find();
        if(!$memberinfo)$this->error('手机号有误..');
        //if($memberinfo['first_leader'] != $this->uid )$this->error('手机号有误.');

        $mlever = Db::name('member_machine')->where(['mid'=>$this->uid])->whereIn('type','1,3,6')->count();
        if(($mlever-count($machineno))<5)$this->error('机具必须留存5台.');

        $mleadsaver = Db::name('member_machine')->where(['mid'=>$memberinfo['id']])->whereIn('type','1,3,6')->count();//伙伴机具数量
        $mlefgdfgdfver = Db::name('store_member_laver')->where('num','<=',$mleadsaver+count($machineno))->order('num desc')->find();

        if(!is_array($machineno)){
            $machlist[] = $machineno;
        }else{
            $machlist = $machineno;
        }

        Db::startTrans();

        foreach ($machlist as $value){
            $res[] = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$value])->update(['mid'=>$memberinfo['id'],'update_time'=>date('Y-m-d H:i:s')]);

            $where['mid'] = $memberinfo['id'];
            $where['form_mid'] = $this->uid;
            $where['machine_no'] = $value;
            $where['type'] = 1;
            $where['num'] = 1;
            $where['yuanyin'] = '【' . $memberinfo['username'] . '】收到【'.$this->user['username'].'】划拨机具，SN号【' . $value . '】';
            $res[] = Db::name('MemberMachineLog')->insert($where);
        }
        if($memberinfo['recommend_one'] == 0){
            
            $res[] = $this->mlog($memberinfo['first_leader'], 'recommend_prize', 1, 150, '推荐奖', $memberinfo['id']);//推荐奖150元
            $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['recommend_one'=>1]);
            
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
     * 机具下发 下级用户确认机具入库
     * @return [type] [description]
     */
    public function machine_upruku()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');

        $asdfd = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->find();
        if(!$asdfd)$this->error('参数错误.');
        $where['mid'] = $asdfd['mid'];
        $where['form_mid'] = $asdfd['form_mid'];
        $where['machine_no'] = $asdfd['machine_no'];
        $where['type'] = 1;
        $mlever = Db::name('store_member_laver')->where('num','<=',$this->user['jiju_num']+1)->order('num desc')->find();

        Db::startTrans();
        $res[] = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->update(['update_time'=>date('Y-m-d H:i:s')]);
        $res[] = Db::name('MemberMachineLog')->insert($where);
        $res[] = Db::name('store_member')->where(['id'=>$this->uid])->setInc('jiju_num');
        if($mlever['id']!=$this->user['vip_level']&&!empty($mlever)){
            $res[] = Db::name('store_member')->where(['id'=>$this->uid])->update(['vip_level'=>$mlever['id']]);
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
     * 机具激活确认
     * @return [type] [description]
     */
    public function machine_upjihuo()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');

        $asdfd = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->find();
        if(!$asdfd)$this->error('参数错误.');
        $where['mid'] = $asdfd['mid'];
        $where['form_mid'] = $asdfd['form_mid'];
        $where['machine_no'] = $asdfd['machine_no'];
        $where['type'] = 3;

        $banding['mid'] = $asdfd['mid'];
        $banding['machine_no'] = $asdfd['machine_no'];
        $banding['type'] = 1;

        Db::startTrans();
        $res[] = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->update(['type'=>3,'update_time'=>date('Y-m-d H:i:s')]);
        $res[] = Db::name('MemberMachineLog')->insert($where);
        $res[] = Db::name('MemberShanghu')->insert($banding);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 机具解绑机具详情
     * @return [type] [description]
     */
    public function machine_jiebanginfo()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');

        $asdfd = Db::name('MemberMachine')->where(['type'=>3,'mid'=>$this->uid,'id'=>$id])->find();
        if(!$asdfd)$this->error('参数错误.');
        $asdfd['shanghu'] = Db::name('MemberShanghu')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$asdfd['machine_no'],'type_statu' => 1])->find();
        $asdfd['member'] = $this->user;
        $this->success('成功',$asdfd);

    }


    /**
     * 机具解绑提交
     * @return [type] [description]
     */
    public function machine_jiebang()
    {
        $id = $this->request->post('id','');
        $yuanyin = $this->request->post('yuanyin','');

        if(!$id||!$yuanyin)$this->error('参数错误.');
        $jijuinfo = Db::name('MemberMachine')->where(['type'=>3,'mid'=>$this->uid,'id'=>$id])->find();
        if(!$jijuinfo)$this->error('参数错误.');
        $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$jijuinfo['machine_no'],'type_statu' => 1])->find();
        if(!$shanghuinfo)$this->error('参数错误.');

        $where['mid'] = $jijuinfo['mid'];
        $where['form_mid'] = $jijuinfo['form_mid'];
        $where['machine_no'] = $jijuinfo['machine_no'];
        $where['type'] = 5;
        $where['yuanyin'] = $yuanyin;

        Db::startTrans();
        $res[] = Db::name('MemberShanghu')->where(['id'=>$shanghuinfo['id'],'type'=>1])->update(['type'=>5,'update_time'=>date('Y-m-d H:i:s'),'yuanyin'=>$yuanyin,'status'=>1]);
        $res[] = Db::name('MemberMachineLog')->insert($where);
        $res[] = Db::name('MemberMachine')->where(['id'=>$jijuinfo['id'],'type'=>3])->update(['type'=>5,'update_time'=>date('Y-m-d H:i:s'),'status'=>1]);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }


    /**
     * 机具换机机具详情
     * @return [type] [description]
     */
    public function machine_huanjiinfo()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');
        $id = explode(',',$id);
        //$id = json_encode($id,true);
        $asdfd['list'] = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->select();
        if(!$asdfd)$this->error('参数错误.');
        $asdfd['member'] = $this->user;
        $this->success('成功',$asdfd);

    }


    /**
     * 机具换机提交
     * @return [type] [description]
     */
    public function machine_huanji()
    {
        $id = $this->request->post('id','');
        $yuanyin = $this->request->post('yuanyin','');

        if(!$id||!$yuanyin)$this->error('参数错误.');
        $jijuinfo = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'id'=>$id])->find();
        if(!$jijuinfo)$this->error('参数错误.');

        $where['mid'] = $jijuinfo['mid'];
        $where['form_mid'] = $jijuinfo['form_mid'];
        $where['machine_no'] = $jijuinfo['machine_no'];
        $where['type'] = 4;
        $where['yuanyin'] = $yuanyin;

        Db::startTrans();
        $res[] = Db::name('MemberMachineLog')->insert($where);
        $res[] = Db::name('MemberMachine')->where(['id'=>$jijuinfo['id'],'type'=>1])->update(['type'=>4,'update_time'=>date('Y-m-d H:i:s'),'status'=>1]);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 我的商户
     * @return [type] [description]
     */
    public function shanghulist()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $jiaoyi = $this->request->post('jiaoyi','desc');
        $sjian = $this->request->post('sjian','desc');

        $where['a.type'] = 1;
        $where['a.mid'] = $this->uid;
        $count = Db::name('MemberShanghu')->alias('a')->leftJoin('store_member b','a.mid = b.id')
            ->where($where)->count();
        $machine = Db::name('MemberShanghu')->alias('a')->leftJoin('store_member b','a.mid = b.id')
            ->where($where)->order('create_time '.$sjian)->field('a.*,b.phone')->limit($start,$num)->select();
        foreach ($machine as $key =>$val){
            //var_dump($val['machine_no']);die;
            $txnAmt = Db::name('member_shanghu_order_member')->where('snNo',$val['machine_no'])->where('yue_time',date('Y-m'))->sum('txnAmt');
            // $txnAmtad = Db::name('member_shanghu_order_member')->where('snNo',$val['machine_no'])->whereIn('txnCd',[6,8,9,10,12])->where('yue_time',date('Y-m'))->sum('txnAmt');
            $machine[$key]['num_money'] = $txnAmt;
            $machine[$key]['count'] = $count;
        }
        $last_names = array_column($machine,'num_money');
        if($jiaoyi == 'desc'){
            array_multisort($last_names,SORT_DESC,$machine);
        }else{
            array_multisort($last_names,SORT_ASC,$machine);
        }
        $this->success('成功', $machine);
    }

    /**
     * 商户详情
     * @return [type] [description]
     */
    public function shanghuinfo()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');

        $where['id'] = $id;
        $machine = Db::name('MemberShanghu')->where($where)->find();
        $this->success('成功', $machine);
    }

    /**
     * 商户交易
     * @return [type] [description]
     */
    public function shanghulog()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');
        $where['id'] = $id;
        $machine = Db::name('MemberShanghu')->where($where)->find();
        $machine['log'] = Db::name('member_shanghu_order')->where('is_chuli',1)->where('ttxnSts','S')->where(['snNo'=>$machine['machine_no']])->order('id desc')->limit($start,$num)->select();
        $this->success('成功', $machine);
    }

    /**
     * 商户交易
     * @return [type] [description]
     */
    public function shanghulognew()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');
        $where['id'] = $id;
        $machine = Db::name('MemberShanghu')->where($where)->find();

        $info['post'] =  Db::name('member_shanghu_order_member')
            ->where(['snNo'=>$machine['machine_no'],'crdTyp'=>'CUP'])
            ->where('yue_time',date('Y-m'))->count();
        $info['postmoney'] =  Db::name('member_shanghu_order_member')
            ->where(['snNo'=>$machine['machine_no'],'crdTyp'=>'CUP'])
            ->where('yue_time',date('Y-m'))->sum('txnAmt');

        $info['shanfu'] =  Db::name('member_shanghu_order_member')
            ->where(['snNo'=>$machine['machine_no']])->whereIn('crdTyp',[10,12])
            ->where('yue_time',date('Y-m'))->count();
        $info['shanfumoney'] =  Db::name('member_shanghu_order_member')
            ->where(['snNo'=>$machine['machine_no']])->whereIn('crdTyp',[10,12])
            ->where('yue_time',date('Y-m'))->sum('txnAmt');

        $info['shaoma'] =  Db::name('member_shanghu_order_member')
        ->where(['snNo'=>$machine['machine_no']])->where('crdTyp','<>','CUP')
            ->where('yue_time',date('Y-m'))->count();
        $info['shaomamoney'] =  Db::name('member_shanghu_order_member')
        ->where(['snNo'=>$machine['machine_no']])->where('crdTyp','<>','CUP')
            ->where('yue_time',date('Y-m'))->sum('txnAmt');

        $info['meyue'] =  $info['postmoney']+$info['shanfumoney']+$info['shaomamoney'];
        $info['mesum'] = Db::name('member_shanghu_order_member') ->where(['snNo'=>$machine['machine_no']])->sum('txnAmt');
        $machine['log'] = $info;
        $this->success('成功', $machine);
    }

    /**
     * 添加商户
     * @return [type] [description]
     */
    public function shanghuadd()
    {
        $shanghu_name = $this->request->post('name','');
        $machine_no = $this->request->post('machine_no','');
        if(!$shanghu_name||!$machine_no)$this->error('参数错误.');

        $shanghifn = Db::name('MemberShanghu')->where(['machine_no'=>$machine_no])->whereIn('type_statu','0,1')->find();
        $shanghifnad = Db::name('member_machine')->where(['machine_no'=>$machine_no])->find();
        if($shanghifn || $shanghifnad)$this->error('商户已存在，请不要重复提交.');

        $where['mid'] = $this->uid;
        $where['shanghu_name'] = $shanghu_name;
        $where['machine_no'] = $machine_no;
        $where['type'] = 1;
        if(Db::name('MemberShanghu')->insert($where)){
            $this->success('成功');
        }else{
            $this->error('失败');
        }
    }

    /**
     * 添加商户->审核列表
     * @return [type] [description]
     */
    public function shanghu_shenghelist()
    {
        $member = Db::name('store_member')->where(['first_leader'=>$this->uid])->field('id')->select();
        $memberid = array_column($member,'id');
        $jijulist = Db::name('MemberShanghu')->alias('a')->leftJoin('store_member b','a.mid = b.id')
            ->whereIn('mid',$memberid)->where(['type'=>1,'type_statu'=>0])->field('a.*,b.nickname,b.username,b.phone')->select();

        $this->success('成功',$jijulist);
    }

    /**
     * 添加商户->审核
     * @return [type] [description]
     */
    public function shanghu_shengheup()
    {
        $id = $this->request->post('id','');
        $type = $this->request->post('type','1');
        if(!$id)$this->error('参数错误.');
        if($type!=1&&$type!=2)$this->error('参数错误');
        if(Db::name('MemberShanghu')->where(['id'=>$id,'type'=>1,'type_statu'=>0])->update(['type_statu'=>$type])){
            $this->success('成功');
        }
        $this->error('失败.');

    }

    /**
     * 流动记录
     * @return [type] [description]
     */
    public function machinelog()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $type = $this->request->post('type','1');
        if($type == 2 || $type == 7){
            $where['a.form_mid'] = $this->uid;
            if($type == 2){
                $where['a.type'] = 1;
            }else{
                $where['a.type'] = $type;
            }
        }else{
            $where['a.mid'] = $this->uid;
            $where['a.type'] = $type;
        }
        $info = Db::name('member_machine_log')->alias('a')->leftJoin('store_member b','a.form_mid = b.id')->where($where)->field('a.*,b.nickname')->limit($start,$num)->order('id desc')->select();
        foreach ($info as $key=>$val){
            if($type == 2){
                $info[$key]['phone'] = Db::name('store_member')->where('id',$val['mid'])->value('phone');
            }else{
                $info[$key]['phone'] = Db::name('store_member')->where('id',$val['form_mid'])->value('phone');
            }
        }

        $this->success('成功',$info);

    }

    /**
     * 我的
     * @return [type] [description]
     */
    public function shousuomemer()
    {
        $num = $this->request->post('num','');
        $phone = $this->request->post('phone','');
        if(!$num || $num<1)$this->error('参数错误.');
        $merber = Db::name('store_member')->where(['phone'=>$phone])->find();
        if($this->user['first_leader']!=$merber['id'])$this->error('未找到伙伴');
        $shuliang  = Db::name('member_machine')->where(['mid'=>$merber['id'],'type'=>1])->count();
        if($shuliang <$num)$this->error('机具不足.');

        $this->success('成功', $merber);
    }

    /**
     * 机具解绑机具详情
     * @return [type] [description]
     */
    public function machineinfo()
    {
        $machine_no = $this->request->post('machine_no','');
        if(!$machine_no)$this->error('参数错误.');
        $asdfd = Db::name('member_shanghu')->where(['machine_no'=>$machine_no,'mid'=>$this->user,'type'=>1])->find();
        $this->success('成功',$asdfd);

    }

    /**
     * 机具转移详情
     * @return [type] [description]
     */
    public function machine_zhuanuser()
    {
        $phone = $this->request->post('phone');
        if(empty($phone))$this->error('手机号不能为空');

        $member = Db::name('store_member')->where('phone',$phone)->where('find_in_set('.$this->uid.',leaders)')->find();
        if(empty($member)){$this->error('无此用户.');}
//         if($member['vip_level'] == 1)$this->error('用户权限不足');
        if($member['id'] == $this->uid)$this->error('请不要重复提交');

        $this->success('成功',$member);

    }

    /**
     * 机具转移详情
     * @return [type] [description]
     */
    public function machine_zhuanyiinfo()
    {
        $id = $this->request->post('id','');
        $phone = $this->request->post('phone');
        if(!$id)$this->error('参数错误.');
        if(empty($phone))$this->error('手机号不能为空');

        $member = Db::name('store_member')->where('phone',$phone)->find();
        if(empty($member)){$this->error('无此用户.');}
//         if($member['vip_level'] == 1)$this->error('用户权限不足');
        if($member['id'] == $this->uid)$this->error('请不要重复提交');

        //$jijuinfo = Db::name('MemberMachine')->where(['type'=>3,'mid'=>$this->uid,'id'=>$id])->find();
        $jijuinfo = Db::name('MemberMachine')->where(['mid'=>$this->uid,'id'=>$id])->order('id desc')->find();
        if(!$jijuinfo)$this->error('机具错误.');

        $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$jijuinfo['machine_no']])->find();
        if(!$shanghuinfo)$this->error('商户错误.');

        $shanghuinfo['member'] = $member;

        $this->success('成功',$shanghuinfo);

    }

    /**
     * 机具转移提交
     * @return [type] [description]
     */
    public function machine_zhuanyi()
    {
        $id = $this->request->post('id','');
        if(!$id)$this->error('参数错误.');
        $phone = $this->request->post('phone');
        if(!$id)$this->error('参数错误.');
        if(empty($phone))$this->error('手机号不能为空');

        $member = Db::name('store_member')->where('phone',$phone)->find();
        if(empty($member)){$this->error('无此用户.');}
        //if($member['vip_level'] == 1)$this->error('用户权限不足');
        if($member['id'] == $this->uid)$this->error('请不要重复提交');

        //$jijuinfo = Db::name('MemberMachine')->where(['type'=>3,'mid'=>$this->uid,'id'=>$id])->find();
        $jijuinfo = Db::name('MemberMachine')->where(['mid'=>$this->uid,'id'=>$id])->order('id desc')->find();
        if(!$jijuinfo)$this->error('机具错误.');

        $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$jijuinfo['machine_no']])->find();
        if(!$shanghuinfo)$this->error('商户错误.');

        $where['mid'] = $member['id'];
        $where['form_mid'] = $jijuinfo['mid'];
        $where['machine_no'] = $jijuinfo['machine_no'];
        $where['type'] = 7;

        Db::startTrans();
        $res[] = Db::name('MemberShanghu')->where(['id'=>$shanghuinfo['id'],'type'=>1])->update(['mid'=>$member['id'],'update_time'=>date('Y-m-d H:i:s')]);
        $res[] = Db::name('MemberMachineLog')->insert($where);
        $res[] = Db::name('MemberMachine')->where(['id'=>$jijuinfo['id']])->update(['type'=>6,'update_time'=>date('Y-m-d H:i:s')]);
        unset($where['form_mid']);
        $where['ver'] = $jijuinfo['ver'];
        $where['class'] = $jijuinfo['class'];
        $res[] = Db::name('MemberMachine')->insert($where);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 商户名字录入
     * @return [type] [description]
     */
    public function machine_name()
    {
        $id = $this->request->post('id','');
        $name = $this->request->post('name','');
        $phone = $this->request->post('phone','');
        $bank_name = $this->request->post('bank_name','');
        $bank_no = $this->request->post('bank_no','');
        $bank_zhi = $this->request->post('bank_zhi','');
        if(!$id || !$name || !$phone|| !$bank_name|| !$bank_no)$this->error('参数不能为空.');
        
        $asdfd = Db::name('member_shanghu')->where(['id'=>$id])->find();
        if(!$asdfd)$this->error('参数错误.');
        $update = array(
            'shanghu_name'=>$name,
            'shanghu_phone'=>$phone,
            'bank_name'=>$bank_name,
            'bank_no'=>$bank_no,
            'bank_zhi'=>$bank_zhi,
        );
        $res = Db::name('member_shanghu')->where(['id'=>$id])->update($update);
        if ($res) {
            $this->success('成功');
        } else {
            $this->error('失败');
        }
    }

}
