<?php

namespace app\common\validate;

use think\Validate;

/**
 * 轮播-校验
 */
class CourseOrderDetail extends Validate
{
    protected $rule = [
        'course_uuid' => 'require',
    ];

    protected $field = [
        'course_uuid' => '课程uuid',
    ];

    protected $message = [];

    protected $scene = [
        'list' => ['course_uuid'],
        'save' => [],
        'update' => [],
    ];


}
