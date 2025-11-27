<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CourseCateLogic;

/**
 * 分类-控制器
 */
class CourseCate extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'pid',
        ]);
        $result = CourseCateLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
