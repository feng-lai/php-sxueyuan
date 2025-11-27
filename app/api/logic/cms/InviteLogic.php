<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Invite;
use think\Exception;
use think\Db;

/**
 *培训订单逻辑
 */
class InviteLogic
{
    static public function menu()
    {
        return ['用户管理', '用户列表'];
    }

    static public function cmsList($request, $userInfo)
    {
        $where = ['i.is_deleted' => 1];
        if($request['user_uuid']){
            $where['i.user_uuid'] = $request['user_uuid'];
        }
        $result = Invite::build()
            ->alias('i')
            ->field('
                i.uuid,
                u.name,
                u.phone,
                u.create_time,
                u.last_login_time
            ')
            ->join('user u', 'u.uuid = i.invite_user_uuid', 'left')
            ->where($where)
            ->order('i.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }


}
