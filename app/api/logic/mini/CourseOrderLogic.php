<?php

namespace app\api\logic\mini;

use app\api\model\Cart;
use app\api\model\Chapter;
use app\api\model\Course;
use app\api\model\CourseOrder;
use app\api\model\CourseOrderDetail;
use app\api\model\CourseOrderEvaluate;
use app\api\model\Message;
use app\api\model\User;
use app\api\model\UserScore;
use app\api\model\UserCourseChapter;
use think\Exception;
use think\Db;

/**
 * 课程订单-逻辑
 */
class CourseOrderLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where['is_deleted'] = 1;
        $where['user_uuid'] = $userInfo['uuid'];
        if($request['course_uuid']){
            $where['course_uuid'] = $request['course_uuid'];
        }
        $result = CourseOrder::build()
            ->field('uuid,course_uuid,order_id,score,create_time')
            ->where($where)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function($item){
                $item->chapter = CourseOrderDetail::build()
                    ->alias('c')
                    ->join('chapter ch','ch.uuid = c.chapter_uuid')
                    ->field('c.uuid,ch.name,c.chapter_uuid,ch.file')
                    ->where('c.order_id',$item['order_id'])
                    ->select()->each(function($item){
                        $item->file = json_decode($item['file'],true);
                    });
                $item->evaluate = CourseOrderEvaluate::build()->where('course_order_uuid',$item['uuid'])->find();
            });
        return $result;
    }

    static public function cmsDetail($id)
    {
        return Member::build()
            ->where('uuid', $id)
            ->field('*')
            ->find();
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $chapter_uuid = implode(',', $request['chapter_uuid']);
            //章节信息
            if($request['type'] == 2){
                $data = CartLogic::list(['chapter_uuid' => $chapter_uuid], $userInfo);
            }else{
                $data = CourseOrder::build()->getCourse($request['chapter_uuid'],$userInfo);
            }
            $all_score = 0;
            foreach ($data as $k => $v) {
                if($v->is_deleted ==2){
                    return ['msg'=>'课程已被删除'];
                }
                if ($v->vis == 2) {
                    return ['msg' => '课程已下架'];
                }
                //计算积分
                $score = 0;
                $order_id = getOrderNumber();
                if (Chapter::build()->where('course_uuid', $v['course_uuid'])->where('is_deleted', 1)->count() == count($v['chapter'])) {
                    $score = $v['member_score'];
                } else {
                    foreach ($v['chapter'] as $kk => $vv) {
                        $score += $vv['member_score'];
                    }
                }
                $all_score += $score;
                $course_order_uuid = uuid();
                CourseOrder::build()->insert([
                    'uuid' => $course_order_uuid,
                    'user_uuid' => $userInfo['uuid'],
                    'order_id' => $order_id,
                    'course_uuid' => $v->course_uuid,
                    'score' => $v['score'],
                    'score_cost' => $score,
                    'course_data'=>json_encode(Course::build()->where('uuid', $v['course_uuid'])->find(), JSON_UNESCAPED_UNICODE),
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ]);
                foreach ($v['chapter'] as $kk => $vv) {
                    //订单详情
                    CourseOrderDetail::build()->insert([
                        'uuid' => uuid(),
                        'user_uuid' => $userInfo['uuid'],
                        'course_order_uuid' => $course_order_uuid,
                        'course_uuid' => $v['course_uuid'],
                        'chapter_uuid' => $vv['uuid'],
                        'score' => $vv['score'],
                        'order_id' => $order_id,
                        'chapter_data'=>json_encode(Chapter::build()->where('uuid', $vv['uuid'])->find(), JSON_UNESCAPED_UNICODE),
                        'score_cost' => $vv['member_score'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ]);
                    $user_course_chapter = UserCourseChapter::build()->where([
                        'user_uuid' => $userInfo['uuid'],
                        'chapter_uuid' => $vv['uuid'],
                        'course_uuid' => $v['course_uuid'],
                        'is_deleted' => 1
                    ])->find();
                    if ($user_course_chapter && strtotime($user_course_chapter['end_time']) > time()) {
                        return ['msg' => '章节' . $vv['name'] . '已购买,请重新下单'];
                    } else {
                        //加入到我的课程
                        UserCourseChapter::build()->insert([
                            'uuid' => uuid(),
                            'user_uuid' => $userInfo['uuid'],
                            'course_uuid' => $v['course_uuid'],
                            'chapter_uuid' => $vv['uuid'],
                            'order_id' => $order_id,
                            'end_time' => date('Y-m-d H:i:s', strtotime('+1 year')),
                            'create_time' => now_time(time()),
                            'update_time' => now_time(time()),
                        ]);
                    }
                }
                //用户积分扣除/积分明细
                User::build()->change_score(-$score, '购买章节', $userInfo['uuid'], ['course_order_uuid',$course_order_uuid]);

                //通知
                Message::build()->insert([
                    'uuid' => uuid(),
                    'user_uuid' => $userInfo['uuid'],
                    'type'=>4,
                    'title' => '您成功购买《'.Course::build()->where('uuid',$v['course_uuid'])->value('name').'》'.count($v['chapter']).'个章节课程，快去学习吧',
                    'content' => '您成功购买《'.Course::build()->where('uuid',$v['course_uuid'])->value('name').'》'.count($v['chapter']).'个章节课程，快去学习吧',
                    'url_type'=>8,
                    'course_uuid'=>$v['course_uuid'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ]);
            }
            if ($all_score > $userInfo['score']) {
                return ['msg' => '用户积分余额不足'];
            }
            if ($request['type'] == 2) {
                //清购物车
                Cart::build()->where('user_uuid', $userInfo['uuid'])->whereIn('chapter_uuid', $request['chapter_uuid'])->delete();
            }
            //新用户变旧用户
            User::build()->where('uuid', $userInfo['uuid'])->update(['is_new'=>2]);
            Db::commit();
            return $course_order_uuid;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


}
