<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserScoreLogic;

/**
 * 用户积分明细-控制器
 */
class UserScore extends Api
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
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = UserScoreLogic::miniList($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            return json_encode(['result'=>$result]);
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = UserScoreLogic::miniDetail($this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            return json_encode(['result'=>$result]);
            $this->render(200, ['result' => $result]);
        }
    }
}
