<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Config;
use think\Exception;
use think\Db;

/**
 * 配置-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ConfigLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Config::build();
        if ($request) {
            $result = $result->where('key', 'in', $request);
        }
        $result = $result->select();
        AdminLog::build()->add($userInfo['uuid'], '营销管理', '积分管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Config::build()
            ->where('key', $id)
            ->field('*')
            ->find();
        AdminLog::build()->add($userInfo['uuid'], '营销管理', '积分管理');
        return $data;
    }


    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = Config::build()->where('key', $request['key'])->findOrFail();
            $data = Config::build()->where('key', $request['key'])->findOrFail();
            $data->save(['value' => $request['value']]);
            AdminLog::build()->add($userInfo['uuid'], '营销管理', '积分管理',[$old->content=>$old->value],[$data->content=>$data->value]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
