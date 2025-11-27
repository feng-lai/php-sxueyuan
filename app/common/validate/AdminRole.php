<?php

namespace app\common\validate;

use think\Validate;

/**
 * 后台角色-校验
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:25
 */
class AdminRole extends Validate
{
    protected $rule = [
        'name' => 'require|checkName',
        'menus' => 'require',
    ];

    protected $field = [
        'name' => '名称',
        'menus' => '菜单uuid数组'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'menus'],
        'edit' => [],
    ];

    protected function checkName($value, $rule, $data){
        $where = ['is_deleted'=>1,'name'=>$value];
        if(isset($data['uuid'])){
            $where['uuid'] = ['<>', $data['uuid']];
        }
        if(\app\api\model\AdminRole::where($where)->count()){
            return '管理员名称已存在';
        }
        return true;
    }
}
