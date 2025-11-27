<?php

namespace app\common\validate;

use think\Validate;

/**
 * 分享课程-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class CourseShare extends Validate
{
  protected $rule = [
        'course_uuid'=>'require',
  ];

  protected $field = [
        'course_uuid'=>'课程uuid'
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['course_uuid'],
    'edit' => [],
  ];
}
