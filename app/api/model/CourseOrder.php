<?php

namespace app\api\model;

/**
 * 课程订单-模型
 */
class CourseOrder extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getCourseDataAttr($value)
    {
        return json_decode($value);
    }

    public function setCourseDataAttr($value)
    {
        return json_encode($value);
    }

    public function getCourse($chapter_uuid,$userInfo)
    {
        $member = Member::build()->where('uuid',$userInfo['member_uuid'])->find();
        $chapter = Chapter::build()->whereIn('uuid',$chapter_uuid)->select()->each(function ($item) use ($userInfo,$member){
            $member_score = $item['score'];
            if($member->level > 1 && strtotime($userInfo['member_time']) > time()){
                $member_score = $item['score']-$member->discount;
            }
            $item['member_score'] = max($member_score,0);
        });
        $course = Course::build()->whereIn('uuid',$chapter[0]['course_uuid'])->find();
        $member_score = $course['score'];
        if($member->level > 1 && strtotime($userInfo['member_time']) > time()){
            $member_score = $course['score']-$member->all_discount;
        }
        $course['member_score'] = max($member_score,0);
        $course['chapter'] = $chapter;
        $course['course_uuid'] = $course['uuid'];
        return [$course];
    }
}
