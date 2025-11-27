<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseOrderLogic;

/**
 * 课程订单-控制器
 */
class CourseOrder extends Api
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
            'order_id',
            'course_name',
            'user_name',
            'user_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = CourseOrderLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = CourseOrderLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }


}
