<?php

namespace app\api\logic\mini;

use app\api\model\CashOut;
use app\api\model\Config;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 提现-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class CashOutLogic
{


  static public function miniAdd($request, $userInfo)
  {
    try {
      Db::startTrans();
      //手续费
      $commission = round(Config::build()->where('key','CASH_OUT')->value('value')/100*$request['price'],2);
      //总金额
      $price = $request['price']+$commission;

      //判断余额
      if($price > $userInfo['wallet']){
        throw new Exception('余额不足', 500);
      }
      $cash_out = CashOut::build();
      $cash_out->uuid = uuid();
      $cash_out->user_uuid = $userInfo['uuid'];
      $cash_out->total = $price;
      $cash_out->cash_sn = numberCreate();
      $cash_out->price = $request['price'];
      $cash_out->commission = $commission;
      $cash_out->type = $request['type'];
      if($request['type'] == 2) $cash_out->bank = $request['bank'];
      $cash_out->save();
      //用户余额扣除
      User::build()->where('uuid',$userInfo['uuid'])->setDec('wallet',$request['price']);
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

  // static public function miniEdit($request, $userInfo)
  // {
  //   try {
  //     Db::startTrans();
  //     $user = User::build()->where('uuid', $request['uuid'])->find();
  //     $user['update_time'] = now_time(time());
  //     $user->save();
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //     Db::rollback();
  //     throw new Exception($e->getMessage(), 500);
  //   }
  // }

  // static public function miniDelete($id, $userInfo)
  // {
  //   try {
  //     Db::startTrans();
  //     User::build()->where('uuid', $id)->update(['is_deleted' => 2]);
  //     Db::commit();
  //     return true;
  //   } catch (Exception $e) {
  //     Db::rollback();
  //     throw new Exception($e->getMessage(), 500);
  //   }
  // }
}
