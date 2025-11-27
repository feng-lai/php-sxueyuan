<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\CourseShare;
use app\api\model\User;
use app\api\model\UserCourseChapter;
use app\api\model\UserScore;
use think\Exception;
use think\Db;

/**
 * 分享课程-逻辑
 */
class CourseShareLogic
{
    static public function miniAdd($request, $userInfo)
    {
        try {

            $data = UserCourseChapter::build()
                ->where('user_uuid', $userInfo['uuid'])
                ->where('course_uuid', $request['course_uuid'])
                ->where('is_deleted', 1)
                ->where('end_time', '>', date('Y-m-d H:i:s'))
                ->find();
            if(!$data){
                return ['msg'=>'课程已过期/课程还没购买'];
            }
            if ($data && !CourseShare::build()->where(['user_uuid' => $userInfo['uuid'], 'course_uuid' => $request['course_uuid'],'is_deleted'=>1])->count()) {
                Db::startTrans();
                CourseShare::build()->insert([
                    'user_uuid' => $userInfo['uuid'],
                    'uuid' => uuid(),
                    'course_uuid' => $request['course_uuid'],
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
                $score = Config::build()->where('key', 'SHARE_COURSE')->value('value') * User::build()->get_double($userInfo);
                User::build()->where('uuid', $userInfo['uuid'])->setInc('score', $score);
                UserScore::build()->insert([
                    'user_uuid' => $userInfo['uuid'],
                    'uuid' => uuid(),
                    'score' => $score,
                    'content' => '分享已购课程',
                    'left_score'=>User::build()->where('uuid', $userInfo['uuid'])->value('score'),
                    'course_uuid' => $request['course_uuid'],
                    'create_time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s'),
                ]);
                
                Db::commit();
            }
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


}
