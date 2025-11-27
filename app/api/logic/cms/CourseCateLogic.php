<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Course;
use app\api\model\CourseCate;
use think\Exception;
use think\Db;

/**
 * 分类逻辑
 */
class CourseCateLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['is_deleted' => 1];
        $request['name']?$where['name'] = ['like', '%' . $request['name'] . '%']:'';
        $where['pid'] = $request['pid']?$request['pid']:'';
        if($request['level'] == 2 && !$request['pid']){
            $where['pid'] = ['<>',''];
        }
        if($request['level'] == 1 && $request['pid']){
            $where['pid'] = '';
        }
        $result = CourseCate::build()
            ->field('uuid,weight,name,create_time,pid')
            ->where($where)
            ->order('weight asc')
            ->order('create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])
            ->each(function ($item) {
                $child = CourseCate::build()
                    ->field('uuid,name,create_time,weight')
                    ->where('pid', $item->uuid)
                    ->where('is_deleted', 1)
                    ->order('weight asc')
                    ->order('create_time desc')
                    ->select();
                $item->child = $child;
            });


        AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程分类管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = CourseCate::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->pid_name = CourseCate::build()->where('uuid', $data->pid)->where('is_deleted', 1)->value('name');
        AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程分类管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            if ($request['pid']) {
                $cate = CourseCate::build()->where('uuid', $request['pid'])->findOrFail();
            }
            $where = ['name'=>$request['name'],'is_deleted'=>1,'pid'=>$request['pid']];
            //判断名称重复
            if (CourseCate::build()->where($where)->count()) {
                return ['msg' => '分类已存在'];
            }
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'weight' => $request['weight'],
                'pid' => $request['pid'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            CourseCate::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程分类管理', '', CourseCate::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $uuid = $request['uuid'];
            $old = CourseCate::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $user = CourseCate::build()->where('uuid', $uuid)->findOrFail();
            //判断名称重复
            if (CourseCate::build()->where('name', $request['name'])->where('is_deleted', 1)->where('uuid', '<>', $uuid)->count()) {
                return ['msg' => '分类已存在'];
            }
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程分类管理', CourseCate::build()->logData($old), CourseCate::build()->logData($user));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = CourseCate::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
            if(Course::build()->where('course_cate_uuid',$id)->where('is_deleted', 1)->count()){
                return ['msg'=>'失败，有课程绑定了该分类'];
            }
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程分类管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
