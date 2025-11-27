<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 轮播-控制器
 */
class TrainCate extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $result = \app\api\model\TrainCate::build()->order('weight','desc')->where(['is_deleted'=>1])->select();
        $this->render(200, ['result' => $result]);
    }

}
