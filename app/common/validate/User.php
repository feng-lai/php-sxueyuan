<?php

namespace app\common\validate;

use app\api\model\Business;
use think\Validate;

/**
 * 用户列表-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 19:38
 */
class User extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'phone' => 'require|checkPhone',
        'business_uuid' => 'require|checkBusinessUuid',
        'code'=>'require',
        'member_uuid'=>'require',
        'member_time'=>'require',
    ];

    protected $field = [
        'name' => '昵称',
        'phone' => '电话',
        'business_uuid' => '企业',
        'code'=>'验证码',
        'member_uuid'=>'会员uuid',
        'member_time'=>'会员到期时间',
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'phone'],
        'edit' => ['member_uuid','member_time'],
        'checkPhone'=>['code']
    ];

    protected function checkBusinessUuid($value, $rule, $data)
    {
        if(!Business::build()->where('uuid', $value)->where('is_deleted',1)->find()){
            return '当前企业已被删除，请重新选择';
        }
        return true;
    }

    protected function checkName($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\User::where('name', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前昵称已存在';
            }
        }else{
            if(\app\api\model\User::where('name', $value)->where('is_deleted',1)->count() > 0){
                return '当前昵称已存在';
            }
        }

        return true;
    }

    protected function checkPhone($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\User::where('phone', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前电话已存在';
            }
        }else{
            if(\app\api\model\User::where('phone', $value)->where('is_deleted',1)->count() > 0){
                return '当前电话已存在';
            }
        }
        $pattern = '/^1[3-9]\d{9}$/';
        if(!preg_match($pattern, $value)){
            return '电话格式有误';
        }

        return true;
    }
}
