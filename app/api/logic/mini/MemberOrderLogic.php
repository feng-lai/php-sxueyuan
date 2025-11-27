<?php

namespace app\api\logic\mini;

use app\api\model\Member;
use app\api\model\MemberOrder;
use app\api\model\MemberOrderLog;
use app\api\model\User;
use app\api\model\UserScore;
use app\common\tools\wechatPay;
use think\Exception;
use think\Db;

/**
 * 会员续费/升级-逻辑
 */
class MemberOrderLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where['is_deleted'] = 1;
        $where['user_uuid'] = $userInfo['uuid'];
        $result = MemberOrder::build();
        return $result->where($where)->order('create_time desc')->select();
    }

    static public function cmsDetail($id)
    {
        return Member::build()
            ->where('uuid', $id)
            ->field('*')
            ->find();
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $member = Member::build()->where(['uuid' => $request['member_uuid']])->findOrFail();
            $user_member = Member::build()->where(['uuid' => $userInfo['member_uuid']])->findOrFail();
            if (($user_member['level'] > $member['level']) && strtotime($userInfo['member_time']) > time()) {
                return ['msg' => '不能购买/续费比现有级别低的会员'];
            }
            $content = '会员开通';
            $type = 1;//类型 1=首次升级 2=自动续费 3=手动升级 4=手动续费
            $order = [
                'uuid' => uuid(),
                'order_id' => getOrderNumber(),
                'pay_type' => $request['pay_type'],
                'member_uuid' => $request['member_uuid'],
                'last_member_uuid' => $userInfo['member_uuid'],
                'user_uuid' => $userInfo['uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];

            //积分
            $score = $member['score'];
            $order['score'] = $score;
            //价格
            $price = $member['price'];
            $order['price'] = $price;
            //过期
            if (strtotime($userInfo['member_time']) < time() && $userInfo['member_time']) {
                if ($member['level'] > $user_member['level']) {
                    //升级
                    $type = 3;
                } else {
                    //续费
                    $type = 4;
                }
            }
            //未过期
            if ($member['level'] > $user_member['level'] && $user_member['level'] > 1 && strtotime($userInfo['member_time']) > time() && $userInfo['member_time']) {
                $content = '会员升级';
                //有折扣
                $score = max(0, $member['score'] - $user_member['score']);
                $price = max(0, $member['price'] - $user_member['price']);
                $type = 3;
            }
            if ($member['level'] == $user_member['level'] && $user_member['level'] > 1 && strtotime($userInfo['member_time']) > time() && $userInfo['member_time']) {
                $content = '手动续费';
                $type = 4;
            }
            //积分够不够
            if ($score > $userInfo['score']) {
                return ['msg' => '积分余额不足'];
            }
            $order['score_cost'] = $score;
            $order['price_cost'] = $price;
            $order['status'] = 2;
            $order['type'] = $type;
            $order['pay_time'] = now_time(time());
            MemberOrder::build()->insert($order);
            if ($request['pay_type'] == 1) {
                //更新用户会员/开启自动续费
                if($type != 3){
                    User::build()->where(['uuid' => $userInfo['uuid']])->update([
                        'member_uuid' => $request['member_uuid'],
                        'member_time' => date('Y-m-d H:i:s', strtotime('+1 year')),
                        'is_new' => 2,
                        'auto_member' => 1
                    ]);
                }else{
                    //升级不更新到期时间
                    User::build()->where(['uuid' => $userInfo['uuid']])->update([
                        'member_uuid' => $request['member_uuid'],
                        'is_new' => 2,
                        'auto_member' => 1
                    ]);
                }
                //用户积分扣除/积分明细
                User::build()->change_score(-$score, $content . '-' . $member['name'], $userInfo['uuid'], ['member_order_uuid', $order['uuid']]);
                //会员开通记录
                MemberOrderLog::build()->insert([
                    'uuid' => uuid(),
                    'user_uuid' => $userInfo['uuid'],
                    'pay_type' => $request['pay_type'],
                    'score' => $score,
                    'price' => $price,
                    'member_uuid' => $request['member_uuid'],
                    'order_id' => $order['uuid'],
                    'content' => $content . '-' . $member['name'],
                    'left_score' => User::build()->where(['uuid' => $userInfo['uuid']])->value('score'),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ]);
                //新用户变旧用户
                User::build()->where('uuid', $userInfo['uuid'])->update(['is_new' => 2]);
                Db::commit();
                return true;
            }
            if ($request['pay_type'] == 2) {
                wechatPay::store($price,$order['order_id'],'');
            }

        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


}
