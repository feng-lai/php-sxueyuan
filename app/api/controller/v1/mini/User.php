<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserLogic;

/**
 * 用户信息-控制器
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class User extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $result = UserLogic::miniList($this->userInfo);
        $this->render(200, ['result' => $result]);
    }
    /**
    public function update($id)
    {

        $request = $this->selectParam([
            'code'
        ]);
        $this->check($request,'User.checkPhone');
        $result = true;
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }**/

    public function save(){
        $request = $this->selectParam([
            'name',
            'gender',
            'business',
            'phone_type',
            'img',
            'phone',
            'code'
        ]);
        $result = UserLogic::miniAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
