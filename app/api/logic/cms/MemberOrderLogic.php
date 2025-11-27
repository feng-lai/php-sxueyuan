<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Member;
use app\api\model\MemberOrder;
use think\Exception;
use think\Db;

/**
 *培训订单逻辑
 */
class MemberOrderLogic
{
    static public function menu()
    {
        return ['订单管理', '会员订单'];
    }

    static public function cmsList($request, $userInfo)
    {
        $where = ['mo.is_deleted' => 1];
        if ($request['user_name']) {
            $where['u.name'] = ['like', '%' . $request['user_name'] . '%'];
        }
        if ($request['member_name']) {
            $where['m.name'] = ['like', '%' . $request['member_name'] . '%'];
        }
        if ($request['order_id']) {
            $where['mo.order_id'] = ['like', '%' . $request['order_id'] . '%'];
        }
        if($request['user_uuid']){
            $where['mo.user_uuid'] = $request['user_uuid'];
        }
        if($request['pay_type']){
            $where['mo.pay_type'] = $request['pay_type'];
        }
        if($request['type']){
            $where['mo.type'] = $request['type'];
        }
        $result = MemberOrder::build()
            ->alias('mo')
            ->field('
                mo.uuid,
                mo.user_uuid,
                mo.order_id,
                m.name as member_name,
                u.name as user_name,
                mo.create_time,
                mo.score_cost,
                mo.pay_type,
                mo.price_cost,
                mo.type
            ')
            ->join('member m', 'm.uuid = mo.member_uuid', 'left')
            ->join('user u', 'u.uuid = mo.user_uuid', 'left')
            ->where($where)
            ->order('mo.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = MemberOrder::build()
            ->alias('mo')
            ->field('
                mo.uuid,
                mo.user_uuid,
                mo.order_id,
                m.name as member_name,
                u.name as user_name,
                mo.create_time,
                mo.score_cost,
                mo.pay_type,
                mo.price_cost,
                mo.type,
                mo.price,
                mo.score,
                mo.last_member_uuid
            ')
            ->join('member m', 'm.uuid = mo.member_uuid', 'left')
            ->join('user u', 'u.uuid = mo.user_uuid', 'left')
            ->where('mo.uuid', $id)
            ->where('mo.is_deleted', 1)
            ->findOrFail();
        $data->last_member_name = Member::build()->where('uuid',$data->last_member_uuid)->value('name');
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }


}
