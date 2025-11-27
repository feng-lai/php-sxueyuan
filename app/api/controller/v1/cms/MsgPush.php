<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\MsgPushLogic;

/**
 * 消息推送-控制器
 */
class MsgPush extends Api
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
            'start_time',
            'end_time',
            'status',
            'title',
            'page_size' => 10,
            'page_index' => 1,
        ]);
        $result = MsgPushLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = MsgPushLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'title',
            'content',
            'type',
            'user_type',
            'push_time',
            'course_uuid',
            'train_uuid',
            'business_uuid',
            'user_uuid'
        ]);
        $this->check($request, "MsgPush.save");
        $result = MsgPushLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'title',
            'content',
            'type',
            'user_type',
            'push_time',
            'course_uuid',
            'train_uuid',
            'business_uuid',
            'user_uuid'
        ]);
        $request['uuid'] = $id;
        $this->check($request, "MsgPush.save");
        $result = MsgPushLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = MsgPushLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
