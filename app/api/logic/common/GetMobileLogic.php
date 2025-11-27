<?php

namespace app\api\logic\common;

use app\api\model\ActivitiesTurntable;
use app\api\model\Captcha;
use app\api\model\UserRelation;
use app\api\model\UserToken;
use app\api\model\Employee;
use app\api\model\EmployeeToken;
use app\api\model\Interest;
use app\api\model\User;
use app\api\model\UserInterrest;
use app\api\model\WechatLogin;
use app\api\model\Contestant;
use app\common\tools\GetMobile;
use think\Exception;
use think\Db;
use think\Config;

/**
 * 获取手机号码-逻辑
 * User: Yacon
 * Date: 2022-02-15
 * Time: 10:36
 */
class GetMobileLogic
{
  static public function commonAdd($request)
  {
    $path = ROOT_PATH.'extend/alibabacloud/dypnsapi-20170525/autoload.php';
    if (file_exists($path)) {
      require_once $path;
    }
    $mobile =  GetMobile::main($request);
    $request['mobile'] = $mobile;
    try {
      Db::startTrans();
      $user = User::where(['mobile' => $request['mobile'], 'is_deleted' => 1])->find();
      if ($user) {
        if ($user['disabled'] == 2) {
          throw new Exception('您已被禁用，无法登陆');
        }
        $user['update_time'] = date("Y-m-d H:i:s", time());
        $user['last_login_time'] = date("Y-m-d H:i:s", time());
        $user->save();
      }else{
        $number = User::build()->createUserID();
        $user = [
          'uuid' => uuid(),
          'mobile' => $request['mobile'],
          'user_id' => $number[1],
          'serial_number' => $number[0],
          'create_time' => date("Y-m-d H:i:s", time()),
          'update_time' => date("Y-m-d H:i:s", time()),
        ];
        User::build()->insert($user);
        //绑定分享关系
        if($request['user_uuid']){
          UserRelation::build()->to_relation($request['user_uuid'],$user['uuid']);
        }
        $user = User::build()->where(['mobile' => $request['mobile']])->find();
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
      $user->contestant_uuid = Contestant::build()->where('user_uuid',$user->uuid)->where('state','in','2,4')->value('uuid');
      return ['token' => $userToken['token'], 'user' => $user];
    } catch (Exception $e) {
      Db::rollback();
      return ['msg' => $e->getMessage()];
    }
  }

}
