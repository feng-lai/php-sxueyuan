<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程评价点赞-校验
 */
class CourseOrderEvaluateAgree extends Validate
{
    protected $rule = [
        'course_order_uuid' => 'require|checkCourseOrder',
        'agree' => 'require|in:1,2'
    ];

    protected $field = [
        'course_order_uuid' => '课程订单uuid',
        'agree'=>'点赞',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['course_order_uuid','agree'],
        'edit' => [],
    ];

    protected function checkCourseOrder($value, $rule, $data)
    {

        if(!\app\api\model\CourseOrderEvaluate::where('course_order_uuid', $value)->where('is_deleted',1)->count()){
            return '课程订单不存在，请检查参数是否传递正确';
        }
        return true;
    }
}
