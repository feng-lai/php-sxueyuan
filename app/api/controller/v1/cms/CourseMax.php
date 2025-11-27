<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseLogic;

/**
 * 课程追加最大拼团人数
 */
class CourseMax extends Api
{
    public $restMethodList = 'put';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'max'
        ]);
        $this->check($request, "Course.max");
        $result = CourseLogic::setMax($request, $this->userInfo, $id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
