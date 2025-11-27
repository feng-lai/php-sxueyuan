<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\MemberLogic;

/**
 * 会员-控制器
 */
class Member extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $result = MemberLogic::cmsList();
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = MemberLogic::cmsDetail($id);
        $this->render(200, ['result' => $result]);
    }

}
