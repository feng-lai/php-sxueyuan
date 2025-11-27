<?php

namespace app\api\model;

/**
 * 积分下发-模型
 * User:
 * Date:
 * Time:
 */
class SendScore extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data)
    {
        $user = User::build()->where('uuid', $data['user_uuid'])->find();
        $name = $user['name'];
        $phone = $user['phone'];
        return [
            '操作人' => Admin::build()->where('uuid', $data['admin_uuid'])->value('name'),
            '用户' => $name?$name:$phone,
            '积分' => $data['score'],
        ];
    }

}
