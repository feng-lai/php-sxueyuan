<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台菜单-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminMenu extends Validate
{
  protected $rule = [
    'name' => 'require',
    'url' => 'require',
    'pid' => '',
    'level' => ''
  ];

  protected $field = [
    'name' => '名称',
    'url' => 'url',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['name', 'url'],
    'edit' => [],
  ];
}
