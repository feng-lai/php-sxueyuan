<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程订单-校验
 */
class CourseOrderEvaluate extends Validate
{
    protected $rule = [
        'course_order_uuid' => 'require',
        'star'=>'require|number|between:1,5',
        'content'=>'require',
        'status'=>'require|in:2,3',
    ];
    protected $field = [
        'course_order_uuid' => '课程订单uuid',
        'star'=>'星级',
        'content'=>'内容',
        'status'=>'状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['course_order_uuid','star','content'],
        'edit' => ['status'],
    ];
}
