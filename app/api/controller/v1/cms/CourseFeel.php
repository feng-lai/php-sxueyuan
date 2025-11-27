<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseFeelLogic;

/**
 * 心得-控制器
 */
class CourseFeel extends Api
{
    public $restMethodList = 'get|put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'course_uuid',
            'page_size' => 10,
            'page_index' => 1,
            'status',
            'user_uuid'
        ]);
        $result = CourseFeelLogic::cmsList($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'status',
            'reason'
        ]);
        $request['uuid'] = $id;
        $result = CourseFeelLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
