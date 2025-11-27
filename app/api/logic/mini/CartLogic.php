<?php

namespace app\api\logic\mini;

use app\api\model\Cart;
use app\api\model\Chapter;
use app\api\model\Course;
use app\api\model\Member;
use app\api\model\OrderDetail;
use app\api\model\UserCourseChapter;
use think\Exception;
use think\Db;

/**
 * 购物车-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class CartLogic
{
    static public function miniAdd($request)
    {
        try {
            $chapter = Chapter::build()->whereIn('uuid', $request['chapter_uuid'])->where('is_deleted', 1)->select();
            if (!$chapter) {
                return ['msg' => '数据不存在'];
            }
            $res = [];
            foreach ($chapter as $v) {
                if (!Course::build()->where(['uuid' => $v['course_uuid']])->where('is_deleted', 1)->where('vis', 1)->count()) {
                    return ['msg' => '课程已下架或者不存在'];
                }
                $res[] = [
                    'uuid' => uuid(),
                    'user_uuid' => $request['user_uuid'],
                    'chapter_uuid' => $v['uuid'],
                    'course_uuid' => $v['course_uuid'],
                    'score' => $v['score'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ];
            }
            $query = arrayToInsertSql('cart', $res);
            Db::query($query);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function list($request, $userInfo)
    {
        $member = Member::build()->where('uuid',$userInfo['member_uuid'])->find();
        $where = ['c.user_uuid' => $userInfo['uuid']];
        if ($request['chapter_uuid']) {
            $where['c.chapter_uuid'] = ['in', explode(',', $request['chapter_uuid'])];
        }
        return Cart::build()
            ->alias('c')
            ->field('c.uuid,cc.name as course_name,cc.score,cc.desc,cc.img,c.course_uuid,cc.vis,cc.is_deleted')
            ->join('course cc', 'cc.uuid = c.course_uuid','left')
            ->where($where)
            ->group('c.course_uuid')
            ->order('c.create_time desc')
            ->select()
            ->each(function ($item) use ($userInfo, $where,$member) {
                $member_score = $item['score'];
                if($member->level > 1 && strtotime($userInfo['member_time']) > time()){
                    $member_score = $item['score']-$member->all_discount;
                }
                $item['member_score'] = $member_score;
                $where['c.course_uuid'] = $item['course_uuid'];
                $item['chapter'] = Cart::build()
                    ->alias('c')
                    ->field('cc.uuid,cc.name,cc.desc,cc.score,cc.file,cc.type')
                    ->join('chapter cc', 'cc.uuid = c.chapter_uuid')
                    ->order('cc.create_time asc')
                    ->where($where)
                    ->select()->each(function ($items) use ($userInfo,$member) {
                        $items['file'] = json_decode($items['file'],true);
                        $items['file'] = ['second'=>$items['file']['second']];
                        $member_score = $items['score'];
                        if($member->level > 1 && strtotime($userInfo['member_time']) > time()){
                            $member_score = $items['score']-$member->discount;
                        }
                        $items['member_score'] = $member_score;
                        $is_buy = UserCourseChapter::build()->where(['user_uuid' => $userInfo['uuid'], 'chapter_uuid' => $items['uuid'], 'is_deleted' => 1])->where('end_time', '>=', now_time(time()))->count();
                        if ($is_buy) {
                            $items['is_buy'] = 1;
                        } else {
                            $items['is_buy'] = 2;
                        }
                    });
            });
    }

    static public function miniDelete($uuid, $userInfo)
    {
        Cart::build()->whereIn('chapter_uuid', $uuid)->where('user_uuid', $userInfo['uuid'])->delete();
        return true;
    }

}
