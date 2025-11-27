<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Problem;
use think\Exception;
use think\Db;

/**
 * 常见问题-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ProblemLogic
{
    static public function cmsList($userInfo)
    {
        $result = Problem::build()
            ->field('*')
            ->order('create_time desc')
            ->where('is_deleted', 1)
            ->select();
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '常见问题');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Problem::build()
            ->where('uuid', $id)
            ->field('*')
            ->where('is_deleted', 1)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '展示管理', '常见问题');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'problem' => $request['problem'],
                'answer' => $request['answer'],
                'weight' => $request['weight'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Problem::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '常见问题', '', Problem::build()->logData($request));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = Problem::where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $data = Problem::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '常见问题',Problem::build()->logData($old), Problem::build()->logData($data));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Problem::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', '常见问题');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
