<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Member;
use app\api\model\MemberOrder;
use app\api\model\MemberOrderLog;
use app\api\model\Message;
use app\api\model\User;
use think\Db;
use think\Exception;

class AutoMemberLogic
{
    public static function sync()
    {
        try {
            Db::startTrans();
            //会员自动续费
            $user = User::build()->field('member_uuid,uuid,score')->where('auto_member',1)->where('member_time','<',now_time(time()))->select();
            foreach($user as $v){
                $member = Member::build()->where('uuid',$v['member_uuid'])->find();
                $score = $member['score'];
                if($score <= $v['score']){
                    //积分够续费
                    //订单
                    $order_id = getOrderNumber();
                    MemberOrder::build()->insert([
                        'uuid'=>uuid(),
                        'user_uuid'=>$v['uuid'],
                        'score'=>$score,
                        'member_uuid'=>$v['member_uuid'],
                        'order_id'=>$order_id,
                        'pay_type'=>1,
                        'type'=>2,
                        'score_cost'=>$score,
                        'last_member_uuid'=>$v['member_uuid'],
                        'pay_time'=>now_time(time()),
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ]);

                    //订单记录
                    MemberOrderLog::build()->insert([
                        'uuid'=>uuid(),
                        'user_uuid'=>$v['uuid'],
                        'order_id'=>$order_id,
                        'pay_type'=>1,
                        'member_uuid'=>$v['member_uuid'],
                        'score'=>$score,
                        'content'=>'自动续费-'.$member->name,
                        'left_score'=>$v['score'] - $score,
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ]);

                    //更新用户会员到期时间
                    User::build()->where(['uuid' => $v['uuid']])->update([
                        'member_time' => date('Y-m-d H:i:s', strtotime('+1 year'))
                    ]);
                    //用户积分明细/积分余额更新
                    User::build()->change_score(-$score,'自动续费-'.$member->name,$v['uuid'],['member_order_uuid',$order_id]);

                    //续费通知
                    Message::build()->insert([
                        'uuid'=>uuid(),
                        'user_uuid'=>$v['uuid'],
                        'type'=>5,
                        'url_type'=>7,
                        'title'=>'您的会员已到期,已帮您进行自动续费',
                        'content'=>'您的会员已到期,已帮您进行自动续费 续费时间1年 到期时间：'.date('Y-m-d H:i:s', strtotime('+1 year')),
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ]);
                }else{
                    //会员积分不足,过期通知
                    Message::build()->insert([
                        'uuid'=>uuid(),
                        'user_uuid'=>$v['uuid'],
                        'type'=>5,
                        'url_type'=>7,
                        'title'=>'您的会员已到期，会员等级已降至为基础会员',
                        'content'=>'您的会员已到期，会员等级已降至为基础会员',
                        'create_time'=>now_time(time()),
                        'update_time'=>now_time(time()),
                    ]);
                    //关闭自动续费
                    User::build()->where(['uuid' => $v['uuid']])->update(['auto_member'=>2]);
                }
            }
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
