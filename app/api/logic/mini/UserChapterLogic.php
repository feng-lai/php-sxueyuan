<?php

namespace app\api\logic\mini;

use app\api\model\Member;
use app\api\model\UserCourseChapter;
use app\api\model\Chapter;
use think\Db;
use think\Exception;

/**
 * 我的课程章节-逻辑
 */
class UserChapterLogic
{
    static public function miniList($request, $userInfo)
    {
        try {
            //$where['is_deleted'] = 1;
            $where['course_uuid'] = $request['course_uuid'];
            $result = Chapter::build()
                ->field('
                    uuid,
                    name,
                    file,
                    type,
                    score,
                    is_deleted
                ')
                ->where($where)
                ->order('sort asc');
            $member = Member::build()->where('uuid', $userInfo['member_uuid'])->find();
            $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) use ($member, $userInfo) {
                $item['member_score'] = $item['score'];
                if ($member->level > 1 && $userInfo['member_time'] > now_time(time())) {
                    $item['member_score'] = max($item['score'] - $member->discount,0);
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
            if($request['persent'] >= $data->persent){
                if($request['persent'] > 100){
                    $request['persent'] = 100;
                }
                $data->save(['persent' => $request['persent']]);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
