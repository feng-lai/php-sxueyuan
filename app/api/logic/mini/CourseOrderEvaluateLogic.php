<?php

namespace app\api\logic\mini;

use app\api\model\Cart;
use app\api\model\Chapter;
use app\api\model\Config;
use app\api\model\Course;
use app\api\model\CourseOrder;
use app\api\model\CourseOrderDetail;
use app\api\model\CourseOrderEvaluate;
use app\api\model\CourseOrderEvaluateAgree;
use app\api\model\Member;
use app\api\model\Score;
use app\api\model\User;
use app\api\model\UserScore;
use app\api\model\UserCourseChapter;
use think\Exception;
use think\Db;

/**
 * 课程订单评价-逻辑
 */
class CourseOrderEvaluateLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where['is_deleted'] = 1;
        $where['status'] = 2;
        if($request['course_uuid']){
            $where['course_uuid'] = $request['course_uuid'];
        }
        $result = CourseOrderEvaluate::build()
            ->field('user_uuid,course_order_uuid,course_uuid,content,star,create_time')
            ->where($where)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function($item) use ($userInfo){
                $user = User::build()
                    ->field('uuid,name,img,phone,member_uuid,member_time')
                    ->where('uuid',$item['user_uuid'])
                    ->find();
                $user->member_name = Member::build()->where('level',1)->where('is_deleted',1)->value('name');
                if($user->member_time && $user->member_time > now_time(time())){
                    $user->member_name = Member::build()->where(['uuid' => $user['member_uuid']])->where('is_deleted',1)->value('name');
                }
                $item['user'] = $user;

                $item->chapter_order_num = CourseOrderDetail::build()->where('order_id',CourseOrder::build()->where('uuid',$item['course_order_uuid'])->value('order_id'))->count();
                $item->chapter_num = Chapter::build()->where('course_uuid',$item['course_uuid'])->count();
                $item->agree = CourseOrderEvaluateAgree::build()->where('course_order_uuid',$item['course_order_uuid'])->where('is_deleted',1)->count();
                $item->is_agree = 2;
                if($userInfo && CourseOrderEvaluateAgree::build()->where('course_order_uuid',$item['course_order_uuid'])->where('is_deleted',1)->where('user_uuid',$userInfo['uuid'])->count()){
                    $item->is_agree = 1;
                }

            });
        return $result;
    }

    static public function cmsDetail($id,$userInfo)
    {
        return CourseOrderEvaluate::build()
            ->where('course_order_uuid', $id)
            ->where('user_uuid',$userInfo['uuid'])
            ->find();
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = CourseOrder::build()->where(['uuid'=>$request['course_order_uuid'],'user_uuid'=>$userInfo['uuid']])->findOrFail();
            $evaluate = CourseOrderEvaluate::build()->where(['user_uuid'=>$userInfo['uuid'],'course_order_uuid'=>$request['course_order_uuid']])->find();

            if($evaluate && in_array($evaluate->status,[1,2])){
                return ['msg'=>'已评价'];
            }

            $order_detail = CourseOrderDetail::build()
                ->alias('c')
                ->join('user_course_chapter u','u.chapter_uuid = c.chapter_uuid and u.user_uuid = c.user_uuid and u.end_time >"'.now_time(time()).'"','left')
                ->where(['c.order_id'=>$data['order_id'],'c.user_uuid'=>$userInfo['uuid']])
                ->where('u.persent','<',100)
                ->count();
            if($order_detail){
                return ['msg'=>'完成课程学习才可评价课程'];
            }


            $res = [
                'user_uuid'=>$userInfo['uuid'],
                'course_order_uuid'=>$request['course_order_uuid'],
                'course_uuid'=>$data->course_uuid,
                'content'=>$request['content'],
                'star'=>$request['star'],
                'status'=>1,
                'reason'=>'',
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ];
            if(!$evaluate){
                $score = Config::build()->where('key','EAVLUATE')->value('value') * User::build()->get_double($userInfo);
                //用户积分
                User::build()->where('uuid', $userInfo['uuid'])->setInc('score', $score);
                //积分明细
                UserScore::build()->insert([
                    'uuid' => uuid(),
                    'user_uuid' => $userInfo['uuid'],
                    'score' => $score,
                    'content'=>'评价课程',
                    'left_score'=>User::build()->where('uuid', $userInfo['uuid'])->value('score'),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time())
                ]);
                $res['uuid'] = uuid();
                CourseOrderEvaluate::build()->save($res);
            }else{
                $evaluate->save($res);
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

}
