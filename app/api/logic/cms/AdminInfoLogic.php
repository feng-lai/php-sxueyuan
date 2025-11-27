<?php

namespace app\api\logic\cms;

use app\api\model\AdminMenu;
use app\api\model\AdminRole;
use app\api\model\College;
use think\Exception;
use think\Db;

/**
 * 后台用户-逻辑
 */
class AdminInfoLogic
{
    static public function info($userInfo)
    {
        $result = $userInfo;
        if ($result['college_uuid']) {
            $result['college_name'] = College::build()->where('uuid', $result['college_uuid'])->value('name');
        } else {
            $result['college_name'] = '';
        }
        if ($result['level'] == 3) {
            // 角色名
            $result['role_name'] = '超级管理员';
            // 获取用户权限
            $result['menus'] = AdminMenu::build()->where(['is_deleted' => 1])->column('uuid');
        } else {
            // 查询角色
            $adminRole = AdminRole::build()->where(['uuid' => $result['role_uuid']])->find();
            // 角色名
            $result['role_name'] = $adminRole['name'];
            // 获取用户权限
            $result['menus'] = $adminRole['menus'];
        }
        $map['is_deleted'] = 1;
        $map['uuid'] = ['in', $result['menus']];
        $menus_all = AdminMenu::build()->field('uuid,name,pid,level')->where($map)->where('level',1)->order('serial_number asc')->select();
        foreach ($menus_all as $v) {
            $v->child = AdminMenu::build()->field('uuid,name,pid,level')-> where('pid', $v->uuid)->where('level',2)->where($map)->order('serial_number asc')->select();
            foreach ($v->child as $v2) {
                $v2->child = AdminMenu::build()->field('uuid,name,pid,level')->where('pid', $v2->uuid)->where($map)->where('level',3)->order('serial_number asc')->select();
            }
        }
        $result['menus_all'] = $menus_all;
        unset($result['role_uuid']);
        unset($result['disabled']);
        unset($result['password']);
        unset($result['is_deleted']);
        unset($result['update_time']);
        unset($result['level']);
        return $result;
    }


}
