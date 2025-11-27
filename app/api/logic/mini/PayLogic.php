<?php

namespace app\api\logic\mini;

use app\api\model\Order;
use app\api\model\Bill;
use app\api\model\RechangeSet;
use app\api\model\ContestantGift;
use app\api\model\GiftSet;
use app\api\model\User;
use think\Exception;
use think\Db;
use Alipay\aop\AopClient;
use Alipay\aop\request\AlipayTradeAppPayRequest;
use Alipay\aop\request\AlipayTradeWapPayRequest;
use app\common\tools\wechatPay;
use app\common\tools\IosPay;

/**
 * 支付-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class PayLogic
{
  /**
   * Author: Administrator
   * Date: 2023/8/15 0015
   * Time: 16:06
   * 余额支付
   */
  static public function wallet($request, $userInfo)
  {
    try {
      Db::startTrans();
      $order = Order::build()->where('order_sn',$request['order_sn'])->find();
      if($order->status == 1){
        return ['msg'=>'订单已支付'];
      }
      if(!$order){
        return ['msg'=>'订单不存在'];
      }
      //判断余额
      if($userInfo['wallet'] < $order->price){
        throw new Exception('余额不足', 500);
      }
      //判断订单状态
      if($order->pay_type != 0){
        throw new Exception('订单必须是待支付状态', 500);
      }
      //已支付
      $order->pay_type = 1;
      $order->status = 1;
      $order->pay_time = date('Y-m-d H:i:s');
      $order->save();
      //支付成功后处理用户账户/账单
      Order::build()->pay_success($order);
      Db::commit();
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

  /**
   * Author: Administrator
   * Date: 2023/8/15 0015
   * Time: 13:58
   * 支付宝 app支付
   */
  static public function zfb($request){
    $order = Order::build()->where('order_sn',$request['order_sn'])->find();
    if($order->status == 1){
      return ['msg'=>'订单已支付'];
    }
    if(!$order){
      return ['msg'=>'订单不存在'];
    }
    $notify_url = request()->domain() . '/v1/mini/AliPayCallback';
    $config = config('alipay');
    $aop = new AopClient;
    //$aop->gatewayUrl = "https://openapi.alipaydev.com/gateway.do";     //网关地址要使用沙箱网关alipaydev
    $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
    $aop->appId = $config['appId'];
    $aop->rsaPrivateKey = $config['rsaPrivateKey'];
    $aop->format = "json";
    $aop->postCharset = "utf-8";
    $aop->signType = "RSA2";
    $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
    //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
    $request = new AlipayTradeAppPayRequest();
    //SDK已经封装掉了公共参数，这里只需要传入业务参数，沙箱环境的product_code只能是FAST_INSTANT_TRADE_PAY
    // $info = json_encode(['body'=>'灯饰商品支付宝','subject'=>'灯饰商品','out_trade_no'=>$order['uuid'],
    $info = json_encode(['body' => '购买星豆费用支付', 'subject' => 'product', 'out_trade_no' => $order->order_sn,'total_amount' => $order->price, 'product_code' => 'FAST_INSTANT_TRADE_PAY'], JSON_UNESCAPED_UNICODE);
    //     'timeout_express'=>'30m','total_amount'=>$order['total_price'],'product_code'=>'FAST_INSTANT_TRADE_PAY'],JSON_UNESCAPED_UNICODE);
    $request->setNotifyUrl($notify_url);
    $request->setBizContent($info);
    //这里和普通的接口调用不同，使用的是sdkExecute
    $response = $aop->sdkExecute($request);
    //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
    return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
  }

  /**
   * Author: Administrator
   * Date: 2023/8/15 0015
   * Time: 13:58
   * 支付宝 H5支付
   */
  static public function zfb_h5($request){
    $order = Order::build()->where('order_sn',$request['order_sn'])->find();
    if($order->status == 1){
      return ['msg'=>'订单已支付'];
    }
    if(!$order){
      return ['msg'=>'订单不存在'];
    }
    $notify_url = request()->domain() . '/v1/mini/AliPayCallback';
    $config = config('alipay.h5');
    $aop = new AopClient;
    $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
    $aop->appId = $config['appId'];
    $aop->apiVersion = '1.0';
    $aop->rsaPrivateKey = $config['rsaPrivateKey'];
    $aop->format = "json";
    $aop->postCharset = "utf-8";
    $aop->signType = "RSA2";
    $aop->alipayrsaPublicKey = $config['alipayrsaPublicKey'];
    $requests = new AlipayTradeWapPayRequest();
    $info = json_encode(['body' => '购买星豆费用支付', 'subject' => 'product', 'out_trade_no' => $order->order_sn,'total_amount' => $order->price, 'product_code' => 'QUICK_WAP_WAY'], JSON_UNESCAPED_UNICODE);
    if($request['return_url']){
      $requests->setReturnUrl($request['return_url']);
    }
    $requests->setNotifyUrl($notify_url);
    $requests->setBizContent($info);
    //这里和普通的接口调用不同，使用的是sdkExecute
    $response = $aop->pageExecute($requests);
    //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
    return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
  }

  /**
   * Author: Administrator
   * Date: 2023/8/15 0015
   * Time: 16:07
   * 微信 app支付
   */
  static public function wx($request){
    $order = Order::build()->where('order_sn',$request['order_sn'])->find();
    if($order->status == 1){
      return ['msg'=>'订单已支付'];
    }
    if(!$order){
      return ['msg'=>'订单不存在'];
    }
    return wechatPay::store($order->price,$order->order_sn);
  }

  static public function ios_pay($request){
    if(!$request['receipt_data']){
      return ['msg'=>'ios支付参数不能为空'];
    }
    $order = Order::build()->where('order_sn',$request['order_sn'])->find();
    if($order->status == 1){
      return ['msg'=>'订单已支付'];
    }
    if(!$order){
      return ['msg'=>'订单不存在'];
    }
    return  IosPay::validate_apple_pay($request['receipt_data'],$request['order_sn']);
  }

}
