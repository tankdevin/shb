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
class Update extends BasicIndex
{

    /**
     * 上传身份证认证
     * @return [type] [description]
     */
    public function merber_upcard()
    {
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'cardimagse');
        } else {
            $this->error('上传失败');
        }
        if($res['code'] == 1){
            $this->success('成功', ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
        }else{
            $this->error($res['msg']);
        }
        $this->error('失败');
    }

    /**
     * 上传企业
     * @return [type] [description]
     */
    public function merber_upqiye()
    {
        $image = request()->post('image');
        if ($image) {
            $res = $this->base64Image($image,'qiyeimagse');
        } else {
            $this->error('上传失败');
        }
        if($res['code'] == 1){
            $this->success('成功', ['imgname'=>$res['imageName'],'imgurl'=>$res['image_url']]);
        }else{
            $this->error($res['msg']);
        }
        $this->error('失败');
    }
    
}
