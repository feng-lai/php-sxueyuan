<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseOrderEvaluateLogic;

/**
 * 课程评论-控制器
 */
class CourseOrderEvaluate extends Api
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
            'course_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = CourseOrderEvaluateLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = CourseOrderEvaluateLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }


    public function update($id)
    {
        $request = $this->selectParam([
            'status',
            'reason'
        ]);
        $request['uuid'] = $id;
        $this->check($request,'CourseOrderEvaluate.edit');
        $result = CourseOrderEvaluateLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
