<?php

namespace app\api\model;

/**
 * 消息推送-模型
 * User:
 * Date:
 * Time:
 */
class MsgPush extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getUserUuidAttr($value)
    {
        return json_decode($value);
    }

    public function setUserUuidAttr($value)
    {
        return json_encode($value);
    }

    public function logData($data)
    {
        $text = '纯文本';
        switch ($data['type']) {
            case 1:
                $text = '纯文本';
                break;
            case 2:
                $text = '关联课程';
                break;
            case 3:
                $text = '关联培训';
                break;
        }
        $user_text = '个人用户';
        switch ($data['type']) {
            case 1:
                $user_text = '个人用户';
                break;
            case 2:
                $user_text = '企业用户';
                break;
            case 3:
                $user_text = '全体用户';
                break;
        }
        $name = User::build()->whereIn('uuid',$data['user_uuid'])->column('name');
        if(count($name)){
            $name = implode(',',$name);
        }else{
            $name = '';
        }
        return [
            '推送分类' => $text,
            '课程' => Course::build()->where('uuid',$data['course_uuid'])->value('name'),
            '培训' => Train::build()->where('uuid',$data['course_uuid'])->value('name'),
            '推送类型' => $user_text,
            '标题' => $data['title'],
            '内容' => $data['content'],
            '推送时间' => $data['push_time'],
            '企业'=>Business::build()->where('uuid',$data['business_uuid'])->value('name'),
            '用户'=>$name,
        ];
    }

}
