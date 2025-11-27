<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Train;
use think\Db;

class UpdateTrainStatusLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            //更新为进行中
            Train::build()
                ->where('is_deleted',1)
                ->where('status',1)
                ->where('begin_time','<',now_time(time()))
                ->where('end_time','>',now_time(time()))
                ->select()->each(function($item){
                    $item->save(['status'=>2]);
                });
            //更新为已结束
            $data = Train::build()
                ->where('is_deleted',1)
                ->whereIn('status',[1,2])
                ->where('end_time','<',now_time(time()))
                ->select()
                ->each(function($item){
                    $item->save(['status'=>3]);
                    //订单
                });
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollback();
        }

    }
}
