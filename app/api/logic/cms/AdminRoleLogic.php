<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminRole;
use app\api\model\AdminMenu;
use think\Exception;
use think\Db;

/**
 * 后台菜单-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminRoleLogic
{
    static public function cmsList($request,$userInfo)
    {
        $where = ['is_deleted' => 1];
        $list = AdminRole::build()->where($where)->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '角色管理');
        return $list;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = AdminRole::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('*')
            ->find();
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '角色管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'menus' => $request['menus'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            AdminRole::build()->save($data);
            $datas = [
                "名称"=>$data['name'],
                "菜单"=>implode(',',AdminMenu::build()->where('uuid','in',$data['menus'])->column('name')),
            ];
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '角色管理', '', $datas);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = AdminRole::build()->where('uuid', $request['uuid'])->find();
            $data = AdminRole::build()->where('uuid', $request['uuid'])->find();
            $data->save($request);
            $old = [
                "名称"=>$old['name'],
                "菜单"=>implode(',',AdminMenu::build()->where('uuid','in',$old['menus'])->column('name')),
            ];
            $data = [
                "名称"=>$data['name'],
                "菜单"=>implode(',',AdminMenu::build()->where('uuid','in',$data['menus'])->column('name')),
            ];
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '角色管理', $old, $data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            if (Admin::build()->where('role_uuid', $id)->where('is_deleted', 1)->find()) {
                return ['msg' => '请先移除角色下相关管理员'];
            }
            $data = AdminRole::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '角色管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
