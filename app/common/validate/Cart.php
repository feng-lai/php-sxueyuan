<?php

namespace app\common\validate;

use think\Validate;

/**
 * 购物车-校验

 */
class Cart extends Validate
{
    protected $rule = [
        'chapter_uuid' => 'require',
    ];

    protected $field = [
        'chapter_uuid' => '章节uuid',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['chapter_uuid'],
        'edit' => [],
    ];
}
