<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseLogic;

/**
 * 课程推荐-控制器
 */
class CourseRecommend extends Api
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
            'is_hot',
            'is_quality',
            'is_home'
        ]);
        $this->check($request, "Course.recommend");
        $result = CourseLogic::recommend($request,$this->userInfo, $id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
