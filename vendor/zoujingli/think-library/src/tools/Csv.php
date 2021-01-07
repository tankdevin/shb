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
 * CSV 导出工具
 * Class Csv
 * @package library\tools
 */
class Csv
{
    /**
     * 写入CSV文件头部
     * @param string $filename 导出文件
     * @param array $headers CSV 头部(一级数组)
     */
    public static function header($filename, array $headers)
    {
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . iconv('UTF-8', 'GB2312//IGNORE', $filename));
        $handle = fopen('php://output', 'w');
        foreach ($headers as $key => $value) $headers[$key] = iconv("UTF-8", "GB2312//IGNORE", $value);
        fputcsv($handle, $headers);
        if (is_resource($handle)) fclose($handle);
    }

    /**
     * 写入CSV文件内容
     * @param array $list 数据列表(二维数组或多维数组)
     * @param array $rules 数据规则(一维数组)
     */
    public static function body($filename, array $headers,array $list, array $rules)
    {
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . iconv('UTF-8', 'GB2312//IGNORE', $filename));
        $handle = fopen('php://output', 'w');
        foreach ($headers as $key => $value) $headers[$key] = iconv("UTF-8", "GB2312//IGNORE", $value);
        fputcsv($handle, $headers);

        $handle = fopen('php://output', 'w');
        foreach ($list as $data) {
            $rows = [];
            foreach ($rules as $rule)
            {
                $rows[] = self::parseKeyDotValue($data, $rule);
            }
            fputcsv($handle, $rows);
        }
        exit;
        if (is_resource($handle)) fclose($handle);
    }

    /**
     * 根据数组key查询(可带点规则)
     * @param array $data 数据
     * @param string $rule 规则，如: order.order_no
     * @return mixed
     */
    public static function parseKeyDotValue(array $data, $rule)
    {
        list($temp, $attr) = [$data, explode('.', trim($rule, '.'))];
        while ($key = array_shift($attr)) $temp = isset($temp[$key]) ? $temp[$key] : $temp;
        return (is_string($temp) || is_numeric($temp)) ? @iconv('UTF-8', 'GB2312//IGNORE', "{$temp}") : '';
    }

    // csv导入,此格式每次最多可以处理1000条数据
    public function input_csv($csv_file) {
        $result_arr = array ();
        $i = 0;
        while($data_line = fgetcsv($csv_file,10000)) {
            if ($i == 0) {
                $GLOBALS ['csv_key_name_arr'] = $data_line;
                $i ++;
                continue;
            }
            foreach($GLOBALS['csv_key_name_arr'] as $csv_key_num => $csv_key_name ) {
                $result_arr[$i][$csv_key_name] = $data_line[$csv_key_num];
            }
            $i++;
        }
        return $result_arr;
    }

}
