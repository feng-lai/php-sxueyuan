<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程订单-校验
 */
class CourseOrder extends Validate
{
    protected $rule = [
        'chapter_uuid' => 'require',
        'type' => 'require|in:1,2',
    ];
    protected $field = [
        'chapter_uuid' => '章节uuid',
        'type' => '类型',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['chapter_uuid', 'type'],
        'edit' => [],
    ];
}
