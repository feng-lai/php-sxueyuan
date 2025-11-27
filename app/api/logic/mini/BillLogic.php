<?php

namespace app\api\logic\mini;

use app\api\model\Bill;
use think\Exception;
use think\Db;

/**
 * 账单-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class BillLogic
{
  static public function cmsList($request,$userinfo)
  {
    $result = Bill::build()
      ->alias('b')
      ->field('b.uuid,b.price,b.coins,b.type,b.create_time,o.pay_type,c.type as cash_out_type')
      ->join('order o','o.order_sn = b.order_sn','LEFT')
      ->join('cash_out c','c.uuid = b.cash_out_uuid','LEFT')
      ->where('b.user_uuid',$userinfo['uuid'])
      ->order('b.create_time desc')
      ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
    return $result;
  }

  static public function miniAdd($request, $userInfo)
  {
    try {
      Contestant::build()->where('uuid',$request['contestant_uuid'])->findOrFail();
      if($request['type'] == 1 && !Attention::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->count()){
        $agree = Attention::build();
        $agree->uuid = uuid();
        $agree->contestant_uuid = $request['contestant_uuid'];
        $agree->user_uuid = $userInfo['uuid'];
        $agree->create_time = date("Y-m-d H:i:s", time());
        $agree->update_time = date("Y-m-d H:i:s", time());
        $agree->save();
      }
      if($request['type'] == -1){
        Agree::build()->where(['contestant_uuid'=>$request['contestant_uuid'],'user_uuid'=>$userInfo['uuid']])->delete();
      }
      return true;

    } catch (Exception $e) {
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
