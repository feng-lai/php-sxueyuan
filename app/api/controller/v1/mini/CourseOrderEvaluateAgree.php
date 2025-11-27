<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CourseOrderEvaluateAgreeLogic;

/**
 * 课程订单评价点赞-控制器
 */
class CourseOrderEvaluateAgree extends Api
{
    public $restMethodList = 'post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function save()
    {
        $request = $this->selectParam([
            'course_order_uuid',
            'agree',
        ]);
        $this->check($request, "CourseOrderEvaluateAgree.save");
        $result = CourseOrderEvaluateAgreeLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
