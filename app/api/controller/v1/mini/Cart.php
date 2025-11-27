<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CartLogic;

/**
 * 购物车-控制器
 */
class Cart extends Api
{
    public $restMethodList = 'post|get|delete';

    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'chapter_uuid'
        ]);
        $result = CartLogic::list($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([
            'chapter_uuid'
        ]);
        $request['user_uuid'] = $this->userInfo['uuid'];
        $this->check($request, "Cart.save");
        $result = CartLogic::miniAdd($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = CartLogic::miniDelete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
