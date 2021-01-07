<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2020 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://library.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | gitee 仓库地址 ：https://gitee.com/zoujingli/ThinkLibrary
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

namespace library\tools;

/**
 * 快递100查询接口
 * Class Express
 * @package library\tools
 */
class Kuaidi
{
    /**
     * 查询物流信息
     * @param string $code 快递公司编辑
     * @param string $number 快递物流编号
     * @return array
     */
    public static function query($code, $number)
    {
        list($list, $cache) = [[], app()->cache->get($ckey = md5($code . $number))];
        if (!empty($cache)) return ['message' => 'ok', 'com' => $code, 'nu' => $number, 'data' => $cache];
        if (is_array($result = self::doExpress($code, $number))) {
            if (!empty($result['message'] == 'ok')) {
                foreach ($result['data'] as $vo) $list[] = [
                    'time' => $vo['time'], 'context' => $vo['context'],
                ];
                app()->cache->set($ckey, $list, 10);
                return ['message' => 'ok', 'com' => $code, 'nu' => $number, 'data' => $list];
            }else{
                $list[] = [
                    'time' => date('Y-m-d H:i:s'), 'context' => $result['message'],
                ];
                return ['message' => 'ok', 'com' => $code, 'nu' => $number, 'data' => $list];
            }
        }
        return ['message' => 'ok', 'com' => $code, 'nu' => $number, 'data' => $list];
    }
    
    /**
     * 执行快递100应用查询请求
     * @param string $code 快递公司编号
     * @param string $number 快递单单号
     * @return mixed
     */
    private static function doExpress($code, $number)
    {
        $rand = '0.'.rand(10000,99999).rand(100000,999999).rand(100000,999999);
        $url = "https://www.kuaidi100.com/query?type={$code}&postid={$number}&temp={$rand}&phone=";
        return json_decode(Http::get($url, [], self::getOption()), true);
    }
    
    /**
     * 获取HTTP请求配置
     * @return array
     */
    private static function getOption()
    {
        return [
            'header'     => [
                'referer: https://www.kuaidi100.com/?from=openv'
            ],
        ];
    }
    
}
