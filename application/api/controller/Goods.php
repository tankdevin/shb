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
class Goods extends BasicIndex
{

    /**
     * banner
     * @return [type] [description]
     */
    public function goodslist()
    {
        $id = $this->request->post('id','');
        $source = $this->request->post('source','');
        if(empty($source)) $this->error('参数错误');
        $bannerlist = Db::name('StoreGoods')->where(['is_deleted' => 0,'source'=>$source])->order('sort desc');
        if($id){
            $where['id'] = $id;
            $banner = $bannerlist->where($where)->find();
        }else{
            $banner = $bannerlist->select();
        }
        $this->success('成功', $banner);
    }

    /**
     * 兑换下单
     * @return [type] [description]
     */
    public function goods_order()
    {
        $goodsid = $this->request->post('goodsid','');
        $addressid = $this->request->post('addressid','');
        $num = $this->request->post('num','');
        $type = $this->request->post('type','');
        $phone = $this->request->post('phone','');
        if($type==2){
            if(!$addressid)$this->error('请选择邮寄地址');
            $city = Db::name('MemberCity')->column('area_name','id');
            $addressinfo = Db::name('MemberAddress')->where(['id'=>$addressid,'mid'=>$this->uid])->find();
            if(!$addressinfo)$this->error('参数错误。');
        }
        
        if($num<5 || $num%5 !=0)$this->error('必须大于5台，并且是5的倍数.');

        $goodsinfo = Db::name('StoreGoods')->where(['id'=>$goodsid])->find();
        if(!$goodsinfo)$this->error('参数错误.');

        if($phone){
            $merber = Db::name('store_member')->where(['phone'=>$phone])->find();
            $shuliang  = Db::name('member_machine')->where(['mid'=>$merber['id'],'type'=>1])->count();
            if($shuliang <$num)$this->error('机具不足.');
            $where['formid'] = $merber['id'];
        }else{
            if($goodsinfo['number_stock']<$num)$this->error('库存不足.');
        }
        $money = bcmul($goodsinfo['goods_price'],$num,2);
        if($money>$this->user['integral'])$this->error('积分不足.');

        $where['mid'] = $this->uid;
        $where['from_gid'] = $goodsinfo['id'];
        $where['goods_name'] = $goodsinfo['title'];
        $where['order_no'] = time() . mt_rand(10000, 99999);
        $where['order_no'] = time() . mt_rand(10000, 99999);
        $where['price_total'] = $money;
        $where['price_goods'] = bcmul($goodsinfo['goods_price'],1,2);
        $where['goods_num'] = $num;
        $where['pay_price'] = $money;
        $where['status'] = 3;
        if($type == 2){
            $where['express_address_id'] = $addressinfo['id'];
            $where['express_name'] = $addressinfo['name'];
            $where['express_phone'] = $addressinfo['mobile'];
            $where['express_province'] = $city[$addressinfo['provinceid']];
            $where['express_city'] = $city[$addressinfo['cityid']];
            $where['express_area'] = $city[$addressinfo['countyid']];
            $where['express_address'] = $addressinfo['address'];
        }
        
        $where['source'] = 1;
        $where['express_type'] = $type;

        Db::startTrans();
        $res[] = Db::name('StoreOrder')->insert($where);
        $res[] = Db::name('StoreGoods')->where('id',$goodsinfo['id'])->setDec('number_stock',$num);
        $res[] = Db::name('StoreGoods')->where('id',$goodsinfo['id'])->setInc('number_sales',$num);
        $res[] = $this->mlog($this->user['id'],'integral','1',-$money,'积分置换商品');
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }

    }
    

    /**
     * 商城下单
     * @return [type] [description]
     */
    public function mall_order()
    {
        $goodsid = $this->request->post('goodsid','');
        $addressid = $this->request->post('addressid','');
        $num = $this->request->post('num','');
//         $phone = $this->request->post('phone','');
        if(!$addressid)$this->error('请选择邮寄地址');
        
        if($num<5 || $num%5 !=0)$this->error('必须大于5台，并且是5的倍数.');

        $goodsinfo = Db::name('StoreGoods')->where(['id'=>$goodsid])->find();
        if(!$goodsinfo)$this->error('参数错误.');

        $addressinfo = Db::name('MemberAddress')->where(['id'=>$addressid,'mid'=>$this->uid])->find();
        if(!$addressinfo)$this->error('参数错误。');

        if($goodsinfo['number_stock']<$num)$this->error('库存不足.');
        $money = bcmul($goodsinfo['goods_price'],$num,2);
//         if($money>$this->user['integral'])$this->error('积分不足.');
        $city = Db::name('MemberCity')->column('area_name','id');
        $where['mid'] = $this->uid;
        $where['from_gid'] = $goodsinfo['id'];
        $where['goods_name'] = $goodsinfo['title'];
        $where['order_no'] = time() . mt_rand(10000, 99999);
        $where['price_total'] = $money;
        $where['price_goods'] = bcmul($goodsinfo['goods_price'],1,2);
        $where['goods_num'] = $num;
        $where['pay_price'] = $money;
        $where['pay_state'] = 0;
        $where['status'] = 2;

        $where['express_address_id'] = $addressinfo['id'];
        $where['express_name'] = $addressinfo['name'];
        $where['express_phone'] = $addressinfo['mobile'];
        $where['express_province'] = $city[$addressinfo['provinceid']];
        $where['express_city'] = $city[$addressinfo['cityid']];
        $where['express_area'] = $city[$addressinfo['countyid']];
        $where['express_address'] = $addressinfo['address'];
        $where['source'] = 2;

        Db::startTrans();
        $res[] = Db::name('StoreOrder')->insert($where);
//         $res[] = Db::name('StoreGoods')->where('id',$goodsinfo['id'])->setDec('number_stock',$num);
//         $res[] = Db::name('StoreGoods')->where('id',$goodsinfo['id'])->setInc('number_sales',$num);
//         $res[] = $this->mlog($this->user['id'],'integral','1',-$money,'积分置换商品');
        
        $data['WIDout_trade_no'] =  $where['order_no'];
        $data['WIDsubject'] =   $where['goods_name'];
        $data['WIDtotal_amount'] =  $money;
        $data['url'] = "http://www.shenghb.com/alipay.trade.wap.pay-PHP-UTF-8/alipay.trade.wap.pay-PHP-UTF-8/wappay/pay.php";
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功',$data);
        } else {
            Db::rollback();
            $this->error('失败');
        }

    }

    /**
     * 订单列表
     * @return [type] [description]
     */
    public function order_list()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $source = $this->request->post('source','');//订单来源 1兑换2商城
        if(empty($source)) $this->error('参数错误');
        $type = $this->request->post('type','');//订单状态 空全部 0已取消,2新订单待支付,3已支付待发货,4已发货待签收,5已完成 6待退款,7已退款
        $where['mid'] = $this->uid;
        $where['source'] = $source;
        $where['is_deleted'] = 0;
        if($type){
            $where['status'] = $type;
        }
        $orderlist = Db::name('StoreOrder')->where($where)->field('id,order_no,from_gid,goods_num,goods_name,price_goods,pay_price,status,create_at,formid')->order('create_at desc')->limit($start,$num)->select();
        foreach ($orderlist as $key=>$val){
            $orderlist[$key]['goods'] = Db::name('store_goods')->where(['id'=>$val['from_gid']])->value('image');
            if($val['formid']){
                $meber = Db::name('store_member')->where(['id'=>$val['formid']])->find();
                $orderlist[$key]['formname'] = '伙伴：'.$meber['phone'];
            }else{
                $orderlist[$key]['formname'] = '总部';
            }
        }
        $this->success('成功',$orderlist);

    }

    /**
     * 订单详情
     * @return [type] [description]
     */
    public function orderinfo()
    {
        $id = $this->request->post('id','');//订单状态 空全部 0已取消,2新订单待支付,3已支付待发货,4已发货待签收,5已完成 6待退款,7已退款
        if(!$id)$this->error('订单异常.');

        $orderlist = Db::name('StoreOrder')->where(['id'=>$id])->find();
        $orderlist['goods'] = Db::name('store_goods')->where(['id'=>$orderlist['from_gid']])->find('image');
        if($orderlist['formid']){
            $meber = Db::name('store_member')->where(['id'=>$orderlist['formid']])->find();
            $orderlist['formname'] = '伙伴：'.$meber['phone'];
        }else{
            $orderlist['formname'] = '总部';
        }
        $this->success('成功',$orderlist);

    }

    /**
     * 取消订单
     * @return [type] [description]
     */
    public function order_del()
    {
        $id = $this->request->post('id','');//订单状态 空全部 0已取消,2新订单待支付,3已支付待发货,4已发货待签收,5已完成 6待退款,7已退款
        if(!$id)$this->error('订单异常.');

        $orderlist = Db::name('StoreOrder')->where(['id'=>$id,'is_deleted'=>0,'status'=>3])->find();
        if(!$orderlist)$this->error('订单异常.');

        Db::startTrans();
        $res[] = Db::name('StoreOrder')->where(['id'=>$id,'is_deleted'=>0,'status'=>3])->update(['status'=>0,'refund_at'=>date('Y-m-d H:i:s')]);
        $res[] = $this->mlog($this->user['id'],'integral','1',$orderlist['pay_price'],'积分置换商品,取消订单：'.$orderlist['id']);
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 支付
     * @return [type] [description]
     */
    public function order_pay()
    {
        $orderid = $this->request->post('orderid','');

        $orderinfo = Db::name('StoreOrder')->where(['status'=>2,'id'=>$orderid])->find();
        if(!$orderinfo)$this->error('订单异常.');

        $memberinfo = $this->user;

        if($orderinfo['pay_price']>$memberinfo['integral'])$this->error('积分不足.');

        Db::startTrans();

        $res[] = Db::name('StoreOrder')->where(['status'=>2,'id'=>$orderid])->update(['status'=>3,'pay_at'=>date('Y-m-d H:i:s')]);
        $res[] = $this->mlog($orderinfo['mid'],'integral','1',-$orderinfo['pay_price'],'积分置换商品');
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }

    /**
     * 完成
     * @return [type] [description]
     */
    public function order_bay()
    {
        $orderid = $this->request->post('orderid','');
        $orderinfo = Db::name('StoreOrder')->where(['status'=>4,'id'=>$orderid])->find();
        if(!$orderinfo)$this->error('订单异常.');
        if (Db::name('StoreOrder')->where(['status'=>4,'id'=>$orderid])->update(['status'=>5])) {
            $this->success('成功');
        }
        $this->error('失败');

    }

    /**
     * 兑换列表
     * @return [type] [description]
     */
    public function duihuan_list()
    {
        $page   = request()->post('page', 1);
        $num   = $this->number;
        $start = ($page - 1) * $num;

        $where['formid'] = $this->uid;
        $where['is_deleted'] = 0;

        $orderlist = Db::name('StoreOrder')->where($where)->field('id,mid,order_no,from_gid,goods_num,goods_name,price_goods,pay_price,status,create_at,formid')->order('create_at desc')->limit($start,$num)->select();
        foreach ($orderlist as $key=>$val){
            $orderlist[$key]['goods'] = Db::name('store_goods')->where(['id'=>$val['from_gid']])->value('image');
            $meber = Db::name('store_member')->where(['id'=>$val['mid']])->find();
            $orderlist[$key]['midname'] = '购机人：'.$meber['phone'];
        }
        $this->success('成功',$orderlist);

    }

    /**
     * 兑换详情
     * @return [type] [description]
     */
    public function duihuaninfo()
    {
        $id = $this->request->post('id','');//订单状态 空全部 0已取消,2新订单待支付,3已支付待发货,4已发货待签收,5已完成 6待退款,7已退款
        if(!$id)$this->error('订单异常.');

        $orderlist = Db::name('StoreOrder')->where(['id'=>$id])->find();
        $orderlist['goods'] = Db::name('store_goods')->where(['id'=>$orderlist['from_gid']])->find('image');

        $meber = Db::name('store_member')->where(['id'=>$orderlist['mid']])->find();
        $orderlist['midname'] = '购机人：'.$meber['phone'];

        $this->success('成功',$orderlist);

    }

    /**
     * 机具下发
     * @return [type] [description]
     */
    public function duihuan_xiafa()
    {
        $machineno = $this->request->post('machineno','');
        $id = $this->request->post('id','');
        //$machineno = json_decode($machineno,true);
        if(count($machineno)<1 || !$id)$this->error('参数错误..');
        $orderlist = Db::name('StoreOrder')->where(['id'=>$id,'status'=>3])->find();
        if(!$orderlist)$this->error('参数错误.');
        if(count($machineno) !=$orderlist['goods_num'])$this->error('参数错误');

        if(count($machineno)<5 || count($machineno)%5 !=0)$this->error('机具必须大于5台，并且是5的倍数.');

        $mlever = Db::name('member_machine')->where(['mid'=>$this->uid])->whereIn('type','1,3,6')->count();
        if(($mlever-count($machineno))<5)$this->error('机具必须留存5台.');

        $memberinfo = DB::name('StoreMember')->where(['id'=>$orderlist['mid'],'status'=>0])->find();
        $mleadsaver = Db::name('member_machine')->where(['mid'=>$memberinfo['id']])->whereIn('type','1,3,6')->count();//伙伴机具数量
        $mlefgdfgdfver = Db::name('store_member_laver')->where('num','<=',$mleadsaver+count($machineno))->order('num desc')->find();

        $where['mid'] = $orderlist['mid'];
        $where['type'] = 1;
        $where['is_huabo'] = 0;

        if(!is_array($machineno)){
            $machlist[] = $machineno;
        }else{
            $machlist = $machineno;
        }

        Db::startTrans();
        $asdf = '';
        foreach ($machlist as $value){
            $res[] = Db::name('MemberMachine')->where(['type'=>1,'mid'=>$this->uid,'machine_no'=>$value])->update(['type'=>2,'update_time'=>date('Y-m-d H:i:s')]);
            $where['machine_no'] = $value;
            $res[] = Db::name('MemberMachine')->insert($where);
            if($asdf){
                $asdf = $asdf.','.$value;
            }else{
                $asdf = $value;
            }
            $wheresd['mid'] = $orderlist['mid'];
            $wheresd['form_mid'] = $this->uid;
            $wheresd['machine_no'] = $value;
            $wheresd['type'] = 1;
            $wheresd['yuanyin'] = '【' . $memberinfo['username'] . '】收到【'.$this->user['username'].'】划拨机具，SN号【' . $value . '】';
            $res[] = Db::name('MemberMachineLog')->insert($wheresd);
        }
        if($mlefgdfgdfver['id']>$memberinfo['vip_level']){
            if($memberinfo['vip_level'] == 1){
                $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id'],'create_at'=>date('Y-m-d H:i:s')]);
            }else{
                $res[] = Db::name('store_member')->where(['id'=>$memberinfo['id']])->update(['vip_level'=>$mlefgdfgdfver['id']]);
            }
        }
        /*$wheread['mid'] = $orderlist['mid'];
        $wheread['form_mid'] = $this->uid;
        $wheread['machine_no'] = $asdf;
        $wheread['type'] = 2;
        $wheread['num'] = count($machlist);
        $res[] = Db::name('MemberMachineLog')->insert($wheread);*/

        //$member = Db::name('store_member')->where('id',$orderlist['mid'])->find();
        $res[] = Db::name('StoreOrder')->where(['id'=>$id,'status'=>3])->update(['status'=>5]);
        $res[] = $this->mlog($this->uid,'integral','1',$orderlist['pay_price'],'来自'.$memberinfo['username'].'伙伴积分兑换');
        if ($this->check_arr($res)) {
            Db::commit();
            $this->success('成功');
        } else {
            Db::rollback();
            $this->error('失败');
        }
    }



}
