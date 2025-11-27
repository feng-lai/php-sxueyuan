<?php

namespace app\api\logic\mini;

use app\api\model\Business;
use app\api\model\Config;
use app\api\model\Invite;
use app\api\model\Member;
use app\api\model\User;
use app\api\model\UserCourseChapter;
use app\api\model\UserScore;
use app\api\model\UserToken;
use think\Exception;

/**
 * 用户信息-逻辑
 */
class UserLogic
{
    static public function miniList($userInfo)
    {
        // 用户信息
        $result = User::build()
            ->where('uuid', $userInfo['uuid'])
            ->find();
        $result->member = Member::build()->where('level',1)->where('is_deleted',1)->find();
        $result->bussiness_name = Business::build()->where('uuid',$result->business_uuid)->where('is_deleted',1)->value('name');
        if($result->member_time && $result->member_time > now_time(time())){
            $result->member = Member::build()->where(['uuid' => $result['member_uuid']])->where('is_deleted',1)->find();
        }
        unset($result->unionid);
        $result->invite_user = Invite::build()
            ->field('u.uuid,u.name,u.phone')
            ->alias('i')
            ->join('user u','u.uuid = i.user_uuid','left')
            ->where('invite_user_uuid',$userInfo['uuid'])
            ->find();
        //完成课程数
        $result->finish = UserCourseChapterLogic::miniList(['status'=>3,'page_size'=>999,'page_index'=>1],$userInfo)->total();
        UserToken::build()->login_record($result);
        UserToken::build()->sign($result);
        return $result;
    }

    static public function miniAdd($request,$userInfo){
        try {
            $data = [];
            //绑定新手机
            if ($request['phone']) {
                if (!$request['code']) {
                    return ['msg' => '验证码不能为空'];
                }
                if (User::build()->where('phone', $request['phone'])->where('uuid', '<>', $userInfo['uuid'])->count()) {
                    return ['msg' => '手机号已被绑定'];
                }
                $data['phone'] = $request['phone'];
            }
            $request['name'] ? $data['name'] = $request['name'] : '';
            $request['gender'] ? $data['gender'] = $request['gender'] : '';
            $request['business'] ? $data['business'] = $request['business'] : '';
            $request['phone_type'] ? $data['phone_type'] = $request['phone_type'] : '';
            $request['img'] ? $data['img'] = $request['img'] : '';
            $user = User::build()->where('uuid', $userInfo['uuid'])->findOrFail();
            $user->save($data);
            if ($user['name'] && $user['img'] && $user['business'] && $user['phone_type'] && $user['gender']) {
                $content = '完善个人信息';
                if (!UserScore::build()->where('user_uuid', $userInfo['uuid'])->where('content', $content)->count()) {
                    $score = Config::build()->where('key','IMPROVE_INFO')->value('value')*User::build()->get_double($userInfo);
                    User::build()->where('uuid', $userInfo['uuid'])->setInc('score',$score);
                    UserScore::build()->insert([
                        'uuid' => uuid(),
                        'user_uuid' => $userInfo['uuid'],
                        'content' => $content,
                        'score' => $score,
                        'left_score'=>User::build()->where('uuid', $userInfo['uuid'])->value('score'),
                        'create_time' => now_time(time()),
                        'update_time' => now_time(time()),
                    ]);
                    
                }
            }
            return true;
        }catch (\Exception $e){
            throw new Exception($e->getMessage(), 500);
        }
    }
}
