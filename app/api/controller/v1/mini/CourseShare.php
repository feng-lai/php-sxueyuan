<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\CourseShareLogic;

/**
 * 课程分享-控制器
 */
class CourseShare extends Api
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
            'course_uuid'
        ]);
        $request['user_uuid'] = $this->userInfo['uuid'];
        $this->check($request, "CourseShare.save");
        $result = CourseShareLogic::miniAdd($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
