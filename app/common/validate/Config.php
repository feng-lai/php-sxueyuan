<?php

namespace app\common\validate;

use think\Validate;

/**
 * 配置-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class Config extends Validate
{
  protected $rule = [
    'content' => 'require'
  ];

  protected $field = [
    'content' => '配置内容',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['content'],
    'edit' => [],
  ];
}
