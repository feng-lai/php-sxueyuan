<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Order;
use think\Exception;
use think\Db;

/**
 * 报名逻辑
 * User:
 * Date: 2022-08-11
 * Time: 21:24
 */
class OrderLogic
{
    static public function cmsList($request, $userInfo)
    {
        //$map['o.status'] = ['=', 1];
        $map['o.is_deleted'] = ['=', 1];
        $request['user_uuid'] ? $map['o.user_uuid'] = ['=', $request['user_uuid']] : '';
        $request['course_uuid'] ? $map['o.course_uuid'] = ['=', $request['course_uuid']] : '';
        $request['status'] ? $map['c.status'] = ['=', $request['status']] : '';
        $result = Order::build()
            ->field('
                o.uuid,
                o.course_uuid,
                c.name,
                c.img,
                c.status,
                o.status as order_status,
                o.create_time,
                a.name as admin_name,
                ca.name as cate_name,
                c.status,
                c.class_begin,
                o.user_uuid,
                u.avatar,
                u.name as user_name,
                u.number,
                u.major,
                co.name as college_name,
                u.grade,
                u.class,
                (select count(1) as num from sign where user_uuid = o.user_uuid and course_uuid = o.course_uuid and is_deleted = 1) as is_sign,
                u.mobile,
                s.create_time as sign_time,
                (select count(1) as num from `sign` where user_uuid = o.user_uuid and is_deleted = 1) as sign_num,
                (select count(1) as num from `order` where user_uuid = o.user_uuid and is_deleted = 1 and status = 1) as order_num
            ')
            ->alias('o')
            ->join('course c', 'c.uuid = o.course_uuid', 'LEFT')
            ->join('admin a', 'a.uuid = c.admin_uuid', 'LEFT')
            ->join('cate ca', 'ca.uuid = c.cate_uuid', 'LEFT')
            ->join('user u', 'u.uuid = o.user_uuid', 'LEFT')
            ->join('college co', 'co.uuid = u.college_uuid', 'LEFT')
            ->join('sign s', 's.user_uuid = o.user_uuid and s.course_uuid = o.course_uuid', 'LEFT')
            ->where($map);
        if(is_numeric($request['is_sign'])){
            if($request['is_sign'] == 1){
                $result = $result->whereNotNull('s.create_time');
            }else{
                $result = $result->whereNull('s.create_time');
            }
        }
        $result = $result->order('o.create_time desc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '报名管理', '查询列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data['income'] = Bill::build()->where('type', 'in', '1,2,5')->sum('price');
        $data['pay_out'] = Bill::build()->where('type', 'in', '3,4')->sum('price');
        AdminLog::build()->add($userInfo['uuid'], '平台流水管理', '查询统计');
        return $data;
    }



    static public function cmsDelete($request, $userInfo)
    {
        try {
            $data = Order::build()->where('uuid', $request['uuid'])->where('is_deleted',1)->where('status',1)->findOrFail();
            $data->save(['reason'=>$request['reason'],'status'=>2,'cancel_type'=>3]);
            AdminLog::build()->add($userInfo['uuid'], '报名管理', '取消报名',$request);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
