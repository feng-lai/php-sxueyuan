<?php

/**
 * Author Yacon
 * Date 2022/02/15 16:45
 */

namespace app\api\logic\common;

use app\api\model\Member;
use app\api\model\User;
use think\Db;
use think\Exception;

class UserMemberLogLogic
{

    static public function sync()
    {
        try {
            $data = [];
            User::build()
                ->field('u.uuid,u.member_uuid,u.member_time')
                ->alias('u')
                ->join('member m', 'm.uuid = u.member_uuid')
                ->where('u.is_deleted', 1)
                ->select()
                ->each(function ($item) use (&$data) {
                    $member_uuid = Member::build()->where('level', 1)->where('is_deleted', 1)->value('uuid');
                    if ($item['member_time'] < now_time(time())) {
                        $item['member_uuid'] = $member_uuid;
                    }
                    $day = date('Y-m', time()).'-01';
                    $data[] = [
                        'uuid' => uuid(),
                        'user_uuid' => $item->uuid,
                        'member_uuid' => $item->member_uuid,
                        'month'=>$day,
                        'create_time' => now_time(time()),
                    ];
                });
            $res = self::arrayToInsertSql('user_member_log', $data);
            Db::query($res);
            return true;
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function arrayToInsertSql($table, $data)
    {
        $keys = array_keys($data[0]); // 获取所有列名

        $columns = implode(', ', $keys); // 列名组合成字符串

        $sql = "INSERT INTO " . $table . " (" . $columns . ") VALUES ";

        $values = [];
        foreach ($data as $row) {
            $values[] = '(' . implode(', ', array_map(function ($value) {
                    return is_null($value) ? 'NULL' : "'$value'";
                }, $row)) . ')';
        }

        $sql .= implode(', ', $values);

        $end = [];
        foreach ($keys as $v) {
            if ($v != 'uuid') {
                $end[] = $v . '= values(' . $v . ')';
            }

        }
        $end = implode(', ', $end);
        return $sql . ' ON duplicate KEY UPDATE ' . $end;
    }
}
