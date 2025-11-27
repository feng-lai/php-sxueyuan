<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\PayLogic;

/**
 * 订单-控制器
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class Pay extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->miniValidateToken();
  }

  public function save()
  {
    $request = $this->selectParam([
      'order_sn', // 订单号
      'pay_type',
      'return_url',
      'receipt_data'
    ]);
    $this->check($request, "Pay.save");
    switch ($request['pay_type']){
      case 1://余额支付
        $result = PayLogic::wallet($request, $this->userInfo);
      break;
      case 2://微信支付
        $result = PayLogic::wx($request);
        break;
      case 3://支付宝支付
        $result = PayLogic::zfb($request);
        break;
      case 4://通联支付
        $result = PayLogic::tl($request);
        break;
      case 5://支付宝h5支付
        $result = PayLogic::zfb_h5($request);
        break;
      case 6://ios支付
        $result = PayLogic::ios_pay($request);
        break;
    }
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      return json_encode(['result'=>$result]);
      $this->render(200, ['result' => $result]);
    }
  }

  public function update($id){
     $request = $this->selectParam([]);
     $request['uuid'] = $id;
     $result = OrderLogic::miniEdit($request,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
  }

  // public function delete($id){
  //   $result = UserLogic::miniDelete($id,$this->userInfo);
  //   if (isset($result['msg'])) {
  //     $this->returnmsg(400, [], [], '', '', $result['msg']);
  //   } else {
  //     $this->render(200, ['result' => $result]);
  //   }
  // }
}
