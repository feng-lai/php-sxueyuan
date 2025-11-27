<?php

namespace app\api\model;

/**
 * 用户列表-模型
 * User: Yacon
 * Date: 2022-07-20
 * Time: 19:38
 */
class User extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    private $primaryKey = 'uuid';

    public function getExtendAttr($value)
    {
        return json_decode($value);
    }

    /**
     * 生成ID号
     */
    public function createUserID()
    {
        $number = $this->max('serial_number');
        $number++;
        $count = strlen($number);
        $pre = 'AM';
        for ($i = 0; $i < 7 - $count; $i++) {
            $pre .= '0';
        }
        $result = $pre . $number;
        return [$number, $result];
    }

    public function logData($data)
    {
        return [
            '昵称' => $data['name'],
            '手机' => $data['phone'],
            '企业' => Business::build()->where('uuid', $data['business_uuid'])->value('name'),
        ];
    }

    public function logData_member($data)
    {
        return [
            '会员' => Member::build()->where('uuid', $data['member_uuid'])->value('name'),
            '会员到期时间' =>$data['member_time'],
        ];
    }

    //积分扣除和明细
    public function change_score($score, $content, $user_uuid, $order = '')
    {
        //用户积分扣除
        if($score > 0){
            self::build()->where(['uuid' => $user_uuid])->setInc('score', $score);
        }else{
            self::build()->where(['uuid' => $user_uuid])->setDec('score', abs($score));
        }

        $res = [
            'uuid' => uuid(),
            'user_uuid' => $user_uuid,
            'score' => $score,
            'content' => $content,
            'left_score' => self::build()->where(['uuid' => $user_uuid])->value('score'),
            'create_time' => now_time(time()),
            'update_time' => now_time(time()),
        ];
        if ($order) {
            $res[$order[0]] = $order[1];
        }

        //用户积分明细
        UserScore::build()->insert($res);

    }

    //会员
    public function get_double($user)
    {
        //会员获得积分翻倍
        $level = Member::build()->where('uuid', $user['member_uuid'])->find();
        //if($level){
            if ($user['member_time'] && strtotime($user['member_time']) >= time() && $level->level > 1 && $level->doubled) {
                return $level->doubled;
            }
        //}

        return 1;
    }
}
