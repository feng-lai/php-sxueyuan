<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Member;
use app\api\model\Train;
use app\api\model\TrainOrder;
use app\api\model\User;
use app\api\model\UserScore;
use think\Exception;
use think\Db;

/**
 *培训订单逻辑
 */
class TrainOrderLogic
{
    static public function menu()
    {
        return ['订单管理', '培训订单'];
    }

    static public function cmsList($request, $userInfo)
    {
        $where = ['to.is_deleted' => 1,'to.status' => ['<>',1]];
        if ($request['user_name']) {
            $where['u.name'] = ['like', '%' . $request['user_name'] . '%'];
        }
        if ($request['train_name']) {
            $where['t.name'] = ['like', '%' . $request['train_name'] . '%'];
        }
        if ($request['order_id']) {
            $where['to.order_id'] = ['like', '%' . $request['order_id'] . '%'];
        }
        if ($request['order_status']) {
            switch ($request['order_status']) {
                case '1':
                    $where['t.status'] = ['<>', 3];
                    $where['to.status'] = 2;
                    break;
                case '2':
                    $where['t.status'] = 3;
                    $where['to.status'] = 2;
                    break;
                case '3':
                    $where['to.status'] = 3;
                    break;
                case '4':
                    $where['to.status'] = 4;
                    break;
                case '1,2':
                    $where['to.status'] = 2;
                    break;
            }
        }
        if ($request['user_uuid']) {
            $where['to.user_uuid'] = $request['user_uuid'];
        }
        if ($request['train_uuid']) {
            $where['to.train_uuid'] = $request['train_uuid'];
        }
        if ($request['pay_type']) {
            $where['to.pay_type'] = $request['pay_type'];
        }
        $result = TrainOrder::build()
            ->alias('to')
            ->field('
                to.uuid,
                to.train_uuid,
                to.name,
                to.user_uuid,
                to.order_id,
                t.name as train_name,
                u.name as user_name,
                u.phone,
                b.name as business_name,
                u.business,
                u.member_time,
                u.member_uuid,
                to.create_time,
                to.score,
                to.pay_type,
                to.price,
                t.status as train_status,
                to.status,
                to.get_score
            ')
            ->join('train t', 't.uuid = to.train_uuid', 'left')
            ->join('user u', 'u.uuid = to.user_uuid', 'left')
            ->join('business b', 'b.uuid = u.business_uuid', 'left')
            ->where($where)
            ->order('to.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                if ($item['train_status'] != 3 && $item['status'] == 2) {
                    $item['order_status'] = 1;
                }
                if ($item['train_status'] == 3 && $item['status'] == 2) {
                    $item['order_status'] = 2;
                }
                if ($item['status'] == 3) {
                    $item['order_status'] = 3;
                }
                if ($item['status'] == 4) {
                    $item['order_status'] = 4;
                }
                unset($item['status']);
                if ($item['member_time'] && $item['member_time'] >= now_time(time())) {
                    $item['member_name'] = Member::build()->where('uuid', $item['member_uuid'])->where('is_deleted', 1)->value('name');
                } else {
                    $item['member_name'] = Member::build()->where('level', 1)->where('is_deleted', 1)->value('name');
                }
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = TrainOrder::build()
            ->alias('co')
            ->field('
                co.uuid,
                co.order_id,
                t.name as train_name,
                u.name as user_name,
                co.create_time,
                co.score,
                co.price,
                co.pay_type,
                u.member_uuid,
                u.member_time,
                t.status as train_status,
                co.status
            ')
            ->join('train t', 't.uuid = co.train_uuid', 'left')
            ->join('user u', 'u.uuid = co.user_uuid', 'left')
            ->where('co.uuid', $id)
            ->where('co.is_deleted', 1)
            ->findOrFail();
        if (strtotime($data->member_time) > time()) {
            $data['member_name'] = Member::build()->where('uuid', $data['member_uuid'])->value('name');
        } else {
            $data['member_name'] = Member::build()->where('level', 1)->value('name');
        }
        if ($data['train_status'] != 3 && $data['status'] == 2) {
            $data['order_status'] = 1;
        }
        if ($data['train_status'] == 3 && $data['status'] == 2) {
            $data['order_status'] = 2;
        }
        if ($data['status'] == 3) {
            $data['order_status'] = 3;
        }
        if ($data['status'] == 4) {
            $data['order_status'] = 4;
        }
        //unset($data['train_status']);
        unset($data['status']);
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }
    static public function cmsDelete($id, $userInfo)
    {
        try {
            Db::startTrans();
            $data = TrainOrder::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            $train = Train::build()->where('uuid',$data->train_uuid)->where('is_deleted',1)->findOrFail();
            if($train->status == 3){
                return ['msg'=>'已完成的培训不能取消'];
            }
            if(in_array($data->status,[3,4])){
                return true;
            }
            $res = [];
            $res['status'] = 4;
            if($data->pay_type == 1){
                //积分
                User::build()->change_score($data['score'],'培训取消报名',$data['user_uuid'],['train_order_uuid',$data['uuid']]);
            }
            $data->save($res);
            Db::commit();
            return true;
        }catch (\Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

}
