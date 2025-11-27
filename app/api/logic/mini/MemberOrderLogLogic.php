<?php

namespace app\api\logic\mini;

use app\api\model\MemberOrderLog;
use think\Exception;
use think\Db;

/**
 * 会员开通记录-逻辑
 */
class MemberOrderLogLogic
{
    static public function cmsList($request,$userInfo)
    {
        $where['is_deleted'] = 1;
        $where['user_uuid'] = $userInfo['uuid'];
        $result = MemberOrderLog::build();
        return $result->where($where)->order('create_time desc')->select();
    }

}
