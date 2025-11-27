<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Message;
use app\api\model\User;
use think\Db;
use think\Exception;

class ExpireMemberLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $user = User::build()
                ->field([
                    'u.uuid',
                    'u.member_time',
                    'msg.uuid as msg_uuid',
                ])
                ->alias('u')
                ->where([
                    'u.auto_member' => 2,
                    'u.member_time' => ['<', now_time(time())],
                    'm.level' => ['<>', 1]
                ])
                ->join('member m', 'u.member_uuid=m.uuid', 'LEFT')
                ->join('message msg','msg.user_uuid = u.uuid and msg.member_time = u.member_time','left')
                ->select()
                ->each(function ($item) {
                    if(!$item['msg_uuid']){
                        Message::build()->insert([
                            'uuid'=>uuid(),
                            'user_uuid'=>$item['uuid'],
                            'type'=>5,
                            'url_type'=>7,
                            'title'=>'您的会员已到期，会员等级已降至为基础会员',
                            'content'=>'您的会员已到期，会员等级已降至为基础会员',
                            'member_time'=>$item['member_time'],
                            'create_time'=>now_time(time()),
                            'update_time'=>now_time(time()),
                        ]);
                    }

                });
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
