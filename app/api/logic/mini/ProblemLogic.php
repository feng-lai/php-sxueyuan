<?php

namespace app\api\logic\mini;

use app\api\model\Problem;
use think\Exception;
use think\Db;

/**
 * 配置-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class ProblemLogic
{
  static public function cmsList($request)
  {
      $where['is_deleted'] = 1;
      if($request['keyword']){
          $where['problem|answer'] = ['like', '%'.$request['keyword'].'%'];
      }
    $result = Problem::build();
    return $result->where($where)->order('weight desc')->select();
  }

  static public function cmsDetail($id)
  {
    return  Config::build()
      ->where('key', $id)
      ->field('*')
      ->find();
  }

   static public function cmsAdd($request){
     try {
       $data = [
         'uuid' => uuid(),
         'content'=>$request['content'],
         'create_time' => now_time(time()),
         'update_time' => now_time(time()),
       ];
       Config::build()->insert($data);
       return $data['uuid'];
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }

  static public function cmsEdit($request)
  {
    try {
      $user = Config::build()->where('key', $request['key'])->find();
      $user->save(['value'=>$request['value']]);
      return true;
    } catch (Exception $e) {
      throw new Exception($e->getMessage(), 500);
    }
  }

   static public function cmsDelete($id){
     try {
       $user = Config::build()->where('uuid', $id)->find();
       $user->save(['delete'=>1]);
       return true;
     } catch (Exception $e) {
         throw new Exception($e->getMessage(), 500);
     }
   }
}
