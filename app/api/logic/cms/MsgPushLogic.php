<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Business;
use app\api\model\MsgPush;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 消息推送逻辑
 */
class MsgPushLogic
{
    static public function getMenu()
    {
        return ['营销管理','消息推送'];
    }
    static public function cmsList($request, $userInfo)
    {
        $result = MsgPush::build();
        if ($request['status']) $result = $result->where('status', '=', $request['status']);
        if ($request['title']) $result = $result->where('title', 'like', '%' . $request['title'] . '%');
        if ($request['start_time'] && $request['end_time']) $result = $result->where('push_time', 'between', [$request['start_time'], $request['end_time']]);
        $result = $result->where('is_deleted', 1)->order('create_time asc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
            if($item['user_type'] == 1){
                $item['user_uuid'] = User::build()->whereIn('uuid', $item['user_uuid'])->column('name');
            }
            if($item['user_type'] == 2){
                $item['business_uuid'] = Business::build()->where('uuid', $item['business_uuid'])->value('name');
            }
        });
        AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = MsgPush::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1]);
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            if($request['user_type'] == 2){
                $request['user_uuid'] = User::build()->where('is_deleted',1)->where('business_uuid', $request['business_uuid'])->column('uuid');
            }
            if($request['user_type'] == 3){
                $request['user_uuid'] = User::build()->where('is_deleted',1)->column('uuid');
            }
            $data = [
                'uuid' => uuid(),
                'type' => $request['type'],
                'course_uuid' => $request['course_uuid'],
                'train_uuid' => $request['train_uuid'],
                'user_type' => $request['user_type'],
                'title' => $request['title'],
                'content' => $request['content'],
                'user_uuid'=>$request['user_uuid'],
                'business_uuid' => $request['business_uuid'],
                'push_time' => $request['push_time'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            MsgPush::build()->save($data);

            AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1], '', MsgPush::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            if($request['user_type'] == 2){
                $request['user_uuid'] = User::build()->where('is_deleted',1)->where('business_uuid', $request['business_uuid'])->column('uuid');
            }
            if($request['user_type'] == 3){
                $request['user_uuid'] = User::build()->where('is_deleted',1)->column('uuid');
            }
            $data = [
                'type' => $request['type'],
                'course_uuid' => $request['course_uuid'],
                'train_uuid' => $request['train_uuid'],
                'user_type' => $request['user_type'],
                'title' => $request['title'],
                'content' => $request['content'],
                'user_uuid'=>$request['user_uuid'],
                'business_uuid' => $request['business_uuid'],
                'push_time' => $request['push_time'],
                'update_time' => now_time(time()),
            ];
            $old = MsgPush::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $user = MsgPush::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($data);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1], MsgPush::build()->logData($old), MsgPush::build()->logData($user));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = MsgPush::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function vis($request, $userInfo)
    {
        if (!$request['vis']) {
            return ['msg' => 'vis不能为空'];
        }
        $old = MsgPush::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $MsgPush = MsgPush::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $MsgPush->save(['vis' => $request['vis']]);
        AdminLog::build()->add($userInfo['uuid'], self::getMenu()[0], self::getMenu()[1], MsgPush::build()->logData($old), MsgPush::build()->logData($MsgPush));
        return true;
    }
}
