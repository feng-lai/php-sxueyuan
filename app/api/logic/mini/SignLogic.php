<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Sign;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 签到-逻辑
 */
class SignLogic
{

    static public function miniList($userInfo)
    {
        $weekDay = date('N');

        // 计算本周周一的时间戳
        $mondayTimestamp = strtotime('today -' . ($weekDay - 1) . ' days');
        // 计算本周周日的时间戳
        $sundayTimestamp = strtotime('today +' . (7 - $weekDay) . ' days');

        $list_date = cut_date($mondayTimestamp, $sundayTimestamp,2);

        // 格式化为年-月-日
        $mondayDate = date('Y-m-d', $mondayTimestamp);
        $sundayDate = date('Y-m-d', $sundayTimestamp);

        $where = [
            'user_uuid' => $userInfo['uuid'],
            'is_deleted' => 1,
            'create_time' => ['between', [$mondayDate, $sundayDate . ' 23:59:59']]
        ];
        $data = Sign::build()
            ->field([
            'DATE(create_time) as stat_date'
            ])->where($where)
            ->group('DATE(create_time)')
            ->column('DATE(create_time) as stat_date');
        $res = [];
        foreach ($list_date as &$v) {
            if(in_array($v, $data)){
                $is_sign = 1;
            }else{
                $is_sign = 2;
            }
            $res[] = ['is_sign' => $is_sign, 'date' => date('m-d',strtotime($v)),'score'=>Config::build()->where('key','SIGN')->value('value') * User::build()->get_double($userInfo)];
        }
        return $res;
    }

}
