<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台用户-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Admin extends Validate
{
    protected $rule = [
        'role_uuid' => 'require',
        'name' => 'require',
        'uname' => 'require',
        'password' => 'require',
    ];

    protected $field = [
        'name' => '用户名',
        'uname' => '账号',
        'role_uuid' => '角色uuid',
        'password' => '密码',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'set_permission' => ['name', 'uname', 'role_uuid','password'],
        'edit' => [],
    ];
}
