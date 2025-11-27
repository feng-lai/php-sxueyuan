<?php
namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Chapter;
use app\api\model\Course;
use app\api\model\CourseOrder;
use app\api\model\CourseOrderDetail;
use app\api\model\CourseOrderEvaluate;
use app\api\model\Member;
use app\api\model\Message;
use app\api\model\OrderDetail;
use think\Exception;
use think\Db;

/**
 *课程订单评论逻辑
 */
class CourseOrderEvaluateLogic
{
    static public function menu()
    {
        return ['订单管理','课程订单评论'];
    }
    static public function cmsList($request, $userInfo)
    {
        $where = ['co.is_deleted' => 1];
        if($request['course_uuid']){
            $where['co.course_uuid'] = $request['course_uuid'];
        }
        $result = CourseOrderEvaluate::build()
            ->alias('co')
            ->field('
                co.uuid,
                co.course_uuid,
                co.create_time,
                co.content,
                co.star,
                u.name,
                u.phone,
                status,
                cr.order_id
            ')
            ->join('user u', 'u.uuid = co.user_uuid', 'left')
            ->join('course_order cr','cr.uuid = co.course_order_uuid', 'left')
            ->where($where)
            ->order('co.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = CourseOrderEvaluate::build()->field('uuid,content,star')->where('uuid',$id)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = CourseOrderEvaluate::build()->where('uuid', $request['uuid'])->findOrFail();
            if($data->status != 1){
                return ['msg'=>'非待审核状态'];
            }
            $data->save($request);
            $course = Course::build()->where('uuid',$data['course_uuid'])->value('name');
            $content = '您好，您关于《'.$course.'》的评论已通过审核';
            $title = '您好，您的评论已通过审核';
            if($request['status'] == 3){
                $content = '您好，您关于《'.$course.'》的评论已被拒绝 拒绝原因:'.$request['reason'];
                $title = '您好，您的评论不通过审核';
            }
            $res = [
                'uuid' => uuid(),
                'user_uuid'=>$data['user_uuid'],
                'content'=>$content,
                'type'=>1,
                'title'=>$title,
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ];
            if($request['status'] == 2){
                $res['url_type'] = 4;
                $res['course_order_uuid'] = $data->course_order_uuid;
            }
            if($request['status'] == 3){
                $res['url_type'] = 6;
                $res['course_uuid'] = $data->course_uuid;
            }
            //通知
            Message::build()->insert($res);
            AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
            Db::commit();
            return true;
        }catch (Exception $e){
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


}
