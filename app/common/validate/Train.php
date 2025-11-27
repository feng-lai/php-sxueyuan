<?php

namespace app\common\validate;

use think\Validate;

/**
 * 培训-校验
 */
class Train extends Validate
{
    protected $rule = [
        'name' => 'require',
        'train_cate_uuid' => 'require',
        'begin_time' => 'require',
        'end_time' => 'require',
        'sign_begin_time' => 'require',
        'sign_end_time'=>'require',
        'address'=>'require',
        'num' => 'require|number',
        'member_uuid' => 'require',
        'pay_type' => 'require|in:1,2,3',
        'price' => 'require|float',
        'score' => 'require',
        'cancel_phone'=>'require',
        'get_score'=>'require',
        'is_get_score' => 'require|in:1,2',
        'weight' => 'require|integer',
        'img' => 'require',
        'desc' => 'require',
        'is_recommend'=>'require|in:1,2',

    ];
    protected $field = [
        'name' => '名称',
        'train_cate_uuid' => '培训分类uuid',
        'begin_time' => '开始时间',
        'end_time' => '结束时间',
        'sign_begin_time' => '报名开始时间',
        'sign_end_time'=>'报名结束时间',
        'address'=>'地点',
        'num' => '报名人数',
        'member_uuid' => '最低级别会员uuid',
        'pay_type' => '支付方式',
        'price' => '微信支付售价',
        'score' => '积分售价',
        'cancel_phone'=>'取消报名手机号',
        'get_score'=>'奖励积分数',
        'is_get_score' => '是否有积分奖励',
        'weight' => '权重',
        'img' => '封面',
        'desc' => '介绍',
        'is_recommend'=>'是否推荐'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name','train_cate_uuid','begin_time','end_time','sign_begin_time','sign_end_time','address','num','member_uuid','pay_type','cancel_phone','is_get_score','weight','img','desc'],
        'edit' => [],
        'recommend' => ['is_recommend'],
    ];
}
