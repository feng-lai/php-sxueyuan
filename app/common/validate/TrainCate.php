<?php

namespace app\common\validate;

use think\Validate;

/**
 * 培训分类-校验
 */
class TrainCate extends Validate
{
    protected $rule = [
        'name' => 'require|checkData',
        'weight' => 'require|number|checkRepeat',
    ];

    protected $field = [
        'name' => '名称',
        'weight' => '权重',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'weight', 'type', 'weight', 'link_type']
    ];

    protected function checkRepeat($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\TrainCate::where('weight', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前权重已存在';
            }
        }else{
            if(\app\api\model\TrainCate::where('weight', $value)->where('is_deleted',1)->count() > 0){
                return '当前权重已存在';
            }
        }

        return true;
    }

    protected function checkData($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\TrainCate::where('name', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前名称已存在';
            }
        }else{
            if(\app\api\model\TrainCate::where('name', $value)->where('is_deleted',1)->count() > 0){
                return '当前名称已存在';
            }
        }

        return true;
    }
}
