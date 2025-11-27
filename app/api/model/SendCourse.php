<?php

namespace app\api\model;

/**
 * 课程下发-模型
 * User:
 * Date:
 * Time:
 */
class SendCourse extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getChapterUuidAttr($value)
    {
        return json_decode($value);
    }

    public function setChapterUuidAttr($value)
    {
        return json_encode($value);
    }


    public function getUserUuidAttr($value)
    {
        return json_decode($value);
    }

    public function setUserUuidAttr($value)
    {
        return json_encode($value);
    }

    public function getBusinessUuidAttr($value)
    {
        return json_decode($value);
    }

    public function setBusinessUuidAttr($value)
    {
        return json_encode($value);
    }

    public function logData($data)
    {
        $type = '个人用户';
        if ($data['type'] == 2) {
            $type = '全体用户';
        }
        if ($data['type'] == 3) {
            $type = '企业用户';
        }
        $user = User::build()->whereIn('uuid', $data['user_uuid'])->column('name');
        $business = Business::build()->whereIn('uuid', $data['business_uuid'])->column('name');
        return [
            '课程' => Course::build()->where('uuid', $data['course_uuid'])->value('name'),
            '章节' => implode(',', Chapter::build()->whereIn('uuid', $data['chapter_uuid'])->column('name')),
            '下发类型' => $type,
            '用户' => $user ? implode(',', $user) : '',
            '企业' => $business ? implode(',', $business) : '',
        ];
    }

    //下发到用户-课程
    public function to_user_course_chapter($data)
    {
        $res = [];
        //1个人用户 2全体用户 3企业用户
        switch ($data['type']) {
            case 1:
                $user = $data['user_uuid'];
                break;
            case 2:
                $user = User::build()->where('is_deleted',1)->column('uuid');
                break;
            case 3:
                $user = User::build()->whereIn('business_uuid', $data['business_uuid'])->column('uuid');
                break;
            default:
                $user = $data['user_uuid'];
        }
        $chapter_uuid = UserCourseChapter::build()->whereIn('user_uuid', $user)->where('is_deleted',1)->column('chapter_uuid');
        foreach ($user as $v) {
            foreach ($data['chapter_uuid'] as $val) {
                if(!in_array($val, $chapter_uuid)) {
                    $res =  [
                        'uuid' => uuid(),
                        'send_course_uuid' => $data['uuid'],
                        'user_uuid' => $v,
                        'course_uuid' => $data['course_uuid'],
                        'chapter_uuid' => $val,
                        'end_time' => date('Y-m-d H:i:s',strtotime('+1 year')),
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ];
                    UserCourseChapter::build()->insert($res);
                }else{
                    UserCourseChapter::build()->where('user_uuid', $v)->where('course_uuid', $data['course_uuid'])->where('chapter_uuid',$val)->update(['end_time' => date('Y-m-d H:i:s',strtotime('+1 year'))]);
                }
            }
            //通知
            Message::build()->insert([
                'uuid' => uuid(),
                'user_uuid'=>$v,
                'content'=>'已为您下发一个新课程，请及时查看 下发章节: '.implode(' ',Chapter::build()->whereIn('uuid',$data['chapter_uuid'])->column('name')),
                'type'=>1,
                'course_uuid'=>$data['course_uuid'],
                'url_type'=>3,
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
                'title'=>'您收到一个新课程，请及时查看'
            ]);
        }
        return $res;
    }

    //下发撤回

    public function back_user_course_chapter($data)
    {
        $res = [];
        //1个人用户 2全体用户 3企业用户
        switch ($data['type']) {
            case 1:
                $user = $data['user_uuid'];
                break;
            case 2:
                $user = User::build()->where('is_deleted',1)->column('uuid');
                break;
            case 3:
                $user = User::build()->whereIn('business_uuid', $data['business_uuid'])->column('uuid');
                break;
            default:
                $user = $data['user_uuid'];
        }
        foreach ($user as $v) {
            foreach ($data['chapter_uuid'] as $val) {
                UserCourseChapter::build()->where('is_deleted',1)->where('order_id',NULL)->where('user_uuid',$v)->where('chapter_uuid',$val)->update(['is_deleted' => 2]);
            }
        }
        return true;
    }
}
