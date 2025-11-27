<?php

namespace app\api\model;

/**
 * 企业-模型
 * User:
 * Date:
 * Time:
 */
class Business extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data)
    {
        return [
            '名称' => $data['name'],
        ];
    }
}
