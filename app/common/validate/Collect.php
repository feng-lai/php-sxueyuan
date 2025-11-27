<?php

namespace app\common\validate;

use think\Validate;

/**
 * 收藏-校验
 */
class Collect extends Validate
{
  protected $rule = [
    'course_uuid'=>'require'
  ];

  protected $field = [
    'course_uuid'=>'课程'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['course_uuid'],
    'edit' => [],
  ];
}
