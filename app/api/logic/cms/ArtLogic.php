<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Art;
use think\Exception;
use think\Db;

/**
 * 核心技术逻辑
 */
class ArtLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Art::build()->where('is_deleted', 1)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Art::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'title' => $request['title'],
                'desc' => $request['desc'],
                'img'=> $request['img'],
                'detail' => $request['detail'],
                'weight' => $request['weight'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            Art::build()->insert($data);
            $datas = [
                "标题"=>$data['title'],
                '详情' => $request['desc'],
                '简述' => $request['detail'],
                '权重' => $request['weight'],
            ];
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理','',$datas);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = Art::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            if($old->vis == 1){
                return ['msg'=>'下架状态才能进行编辑'];
            }
            $user = Art::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            $old = [
                "标题"=>$old['title'],
                '详情' => $old['desc'],
                '简述' => $old['detail'],
                '权重' => $old['weight'],
            ];
            $new = [
                "标题"=>$user['title'],
                '详情' => $user['desc'],
                '简述' => $user['detail'],
                '权重' => $user['weight'],
            ];
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理', $old,$new);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Art::build()->where('uuid',$id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function vis($request,$userInfo){
        if(!$request['vis']){
            return ['msg'=>'vis不能为空'];
        }
        $old = Art::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $Art = Art::build()->where('uuid',$request['uuid'])->where('is_deleted',1)->findOrFail();
        $Art->save(['vis'=>$request['vis']]);
        $old = [
            "标题"=>$old['title'],
            '详情' => $old['desc'],
            '简述' => $old['detail'],
            '权重' => $old['weight'],
            '状态'=>$old['vis'] == 1?'上架':'下架'
        ];
        $Art = [
            "标题"=>$Art['title'],
            '详情' => $Art['desc'],
            '简述' => $Art['detail'],
            '权重' => $Art['weight'],
            '状态'=>$Art['vis'] == 1?'上架':'下架'
        ];
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '核心技术管理',$old, $Art);
        return true;
    }
}
