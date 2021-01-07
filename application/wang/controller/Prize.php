<?php
namespace app\wang\controller;

use library\Controller;

/**
 * 奖项设置
 * Class ContentBanner
 * @package app\wang\controller
 */
class Prize extends Controller
{
    /**
     * 绑定数据表
     * @var string
     */
    protected $table = 'StorePrize';
    
    /**
     * 奖项管理
     * @auth true
     * @menu true
     */
    public function index()
    {
        $this->title = '奖项管理';
        $query = $this->_query($this->table)->like('name')->equal('status');
        $query->order('sort esc')->page();
    }
    
    /**
     * 编辑奖项
     * @auth true
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑奖项';
        $this->_form($this->table, 'form');
    }
    
    /**
     * 禁用编辑奖项
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        $this->_save($this->table, ['status' => '0']);
    }
    
    /**
     * 启用编辑奖项
     * @auth true
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        $this->_save($this->table, ['status' => '1']);
    }
    
}
