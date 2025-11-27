<?php

namespace app\api\model;

/**
 * 会员设置-模型
 * User:
 * Date:
 * Time:
 */
class Member extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function logData($data){
        return [
            '名称'=>$data['name'],
            '售价'=>$data['price'],
            '图标'=>$data['img'],
            '卡片文字色码'=>$data['text_color'],
            '上级会员'=>$data['pid']?self::build()->where('uuid',$data['pid'])->value('name'):'',
            '单章节优惠积分数'=>$data['discount'],
            '整章节优惠积分数'=>$data['all_discount'],
            '是否免费学习课程'=>$data['is_fee'] == 1?'是':'否',
            '日常积分获取翻倍倍数'=>$data['doubled'],
            '积分售价'=>$data['score'],
        ];
    }
}
