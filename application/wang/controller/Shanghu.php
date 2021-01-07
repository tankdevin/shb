<?php
namespace app\wang\controller;

use library\Controller;
use library\tools\Csv;
use think\db;

/**
 * 内容管理
 * Class ContentBanner
 * @package app\wang\controller
 */
class Shanghu extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'MemberShanghu';

    /**
     * 入网管理
     * @auth true
     * @menu true
     */
    public function ruwang()
    {
        $this->title = '入网管理';
        $query = $this->_query($this->table)->like('title')->equal('status');
        $query->where(['type_statu'=>0])->order('id desc')->page();
    }

    /**
     * 入网管理审核通过
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function ruwangisguo()
    {
        $this->_save($this->table, ['type_statu' => '1']);
    }

    /**
     * 入网管理审核不通过
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function ruwangisnoguo()
    {
        $this->_save($this->table, ['type_statu' => '2']);
    }

    /**
     * 商户管理
     * @auth true
     * @menu true
     */
    public function shanghulist()
    {
        $this->title = '入网管理';

        $userid = $this->request->get('userid');
        $userphone = $this->request->get('userphone');
        $dabiao = $this->request->get('dabiao');
        $mid = '';
        if($userid){
            $mid = Db::name('store_member')->where('invite_code',$userid)->value('id');
        }
        if($userphone){
            $mid = Db::name('store_member')->where('phone',$userphone)->value('id');
        }

        $query = $this->_query($this->table)->like('machine_no')->like('shanghu_name')->equal('status');
        if($userid || $userphone){
            $query->equaladd('mid',$mid);
        }
        if($dabiao == 1){
            $query->where('num_money','>=',5000);
        }
        if($dabiao == 2){
            $query->where('num_money','<',5000);
        }
        $info = $query->where(['type'=>1])->order('id desc')->pagenew();

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
    
    public function shanghueditinfo()
    {
        $this->title = '修改商户信息';
        $id = $this->request->Get('id');
        $info = Db::name('member_shanghu')->field('shanghu_name,shanghu_phone,bank_name,bank_no,bank_zhi,machine_no,jihuo_time')->where(['id'=>$id])->find();
        if ($this->request->isPost()) {
            $update['shanghu_name'] = $this->request->post('shanghu_name');
            $update['shanghu_phone']= $this->request->post('shanghu_phone');
            $update['bank_name']= $this->request->post('bank_name');
            $update['bank_no']= $this->request->post('bank_no');
            $update['bank_zhi']= $this->request->post('bank_zhi');
            $update['jihuo_time']= $this->request->post('jihuo_time');
            
            $res = Db::name('member_shanghu')->where('id',$id)->update($update);
            
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
     * 商户转移
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function shanghulistedit()
    {
        $this->title = '商户转移';
        $aaa = [];
        if ($this->request->isPost()) {
            /*$phone = $this->request->post('phone');
            $id = $this->request->post('id');
            $where = ['phone' =>$phone];
            $mid = Db::name('store_member')->where($where)->value('id');
            $shanghuinfo = Db::name('member_shanghu')->where(['id'=>$id])->find();

            $indewher['mid'] = $mid;
            $indewher['form_mid'] = $shanghuinfo['mid'];
            $indewher['machine_no'] = $shanghuinfo['machine_no'];
            $indewher['type'] = 6;
            $indewher['num'] = 1;
            $indewher['yuanyin'] = '后台管理员转移';

            Db::name('member_shanghu')->where(['id'=>$id])->update(['mid'=>$mid]);
            Db::name('member_machine_log')->insert($indewher);*/

            $id = $this->request->post('id','');
            $phone = $this->request->post('phone');
            if(!$id)$this->error('参数错误.');
            if(empty($phone))$this->error('手机号不能为空');

            $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'id'=>$id])->find();
            if(!$shanghuinfo)$this->error('商户错误.');

            //$jijuinfo = Db::name('MemberMachine')->where(['type'=>3,'machine_no'=>$shanghuinfo['machine_no']])->find();
            $jijuinfo = Db::name('MemberMachine')->where(['mid'=>$shanghuinfo['mid'],'machine_no'=>$shanghuinfo['machine_no']])->order('id desc')->find();
            if(!$jijuinfo)$this->error('机具错误.');

            //$member = Db::name('store_member')->where('phone',$shanghuinfo['shanghu_phone'])->find();
            $member = Db::name('store_member')->where('phone',$phone)->find();
            if(empty($member))$this->error('无此用户.');
            if($member['ver'] != $jijuinfo['ver'])$this->error('版本不同');
            if($member['id'] == $jijuinfo['mid'])$this->error('请不要重复提交');

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

        $this->_form($this->table, 'shanghulistedit');
    }


    /**
     * 添加消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function shanghuadd()
    {
        $this->title = '添加商户';
        $userlaver = Db::name('store_member')->where('id','>',1)->select();
        $this->assign('userlaver',$userlaver);

        $this->_form($this->table, 'shanghuadd');
    }









    /**
     * banner管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '消息管理';
        $query = $this->_query($this->table)->like('title')->equal('status');
        $query->order('id desc')->page();
    }

    /**
     * 添加消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加消息分类';
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑消息分类';
        $this->_form($this->table, 'form');
    }

    /**
     * 禁用消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除消息分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->_delete($this->table);
    }

    /**
     * 录入激活机具
     */
    public function in_activation()
    {
        $this->title = '录入激活';
        if ($this->request->isPost()) {
            $sn = $this->request->post('machine_no');
            $ordTim = $this->request->post('txnTm');
            if (!$sn || !$ordTim) $this->error('参数错误');
            $jijuinfo = Db::name('member_machine')->where(['machine_no' => $sn, 'type' => 1])->where('mid','<>',0)->find();
            $jijuji = Db::name('member_shanghu_jihuo')->where(['machine_no' => $sn])->find();
            if (!$jijuinfo) $this->error('机具不存在或没有归属人..');
            if($jijuji) $this->error('该机具激活数据已存在，请勿重复操作..');
            
            $data['machine_no'] = $sn;
            $data['snNo'] = $sn;
            $data['ordTim'] = $ordTim;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['is_chuli'] = 0;
            $data['update_time'] = time();
            $res = Db::name('member_shanghu_jihuo')->insert($data);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'in_activation');
    }
    
    /**
     * 录入商户
     */
    public function in_merchant()
    {
        $this->title = '录入商户';
        if ($this->request->isPost()) {
            $sn = $this->request->post('machine_no');
            $merNm= $this->request->post('merNm');
            $corpNm= $this->request->post('corpNm');
            $cerNo= $this->request->post('cerNo','');
            $merTel= $this->request->post('merTel','');
            $acNo= $this->request->post('acNo','');
            if (!$sn || !$merNm || !$corpNm) $this->error('参数错误');
            $jijuinfo = Db::name('member_shanghu')->where(['machine_no' => $sn, 'type' => 1])->find();
            if (!$jijuinfo) $this->error('未找到满足条件的商户..');
            $data['machine_no'] = $sn;
            $data['snNo'] = $sn;
            $data['merNm'] = $merNm;
            $data['corpNm'] = $corpNm;
            $data['cerNo'] = $cerNo;
            $data['merTel'] = $merTel;
            $data['acNo'] = $acNo;
            $data['stltyp'] = 0;
            $data['tmBindTm'] = date('YmdHis');
            $data['creTm'] = date('YmdHis');
            $data['create_time'] = date('Y-m-d H:i:s');
            $res = Db::name('member_shanghu_information')->insert($data);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'in_merchant');
    }

    /**
     * 录入订单
     */
    public function in_order()
    {
        $this->title = '录入订单';
        if ($this->request->isPost()) {
            $data['snNo'] = $this->request->post('machine_no');
            $jijuinfo = Db::name('member_shanghu')->where(['machine_no' => $data['snNo'], 'type' => 1])->find();
            if (!$jijuinfo) $this->error('商户未激活或者激活信息暂未生效..');
//             $data['logNo'] = $this->request->post('logNo');
            if(empty($data['logNo'])) $data['logNo'] = 'L'.$data['snNo'].time();
            $data['txnCd'] = $this->request->post('txnCd');
            $data['crdTyp'] = $this->request->post('crdTyp');
            $data['txnTm'] = $this->request->post('txnTm');
            $data['txnAmt'] = $this->request->post('txnAmt');
            $data['crdNo'] = $this->request->post('crdNo');
            $data['trmNo'] = $this->request->post('trmNo');
            $data['batNo'] = $this->request->post('batNo');
            $data['cseqNo'] = $this->request->post('cseqNo');
            if (!$data['snNo']) $this->error('参数错误');
            $data['ttxnSts'] = '00';
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['is_chuli'] = 0;
            $data['update_time'] = time();
            $res = Db::name('member_shanghu_order')->insert($data);
            if ($res) {
                $this->success('成功');
            } else {
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'in_order');
    }
    
    public function daoru()
    {
        // 获取表单上传文件
        $file = request()->file('namefile');
        if(empty($file)) {
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move('public/upload');
        //获取文件（日期/文件），$info->getFilename();
        $filename = 'public/upload/'.$info->getSaveName();
        $handle = fopen($filename,'r');
        $csv = new Csv();
        $result = $csv->input_csv($handle); // 解析csv
        $len_result = count($result);
        if($len_result == 0){
            $this->error('此文件中没有数据！');
        }
        $data_values = '';
        $data_valuesad = '';
        for($i = 1;$i < $len_result+1;$i ++) { // 循环获取各字段值
            //for($i = 1;$i < 2;$i ++) { // 循环获取各字段值
            $arr = array_values($result[$i]);
            $id =$arr[0];
            $phone= iconv('gb2312','utf-8',$arr[1] ); // 中文转码
            $username = $arr[2];//iconv('GBK','utf-8',$arr[2]);
            //$nickname = iconv('gb2312','utf-8',$arr[3]);
            //$username = mb_convert_encoding($arr[2],"gb2312","UTF-8");
            $nickname = mb_convert_encoding($arr[3],"GBK","UTF-8");
            $is_vip = $arr[4];
            $end_time = $arr[5];
            $level = $arr[6];
            //$ordId = $arr[7];
            $time = date('Y-m-d H:i:s',strtotime($nickname));
            $user = Db::name('member_shanghu_jihuo')->where('machine_no',$id)->find();
            if(empty($user)){
                $data_values .= "('$id','$level','$id','$id','$phone','$username','$nickname','$end_time','$time'),";
                $userad = Db::name('member_shanghu_information')->where('machine_no',$id)->find();
                if(empty($userad)){
                    $usernasdadfs = str_replace('个体户','',str_replace(strstr($username, '2'),'',$username));
                    $data_valuesad .= "('$id','','$usernasdadfs','$nickname'),";
                }

            }

        }
        $data_values = substr($data_values,0,- 1 ); // 去掉最后一个逗号
        $data_valuesad = substr($data_valuesad,0,- 1 ); // 去掉最后一个逗号
        fclose($handle); // 关闭指针
        //echo "insert into member_shanghu_jihuo(machine_no,ordId,snNo,trmNo,merId,merNm,ordTim,ordAmt,create_time) values $data_values";
        // 批量插入数据表中
        $result = DB::execute("insert into member_shanghu_jihuo(machine_no,ordId,snNo,trmNo,merId,merNm,ordTim,ordAmt,create_time) values $data_values" );
        $result = DB::execute("insert into member_shanghu_information(machine_no,merTel,corpNm,creTm) values $data_valuesad" );
        if($result){
            $this->success('文件上传成功，数据已经导入！','admin/user/index',3);
        }else{
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }

    public function daoru111()
    {
        // 获取表单上传文件
        $file = request()->file('namefile');
        if(empty($file)) {
            $this->error('请选择上传文件');
        }
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move('public/upload');
        //获取文件（日期/文件），$info->getFilename();
        $filename = 'public/upload/'.$info->getSaveName();
        $handle = fopen($filename,'r');
        $csv = new Csv();
        $result = $csv->input_csv($handle); // 解析csv
        $len_result = count($result);
        if($len_result == 0){
            $this->error('此文件中没有数据！');
        }
        $data_values = '';
        for($i = 1;$i < $len_result+1;$i ++) { // 循环获取各字段值
            //for($i = 1;$i < 2;$i ++) { // 循环获取各字段值
            $arr = array_values($result[$i]);
            $logNo =$arr[0];
            $acDt =$arr[1];
            $txnCd =$arr[2];
            $txnFeeFlg =$arr[3];
            $txnBusTyp =$arr[4];
            $txnTm =$arr[5];
            $ttxnSts =$arr[6];
            $crdNo =$arr[7];
            $crdFlg =$arr[8];
            $txnAmt =$arr[9];
            $mercFeeAmt =$arr[10];
            $businessThrAmt =$arr[11];
            $trmNo =$arr[12];
            $batNo =$arr[13];
            $cseqNo =$arr[14];
            $mercId =$arr[15];
            $mercNm =$arr[16];
            $snNo =$arr[17];
            $stlTyp =$arr[18];
            $feeTyp =$arr[19];
            $agtMercId =$arr[20];
            $agtMercNm =$arr[21];
            $agtMercLvl =$arr[22];
           //$phone= iconv('gb2312','utf-8',$arr[1] ); // 中文转码
            //$username = mb_convert_encoding($arr[2],"gb2312","UTF-8");
            //$time = date('Y-m-d H:i:s',strtotime($nickname));
            $user = Db::name('member_shanghu_order')->where('logNo',$logNo)->where('is_chuli',1)->find();
            if(empty($user)){
                $data_values .= "('$logNo','$acDt','$txnCd','$txnFeeFlg','$txnBusTyp','$txnTm','$ttxnSts','$crdNo','$crdFlg','$txnAmt','$mercFeeAmt','$businessThrAmt','$trmNo','$batNo','$cseqNo','$mercId','$mercNm','$snNo','$stlTyp','$feeTyp','$agtMercId','$agtMercNm','$agtMercLvl'),";
            }

        }
        $data_values = substr($data_values,0,- 1 ); // 去掉最后一个逗号
        fclose($handle); // 关闭指针
        //echo "insert into member_shanghu_jihuo(machine_no,ordId,snNo,trmNo,merId,merNm,ordTim,ordAmt,create_time) values $data_values";
        // 批量插入数据表中
        $result = DB::execute("insert into member_shanghu_order(logNo,acDt,txnCd,txnFeeFlg,txnBusTyp,txnTm,ttxnSts,crdNo,crdFlg,txnAmt,mercFeeAmt,businessThrAmt,trmNo,batNo,cseqNo,mercId,mercNm,snNo,stlTyp,feeTyp,agtMercId,agtMercNm,agtMercLvl) values $data_values" );
        if($result){
            $this->success('文件上传成功，数据已经导入！','admin/user/index',3);
        }else{
            // 上传失败获取错误信息
            $this->error($file->getError());
        }

    }
    
    /**
     * 商户交易积分金额全部转移
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function shanghuzhuanyi()
    {
        $this->title = '商户转移';
        $aaa = [];
        if ($this->request->isPost()) {
            $id = $this->request->post('id','');
            $phone = $this->request->post('phone');
            if(!$id)$this->error('参数错误.');
            if(empty($phone))$this->error('手机号不能为空');

            $shanghuinfo = Db::name('MemberShanghu')->where(['type'=>1,'id'=>$id])->find();
            if(!$shanghuinfo)$this->error('商户错误.');
            $jijuinfo = Db::name('MemberMachine')->where(['mid'=>$shanghuinfo['mid'],'machine_no'=>$shanghuinfo['machine_no']])->order('id desc')->find();
            if(!$jijuinfo)$this->error('机具错误.');
            $member = Db::name('store_member')->where('phone',$phone)->find();
            if(empty($member))$this->error('无此用户.');
            if($member['ver'] != $jijuinfo['ver'])$this->error('版本不同');
            if($member['id'] == $jijuinfo['mid'])$this->error('请不要重复提交');

            Db::startTrans();

//             $res[] = Db::name('store_member')->where('id',$jijuinfo['mid'])->setDec('integral',120);
//             $res[] = Db::name('store_member')->where('id',$jijuinfo['mid'])->setDec('money',100);
//             $res[] = Db::name('store_member')->where('id',$member['id'])->setInc('integral',120);
//             $res[] = Db::name('store_member')->where('id',$member['id'])->setInc('money',100);
            for ($i = 0;$i<5;$i++){
                $timenew = date('Y-m',strtotime('- '.$i.' month'));
                if($timenew<date('Y-m',strtotime($shanghuinfo['jihuo_time']))){
                    break;
                }
                $jiaoyi = Db::name('member_shanghu_order_member')->where(['yue_time'=>$timenew,'snNo'=>$shanghuinfo['machine_no']])->sum('txnAmt');
                $res[] =  Db::name('member_shanghu_order_log')->where(['mid'=>$jijuinfo['mid'],'montime'=>$timenew])->setDec('oneyeji',$jiaoyi);
                $res[] =  Db::name('member_shanghu_order_log')->where(['mid'=>$jijuinfo['mid'],'montime'=>$timenew])->setDec('numyeji',$jiaoyi);

                $member_shanghu_order_log = Db::name('member_shanghu_order_log')->where(['mid'=>$member['id'],'montime'=>$timenew])->find();
                if(!empty($member_shanghu_order_log)){
                    $res[] =  Db::name('member_shanghu_order_log')->where(['mid'=>$member['id'],'montime'=>$timenew])->setInc('oneyeji',$jiaoyi);
                    $res[] =  Db::name('member_shanghu_order_log')->where(['mid'=>$member['id'],'montime'=>$timenew])->setInc('numyeji',$jiaoyi);
                }else{
                    $where['mid'] = $member['id'];
                    $where['montime'] = $timenew;
                    $where['oneyeji'] = $jiaoyi;
                    $where['numyeji'] = $jiaoyi;
                    $res[] = Db::name('member_shanghu_order_log')->insert($where);
                }
            }
            $res[] = Db::name('member_machine')->where('id',$jijuinfo['id'])->update(['mid'=>$member['id']]);
            $res[] = Db::name('member_shanghu')->where('id',$shanghuinfo['id'])->update(['mid'=>$member['id']]);

            $res[] = Db::name('member_money_log')->where(['mid'=>$jijuinfo['mid'],'status'=>4,'extends'=>$shanghuinfo['machine_no']])->update(['mid'=>$member['id']]);
            $res[] = Db::name('member_shanghu_order_member')->where(['mid'=>$jijuinfo['mid'],'snNo'=>$shanghuinfo['machine_no']])->update(['mid'=>$member['id']]);
            if ($this->check_arr($res)) {
                Db::commit();
                $this->success('成功');
            } else {
                Db::rollback();
                $this->error('失败');
            }
        }
        $this->_form($this->table, 'shanghuzhuanyi');
    }

}
