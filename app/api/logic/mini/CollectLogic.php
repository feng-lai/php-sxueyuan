<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Collect;
use app\api\model\Course;
use think\Exception;
use think\Db;

/**
 * 收藏-逻辑
 * User:
 * Date: 2022-07-21
 * Time: 14:31
 */
class CollectLogic
{
    static public function Add($request, $userInfo)
    {
        try {
            //重复收藏
            if (Collect::build()->where('user_uuid', $userInfo['uuid'])->where('course_uuid', $request['course_uuid'])->where('is_deleted', 1)->count()) {
                return ['msg' => '重复收藏'];
            }
            $order = Collect::build();
            $order->uuid = uuid();
            $order->user_uuid = $userInfo['uuid'];
            $order->course_uuid = $request['course_uuid'];
            $order->save();
            return $order->uuid;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function List($request,$userInfo)
    {
        try {
            $where = ['c.is_deleted' => 1,'c.user_uuid'=>$userInfo['uuid']];
            $result = Collect::build()
                ->field('c.uuid,o.name,o.desc,o.img,o.uuid as course_uuid,cc.name as course_cate_name,o.weight')
                ->alias('c')
                ->join('course o','c.course_uuid = o.uuid','LEFT')
                ->join('course_cate cc','cc.uuid = o.course_cate_uuid','LEFT')
                ->where($where)
                ->order('c.create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Delete($id, $userInfo)
    {
        try {
            $res = Collect::build()->where('course_uuid', $id)->where('user_uuid',$userInfo['uuid'])->where('is_deleted',1)->findOrFail();
            $res->save(['is_deleted'=>2]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
