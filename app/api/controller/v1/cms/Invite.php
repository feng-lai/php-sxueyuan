<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\InviteLogic;

/**
 * 拉新-控制器
 */
class Invite extends Api
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
            'user_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = InviteLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
