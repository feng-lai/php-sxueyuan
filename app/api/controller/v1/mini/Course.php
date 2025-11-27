<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use app\api\model\UserToken;
use think\Exception;
use app\api\logic\mini\CourseLogic;

/**
 * 课程-控制器
 */
class Course extends Api
{
    public $restMethodList = 'get|post|put|delete';

    public function index()
    {
        $request = $this->selectParam([
            'page_index'=>1,
            'page_size'=>10,
            'is_hot',
            'is_home',
            'is_quality',
            'course_cate_uuid',
            'sub_course_cate_uuid',
            'keyword'
        ]);
        $result = CourseLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function read($id)
    {
        $this->userInfo = $this->miniValidateToken2();
        $result = CourseLogic::Detail($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
