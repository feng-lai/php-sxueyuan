<?php

namespace app\api\model;

/**
 * 课程订单详情-模型
 */
class CourseOrderDetail extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getChapterDataAttr($value)
    {
        return json_decode($value);
    }

    public function setChapterDataAttr($value)
    {
        return json_encode($value);
    }
}
