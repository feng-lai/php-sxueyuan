<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserLogic;

/**
 * 用户信息更新首次登录-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class UserSetFirstLogin extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        \app\api\model\User::build()->where('uuid',$this->userInfo['uuid'])->update(['first_login'=>2]);
        $this->render(200, ['result' => true]);
    }

}
