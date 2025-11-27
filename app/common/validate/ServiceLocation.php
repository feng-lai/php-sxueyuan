<?php

namespace app\common\validate;

use think\Validate;

/**
 * 服务据点-校验
 */
class ServiceLocation extends Validate
{
    protected $rule = [
        'name' => 'require|checkData',
        'phone' => 'require',
        'address'=>'require',
        'weight'=>'require|number|checkRepeat'
    ];

    protected $field = [
        'name' => '名称',
        'phone'=>'电话',
        'address'=>'地点',
        'weight'=>'权重'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name','phone','address','weight'],
        'edit' => [],
    ];

    protected function checkRepeat($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\ServiceLocation::where('weight', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前权重已存在';
            }
        }else{
            if(\app\api\model\ServiceLocation::where('weight', $value)->where('is_deleted',1)->count() > 0){
                return '当前权重已存在';
            }
        }

        return true;
    }

    protected function checkData($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\ServiceLocation::where('name', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前名称已存在';
            }
        }else{
            if(\app\api\model\ServiceLocation::where('name', $value)->where('is_deleted',1)->count() > 0){
                return '当前名称已存在';
            }
        }

        return true;
    }
}
