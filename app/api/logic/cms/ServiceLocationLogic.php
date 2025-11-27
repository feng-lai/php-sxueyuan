<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\ServiceLocation;
use think\Exception;
use think\Db;

/**
 * 服务据点逻辑
 */
class ServiceLocationLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = ServiceLocation::build()->where('is_deleted', 1)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = ServiceLocation::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'phone' => $request['phone'],
                'address' => $request['address'],
                'weight' => $request['weight'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            ServiceLocation::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理','',ServiceLocation::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = ServiceLocation::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $user = ServiceLocation::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理', ServiceLocation::build()->logData($old),ServiceLocation::build()->logData($user));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = ServiceLocation::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理','', $data);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function vis($request,$userInfo){
        if(!$request['vis']){
            return ['msg'=>'vis不能为空'];
        }
        $old = ServiceLocation::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $Art = ServiceLocation::build()->where('uuid',$request['uuid'])->where('is_deleted',1)->findOrFail();
        $Art->save(['vis'=>$request['vis']]);
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '服务据点管理',$old, $Art);
        return true;
    }
}
