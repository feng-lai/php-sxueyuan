<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Member;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 * 会员设置逻辑
 */
class MemberLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Member::build()->field('uuid,pid,name,level,create_time')->where('is_deleted', 1)->order('level asc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        foreach($result as $v){
            if($v->pid){
                $v->next = Member::build()->where('uuid', $v->pid)->value('name');
                $v->type = 2;
            }else{
                $v->next = '末级会员';
                $v->type = 1;
            }
        }
        AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Member::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->next = $data->pid?Member::build()->where('uuid', $data->pid)->value('name'):'末级会员';
        AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', '','');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $uuid = uuid();
            //名称是否重复
            if (Member::build()->where('name', $request['name'])->where('is_deleted', 1)->count()) {
                return ['msg' => '名称已存在'];
            }
            if($request['pid'] && Member::build()->where('pid', $request['pid'])->where('is_deleted', 1)->count()){
                return ['msg' => '该下级已存在对应级别，请选择其他'];
            }
            $pid = Member::build()->where('uuid', $request['pid'])->where('is_deleted', 1)->find();
            if($pid){
                $request['level'] = $pid->level+1;
            }else{
                $request['level'] = 1;
            }
            $request['uuid'] = $uuid;
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            Member::build()->insert($request);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', '',Member::build()->logData($request));
            return $request['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            //名称是否重复
            if (Member::build()->where('name', $request['name'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()) {
                return ['msg' => '名称已存在'];
            }
            if(Member::build()->where('pid', $request['pid'])->where('is_deleted', 1)->where('uuid','<>',$uuid)->count()){
                return ['msg' => '该下级已存在对应级别，请选择其他'];
            }

            $old  = Member::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data = Member::build()->where('uuid', $uuid)->findOrFail();
            $pid = Member::build()->where('uuid', $request['pid'])->where('is_deleted', 1)->find();
            if($request['pid'] && $data->level == 1){
                return ['msg'=>'基础会员不允许绑定下级'];
            }
            if($pid){
                $request['level'] = $pid->level+1;
            }else{
                $request['level'] = 1;
            }
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理', Member::build()->logData($old),Member::build()->logData($data));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Member::build()->where('uuid', $id)->where('is_deleted',1)->findOrFail();
            if(!$data->pid || $data->level == 1){
                return ['msg'=>'基础会员不能删除'];
            }
            if(Member::build()->where('pid', $id)->where('is_deleted', 1)->count()){
                return ['msg'=>'当前会员等级存在下级，请先删除下级会员'];
            }
            //判断是否有用户
            $user = User::build()->where('member_time','>',now_time(time()))->where('member_uuid',$id)->find();
            if($user){
                return ['msg'=>'请先移除当前会员下用户'];
            }
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '会员配置管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
