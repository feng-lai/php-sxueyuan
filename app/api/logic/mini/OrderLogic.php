<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Order;
use app\api\model\Course;
use think\Exception;
use think\Db;

/**
 * 订单-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class OrderLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            //拉黑用户无法报名
            if($userInfo['disabled'] == 2){
                return ['msg'=>'由于多次拼团违约或评论失范，用户已被拉黑，无法报名，可联系辅导员解封'];
            }
            Db::startTrans();
            $course = Course::build()->where('uuid', $request['course_uuid'])->where('is_deleted', 1)->where('vis', 1)->findOrFail();
            if ($course->end < date('Y-m-d H:i:s')) {
                return ['msg' => '拼课时间已过'];
            }
            if ($course->begin > date('Y-m-d H:i:s')) {
                return ['msg' => '拼课还没开始'];
            }
            //限制书院
            if ($course->college) {
                if (!in_array($userInfo['college_uuid'], explode(',', $course->college))) {
                    return ['msg' => '您所在的书院暂不支持该拼团报名'];
                }
            }
            //人数已满
            $num = Order::build()->where('course_uuid', $request['course_uuid'])->where('status', 1)->where('is_deleted', 1)->count();
            if ($num >= $course->max) {
                return ['msg' => '人数已满'];
            }

            //重复评课
            if (Order::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $request['course_uuid'])->where('status', 1)->where('is_deleted', 1)->count()) {
                return ['msg' => '重复拼课'];
            }
            $order = Order::build();
            $order->uuid = uuid();
            $order->user_uuid = $userInfo['uuid'];
            $order->course_uuid = $request['course_uuid'];
            $order->save();
            //发送拼课成功通知
            send_msg($userInfo['uuid'],$order->uuid,'','你报名的'.$course->name.'已成功拼课，请及时查看',$request['course_uuid']);
            Db::commit();
            return $order->uuid;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function Delete($uuid, $userInfo)
    {
        try {
            $res = Order::build()->where('uuid',$uuid)->where('user_uuid', $userInfo['uuid'])->where('is_deleted',1)->where('status',1)->findOrFail();
            $res->save(['status'=>2,'cancel_type'=>1,'reason'=>'用户主动取消','cancel_time'=>date('Y-m-d H:i:s')]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


}
