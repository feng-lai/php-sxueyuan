<?php

namespace app\api\logic\mini;

use app\api\model\CourseOrderDetail;
use think\Db;
use think\Exception;

/**
 * 我的课程章节订单-逻辑
 */
class CourseOrderDetailLogic
{
    static public function miniList($request, $userInfo)
    {
        try {
            $where['is_deleted'] = 1;
            $where['course_uuid'] = $request['course_uuid'];
            $where['user_uuid'] = $userInfo['uuid'];
            $result = CourseOrderDetail::build()
                ->alias('c')
                ->field('
                    c.uuid,
                    c.course_uuid,
                    c.chapter_uuid,
                    ch.name,
                    c.score
                ')
                ->join('chpater ch','ch.uuid = c.chapter_uuid','left')
                ->where($where)
                ->order('create_time DESC');
            $member = Member::build()->where('uuid', $userInfo['member_uuid'])->find();
            $result = $result->select()->each(function ($item) use ($member, $userInfo) {
                $item['member_score'] = $item['score'];
                if ($member->level > 1 && $userInfo['member_time'] > now_time(time())) {
                    $item['member_score'] = $item['score'] - $member->discount;
                }
                $user_chapter = UserCourseChapter::build()
                    ->where('chapter_uuid', $item['uuid'])
                    ->where('end_time', '>=', now_time(time()))
                    ->where('user_uuid', $userInfo['uuid'])
                    ->find();
                if ($user_chapter) {
                    $item['is_buy'] = 1;
                    $item['persent'] = $user_chapter->persent;
                    $item['end_time'] = $user_chapter->end_time;
                } else {
                    $item['is_buy'] = 2;
                    $item['persent'] = '';
                    $item['end_time'] = '';
                }
            });
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setPersent($request, $userInfo)
    {
        try {
            $where['is_deleted'] = 1;
            $where['end_time'] = ['>=', now_time(time())];
            $where['chapter_uuid'] = $request['chapter_uuid'];
            $where['user_uuid'] = $userInfo['uuid'];
            $data = UserCourseChapter::build()->where($where)->findOrFail();
            $data->save(['persent' => $request['persent']]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
