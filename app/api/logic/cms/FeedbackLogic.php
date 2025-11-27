<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Feedback;
use think\Exception;
use think\Db;

/**
 * 问题反馈-逻辑
 */
class FeedbackLogic
{
    static public function menu()
    {
        return ['营销管理', '问题反馈'];
    }

    static public function cmsList($request, $userInfo)
    {
        $is = Feedback::build()->where('is_read',1)->update(['is_read'=>2]);
        $map['a.is_deleted'] = 1;
        $request['name'] ? $map['u.name'] = ['like', '%' . $request['name'] . '%'] : '';
        $request['type'] ? $map['a.type'] = $request['type'] : '';
        $request['start_time'] ? $map['a.create_time'] = ['between', [$request['start_time'], $request['end_time'].' 23:59:59']] : '';
        $result = Feedback::build()
            ->alias('a')
            ->join('user u', 'a.user_uuid = u.uuid', 'left')
            ->field('a.uuid,u.name,u.phone,a.type,a.create_time,a.is_read')
            ->where($map)
            ->order('a.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);

        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Feedback::build()
            ->field('a.uuid,u.name,u.phone as user_phone,a.type,a.phone,a.content,a.img,a.create_time')
            ->alias('a')
            ->join('user u', 'a.user_uuid = u.uuid', 'left')
            ->where('a.uuid', $id)
            ->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }
}
