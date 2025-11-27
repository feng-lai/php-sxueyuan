<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserCourseChapterLogic;

/**
 * 我的课程-控制器
 */
class UserCourseChapter extends Api
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
            'page_size' => 10,
            'page_index' => 1,
            'status'
        ]);
        $result = UserCourseChapterLogic::miniList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }
    public function read($id)
    {
        $result = UserCourseChapterLogic::miniDetail($id,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
