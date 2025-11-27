<?php
namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Chapter;
use app\api\model\CourseOrder;
use app\api\model\CourseOrderDetail;
use app\api\model\Member;
use app\api\model\OrderDetail;
use think\Exception;
use think\Db;

/**
 *课程订单逻辑
 */
class CourseOrderLogic
{
    static public function menu()
    {
        return ['订单管理','课程订单'];
    }
    static public function cmsList($request, $userInfo)
    {
        $where = ['co.is_deleted' => 1];
        if ($request['user_name']) {
            $where['u.name'] = ['like', '%' . $request['user_name'] . '%'];
        }
        if ($request['course_name']) {
            $where['c.name'] = ['like', '%' . $request['course_name'] . '%'];
        }
        if ($request['order_id']) {
            $where['co.order_id'] = ['like', '%' . $request['order_id'] . '%'];
        }
        if($request['user_uuid']){
            $where['co.user_uuid'] = $request['user_uuid'];
        }
        $result = CourseOrder::build()
            ->alias('co')
            ->field('
                co.uuid,
                co.course_uuid,
                co.order_id,
                c.name as course_name,
                u.name as user_name,
                co.create_time,
                co.score,
                co.score_cost
            ')
            ->join('course c', 'c.uuid = co.course_uuid', 'left')
            ->join('user u', 'u.uuid = co.user_uuid', 'left')
            ->where($where)
            ->order('co.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item['chapter_num'] = CourseOrderDetail::build()->where('order_id',$item['order_id'])->count();
                $num = Chapter::build()->where('course_uuid',$item['course_uuid'])->count();
                if($num == $item['chapter_num']){
                    $item['is_all'] = 1;
                }else{
                    $item['is_all'] = 2;
                }
                $item['chapter'] = CourseOrderDetail::build()
                    ->alias('cod')
                    ->field('cod.score,cod.score_cost,c.name,cc.persent')
                    ->join('chapter c','c.uuid = cod.chapter_uuid','left')
                    ->join('user_course_chapter cc','cc.chapter_uuid = cod.chapter_uuid and cc.user_uuid = cod.user_uuid','left')
                    ->where('cod.order_id',$item['order_id'])
                    ->where('cod.is_deleted',1)
                    ->select();
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = CourseOrder::build()
            ->alias('co')
            ->field('
                co.uuid,
                co.order_id,
                c.name as course_name,
                u.name as user_name,
                co.create_time,
                co.score,
                u.member_uuid,
                u.member_time
            ')
            ->join('course c', 'c.uuid = co.course_uuid', 'left')
            ->join('user u', 'u.uuid = co.user_uuid', 'left')
            ->where('co.uuid', $id)
            ->where('co.is_deleted', 1)
            ->findOrFail();
        if(strtotime($data->member_time) > time()){
            $data['member_name'] = Member::build()->where('uuid',$data['member_uuid'])->value('name');
        }else{
            $data['member_name'] = Member::build()->where('level',1)->value('name');
        }
        $data['detail'] = CourseOrderDetail::build()
            ->alias('co')
            ->field('c.name,co.score,co.score_cost')
            ->join('chapter c', 'c.uuid = co.chapter_uuid', 'left')
            ->where('order_id',$data->order_id)
            ->select();
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }


}
