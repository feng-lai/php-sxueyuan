<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\MemberOrderLogLogic;

/**
 * 会员开通记录-控制器
 */
class MemberOrderLog extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10
        ]);
        $result = MemberOrderLogLogic::cmsList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
