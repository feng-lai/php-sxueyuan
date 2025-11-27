<?php

namespace app\api\model;

use think\Exception;

/**
 * 用户Token-模型
 * User: Yacon
 * Date: 2022-07-21
 * Time: 08:58
 */
class UserToken extends BaseModel
{
    public static function build()
    {
        return new self();
    }


    public function vali($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('user b', 'a.user_uuid=b.uuid')->where($where)->field('b.*')->find();
        if($list['disabled'] == 2){
            return self::returnmsg(401);
        }
        if ($list) {
            //$this->login_record($list);
            //$this->sign($list);
            return $list;
        } else {
            return self::returnmsg(401);
        }
    }

    public function vali2($token)
    {
        $time = now_time(time());
        $where = "token='{$token}' and expiry_time>'{$time}'";
        $list = $this->alias('a')->join('user b', 'a.user_uuid=b.uuid')->where($where)->field('b.*')->find();
        if ($list && $list['disabled'] == 1) {
            //$this->login_record($list);
            //$this->sign($list);
            return $list;
        } else {
            return '';
        }
    }

    public function login_record($user)
    {
        $login_day = date('Y-m-d',strtotime($user->last_login_time));
        $today = date('Y-m-d');
        if($login_day < $today){
            if((strtotime($today) - strtotime($login_day))/3600 > 24){
                $day = 1;
            }else{
                $day = $user->login_day + 1;
            }
            User::build()->where('uuid', $user->uuid)->update(['login_day' => $day, 'last_login_time' => now_time(time())]);
            if(!UserLoginDate::build()->where('create_time',$today)->where('user_uuid',$user->uuid)->count()){
                UserLoginDate::build()->insert([
                    'uuid' => uuid(),
                    'user_uuid'=>$user->uuid,
                    'create_time'=>$today,
                    'day'=>$day,
                ]);
            }
        }


    }

    public function sign($user){
        $data = Sign::build()->where('user_uuid', $user->uuid)->whereTime('create_time',date('Y-m-d'))->find();
        if(!$data){
            Sign::build()->insert([
                'uuid'=>uuid(),
                'user_uuid' => $user->uuid,
                'create_time' => now_time(time()),
                'update_time' => now_time(time())
            ]);

            $score = Config::build()->where('key', 'SIGN')->value('value') * User::build()->get_double($user);
            //用户的积分
            User::build()->where('uuid', $user->uuid)->setInc('score',$score);
            //积分
            UserScore::build()->insert([
                'uuid'=>uuid(),
                'user_uuid' => $user->uuid,
                'score'=>$score,
                'content'=>'每日登录',
                'left_score'=>User::build()->where('uuid', $user->uuid)->value('score'),
                'create_time' => now_time(time()),
                'update_time' => now_time(time())
            ]);
        }
    }
}
