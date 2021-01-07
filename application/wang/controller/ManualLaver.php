<?php
namespace app\wang\controller;

use library\Controller;

/**
 * 内容管理
 * Class Content会员等级1.0
 * @package app\wang\controller
 */
class ManualLaver extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StoreManualLaver';
    
    /**
     * 会员等级管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '会员等级管理1.0';
        $query = $this->_query($this->table);
        $query->order('id asc')->page();
    }
    
    /**
     * 添加商品分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加商品分类';
        $this->_form($this->table, 'form');
    }
    
    /**
     * 编辑商品分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑商品分类';
        $this->_form($this->table, 'form');
    }
    
    /**
     * 禁用商品分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }
    
    /**
     * 启用商品分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }
    
    /**
     * 删除商品分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->_delete($this->table);
    }
    
    
}
