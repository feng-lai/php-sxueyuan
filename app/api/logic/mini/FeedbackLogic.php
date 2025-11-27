<?php

namespace app\api\logic\mini;

use app\api\model\Feedback;
use think\Exception;
use think\Db;

/**
 * 意见反馈-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class FeedbackLogic
{
  static public function miniAdd($request)
  {
    try {
      Db::startTrans();
      $sign = Feedback::build();
      $sign->uuid = uuid();
      $sign->user_uuid = $request['user_uuid'];
      $sign->content = $request['content'];
      $sign->img = $request['img'];
      $sign->phone = $request['phone'];
      $sign->type = $request['type'];
      $sign->create_time = date("Y-m-d H:i:s", time());
      $sign->update_time = date("Y-m-d H:i:s", time());
      $sign->save();
      Db::commit();
      return true;
    } catch (Exception $e) {
      Db::rollback();
      throw new Exception($e->getMessage(), 500);
    }
  }

}
