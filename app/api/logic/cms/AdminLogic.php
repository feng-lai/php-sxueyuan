<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\AdminToken;
use app\api\model\AdminRole;
use app\api\model\College;
use think\Exception;
use think\Db;
use app\common\tools\Sync;

/**
 * 后台用户-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class AdminLogic
{
    static public function cmsList($request,$userInfo)
    {
        $map['a.is_deleted'] = ['=', 1];
        $result = Admin::build()
            ->field(
                'a.uuid,
                a.name,
                r.name as role_name'
            )
            ->alias('a')
            ->join('admin_role r', 'r.uuid = a.role_uuid', 'left')
            ->where($map)
            ->order('a.uuid desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '管理员列表');
        return $result;
    }

    static public function cmsDetail($id,$userInfo)
    {
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '管理员列表');
        return Admin::build()
            ->alias('a')
            ->where('a.uuid', $id)
            ->where('a.is_deleted', '=', 1)
            ->field('
                a.uuid,
                a.name,
                a.create_time
            '
            )
            ->find();
    }

    static public function cmsAdd($request,$userInfo)
    {
        try {
            Db::startTrans();
            AdminRole::build()->findOrFail($request['role_uuid']);
            if (Admin::build()->where('uname', $request['uname'])->count()) {
                throw new Exception('账号已存在', 500);
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'password' => md6($request['password']),
                'uname' => $request['uname'],
                'role_uuid' => $request['role_uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Admin::build()->save($data);
            //添加token
            $token = AdminToken::build();
            $token->uuid = uuid();
            $token->admin_uuid = $data['uuid'];
            $token->create_time = now_time(time());
            $token->update_time = now_time(time());
            $token->save();
            Db::commit();
            $datas = [
                "用户名称"=>$data['name'],
                "账号"=>$data['uname'],
                "角色"=>AdminRole::build()->where('uuid', $data['role_uuid'])->value('name')
            ];
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '管理员管理','',$datas);
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request,$userInfo)
    {
        try {
            $old = Admin::build()->where('uuid', $request['uuid'])->find()->toArray();
            $user = Admin::build()->where('uuid', $request['uuid'])->find();
            if($request['password']) {
                $request['password'] = md6($request['password']);
            }
            $user->save($request);
            $old = [
                "用户名称"=>$old['name'],
                "账号"=>$old['uname'],
                "角色"=>AdminRole::build()->where('uuid', $old['role_uuid'])->value('name')
            ];
            $user = [
                "用户名称"=>$user['name'],
                "账号"=>$user['uname'],
                "角色"=>AdminRole::build()->where('uuid', $user['role_uuid'])->value('name')
            ];
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '管理员列表',$old,$user);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id,$userInfo)
    {
        try {
            $data = Admin::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '权限管理', '管理员列表');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setPermission($request,$userInfo){
        try {
            AdminRole::build()->where('uuid',$request['role_uuid'])->findOrFail();
            College::build()->where('uuid',$request['college_uuid'])->findOrFail();
            foreach(explode(',',$request['uuid']) as $v){
                Admin::build()->where('uuid',$v)->update([
                    'level'=>$request['level'],
                    'role_uuid'=>$request['role_uuid'],
                    'college_uuid'=>$request['college_uuid']
                ]);
            }
            AdminLog::build()->add($userInfo['uuid'], '教师管理', '权限设置');
            return true;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function sync($userInfo){
        try {
            Sync::build()->getData(2);
            AdminLog::build()->add($userInfo['uuid'], '教师管理', 'CAS同步');
            return true;
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
