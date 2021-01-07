<?php
namespace app\wang\controller;

use library\Controller;

/**
 * 内容管理
 * Class ContentBanner
 * @package app\wang\controller
 */
class ProblemHelp extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'WangHelp';

    /**
     * banner管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '问题与帮助管理';
        $query = $this->_query($this->table)->like('title')->equal('status');
        $query->order('id desc')->page();
    }

    /**
     * 添加问题与帮助分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加问题与帮助分类';
        $this->_form($this->table, 'form');
    }

    /**
     * 编辑问题与帮助分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑问题与帮助分类';
        $this->_form($this->table, 'form');
    }

    /**
     * 禁用问题与帮助分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }

    /**
     * 启用问题与帮助分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }

    /**
     * 删除问题与帮助分类
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function remove()
    {
        $this->_delete($this->table);
    }


}
