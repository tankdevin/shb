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
use think\facade\App;
use think\facade\Cache;
/**
 * 应用入口
 * Class Index
 * @package app\index\controller
 */
class Setting extends BasicIndex
{
    /**
     * 奖项
     * @return [type] [description]
     */
    public function prize()
    {
        $bannerlist = Db::name('StorePrize')->where(['status' => 1])->order('sort esc')->field('id,name,field_name')->select();
        $this->success('成功', $bannerlist);
    }
    
    /**
     * vip1.0
     * @return [type] [description]
     */
    public function vip()
    {
        $list = Db::name('StoreManualLaver')->order('id esc')->field('id,name')->select();
        $this->success('成功', $list);
    }
    
    /**
     * 公众号
     * @return [type] [description]
     */
    public function weixin()
    {
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
        $ticket = Cache::get('ticket');
        if(empty($ticket)){
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
            'jsApiList'=>array('onMenuShareTimeline','onMenuShareAppMessage')
        );
        $this->success('成功', $list);
    }
}