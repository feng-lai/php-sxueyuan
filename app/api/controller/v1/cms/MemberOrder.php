<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\MemberOrderLogic;

/**
 * 会员订单-控制器
 */
class MemberOrder extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'order_id',
            'member_name',
            'user_name',
            'pay_type',
            'type',
            'order_status',
            'user_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = MemberOrderLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = MemberOrderLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }


}
