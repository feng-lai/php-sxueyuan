<?php

namespace app\api\model;

/**
 * 课程章节-模型
 */
class Chapter extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function getPointsAttr($value)
    {
        return $value != '[]'?json_decode($value, true):'';
    }


    public function getFileAttr($value)
    {
        return json_decode($value, true);
    }

    public function setFileAttr($value)
    {
        return json_encode($value);
    }

    public function getFeeFileAttr($value)
    {
        return json_decode($value, true);
    }

    public function setFeeFileAttr($value)
    {
        return json_encode($value);
    }

}
