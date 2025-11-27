<?php

namespace app\common\validate;

use think\Validate;

/**
 * 核心技术-校验
 */
class Art extends Validate
{
    protected $rule = [
        'title' => 'require|checkData',
        'detail' => 'require',
        'desc'=>'require',
        'weight'=>'require|number|checkRepeat',
        'vis'=>'require|in:1,2',
        'img'=>'require',
    ];

    protected $field = [
        'title' => '标题',
        'detail'=>'简述',
        'desc'=>'详情',
        'weight'=>'权重',
        'vis'=>'状态',
        'img'=>'封面'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['title','detail','desc','weight','img'],
        'edit' => [],
    ];

    protected function checkRepeat($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\Art::where('weight', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前权重已存在';
            }
        }else{
            if(\app\api\model\Art::where('weight', $value)->where('is_deleted',1)->count() > 0){
                return '当前权重已存在';
            }
        }

        return true;
    }

    protected function checkData($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\Art::where('title', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前标题已存在';
            }
        }else{
            if(\app\api\model\Art::where('title', $value)->where('is_deleted',1)->count() > 0){
                return '当前标题已存在';
            }
        }

        return true;
    }
}
