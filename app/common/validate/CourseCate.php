<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程分类-校验
 */
class CourseCate extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'weight' => 'require|checkWeight'
    ];

    protected $field = [
        'name' => '名称',
        'weight' => '权重'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'weight'],
        'update' => ['name', 'weight'],
    ];
    protected function checkWeight($value,$rule,$data){
        $where = ['is_deleted' => 1,'weight' => $value,'pid'=>$data['pid']];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>',$data['uuid']];
        }
        if(\app\api\model\CourseCate::build()->where($where)->find()){
            return '权重已存在';
        }
        return true;
    }
    protected function checkName($value,$rule,$data){
        $where = ['is_deleted' => 1,'name' => $value,'pid'=>$data['pid']];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>',$data['uuid']];
        }
        if(\app\api\model\CourseCate::build()->where($where)->find()){
            return '名称已存在';
        }
        return true;
    }
}
