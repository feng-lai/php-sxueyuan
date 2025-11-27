<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\TrainLogic;

/**
 * 培训-控制器
 */
class Train extends Api
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
            'status',
            'name',
            'train_cate_uuid',
            'is_recommend',
            'page_size' => 10,
            'page_index' => 1
        ]);
        $result = TrainLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = TrainLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'train_cate_uuid',
            'begin_time', 'end_time',
            'sign_begin_time',
            'sign_end_time',
            'address',
            'num',
            'member_uuid',
            'pay_type',
            'cancel_phone',
            'get_score',
            'is_get_score',
            'weight',
            'img',
            'desc',
            'price',
            'score',
        ]);
        $this->check($request, "Train.save");
        $result = TrainLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'train_cate_uuid',
            'begin_time', 'end_time',
            'sign_begin_time',
            'sign_end_time',
            'address',
            'num',
            'member_uuid',
            'pay_type',
            'cancel_phone',
            'get_score',
            'is_get_score',
            'weight',
            'img',
            'desc',
            'price',
            'score',
        ]);
        $this->check($request, "Train.save");
        $result = TrainLogic::cmsEdit($request, $this->userInfo, $id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = TrainLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
