<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\ServiceLocationLogic;

/**
 * 服务据点-控制器
 */
class ServiceLocation extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10,
        ]);
        $result = ServiceLocationLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
