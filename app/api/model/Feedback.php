<?php

namespace app\api\model;


use think\Exception;

/**
 * 意见反馈-模型
 * User:
 * Date:
 * Time:
 */
class Feedback extends BaseModel
{
    public static function build()
    {
        return new self();
    }


    public function getImgAttr($value)
    {
        return json_decode($value);
    }

    public function setImgAttr($value)
    {
        return json_encode($value);
    }

}
