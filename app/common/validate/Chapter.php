<?php

namespace app\common\validate;

use think\Validate;

/**
 * 章节-校验
 */
class Chapter extends Validate
{
    protected $rule = [
        'course_uuid' => 'require',
        'persent' => 'require',
    ];

    protected $field = [
        'course_uuid' => '课程uuid',
        'persent'=>'学习进度',

    ];

    protected $message = [];

    protected $scene = [
        'list' => ['course_uuid'],
        'setPersent'=>['persent'],
    ];

}
