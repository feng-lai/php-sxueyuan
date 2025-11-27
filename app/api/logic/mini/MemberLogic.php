<?php

namespace app\api\logic\mini;

use app\api\model\Member;
use think\Exception;
use think\Db;

/**
 * 会员-逻辑
 */
class MemberLogic
{
    static public function cmsList()
    {
        $where['is_deleted'] = 1;
        $result = Member::build();
        return $result->where($where)->order('level asc')->select();
    }

    static public function cmsDetail($id)
    {
        return Member::build()
            ->where('uuid', $id)
            ->field('*')
            ->find();
    }


}
