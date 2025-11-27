<?php

namespace app\common\validate;

use think\Validate;

/**
 * 获取手机号码-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class GetMobile extends Validate
{
  protected $rule = [
    'access_token' => 'require',
    'out_id' => '',
  ];

  protected $field = [
    'access_token' => 'App端SDK获取的登录Token',
    'out_id' => '外部流水号',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['access_token', 'out_id'],
    'edit' => [],
  ];
}
