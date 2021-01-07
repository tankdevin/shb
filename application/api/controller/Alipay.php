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
use think\facade\Cache;
// use AlibabaCloud\Client\AlibabaCloud;
// use AlibabaCloud\Client\Exception\ClientException;
// use AlibabaCloud\Client\Exception\ServerException;

/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Alipay extends BasicIndex
{

    /**
     * 支付宝回调
     * @return [type] [description]
     */
    public function notifyurl()
    {
        require $_SERVER['DOCUMENT_ROOT'].'/alipay.trade.wap.pay-PHP-UTF-8/alipay.trade.wap.pay-PHP-UTF-8/config.php';
        require_once $_SERVER['DOCUMENT_ROOT'].'/alipay.trade.wap.pay-PHP-UTF-8/alipay.trade.wap.pay-PHP-UTF-8/wappay/service/AlipayTradeService.php';
        $arr=$_POST;
        $this->prf('回调数据'.json_encode($arr));
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($_POST,true));
        $result = $alipaySevice->check($arr);
        if($result){
            $data = $_REQUEST;
            if($data['trade_status'] == 'TRADE_SUCCESS'){
                //支付成功
                $store_order = db::name('store_order')->Where(['order_no'=>$data['out_trade_no'],'pay_state'=>0,'price_total'=>$data['buyer_pay_amount']])->find();
                if($store_order){
                    $res[] = db::name('store_order')->Where(['order_no'=>$data['out_trade_no'],'pay_state'=>0])->update(['pay_state'=>1,'status'=>3,'pay_at'=>$data['gmt_payment'],'pay_no'=>$data['trade_no'],'pay_type'=>'alipay']);
                        
                         $arr['out_trade_no'] = $data['out_trade_no'];
                         $arr['trade_no'] = $data['trade_no'];
                         $arr['buyer_logon_id'] = $data['buyer_logon_id'];
                         $arr['buyer_pay_amount'] = $data['buyer_pay_amount'];
                         $arr['trade_status'] = $data['trade_status'];
                         $arr['notify_time'] = $data['notify_time'];
                         
    //                     Db::name('alipay_log')->insert($arr);
                        $res[] = Db::name('StoreGoods')->where('id',$store_order['from_gid'])->setDec('number_stock',$store_order['goods_num']);
                        $res[] = Db::name('StoreGoods')->where('id',$store_order['from_gid'])->setInc('number_sales',$store_order['goods_num']);
                    if ($this->check_arr($res)) {
                        $this->prf('回调成功'.json_encode($arr));
                        Db::commit();
                        echo "success";
                    } else {
                        Db::rollback();
                        echo "fail";
                    }
                   
                }else{
                    echo "success";
                }
                
            }else{
                echo "success";
            }
        }else{
            echo "fail";exit;
        }
    }
    
    public function returnurl()
    {
        $data = $_REQUEST;
        if($data){
            $url = "file:///android_asset/apps/H5C4F77CE/www/index.html#/home";
            header("Location: $url");
            // echo "支付成功！";
          //$this->redirect("");
        }
        // $data = json_encode($data);
        // $this->prf('支付宝回调'.$data);
        // $data = json_decode($data);
       
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
    public function prf($param,$path='alipay/')
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
     * 公众号
     * @return [type] [description]
     */
    public function weixin()
    {
        $ticket = Cache::get('ticket');
        if(empty($ticket)){
            $access_token = Cache::get('access_token');
            if(empty($access_token)){
                $token = file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wxe74898228b48ac70&secret=abfd31e87d7669d95340480b2e3b65d5");
                $token = json_decode($token,true);
                if(isset($token['access_token'])){
                    Cache::set('access_token', $token['access_token'], 7200);
                    $access_token = $token['access_token'];
                }else{
                    $this->error('微信错误，请重试');
                }
            }
            $ticketres = file_get_contents("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$access_token."&type=jsapi");
            $ticketres = json_decode($ticketres,true);
            if(isset($ticketres['ticket'])){
                Cache::set('ticket', $ticketres['ticket'], 7200);
                $ticket = $ticketres['ticket'];
            }else{
                $this->error('微信错误，请重试');
            }
        }
        $time = time();
        $singstr = 'jsapi_ticket='.$ticket.'&noncestr=Wm3WZYTPz0wzccnW&timestamp='.$time.'&url=http://www.shenghb.com/#/share';
        $sign = sha1($singstr);
        $list = array(
            'appId'=>'wxe74898228b48ac70',
            'timestamp'=>$time,
            'nonceStr'=>'Wm3WZYTPz0wzccnW',
            'signature'=>$sign,
        );
        $this->success('成功', $list);
    }

}
