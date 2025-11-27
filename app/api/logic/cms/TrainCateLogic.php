<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Train;
use app\api\model\TrainCate;
use think\Exception;
use think\Db;

/**
 * 培训分类逻辑
 */
class TrainCateLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = TrainCate::build()->where('is_deleted', 1)->order('weight asc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训分类管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = TrainCate::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训分类管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'weight' => $request['weight'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            TrainCate::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训分类管理','',TrainCate::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = TrainCate::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $user = TrainCate::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训分类管理', TrainCate::build()->logData($old),TrainCate::build()->logData($user));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = TrainCate::build()->where('uuid',$id)->findOrFail();
            if(Train::build()->where('train_cate_uuid',$id)->where('is_deleted', 1)->count()){
                return ['msg'=>'失败，有培训绑定了该分类'];
            }
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训分类管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
