<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\CourseLogic;

/**
 * 课程-控制器
 */
class Course extends Api
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
            'is_hot',
            'is_quality',
            'is_home',
            'name',
            'vis',
            'course_cate_uuid',
            'sub_course_cate_uuid',
            'page_size'=>10,
            'page_index'=>1
        ]);
        $result = CourseLogic::cmsList($request, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = CourseLogic::cmsDetail($id, $this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'course_cate_uuid',
            'sub_course_cate_uuid',
            'weight',
            'img',
            'desc',
            'score',
            'chapter'
        ]);
        $this->check($request, "Course.save");
        $result = CourseLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'course_cate_uuid',
            'sub_course_cate_uuid',
            'weight',
            'img',
            'desc',
            'score',
            'chapter'
        ]);
        $this->check($request, "Course.save");
        $result = CourseLogic::cmsEdit($request, $this->userInfo, $id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = CourseLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
