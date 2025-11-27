<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\AdminToken;
use app\api\model\AdminRole;
use think\Exception;
use think\Db;

/**
 * 操作日志-逻辑
 */
class AdminLogLogic
{
    static public function cmsList($request,$userInfo)
    {
        $map['l.is_deleted'] = 1;
        if ($request['start_time']) $map['l.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
        if ($request['admin_name']) $map['a.name'] = ['like', '%' . $request['admin_name'] . '%'];
        if ($request['menu']) $map['l.menu'] = ['=',$request['menu']];
        if ($request['sub_menu']) $map['l.sub_menu'] = ['=',$request['sub_menu']];
        $result = AdminLog::build()
            ->field('l.uuid,a.name as admin_name,l.menu,l.sub_menu,l.explain,l.create_time')
            ->alias('l')
            ->join('admin a', 'a.uuid = l.admin_uuid')
            ->where($map)
            ->order('l.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '操作日志管理');
        return $result;
    }

    static public function cmsDetail($id,$userInfo)
    {
        AdminLog::build()->add($userInfo['uuid'], '权限管理', '操作日志管理');
        return AdminLog::build()
            ->where('uuid', $id)
            ->where('is_deleted', '=', 1)
            ->field('*')
            ->find();
    }



    static public function cmsDelete($id)
    {
        try {
            //少于90天不能删除
            $data = AdminLog::build()->where('uuid', $id)->findOrFail();
            if(time() - strtotime($data->create_time) < 90*3600*24){
                return ['msg'=>'少于90天的日志不能删除'];
            }
            $data->save(['is_deleted' => 2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
