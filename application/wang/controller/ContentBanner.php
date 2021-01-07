<?php
namespace app\wang\controller;

use library\Controller;

/**
 * 内容管理
 * Class ContentBanner
 * @package app\wang\controller
 */
class ContentBanner extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'ContentBanner';

    /**
     * banner管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = 'banner管理';
        $query = $this->_query($this->table)->like('title')->equal('status');
        $query->order('id desc')->page();
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
