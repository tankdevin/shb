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
class Plan extends BasicIndex
{
    //TODO：接口未按照文档进行去重判断，会导致交易数据多次下发，导致整个交易数据统计存在问题。2020.7.27 19：12
    /**
     * 激活返现信息通知接口
     * @return [type] [description]
     */
    public function activate_reflow()
    {
        $jsonData = $this->request->post('jsonData','');
        
        $this->prf('激活信息：'.$jsonData);
        //halt($jsonData);
        $checkValue = $this->request->post('checkValue','');
        if(!$jsonData||!$checkValue){return '非法请求';exit;}

        $jsonData = json_decode($jsonData,true);
        $orderinfo = json_decode($jsonData['orderDataList'],true);
        if(!$orderinfo){return '非法请求.';exit;}
        //$orderDataList = $orderinfo[0];
        $membershanghu = Db::name('MemberShanghuJihuo')->where(['extSeqId'=>$jsonData['extSeqId']])->find();
        if($membershanghu){
             $this->prf('激活去重：'.$membershanghu);  
             return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
             exit;
        }
        try {
            foreach ($orderinfo as $orderDataList){

                $membershanghu = Db::name('MemberShanghuJihuo')->where(['machine_no'=>$orderDataList['snNo']])->find();
                if($membershanghu){continue;}

                $where = [];
                $where['machine_no'] = $orderDataList['snNo'];//Sn编号
                $where['ordId'] = time();
                $where['snNo'] = $orderDataList['snNo'];//Sn编号
                $where['trmNo'] = $orderDataList['snNo'];
                $where['merId'] = $orderDataList['mercId'];
                $where['merNm'] = $orderDataList['mercNm'];
                $where['merProv'] = '101';
                $where['merCity'] = '101';
                $where['agentId'] = $orderDataList['agtMercId'];
                $where['agentNm'] = 'Umi伙伴';
                $where['orgCode'] = '101';
                $where['orgName'] = 'Umi伙伴';
                $where['ordTim'] = $orderDataList['vipExtDt'];
                $where['ordAmt'] = $orderDataList['promotionAmt'];
                $where['ordType'] = '99';
                $where['policyId'] = '1';
                $where['activType'] = $orderDataList['termAcesMod'];
                $where['extSeqId'] = $jsonData['extSeqId'];

                $res[] = Db::name('member_shanghu_jihuo')->insert($where);
                //$this->success('成功');
            }
            if ($this->check_arr($res)) {
                Db::commit();
               $this->prf('激活成功：'.$res); 
                return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
            } else {
                Db::rollback();
                $this->prf('激活写入失败：'.$res);
                return '传输失败';
            }
        } catch (Exception $e) {
             $this->prf('激活异常数据'.$e);
            return '异常数据';
            // die(); // 终止异常
        }
    }

    /**
     * 商户信息通知接口
     * @return [type] [description]
     */
    public function activate_information()
    {
        $jsonData = $this->request->post('jsonData','');
        $this->prf('商户信息'.$jsonData);
        $checkValue = $this->request->post('checkValue','');
        if(!$jsonData||!$checkValue){return '非法请求';exit;}

        $jsonData = json_decode($jsonData,true);

        $orderinfo = json_decode($jsonData['orderDataList'],true);

        if(!$orderinfo){return '非法请求';exit;}

        $membershanghu = Db::name('member_shanghu_information')->where(['extSeqId'=>$jsonData['extSeqId']])->find();
        if($membershanghu){
           ;
             $this->prf('商户信息去重：'.$membershanghu);  
             return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
             exit;
        }
        try{
            foreach ( $orderinfo as $orderDataList){
                $membershanghu = Db::name('member_shanghu_information')->where(['machine_no'=>$orderDataList['snNo'],'merId'=>$orderDataList['mercId']])->find();
                if($membershanghu){continue;}

                $where['machine_no'] = $orderDataList['snNo'];
                $where['merId'] = $orderDataList['mercId'];
                $where['merNm'] = $orderDataList['mercNm'];
                $where['posMerId'] = $orderDataList['posMerId'];
                $where['snNo'] = $orderDataList['snNo'];
                $where['corpNm'] = $orderDataList['corpNm'];
                $where['cerNo'] = $orderDataList['cerNo'];
                $where['merTel'] = $orderDataList['merTel'];
                $where['acNo'] = $orderDataList['acNo'];
                $where['stltyp'] = $orderDataList['stlTyp'];
                $where['creTm'] = $orderDataList['creTm'];
                $where['tmBindTm'] = $orderDataList['tmBindTm'];
                $where['extSeqId'] = $jsonData['extSeqId'];

                $res[] = Db::name('member_shanghu_information')->insert($where);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                $this->prf('商户信息成功：'.$res); 
                return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
            } else {
                Db::rollback();
                 $this->prf('商户信息写入失败：'.$res);
                return '传输失败';
            }
        } catch (Exception $e) {
             $this->prf('商户信息异常数据'.$e);
            return '异常数据';
            // die(); // 终止异常
        }
    }

    /**
     * 商户费率通知信息
     * @return [type] [description]
     */
    public function activate_rate()
    {
        $jsonData = $this->request->post('jsonData','');
        $this->prf('商户费率：'.$jsonData);
        $checkValue = $this->request->post('checkValue','');
        if(!$jsonData||!$checkValue){return '非法请求';exit;}

        $jsonData = json_decode($jsonData,true);
        $orderinfo = json_decode($jsonData['orderDataList'],true);
        if(!$orderinfo){return '非法请求';exit;}
        //$orderDataList = $orderDataList[0];
        $membershanghu = Db::name('member_shanghu_rate')->where(['extSeqId'=>$jsonData['extSeqId']])->find();
        if($membershanghu){
           $this->prf('商户费率去重：'.$membershanghu);  
            return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
            exit;
        }
        try{
            foreach ( $orderinfo as $orderDataList) {
                $membershanghu = Db::name('member_shanghu_rate')->where(['mercId' => $orderDataList['mercId']])->find();
                if ($membershanghu) {continue;}

                $where['machine_no'] = $orderDataList['mercId'];
                $where['mercId'] = $orderDataList['mercId'];
                $where['pos'] = $orderDataList['pos'];
                $where['wx'] = $orderDataList['wx'];
                $where['ali'] = $orderDataList['ali'];
                $where['extSeqId'] = $jsonData['extSeqId'];

                $res[] = Db::name('member_shanghu_rate')->insert($where);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                $this->prf('商户费率成功：'.$res);  
                return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
            } else {
                Db::rollback();
                 $this->prf('商户费率写入失败：'.$res);
                return '传输失败';
            }
        } catch (Exception $e) {
             $this->prf('商户费率异常数据：'.$e);
            return '异常数据';
            // die(); // 终止异常
        }
    }

    /**
     * 商户交易信息通知
     * @return [type] [description]
     */
    public function back_activate_order()
    {
        $jsonData = $this->request->post('jsonData','');
        $this->prf('商户交易信息：'.$jsonData);
        $checkValue = $this->request->post('checkValue','');
        if(!$jsonData||!$checkValue){return '非法请求';exit;}

        $jsonData = json_decode($jsonData,true);
        $orderinfo = json_decode($jsonData['orderDataList'],true);
        if(!$orderinfo){return '非法请求';exit;}
        //$orderDataList = $orderinfo[0];
        $membershanghu = Db::name('member_shanghu_order')->where(['extSeqId'=>$jsonData['extSeqId']])->find();
        if($membershanghu){
          $this->prf('商户交易信息去重'.$membershanghu); 
          return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
             exit;
        }
        try{
            foreach ( $orderinfo as $orderDataList) {
                $membershanghu = Db::name('member_shanghu_order')->where(['logNo' => $orderDataList['logNo']])->find();
                if ($membershanghu) {continue;}

                $where['logNo'] = $orderDataList['logNo'];
                $where['acDt'] = $orderDataList['acDt'];
                $where['txnCd'] = $orderDataList['txnCd'];
                $where['txnFeeFlg'] = $orderDataList['txnFeeFlg'];
                $where['txnBusTyp'] = $orderDataList['txnBusTyp'];
                $where['txnTm'] = $orderDataList['txnTm'];
                $where['ttxnSts'] = $orderDataList['ttxnSts'];
                $where['crdNo'] = $orderDataList['crdNo'];
                $where['crdFlg'] = $orderDataList['crdFlg'];
                $where['txnAmt'] = $orderDataList['txnAmt'];
                $where['mercFeeAmt'] = $orderDataList['mercFeeAmt'];
                $where['businessThrAmt'] = $orderDataList['businessThrAmt'];
                $where['trmNo'] = $orderDataList['trmNo'];
                $where['batNo'] = $orderDataList['batNo'];
                $where['cseqNo'] = $orderDataList['cseqNo'];
                $where['mercId'] = $orderDataList['mercId'];
                $where['mercNm'] = $orderDataList['mercNm'];
                $where['snNo'] = $orderDataList['snNo'];
                $where['stlTyp'] = $orderDataList['stlTyp'];
                $where['feeTyp'] = $orderDataList['feeTyp'];
                $where['agtMercId'] = $orderDataList['agtMercId'];
                $where['agtMercNm'] = $orderDataList['agtMercNm'];
                $where['agtMercLvl'] = $orderDataList['agtMercLvl'];
                $where['extSeqId'] = $jsonData['extSeqId'];

                $res[] = Db::name('member_shanghu_order')->insert($where);
            }
            if ($this->check_arr($res)) {
                Db::commit();
                 $this->prf('商户交易信息成功'.$res);  
                return 'SUCCESS';
            } else {
                Db::rollback();
                $this->prf('商户交易信息写入失败'.$res);
                return '传输失败';
            }
        } catch (Exception $e) {
            $this->prf('商户交易信息异常数据'.$e);
            return '异常数据';
            // die(); // 终止异常
        }
    }

    /**
     * 商户交易本机状态修改
     * @return [type] [description]
     */
    public function activate_update()
    {
        $day = date('d');
        $honder = date('H');
        if($day == 1){
            if($honder<6){
                Db::name('member_shanghu')->where(['month_money'=>0])->update(['month_money'=>0]);
            }
        }

        $orderlist =  Db::name('member_shanghu_order')->where(['is_chuli'=>0])->limit(15)->select();
        foreach ($orderlist as $key => $val)
        {
            $res = [];
            $member_shanghu =  Db::name('member_shanghu')->where(['machine_no'=>$val['mercId']])->find();

            Db::startTrans();

            if($member_shanghu){
                if($val['ttxnSts'] == 'S'){
                    $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setInc('num_money',$val['txnAmt']);
                    $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setInc('month_money',$val['txnAmt']);
                }
                if($val['ttxnSts'] == 'C'){
                    $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setDec('num_money',$val['txnAmt']);
                    $res[] = Db::name('member_shanghu')->where(['id'=>$member_shanghu['id']])->setDec('month_money',$val['txnAmt']);
                }
                $where['mid'] = $member_shanghu['mid'];
                $where['machine_no'] = $val['mercId'];
                $where['ttxnSts'] = $val['ttxnSts'];
                $where['txnAmt'] = $val['txnAmt'];
                $where['ordid'] = $val['id'];
                $where['montime'] = date('Y-m');
                $res[] = Db::name('member_shanghu_order_log')->insert($where);
                $res[] = Db::name('member_shanghu_order')->where(['id'=>$val['id']])->update(['is_chuli'=>1,'update_time'=>time()]);
            }else{
                $res[] = Db::name('member_shanghu_order')->where(['id'=>$val['id']])->update(['is_chuli'=>3,'update_time'=>time()]);
            }

            if ($this->check_arr($res)) {
                Db::commit();
     
                 $this->prf('商户交易本机状态成功'.$res);  
                return  'RECV_ORD_ID_'.$jsonData['extSeqId'];
            } else {
                Db::rollback();
                $this->prf('商户交易本机状态写入失败'.$res);
                return '失败';
            }

        }
    }
    
    /**
     * 商户交易信息通知
     * @return [type] [description]
     */
    public function activate_order()
    {
        $data = $this->request->get();
        if(empty($data)){
            $data = $this->request->post();
        }
        $this->prf('商户交易信息：'.json_encode($data));
//         $checkValue = $this->request->post('checkValue','');
        if(!$data){return '非法请求';exit;}
        
        $extData= json_decode($data['extData'],true);
        if(!$extData){return '非法请求';exit;}
        //$orderDataList = $orderinfo[0];
        $membershanghu = Db::name('member_shanghu_order')->where(['logNo'=>$data['orderId']])->find();
        if($membershanghu){
            $this->prf('商户交易信息去重'.json_encode($membershanghu));
            return  'SUCCESS';
            exit;
        }
        if($data['txnType'] == 'PUR' ||$data['txnType'] == 'SCP'){
            Db::startTrans();
            try{
                $where['logNo'] = $data['orderId'];
                $where['txnCd'] = $data['txnType'];
                $where['crdTyp'] = $extData['issuerCode'];
                $where['txnTm'] = $data['txnTime'];
                $where['ttxnSts'] = $data['respCode'];
                $where['crdNo'] = $data['shortPan'];
                $where['crdFlg'] = $extData['cardType'];
                $where['txnAmt'] = $data['amt']/100;
                $where['trmNo'] = $data['terminalId'];
                //$where['batNo'] = $data['batchNo'];
                //$where['cseqNo'] = $data['traceNo'];
                $where['mercId'] = $data['merchantId'];
                $where['snNo'] = $extData['sn'];

                $res[] = Db::name('member_shanghu_order')->insert($where);
                if($data['respCode'] == '00'){
                        $asdfd = Db::name('member_machine')->where(['machine_no'=>$extData['sn'],'type'=>1])->find();
                        if($asdfd){
                            $jihuoshanghu = Db::name('member_shanghu_jihuo')->where(['machine_no'=>$extData['sn']])->find();
                            if($data['amt']>=22000 && !$jihuoshanghu){
                                $shanghu= [];
                                $shanghu['machine_no'] = $extData['sn'];//Sn编号
                                $shanghu['ordId'] = $data['orderId'];
                                $shanghu['snNo'] = $extData['sn'];//Sn编号
                                $shanghu['trmNo'] = $data['terminalId'];
                                $shanghu['merId'] = $data['merchantId'];
                                $shanghu['ordTim'] = $data['txnTime'];
        
                                $res[] = Db::name('member_shanghu_jihuo')->insert($shanghu);
                            }
                        }
                }

                if ($this->check_arr($res)) {
                    Db::commit();
                    $this->prf('商户交易信息成功'.json_encode($res));
                    return  'SUCCESS';exit;
                } else {
                    Db::rollback();
                    $this->prf('商户交易信息写入失败'.json_encode($res));
                    return 'ERROR';exit;
                }
            } catch (\Exception $e) {
                $this->prf('商户交易信息异常数据'.$e);
                return '异常数据';exit;
                // die(); // 终止异常
            }
        }else{
            return 'SUCCESS';exit;
        }
        
    }
    private function verifySign($data, $sign){
        $pubKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCiXYK0TYZDp+haOpiW7ffGZrz1OapX2Bg3glFevK36LQpQqi2o3kHV+Ol5F5T7IdaD0AtFVIFjjaHmKYGhoo61qp54BEwMra8pwtjdItUVH6hHK4Cg1Prw2p2+vTh95RalxbUUoh/uZZPIgac9/zFj5rkad7zzg3PAWVPv/JynDQIDAQAB';
        $sign = base64_decode($sign);
        
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
            
            $key = openssl_pkey_get_public($pubKey);
            $result = openssl_verify($data, $sign, $key, OPENSSL_ALGO_SHA1) === 1;
            return $result;
    }
    
    public function test(){
//         $url = "http://shenghuoban.web368.cn/api/plan/activate_order?cur=CNY&orderId=10178&sign=MHQJwyxcWMOTyypBSPmBycyoG8xhSTRNBE37Ep4VdpUo1NbdDbXgWrVMVrvXxWTrrVNF/bgE9M/RuF3gpV6J/r5Kmy4G29nq/4bPYlD/8lI8/42pYJU8Mlwz2MUs/FAZZCNRVNGM6G/f5a6p5eU+QPabuhDooDkaEtFrhYvX460=&amt=122000&txnRef=795792&txnType=PUR&terminalId=29004199&shortPan=0000&merchantId=820290455320041&extData={"orderType":"3","payAmount":"","cardType":"","discountAmount":"","issuerCode":"CUP","sn":"000006026228258930","hashPan":"","userId":"","authRef":"","authOrderNo":"","sdpMerchant":"93744914"}&txnTime=20200922144247&respCode=00&merOrderId=POS20201029112994"
    }
    
    /**
     * 提现通知接口
     * @return [type] [description]
     */
    public function bank_notify()
    {
        $orderinfo = $this->request->post();
        $this->prf('提现回调-数据：'.json_encode($orderinfo));
        if(!$orderinfo){return '非法请求';exit;}
        $signstr = "charset={$orderinfo['charset']}batchNo={$orderinfo['batchNo']}batchPayStatus={$orderinfo['batchPayStatus']}batchPayStatusMsg={$orderinfo['batchPayStatusMsg']}resultMemo={$orderinfo['resultMemo']}";
        $sign_my = strtoupper(md5($signstr.'QC7FEHCDmzr9xrIF'));
        if($sign_my != $orderinfo['sign']){
            $this->prf('提现回调-签名错误:'.$sign_my.'-'.$signstr);
            return  json_encode(array('code'=>'error'));
            exit;
        }
        $order = Db::name('member_tixian')->where(['id'=>$orderinfo['details'][0]['id'],'status'=>1])->find();
        if(!$order){
            $this->prf('提现回调-订单不存在:'.$orderinfo['details'][0]['id']);
            return  json_encode(array('code'=>'ok'));
            exit;
        }
        Db::startTrans();
        if($orderinfo['details'][0]['payStatusCode'] == '03'){
            $res[] =  Db::name('member_tixian')->where(['id'=>$orderinfo['details'][0]['id'],'status'=>1])->update(['status'=>3,'content'=>$orderinfo['details'][0]['resultRemark'],'update_at'=>date('Y-m-d H:i:s')]);
            if($order['type'] == 'standard_prize' && !empty($order['shanghu_id'])){
                $res[] = Db::name('member_shanghu')->where(['id'=>$order['shanghu_id'],'is_yajin'=>3])->update(['is_yajin'=>4,'yajintime'=>time()]);
            }
        }elseif($orderinfo['details'][0]['payStatusCode'] == '04'){
            $res[] =  Db::name('member_tixian')->where(['id'=>$orderinfo['details'][0]['id'],'status'=>1])->update(['status'=>2,'content'=>$orderinfo['details'][0]['resultRemark'],'update_at'=>date('Y-m-d H:i:s')]);
        }else{
            $this->prf('提现回调-不需处理:'.json_encode($orderinfo));
            return  json_encode(array('code'=>'ok'));
        }
        if ($this->check_arr($res)) {
            Db::commit();
            $this->prf('提现回调-处理成功:'.json_encode($orderinfo));
            return  json_encode(array('code'=>'ok'));
        } else {
            Db::rollback();
            $this->prf('提现回调-处理失败'.json_encode($orderinfo));
            return  json_encode(array('code'=>'error'));
        }
    }
    
    /**
	 * prf
	 *
	 * Lets you determine whether an array index is set and whether it has a value.
	 * If the element is empty it returns NULL (or whatever you specify as the default value.)
	 *
	 * @param	mixed
	 * @return	void
	 */
	public function prf($param,$path='debug/')
	{
		$style = is_bool($param) ? 1 : 0;
		if($style){
			$outStr = "\r\n";
			$outStr .='<------------------------------------------------------------------------';
			$outStr .= "\r\n";
			$outStr .= date('Y-m-d H:i:s',time());
			$outStr .= "\r\n";
			$outStr .= $param == TRUE ? 'bool:TRUE' : 'bool:FALSE';
			$outStr .= "\r\n";
		}else{
			$outStr = "\r\n";
			$outStr .='<------------------------------------------------------------------------';
			$outStr .= "\r\n";
			$outStr .= date('Y-m-d H:i:s',time());
			$outStr .= "\r\n";
			$outStr .= print_r($param,1);
			$outStr .= "\r\n";
		}

		$backTrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		unset($backTrace[0]['args']);
		$outStr .= print_r($backTrace[0],1);
	    $outStr .='------------------------------------------------------------------------>';
		$outStr .= "\r\n";
		$path .= date('Y-m-d',time());
		file_put_contents($path.'-log.txt',$outStr,FILE_APPEND);
	}
	
	/**
     * 交易通知接口
     * @return [type] [description]
     */
    public function notifyUrl()
    {
        $orderinfo = $this->request->post();
        $this->prf('商户交易信息：'.json_encode($orderinfo));
        $extData = $this->request->post('extData','');
        if(!$orderinfo||!$extData){return '非法请求';exit;}
        $extData = json_decode($extData,true);
        if(!$orderinfo){return '非法请求';exit;}
        //$orderDataList = $orderinfo[0];
        $membershanghu = '';
        //$membershanghu = Db::name('member_shanghu_neworder')->where(['orderId'=>$orderinfo['orderId']])->find();
        if($membershanghu){
            $this->prf('商户交易信息去重'.json_encode($membershanghu));
            return  'SUCCESS';
            exit;
        }
        try{

            $where['txnType'] = $orderinfo['txnType'];
            $where['cur'] = $orderinfo['cur'];
            $where['amt'] = $orderinfo['amt'];
            $where['merchantId'] = $orderinfo['merchantId'];
            $where['terminalId'] = $orderinfo['terminalId'];
            $where['traceNo'] = $orderinfo['traceNo'];
            $where['batchNo'] = $orderinfo['batchNo'];
            $where['orderId'] = $orderinfo['orderId'];
            $where['txnTime'] = $orderinfo['txnTime'];
            $where['txnRef'] = $orderinfo['txnRef'];
            $where['respCode'] = $orderinfo['respCode'];
            $where['merOrderId'] = $orderinfo['merOrderId'];
            $where['shortPan'] = $orderinfo['shortPan'];
            $where['extData'] = $orderinfo['extData'];
            $where['origTxnRef'] = $orderinfo['origTxnRef'];
            $where['notifyAccounts'] = $orderinfo['notifyAccounts'];
            $where['clientOSType'] = $orderinfo['clientOSType'];

            $where['create_time'] = date('Y-m-d H:i:s');

            $res[] = Db::name('member_shanghu_neworder')->insert($where);

            if ($this->check_arr($res)) {
                Db::commit();
                $this->prf('商户交易信息成功'.json_encode($res));
                return  'SUCCESS';
            } else {
                Db::rollback();
                $this->prf('商户交易信息写入失败'.json_encode($res));
                return '传输失败';
            }
        } catch (Exception $e) {
            $this->prf('商户交易信息异常数据'.$e);
            return '异常数据';
            // die(); // 终止异常
        }
    }

}
