<?php

namespace app\api\model;

/**
 * 培训-模型
 * User:
 * Date:
 * Time:
 */
class Train extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data)
    {
        switch ($data['pay_type']) {
            case 1:
                $type = '微信';
                break;
            case 2:
                $type = "积分";
                break;
            case 3:
                $type = "微信和积分";

                break;
            default:
                $type = '微信';
        }
        return [
            '名称' => $data['name'],
            '培训分类' => TrainCate::build()->where('uuid', $data['train_cate_uuid'])->value('name'),
            '开始时间' => $data['begin_time'],
            '结束时间' => $data['end_time'],
            '报名开始时间' => $data['sign_begin_time'],
            '报名结束时间' => $data['sign_end_time'],
            '地点' => $data['address'],
            '报名人数' => $data['num'],
            '最低会员' => $data['member_uuid'] ? Member::build()->where('uuid', $data['member_uuid'])->value('name') : '',
            '支付方式' =>$type,
            '取消报名手机号'=>$data['cancel_phone'],
            '积分奖励数'=>$data['get_score'],
            '是否有积分奖励'=>$data['is_get_score'],
            '权重'=>$data['weight'],
            '封面'=>$data['img'],
            '介绍'=>$data['desc'],
            '售价'=>$data['price'],
            '积分售价'=>$data['score'],
        ];
    }

}
