<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\Invite;
use app\api\model\Member;
use app\api\model\User;
use app\api\model\UserScore;
use think\Exception;
use think\Db;

/**
 * 邀请码填写-逻辑
 * User: Yacon
 * Date: 2022-07-21
 * Time: 14:31
 */
class InviteLogic
{


    static public function miniAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if(!$request['code']){
                return ['msg'=>'邀请码不能为空'];
            }
            $res = Invite::build()->to_relation($userInfo['uuid'],$request['code']);
            if(isset($res['msg'])){
                return $res;
            }
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function miniList($request, $userInfo){

        try {
            $data = Invite::build()->field('uuid,invite_user_uuid,score')->where('user_uuid',$userInfo['uuid'])->paginate(['list_rows'=>$request['page_size'],'page'=>$request['page_index']])->each(function($item){
                $item['phone'] = User::build()->where('uuid',$item['invite_user_uuid'])->value('phone');
            });
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
