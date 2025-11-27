<?php

namespace app\common\validate;

use think\Validate;

/**
 * 微信登录-校验
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class WechatLogin extends Validate
{
    protected $rule = [
        'type' => 'require|in:1,2',
        'code' => 'require',
        'invite_code' => 'require',
    ];

    protected $field = [
        'type'=>'终端类型',
        'code'=>'',
        'invite_code'=>'邀请码',
    ];

    protected $message = [];

    protected $scene = [
        'save' => [
            'type',
            'code',
        ],
    ];
}
