<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Course;
use app\api\model\SendCourse;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 课程下发逻辑
 */
class SendCourseLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = SendCourse::build()
            ->where('is_deleted', 1)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $item['num'] = count($item['chapter_uuid']);
                $item['course_name'] = Course::build()->where('uuid', $item['course_uuid'])->value('name');
                if($item['type'] == 1){
                    $item['user'] = implode('、',User::build()->whereIn('uuid', $item['user_uuid'])->column('name'));
                }
                if($item['type'] == 2){
                    $item['user'] = '全体用户';
                }
                if($item['type'] == 3){
                    $item['user'] = implode('、',User::build()->whereIn('business_uuid', $item['business_uuid'])->column('name'));
                }
            });
        AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程下发管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = SendCourse::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程下发管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = [
                'uuid' => uuid(),
                'course_uuid' => $request['course_uuid'],
                'chapter_uuid' => $request['chapter_uuid'],
                'type' => $request['type'],
                'user_uuid' => $request['user_uuid'],
                'business_uuid'=>$request['business_uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            SendCourse::build()->save($data);
            //下发到用户课程
            SendCourse::build()->to_user_course_chapter($data);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程下发管理','',SendCourse::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function cmsDelete($id, $userInfo)
    {
        try {
            Db::startTrans();
            $data = SendCourse::build()->where('uuid',$id)->findOrFail();
            $data->save(['status' => 2]);
            SendCourse::build()->back_user_course_chapter($data);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程下发管理');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

}
