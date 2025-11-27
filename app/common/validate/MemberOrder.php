<?php

namespace app\common\validate;

use think\Validate;

/**
 * 会员设置-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class MemberOrder extends Validate
{
    protected $rule = [
        'member_uuid' => 'require',
        'pay_type' => 'require',
    ];
    protected $field = [
        'member_uuid' => '会员uuid',
        'pay_type' => '支付类型',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['member_uuid', 'pay_type'],
        'edit' => [],
    ];
}
