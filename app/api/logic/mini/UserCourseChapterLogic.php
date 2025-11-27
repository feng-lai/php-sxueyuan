<?php

namespace app\api\logic\mini;

use app\api\model\UserCourseChapter;
use think\Db;
use think\Exception;

/**
 * 我的课程-逻辑
 */
class UserCourseChapterLogic
{
    static public function miniList($request, $userInfo)
    {
        try {
            $where['u.user_uuid'] = $userInfo['uuid'];
            $where['u.is_deleted'] = 1;
            $where['u.end_time'] = ['>',now_time(time())];
            $where['c.vis'] = 1;
            $where['c.is_deleted'] = 1;
            $chapter_count = '(select count(1) as c from user_course_chapter where user_uuid = "' . $userInfo['uuid'] . '" and course_uuid = u.course_uuid and is_deleted = 1 and end_time > "'.now_time(time()).'")';
            $undone_count = '(select count(1) as c from user_course_chapter where user_uuid = "' . $userInfo['uuid'] . '" and course_uuid = u.course_uuid and is_deleted = 1 and persent = 0 and end_time > "'.now_time(time()).'")';
            $doing_count = '(select count(1) as c from user_course_chapter where user_uuid = "' . $userInfo['uuid'] . '" and course_uuid = u.course_uuid and is_deleted = 1 and persent > 0 and persent <100 and end_time > "'.now_time(time()).'")';
            $finish_count = '(select count(1) as c from user_course_chapter where user_uuid = "' . $userInfo['uuid'] . '" and course_uuid = u.course_uuid and is_deleted = 1 and persent = 100 and end_time > "'.now_time(time()).'")';
            $result = UserCourseChapter::build()
                ->alias('u')
                ->field('
                    u.uuid,
                    u.course_uuid,
                    c.name,
                    c.img,
                    ca.name as course_category_name,
                    '.$chapter_count.' as chapter_count,
                    '.$undone_count.' as undone_count,
                    '.$doing_count.' as doing_count,
                    '.$finish_count.' as finish_count
                ')
                ->join('course c', 'c.uuid = u.course_uuid', 'left')
                ->join('course_cate ca', 'ca.uuid = c.course_cate_uuid', 'left')
                ->where($where)
                ->group('u.course_uuid')
                ->order('u.create_time DESC');
            if ($request['status']) {
                switch ($request['status']) {
                    case 1:
                        $result = $result->where(Db::raw($chapter_count.' = '.$undone_count));
                        break;
                    case 2:
                        $result = $result->where(Db::raw($chapter_count.' <> '.$finish_count))->where(Db::raw($chapter_count.' <> '.$undone_count));
                        break;
                    case 3:
                        $result = $result->where(Db::raw($chapter_count.' = '.$finish_count));
                        break;
                    default:
                }
            }
            $result = $result->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                if($item['chapter_count'] == $item['undone_count']){
                    $item['status'] = 1;
                }

                if($item['chapter_count'] != $item['undone_count'] && $item['chapter_count'] != $item['finish_count']){
                    $item['status'] = 2;
                }

                if($item['chapter_count'] == $item['finish_count']){
                    $item['status'] = 3;
                }
            });
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
