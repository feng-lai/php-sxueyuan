<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\UserScore;
use app\api\model\SendScore;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 积分下发逻辑
 */
class SendScoreLogic
{
    static public function menu()
    {
        return ['营销管理','课程下发管理'];
    }

    static public function cmsList($request, $userInfo)
    {
        $result = SendScore::build()
            ->where('is_deleted', 1)
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {
                $user = User::build()->where('uuid', $item['user_uuid'])->find();
                $item['name'] = $user['name'];
                $item['phone'] = $user['phone'];
                $item['admin_name'] = Admin::build()->where('uuid', $item['admin_uuid'])->value('name');
            });
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = SendCourse::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = [
                'uuid' => uuid(),
                'score' => $request['score'],
                'admin_uuid' => $userInfo['uuid'],
                'user_uuid' => $request['user_uuid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            SendScore::build()->save($data);
            //用户积分增加
            User::build()->where('uuid', $request['user_uuid'])->setInc('score', $request['score']);
            //用户积分明细
            UserScore::build()->save([
                'uuid'=>uuid(),
                'score' => $request['score'],
                'user_uuid'=>$request['user_uuid'],
                'content'=>'积分下发',
                'left_score'=>User::build()->where('uuid', $request['user_uuid'])->value('score'),
                'create_time'=>now_time(time()),
                'update_time'=>now_time(time()),
            ]);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1],'',SendScore::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function cmsDelete($id, $userInfo)
    {
        try {
            Db::startTrans();
            $data = SendCourse::build()->where('uuid',$id)->findOrFail();
            $data->save(['status' => 2]);
            SendCourse::build()->back_user_course_chapter($data);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], self::menu()[0], self::menu()[1]);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

}
