<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Message;
use app\api\model\MsgPush;
use think\Db;

class MsgPushLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            MsgPush::build()
                ->where('status',1)
                ->where('push_time','<=',now_time(time()))
                ->where('is_deleted',1)
                ->select()
                ->each(function($item){
                    $item->save(['status'=>2]);
                    $arr = [];
                    foreach ($item['user_uuid'] as $v){
                        $data = [
                            'uuid'=>uuid(),
                            'type'=>1,
                            'title'=>$item['title'],
                            'content'=>$item['content'],
                            'user_uuid'=>$v,
                            'msg_push_uuid'=>$item['uuid'],
                            'create_time'=>now_time(time()),
                            'update_time'=>now_time(time()),
                        ];
                        if($item['type'] == 2){
                            $data['course_uuid'] = $item['course_uuid'];
                            $data['url_type'] = 1;
                        }
                        if($item['type'] == 3){
                            $data['train_uuid'] = $item['train_uuid'];
                            $data['url_type'] = 2;
                        }
                        $arr[] = $data;
                    }
                    Message::build()->insertAll($arr);
                });
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            throw new \Exception($e->getMessage());
        }

    }
}
