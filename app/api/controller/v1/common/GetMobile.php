<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\common\GetMobileLogic;

/**
 * 获取手机号-控制器
 * User:
 * Date: 2022-02-15
 * Time: 10:36
 */
class GetMobile extends Api
{
  public $restMethodList = 'get|post|put|delete';

  public function save()
  {
    $request = $this->selectParam([
      'access_token' => 'user', // App端SDK获取的登录Token
      'out_id', // 外部流水号
      'user_uuid'
    ]);
    $this->check($request, "GetMobile.save");
    $result = GetMobileLogic::commonAdd($request);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }
}
