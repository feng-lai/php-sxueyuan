<?php

namespace app\api\model;

/**
 * 拉新-模型
 * User:
 * Date:
 * Time:
 */
class Invite extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function to_relation($user_uuid, $invite_code)
    {
        $p_user_uuid = User::build()->where(['invite_code' => $invite_code])->where('is_deleted', 1)->find();
        if (!$p_user_uuid) {
            return ['msg'=>'邀请用户不存在'];
        }
        if ($user_uuid == $p_user_uuid->uuid) {
            return ['msg'=>'不可邀请自己'];
        }
        if (self::build()->where('invite_user_uuid', $user_uuid)->count()) {
            return ['msg'=>'已绑定邀请用户'];
        }


        //积分奖励
        $score = Config::build()->where('key', 'INVITE')->value('value');
        //会员获得积分翻倍
        $level = Member::build()->where('uuid', $p_user_uuid->member_uuid)->find();
        if ($p_user_uuid->member_time && strtotime($p_user_uuid->member_time) >= time() && $level->level > 1) {
            $score = $score * $level->doubled;
        }

        $userRelation = self::build();
        $userRelation['uuid'] = uuid();
        $userRelation['create_time'] = now_time(time());
        $userRelation['update_time'] = now_time(time());
        $userRelation['user_uuid'] = $p_user_uuid->uuid;
        $userRelation['invite_user_uuid'] = $user_uuid;
        $userRelation['score'] = $score;
        $userRelation->save();
        User::build()->where('uuid',$p_user_uuid->uuid)->setInc('score',$score);
        //积分明细
        UserScore::build()->insert([
            'uuid'=>uuid(),
            'user_uuid'=>$p_user_uuid->uuid,
            'score'=>$score,
            'content'=>'邀请新用户',
            'left_score'=>User::build()->where('uuid',$p_user_uuid->uuid)->value('score'),
            'invite_uuid'=>$userRelation['uuid'],
            'create_time'=>now_time(time()),
            'update_time'=>now_time(time()),
        ]);
        
        return true;
    }

}
