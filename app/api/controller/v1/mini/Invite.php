<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\InviteLogic;

/**
 * 邀请码填写-控制器
 */
class Invite extends Api
{
    public $restMethodList = 'post|get';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_index' => 1,
            'page_size' => 10
        ]);
        $result = InviteLogic::miniList($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            'code',
        ]);
        $result = InviteLogic::miniAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
