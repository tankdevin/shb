<?php
namespace app\wang\controller;

use library\Controller;
use think\db;

/**
 * 内容管理
 * Class ContentBanner
 * @package app\wang\controller
 */
class MachineLog extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'MemberMachineLog';

    /**
     * 机具管理
     * @auth true
     * @menu true
     */
    public function jijulog()
    {
        $this->title = '机具管理';
        $query = $this->_query($this->table)->like('machine_no')->equal('type');
        $query->order('id desc')->page();
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
            foreach ($bianhaoinfo as $val){
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
            $indewher['num'] = count($bianhaoinfo);
            $indewher['yuanyin'] = '后台管理员入库';
            Db::name('member_machine_log')->insert($indewher);
        }
        $this->_form($this->table, 'shanghulistadd');
    }

    /**
     * 机具出库
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function jijuchuku()
    {
        $this->title = '添加机具';
        if ($this->request->isPost()) {
            $phone = $this->request->post('phone');
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
            }
        }
        $this->_form($this->table, 'jijuchuku');
    }

    function check_arr($rs)
    {
        foreach ($rs as $v) {
            if (is_array($v)) {
                foreach ($v as $val) {
                    if (!$val) {
                        return false;
                    }
                }
            } else {
                if (!$v) {
                    return false;
                }
            }
        }
        return true;
    }







    /**
     * 机具管理审核通过
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function ruwangisguo()
    {
        $this->_save($this->table, ['type_statu' => '1']);
    }

    /**
     * 机具管理审核不通过
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
        $this->title = '机具管理';
        $query = $this->_query($this->table)->like('machine_no')->like('shanghu_name')->equal('status');
        $query->where(['type_statu'=>1])->order('id desc')->page();
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
            $phone = $this->request->post('phone');
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
            Db::name('member_machine_log')->insert($indewher);

        }

        $this->_form($this->table, 'shanghulistedit');
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


}
