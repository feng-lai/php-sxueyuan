<?php

namespace app\common\validate;

use think\Validate;

/**
 * 登陆-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class LoginByCode extends Validate
{
    protected $rule = [
        'phone' => 'require',
        'code' => 'require',
        'invite_code' => 'require',
    ];

    protected $field = [
        'phone' => '手机号',
        'code' => '验证码',
        'invite_code' => '邀请码'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['mobile', 'code'],
        'edit' => [],
    ];
}
