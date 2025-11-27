<?php

namespace app\api\logic\common;

use app\api\model\Business;
use app\api\model\Invite;
use app\api\model\Member;
use app\api\model\UserToken;
use app\api\model\User;
use app\api\model\Contestant;
use think\Exception;
use think\Db;
use think\Config;
use app\api\model\Captcha;

/**
 * 登录-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class loginByCodeLogic
{
  static public function loginByCode($request)
  {
    try {
        Db::startTrans();

        //判断验证码
        Captcha::build()->captchaCheck(['phone' => $request['phone'], 'code' => $request['code']]);


        $user = User::where(['phone' => $request['phone'], 'is_deleted' => 1])->find();

        if ($user) {
            if ($user['disabled'] == 2) {
                throw new Exception('您已被禁用，无法登陆');
            }
            $login_day = date('Y-m-d',strtotime($user->last_login_time));
            $today = date('Y-m-d');
            if($login_day < $today){
                if((strtotime($today) - strtotime($login_day))/3600 > 24){
                    $day = 1;
                }else{
                    $day = $user->login_day + 1;
                }
                $user['login_day'] = $day;
            }

            $user['update_time'] = date("Y-m-d H:i:s", time());
            $user['last_login_time'] = date("Y-m-d H:i:s", time());
            $user->save();
            //绑定分享关系
            if($request['invite_code']){
                Invite::build()->to_relation($user['uuid'],$request['invite_code']);
            }
        }else{
            $user = [
                'uuid' => uuid(),
                'phone' => $request['phone'],
                'name' => getNumberName(20),
                'invite_code' => generateRandomString(),
                'member_uuid' => Member::build()->where('level',1)->where('is_deleted',1)->value('uuid'),
                'last_login_time' => date("Y-m-d H:i:s", time()),
                'create_time' => date("Y-m-d H:i:s", time()),
                'update_time' => date("Y-m-d H:i:s", time()),
            ];
            User::build()->save($user);
            //绑定分享关系
            if($request['invite_code']){
                Invite::build()->to_relation($user['uuid'],$request['invite_code']);
            }
            $user = User::build()->where(['phone' => $request['phone']])->where('is_deleted',1)->find();
            //注册奖励积分
            $score = \app\api\model\Config::build()->where('key','REGISTER')->value('value');
            User::build()->change_score($score,'新用户注册',$user['uuid']);
        }

        // 更新用户token
        $userToken = UserToken::build()->where('user_uuid', $user['uuid'])->find();
        if (null == $userToken) {
            $userToken = UserToken::build();
            $userToken->uuid = uuid();
            $userToken->token = uuid();
            $userToken->user_uuid = $user['uuid'];
            $userToken->create_time = date("Y-m-d H:i:s", time());
        }
        $userToken->expiry_time = date("Y-m-d H:i:s", time() + 3600 * 24 * 90);
        $userToken->update_time = date("Y-m-d H:i:s", time());
        $userToken->save();

        $user->member = Member::build()->where('level',1)->where('is_deleted',1)->find();

        if($user->member_time && $user->member_time > now_time(time())){
            $user->member = Member::build()->where(['uuid' => $user['member_uuid']])->where('is_deleted',1)->find();
        }

        $business = Business::build()->where('uuid', $user['business_uuid'])->value('name');

        if($business){
            $user->business = $business;
        }
        unset($user['unionid']);
        UserToken::build()->login_record($user);
        UserToken::build()->sign($user);

        Db::commit();
        return ['token' => $userToken['token'], 'user' => $user];
    } catch (Exception $e) {
        Db::rollback();
        return ['msg' => $e->getMessage()];
    }
  }
}
