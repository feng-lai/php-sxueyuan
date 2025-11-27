<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台用户-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminLogin extends Validate
{
  protected $rule = [
    'uname' => 'require',
    'password' => 'require'
  ];

  protected $field = [
    'uname' => '账号',
    'password' => '密码',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['uname', 'password'],
    'edit' => [],
  ];
}
