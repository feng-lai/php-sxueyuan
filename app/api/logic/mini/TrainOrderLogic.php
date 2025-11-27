<?php

namespace app\api\logic\mini;

use app\api\model\Message;
use app\api\model\Train;
use app\api\model\TrainOrder;
use app\api\model\User;
use app\api\model\UserScore;
use think\Exception;
use think\Db;

/**
 * 培训订单-逻辑
 */
class TrainOrderLogic
{
    static public function miniAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Train::build()->where('uuid', $request['train_uuid'])->where('is_deleted', 1)->findOrFail();
            if($data->pay_type == 2){
                return ['msg'=>'暂不支持微信支付'];
            }
            if($data->pay_type == 3){
                $data['score'] = 0;
                $data['price'] = 0;
            }
            //状态
            if ($data->status != 1 || strtotime($data->sign_end_time) < time()) {
                return ['msg' => '非报名时间'];
            }

            //是否已报名
            if (TrainOrder::build()->where(['user_uuid' => $userInfo['uuid'], 'train_uuid' => $data['uuid']])->where('status', 2)->count()) {
                return ['msg' => '已报名'];
            }
            //积分 积分余额
            if ($userInfo['score'] < $data->score) {
                return ['msg' => '积分不足'];
            }
            //人数已满
            if (TrainOrder::build()->where(['train_uuid' => $data['uuid']])->where('status', 2)->count() >= $data->num) {
                return ['msg' => '报名人数已满'];
            }

            $train_order_id = 'TO' . getOrderNumber();
            //下单
            $order = [
                'uuid' => uuid(),
                'user_uuid' => $userInfo['uuid'],
                'train_uuid' => $data['uuid'],
                'name' => $request['name'],
                'score' => $data['score'],
                'price' => $data['price'],
                'pay_type' => $data['pay_type'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
                'pay_time' => now_time(time()),
                'status' => 2,
                'order_id' => $train_order_id
            ];

            TrainOrder::build()->insert($order);
            if ($data['pay_type'] == 1) {
                //用户积分扣除/积分明细
                User::build()->change_score(-$data['score'], '培训报名', $userInfo['uuid'], ['train_order_uuid', $order['uuid']]);
            }
            //新用户变旧用户
            User::build()->where('uuid', $userInfo['uuid'])->update(['is_new' => 2]);
            //通知
            Message::build()->insert([
                'uuid' => uuid(),
                'user_uuid' => $userInfo['uuid'],
                'type'=>3,
                'title' => '您好，《'.$data['name'].'》已报名成功，请您按时参加培训',
                'content' => '您好，《'.$data['name'].'》已报名成功，请您按时参加培训',
                'train_uuid'=>$data['uuid'],
                'url_type'=>5,
                'train_order_uuid'=>$order['uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ]);
            Db::commit();
            return $order['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request, $userInfo)
    {
        try {
            $where = [
                'o.user_uuid' => $userInfo['uuid'],
                'o.status' => 2
            ];
            $request['status'] ? $where['t.status'] = $request['status'] : '';
            $request['train_cate_uuid'] ? $where['t.train_cate_uuid'] = $request['train_cate_uuid'] : '';
            $request['keyword'] ? $where['t.name'] = ['like', '%' . $request['keyword'] . '%'] : '';
            $data = TrainOrder::build()
                ->field('o.uuid,t.name,tc.name as train_cate_name,t.img,t.begin_time,t.end_time,t.address,t.status,t.cancel_phone,t.pay_type,t.price,t.score')
                ->alias('o')
                ->join('train t', 'o.train_uuid = t.uuid', 'left')
                ->join('train_cate tc', 't.train_cate_uuid = tc.uuid', 'left')
                ->where($where)
                ->order('o.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {
            $data = TrainOrder::build()
                ->field('
                o.uuid,
                o.name as real_name,
                t.name,
                tc.name as train_cate_name,
                t.img,
                t.begin_time,
                t.end_time,
                t.address,
                t.status,
                t.cancel_phone,
                t.sign_begin_time,
                t.sign_end_time,
                t.is_get_score,
                t.get_score,
                u.phone,
                b.name as business_name,
                business,
                t.desc,
                o.pay_time,
                o.pay_type,
                o.score,
                o.price,
                m.name as member_name,
                t.num,
                o.train_uuid
                ')
                ->alias('o')
                ->join('train t', 'o.train_uuid = t.uuid', 'left')
                ->join('train_cate tc', 't.train_cate_uuid = tc.uuid', 'left')
                ->join('user u', 'o.user_uuid = u.uuid', 'left')
                ->join('business b', 'b.uuid = u.business_uuid', 'left')
                ->join('member m','m.uuid = t.member_uuid', 'left')
                ->where('o.uuid', $uuid)
                ->where('o.user_uuid', $userInfo['uuid'])
                ->findOrFail();
            $data->left = $data->num - TrainOrder::build()->where('train_uuid', $data->train_uuid)->where('status',2)->count();
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
