<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CourseOrderDetailLogic;

/**
 * 课程章节订单-控制器
 */
class CourseOrderDetail extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->miniValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'course_uuid'
        ]);
        $this->check($request,'CourseOrderDetail.list');
        $result = CourseOrderDetailLogic::miniList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
