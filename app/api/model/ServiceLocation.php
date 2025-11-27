<?php

namespace app\api\model;

/**
 * 服务据点-模型
 * User:
 * Date:
 * Time:
 */
class ServiceLocation extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data){
        return [
            '名称'=>$data['name'],
            '电话'=>$data['phone'],
            '地点'=>$data['address'],
            '权重'=>$data['weight'],
        ];
    }

}
