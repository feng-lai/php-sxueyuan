<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\MemberOrderLogic;

/**
 * 会员续费/升级-控制器
 */
class MemberOrder extends Api
{
    public $restMethodList = 'get|post';


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
        $result = MemberOrderLogic::cmsList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = MemberOrderLogic::cmsDetail($id);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'member_uuid',
            'pay_type'
        ]);
        $this->check($request, "MemberOrder.save");
        $result = MemberOrderLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'value',
        ]);
        $request['key'] = $id;
        $result = MemberOrderLogic::cmsEdit($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = MemberOrderLogic::cmsDelete($id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
