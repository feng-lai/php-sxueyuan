<?php

namespace app\common\validate;

use think\Validate;

/**
 * 订单-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Order extends Validate
{
    protected $rule = [
        'course_uuid' => 'require'
    ];

    protected $field = [
        'course_uuid' => '拼团课程'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['course_uuid'],
        'edit' => [],
    ];
}
