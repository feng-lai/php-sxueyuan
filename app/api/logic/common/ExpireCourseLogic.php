<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Message;
use app\api\model\UserCourseChapter;
use think\Db;
use think\Exception;

class ExpireCourseLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $data = UserCourseChapter::build()
                ->field([
                    'm.uuid as msg_uuid',
                    'c.name as course_name',
                    'ch.name as chapter_name',
                    'c.uuid as course_uuid',
                    'uc.chapter_uuid',
                    'uc.user_uuid',
                    'uc.end_time',
                ])
                ->alias('uc')
                ->join('message m', 'm.course_uuid = uc.course_uuid and m.chapter_uuid = uc.chapter_uuid and m.end_time = uc.end_time', 'left')
                ->join('course c', 'c.uuid = uc.course_uuid', 'left')
                ->join('chapter ch', 'ch.uuid = uc.chapter_uuid', 'left')
                ->where('uc.end_time', '<', now_time(time()))
                ->where('uc.is_deleted', 1)
                ->select()
                ->each(function ($item, $key) {
                    if (!$item['msg_uuid']) {
                        Message::build()->insert([
                            'uuid' => uuid(),
                            'user_uuid' => $item['user_uuid'],
                            'type' => 4,
                            'url_type' => 8,
                            'title' => '您所购买的《'.$item['course_name'].'》中《'.$item['chapter_name'].'》已失效',
                            'content' => '您所购买的《'.$item['course_name'].'》 《'.$item['chapter_name'].'》 已失效',
                            'course_uuid' => $item['course_uuid'],
                            'end_time'=>$item['end_time'],
                            'chapter_uuid' => $item['chapter_uuid'],
                            'create_time' => now_time(time()),
                            'update_time' => now_time(time()),
                        ]);
                    }
                });
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
