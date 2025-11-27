<?php

namespace app\common\validate;

use think\Validate;

/**
 * 支付-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Pay extends Validate
{
  protected $rule = [
    'order_sn' => 'require',
    'pay_type' => 'require',
  ];

  protected $field = [
    'order_sn' => '订单号',
    'pay_type' => '支付类型',
  ];

  protected $message = [];

  protected $scene = [
    'list' => [],
    'save' => ['order_sn','pay_type'],
    'edit' => [],
  ];
}
