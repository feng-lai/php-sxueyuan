<?php

namespace app\api\logic\mini;

use app\api\model\Course;
use app\api\model\Message;
use app\api\model\Train;
use think\Exception;
use think\Db;

/**
 * 我的消息-逻辑
 */
class MessageLogic
{
    static public function List($request,$userInfo)
    {
        try {
            $where = ['user_uuid' => $userInfo['uuid'],'is_deleted'=>1];
            if($request['is_read']){
                $where['is_read'] = $request['is_read'];
            }
            $result = Message::build()->where($where)->order('create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
    static public function Edit($uuid,$userInfo)
    {
        try {
            $msg = Message::build()->where('user_uuid',$userInfo['uuid'])->where('uuid',$uuid)->findOrFail();
            $msg->save(['read'=>2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid,$userInfo){
        try {
            $msg = Message::build()
                ->field('m.*,t.address as train_address,t.begin_time as train_begin_time,t.end_time as train_end_time')
                ->alias('m')
                ->join('train t','t.uuid = m.train_uuid','left')
                ->where('m.user_uuid',$userInfo['uuid'])
                ->where('m.uuid',$uuid)
                ->findOrFail();
            $msg->save(['is_read'=>2]);
            $msg->course = Course::build()->where('uuid',$msg->course_uuid)->find();
            $msg->train = Train::build()->where('uuid',$msg->train_uuid)->find();
            return $msg;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
