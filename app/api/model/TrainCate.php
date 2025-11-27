<?php

namespace app\api\model;

/**
 * 培训分类-模型
 * User:
 * Date:
 * Time:
 */
class TrainCate extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data)
    {
        return [
            "名称" => $data['name'],
            "权重" => $data['weight'],
        ];
    }

}
