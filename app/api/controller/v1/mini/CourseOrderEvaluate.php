<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CourseOrderEvaluateLogic;

/**
 * 课程订单评价-控制器
 */
class CourseOrderEvaluate extends Api
{
    public $restMethodList = 'get|post';


    public function _initialize()
    {
        parent::_initialize();

    }

    public function index()
    {
        $request = $this->selectParam([
            'page_size' => 10,
            'page_index' => 1,
            'course_uuid'
        ]);
        $this->userInfo = $this->miniValidateToken2();
        $result = CourseOrderEvaluateLogic::cmsList($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $this->userInfo = $this->miniValidateToken();
        $result = CourseOrderEvaluateLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $this->userInfo = $this->miniValidateToken();
        $request = $this->selectParam([
            'star',
            'course_order_uuid',
            'content',
        ]);
        $this->check($request, "CourseOrderEvaluate.save");
        $result = CourseOrderEvaluateLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
