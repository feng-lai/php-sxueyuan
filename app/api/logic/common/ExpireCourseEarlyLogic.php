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

class ExpireCourseEarlyLogic
{

    public static function sync()
    {
        try {
            Db::startTrans();
            $data = UserCourseChapter::build()
                ->field([
                    'c.name as course_name',
                    'ch.name as chapter_name',
                    'c.uuid as course_uuid',
                    'uc.chapter_uuid',
                    'uc.user_uuid',
                    'uc.end_time',
                    'uc.persent'
                ])
                ->alias('uc')
                ->join('course c', 'c.uuid = uc.course_uuid', 'left')
                ->join('chapter ch', 'ch.uuid = uc.chapter_uuid', 'left')
                ->where('uc.end_time', '<', now_time(time()+30*24*60*60))
                ->where('uc.end_time', '>', now_time(time()+29*24*60*60))
                ->where('uc.is_deleted', 1)
                ->where('uc.persent','<>',100)
                ->select()
                ->each(function ($item) {
                    if($item['persent'] == 0){
                        $title = '您所购买的'.$item['course_name'].'中'.$item['chapter_name'].'还未开始学习，请及时学习';
                        $content = '您所购买的'.$item['course_name'].' '.$item['chapter_name'].' 将在30天后失效，请及时学习 还未开始学习，请及时学习';
                    }else{
                        $title = '您所购买的'.$item['course_name'].'中'.$item['chapter_name'].'将在30天后失效，请及时学习';
                        $content = '您所购买的'.$item['course_name'].' '.$item['chapter_name'].' 将在30天后失效，请及时学习';
                    }
                    Message::build()->insert([
                        'uuid' => uuid(),
                        'user_uuid' => $item['user_uuid'],
                        'type' => 4,
                        'url_type' => 8,
                        'title' => $title,
                        'content' => $content,
                        'course_uuid' => $item['course_uuid'],
                        'chapter_uuid' => $item['chapter_uuid'],
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ]);

                });
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
