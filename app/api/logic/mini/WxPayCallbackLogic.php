<?php

namespace app\api\logic\mini;

use app\api\model\Captcha;
use app\api\model\Interest;
use app\api\model\InterestBirthday;
use app\api\model\Level;
use app\api\model\Message;
use app\api\model\Order;
use app\api\model\Contestant;
use app\api\model\Agree;
use app\api\model\UserInterrest;
use app\api\model\UserToken;
use think\Exception;
use think\Db;

/**
 * 微信支付回调-逻辑
 * User:
 * Date:
 * Time:
 */
class WxPayCallbackLogic
{
  static public function miniAdd($request)
  {
    try {
      $request = xml2array($request);
      if ($request['result_code'] == 'SUCCESS' && $request['return_code'] == 'SUCCESS') {
        $order_sn = $request['out_trade_no'];
        $order = Order::build();
        $data = $order->where('order_sn', $order_sn)->find();
        if ($data['status'] == 1) {
          return "SUCCESS";
        }
        $save_data = ['status' => 1, 'trade_no' => $request['transaction_id'], 'pay_time' => date('Y-m-d H:i:s'), 'pay_type'=>3];
        if ($order->where('order_sn', $order_sn)->update($save_data) !== false) {
          $order->pay_success($data);
          return "SUCCESS";
        }
      }
    } catch (Exception $e) {
      return "FAIL";
    }
  }
}
