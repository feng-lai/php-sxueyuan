<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\model\UserToken;
use think\Exception;
use app\api\logic\mini\TrainOrderLogic;

/**
 * 培训控制器
 */
class TrainOrder extends Api
{
    public $restMethodList = 'get|post|put|delete';
    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }


    public function save()
    {
        $request = $this->selectParam([
            'name',
            'train_uuid',
            'platform'
        ]);
        $result = TrainOrderLogic::miniAdd($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10,
            'status',
            'train_cate_uuid',
            'keyword'
        ]);
        $result = TrainOrderLogic::List($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $result = TrainOrderLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
