<?php

namespace app\common\validate;

use think\Validate;

/**
 * 会员设置-校验
 * User:
 * Date: 2022-07-20
 * Time: 13:25
 */
class Member extends Validate
{
    protected $rule = [
        'name' => 'require',
        'price' => 'require',
        'img' => 'require',
        'text_color' => 'require',
        'pid'=>'require',
        'score'=>'require',
        'bg_img'=>'require',
    ];
    protected $field = [
        'name' => '名称',
        'price' => '售价',
        'img' => '图标',
        'text_color' => '卡片文字颜色',
        'pid'=>'下级会员uuid',
        'score'=>'积分售价',
        'bg_img'=>'背景图'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'img', 'text_color','pid','bg_img'],
        'edit' => [],
    ];
}
