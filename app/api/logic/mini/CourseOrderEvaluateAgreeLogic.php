<?php

namespace app\api\logic\mini;

use app\api\model\CourseOrderEvaluateAgree;
use think\Exception;
use think\Db;

/**
 * 课程订单评价点赞-逻辑
 */
class CourseOrderEvaluateAgreeLogic
{
    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = CourseOrderEvaluateAgree::build()->where('user_uuid', $userInfo['uuid'])->where('course_order_uuid',$request['course_order_uuid'])->where('is_deleted',1)->find();
            if($request['agree'] == 1){
                if($data){
                    return ['msg'=>'已点赞'];
                }
                CourseOrderEvaluateAgree::build()->insert([
                    'user_uuid'=>$userInfo['uuid'],
                    'course_order_uuid'=>$request['course_order_uuid'],
                    'uuid'=>uuid(),
                    'create_time'=>now_time(time()),
                    'update_time'=>now_time(time()),
                ]);
            }else{
                if(!$data){
                    return ['msg'=>'还没点赞'];
                }
                $data->save(['is_deleted'=>2]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
