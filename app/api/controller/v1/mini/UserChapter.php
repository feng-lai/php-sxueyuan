<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\UserChapterLogic;

/**
 * 课程章节-控制器
 */
class UserChapter extends Api
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
            'course_uuid'
        ]);
        $this->check($request,'Chapter.list');
        $result = UserChapterLogic::miniList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }
    public function update($id){
        $request = $this->selectParam([
            'persent'
        ]);
        $request['chapter_uuid'] = $id;
        $this->check($request,'Chapter.setPersent');
        $result = UserChapterLogic::setPersent($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

}
