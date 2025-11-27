<?php

namespace app\api\logic\mini;

use app\api\model\Cart;
use app\api\model\Cate;
use app\api\model\Chapter;
use app\api\model\College;
use app\api\model\Course;
use app\api\model\CourseCate;
use app\api\model\CourseOrderEvaluate;
use app\api\model\Evaluate;
use app\api\model\Member;
use app\api\model\Order;
use app\api\model\Admin;
use app\api\model\Collect;
use app\api\model\Feel;
use app\api\model\Footprint;
use app\api\model\UserCourseChapter;
use think\Exception;
use think\Db;

/**
 * 课程-逻辑
 */
class CourseLogic
{
    static public function List($request)
    {
        try {
            $where = ['is_deleted' => 1, 'vis' => 1];
            if ($request['course_cate_uuid']) {
                $where['course_cate_uuid'] = $request['course_cate_uuid'];
            }
            if ($request['sub_course_cate_uuid']) {
                $where['sub_course_cate_uuid'] = $request['sub_course_cate_uuid'];
            }

            if ($request['is_quality']) {
                $where['is_quality'] = $request['is_quality'];
            }
            if ($request['is_home']) {
                $where['is_home'] = $request['is_home'];
            }
            if ($request['is_hot']) {
                $where['is_hot'] = $request['is_hot'];
            }
            if ($request['keyword']) {
                $where['name'] = ['like', '%' . $request['keyword'] . '%'];
            }
            $result = Course::build()
                ->where($where)
                ->order('weight desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            foreach ($result as $k=>$v){
                $result[$k]->course_cate_name = CourseCate::build()->where('uuid', $v['course_cate_uuid'])->value('name');
                $result[$k]->sub_course_cate_name = CourseCate::build()->where('uuid', $v['sub_course_cate_uuid'])->value('name');
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data->course_cate_name = CourseCate::build()->where('uuid', $data->course_cate_uuid)->value('name');
            //是否收藏
            $data->isCollect = 0;
            if ($userInfo) {
                $data->isCollect = Collect::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $uuid)->where('is_deleted', 1)->count();
            }
            $data->chapter = Chapter::build()->where('course_uuid', $uuid)->order('sort asc')->where('is_deleted', 1)->select()->each(function ($v) use ($uuid, $userInfo) {
                $permission = 0;
                $is_cart = 0;
                //是否已购买
                $is_buy = 0;
                if($userInfo){
                    if(UserCourseChapter::build()->where(['course_uuid'=>$uuid,'user_uuid'=>$userInfo['uuid'],'chapter_uuid'=>$v->uuid])->where('end_time','>=',now_time(time()))->count()){
                        $is_buy = 1;
                    }
                    if(UserCourseChapter::build()->where(['course_uuid'=>$uuid,'user_uuid'=>$userInfo['uuid'],'chapter_uuid'=>$v->uuid])->where('end_time','>=',now_time(time()))->count() || Member::build()->where('uuid',$userInfo['member_uuid'])->value('is_fee') == 1 || $v->is_see == 1){
                        $permission = 1;
                    }else{
                        $v->file = ['second'=>$v->file['second'],'url'=>''];
                    }
                    $is_cart = Cart::build()->where('user_uuid',$userInfo['uuid'])->where('course_uuid',$uuid)->where('chapter_uuid',$v->uuid)->count();
                }else{
                    $permission = 0;
                    $v->file = '';
                }
                $v->permission = $permission;
                $v->is_cart = $is_cart;
                $v->is_buy = $is_buy;
            });
            //课程推荐
            //二级分类
            $sub_cate_pro = Course::build()->field('uuid,img,name,score')->where('uuid','<>',$uuid)->where('is_deleted',1)->where('sub_course_cate_uuid',$data->sub_course_cate_uuid)->order('weight desc')->limit(4)->select()->toArray();
            //一级分类
            $cate_pro = Course::build()->field('uuid,img,name,score')->where('uuid','<>',$uuid)->where('is_deleted',1)->where('course_cate_uuid',$data->course_cate_uuid)->order('weight desc')->limit(4)->select()->toArray();
            //全部
            $pro = Course::build()->field('uuid,img,name,score')->where('uuid','<>',$uuid)->where('is_deleted',1)->order('weight desc')->limit(4)->select()->toArray();
            $all = array_merge($sub_cate_pro,$cate_pro,$pro);
            $data->recommend = [$all[0],$all[1],$all[2],$all[3]];
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
