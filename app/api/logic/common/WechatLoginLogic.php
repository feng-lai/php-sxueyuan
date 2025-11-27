<?php

namespace app\api\logic\common;

use AlibabaCloud\SDK\Dypnsapi\V20170525\Models\GetSmsAuthTokensResponseBody\data;
use app\api\model\ActivitiesTurntable;
use app\api\model\Captcha;
use app\api\model\UserToken;
use app\api\model\Employee;
use app\api\model\EmployeeToken;
use app\api\model\Interest;
use app\api\model\User;
use app\api\model\UserInterrest;
use app\api\model\WechatLogin;
use app\api\model\Contestant;
use app\api\model\UserRelation;
use think\Exception;
use think\Db;
use think\Config;
use app\exception\BaseException;

/**
 * 微信登录-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class WechatLoginLogic
{
    static public function miniApp($request)
    {
        try {
            $config = Config::get('wechat');
            $appid = $config['MinAppID'];
            $appSecret = $config['MinAppSecret'];
            $requestUrl = "https://api.weixin.qq.com/sns/jscode2session";
            $requestUrl .= "?appid={$appid}&secret={$appSecret}&js_code={$request['code']}&grant_type=authorization_code";
            $res = curlSend($requestUrl);
            $jsonArray = json_decode($res, true);
            print_r($jsonArray);exit;
            // 校验是否登陆成功
            if (isset($jsonArray['errcode'])) {
                throw new Exception($jsonArray['errmsg'], 400);
            }
            if (!$request['mobile']) {
                throw new Exception('手机号不能为空', 400);
            }
            if (!$request['v_code'] && $request['type'] == 'app') {
                throw new Exception('验证码不能为空', 400);
            }
            if ($request['type'] == 'app') {
                //判断验证码
                Captcha::build()->captchaCheck(['mobile' => $request['mobile'], 'code' => $request['v_code']]);
            }
            //判断手机号
            $user = User::build()->where('mobile', $request['mobile'])->where('is_deleted', 1)->find();
            if ($user) {
                //更新unionid
                $user['unionid'] = $request['unionid'];
                $user->save();
            } else {
                //新增用户
                $number = User::build()->createUserID();
                $user = [
                    'uuid' => uuid(),
                    'unionid' => $request['unionid'],
                    'mobile' => $request['mobile'],
                    'user_id' => $number[1],
                    'serial_number' => $number[0],
                    'last_login_time' => date("Y-m-d H:i:s", time()),
                    'create_time' => date("Y-m-d H:i:s", time()),
                    'update_time' => date("Y-m-d H:i:s", time()),
                ];
                User::build()->insert($user);
                //绑定分享关系
                if ($request['user_uuid']) {
                    UserRelation::build()->to_relation($request['user_uuid'], $user['uuid']);
                }
            }

            $user = User::build()->where(['unionid' => $request['unionid']])->where('is_deleted', 1)->find();
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
            $user->contestant_uuid = Contestant::build()->where('user_uuid', $user->uuid)->where('state', 'in', '2,4')->value('uuid');
            return ['token' => $userToken['token'], 'user' => $user];


            //$jsonArray['mobile'] = $request['mobile'];
            //$jsonArray['user_uuid'] = $request['user_uuid'];
            $result = self::user_login($jsonArray);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    // 用户端登录逻辑
    static function user_login($jsonArray)
    {
        try {
            Db::startTrans();
            // 根据mobile查询用户
            $user = User::build()->where(['unionid' => $jsonArray['unionid'], 'is_deleted' => 1])->find();
            // 已注册，更新用户的登录会话key
            if ($user) {
                if ($user['disabled'] == 2) {
                    throw new Exception('您已被禁用，无法登陆');
                }
                //$user['openid'] = isset($jsonArray['openid'])?$jsonArray['openid']:'';
                //$user['unionid'] = isset($jsonArray['unionid'])?$jsonArray['unionid']:'';
                //$user['session_key'] = isset($jsonArray['session_key'])?$jsonArray['session_key']:'';
                $user['update_time'] = date("Y-m-d H:i:s", time());
                $user['last_login_time'] = date("Y-m-d H:i:s", time());
                $user->save();
            } // 未注册，则新增用户
            else {
                return ['msg' => '请绑定手机号', 'unionid' => $jsonArray['unionid']];
                /**
                 * $number = User::build()->createUserID();
                 * $user = [
                 * 'uuid' => uuid(),
                 * 'openid' => $jsonArray['openid'],
                 * 'unionid' => isset($jsonArray['unionid'])?$jsonArray['unionid']:'',
                 * 'mobile' => $jsonArray['mobile'],
                 * 'session_key' => isset($jsonArray['session_key'])?$jsonArray['session_key']:'',
                 * 'user_id' => $number[1],
                 * 'serial_number' => $number[0],
                 * 'create_time' => date("Y-m-d H:i:s", time()),
                 * 'update_time' => date("Y-m-d H:i:s", time()),
                 * // 'nickname' => 'wx' . getNumberOne(5),
                 * // 'name' => 'wx' . getNumberOne(5),
                 * // 'avatar' => 'sw_alcohol/d128c6b68b47ddcd4a69923e9d0a7561.png',
                 * ];
                 * User::build()->insert($user);
                 * //绑定分享关系
                 * if($jsonArray['user_uuid']){
                 * UserRelation::build()->to_relation($jsonArray['user_uuid'],$user['uuid']);
                 * }
                 * $user = User::build()->where(['openid' => $jsonArray['openid']])->find();
                 * **/
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
            Db::commit();
            $user->contestant_uuid = Contestant::build()->where('user_uuid', $user->uuid)->where('state', 'in', '2,4')->value('uuid');
            return ['token' => $userToken['token'], 'user' => $user];
        } catch (Exception $e) {
            Db::rollback();
            return ['msg' => $e->getMessage()];
        }
    }
}
