<?php

namespace app\api\logic\cms;

use app\api\model\Admin;
use app\api\model\AdminLog;
use app\api\model\Chapter;
use app\api\model\Course;
use app\api\model\CourseCate;
use app\api\model\Message;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 *课程逻辑
 */
class CourseLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['c.is_deleted' => 1];
        if ($request['name']) {
            $where['c.name'] = ['like', '%' . $request['name'] . '%'];
        }
        if ($request['vis']) {
            $where['c.vis'] = ['=', $request['vis']];
        }
        if ($request['is_quality']) {
            $where['c.is_quality'] = ['=', $request['is_quality']];
        }
        if ($request['is_hot']) {
            $where['c.is_hot'] = ['=', $request['is_hot']];
        }
        if ($request['is_home']) {
            $where['c.is_home'] = ['=', $request['is_home']];
        }
        if ($request['course_cate_uuid']) {
            $where['c.course_cate_uuid'] = ['=', $request['course_cate_uuid']];
        }
        if ($request['sub_course_cate_uuid']) {
            $where['c.sub_course_cate_uuid'] = ['=', $request['sub_course_cate_uuid']];
        }
        $result = Course::build()
            ->alias('c')
            ->field('
                c.uuid,
                c.name,
                ca.name as course_cate_name,
                cu.name as sub_course_cate_name,
                (select count(1) as num from `chapter` where course_uuid = c.uuid and is_deleted = 1) as num,
                c.create_time,
                c.vis,
                c.weight,
                c.is_home,
                c.is_quality,
                c.is_hot
            ')
            ->join('course_cate ca', 'ca.uuid = c.course_cate_uuid', 'left')
            ->join('course_cate cu', 'cu.uuid = c.sub_course_cate_uuid', 'left')
            ->where($where)
            ->order('c.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '拼课课程', '课程列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Course::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->course_cate_name = CourseCate::build()->where('uuid', $data->course_cate_uuid)->value('name');
        $data->sub_course_cate_name = CourseCate::build()->where('uuid', $data->sub_course_cate_uuid)->value('name');
        $data->chapter = Chapter::build()->where('course_uuid', $id)->order('sort asc')->where('is_deleted', 1)->select();
        AdminLog::build()->add($userInfo['uuid'], '拼课课程', '课程列表');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if (Course::build()->where('is_deleted', 1)->where('name', $request['name'])->count()) {
                return ['msg' => '当前课程名称已存在，请重新输入'];
            }
            if (Course::build()->where('is_deleted', 1)->where('weight', $request['weight'])->count()) {
                return ['msg' => '当前课程权重已存在，请重新输入'];
            }
            $chapter = $request['chapter'];

            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            $logdata = $request;
            unset($request['chapter']);
            Course::build()->insert($request);
            //保存章节信息
            foreach ($chapter as $k=>$v) {
                if($v['is_see'] == 1 && $v['type'] == 1){
                    if($v['file']['second'] < $v['seconds']){
                        return ['msg'=>'失败，章节：'.$v['name'].'试看秒数超过视频时长'];
                    }
                }
                $data = $v;
                $data['uuid'] = uuid();
                $data['course_uuid'] = $request['uuid'];
                $data['create_time'] = now_time(time());
                $data['update_time'] = now_time(time());
                $data['sort'] = $k;
                $data['points'] = json_encode($v['points'], JSON_UNESCAPED_UNICODE);
                Chapter::build()->save($data);
            }

            //新课程通知
            $user_uuid = User::build()->where('is_deleted', 1)->column('uuid');
            $res = [];
            foreach ($user_uuid as $v) {
                $res[] = [
                    'uuid' => uuid(),
                    'url_type' => 1,
                    'user_uuid' => $v,
                    'title' => '新课程上线了，快来看看吧',
                    'type' => 1,
                    'content' => '新课程《' . $request['name'] . '》上线了',
                    'course_uuid' => $request['uuid'],
                    'create_time' => now_time(time()),
                    'update_time' => now_time(time()),
                ];
            }
            Message::build()->insertAll($res);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表', '', Course::build()->logData($logdata), $request['name']);
            Db::commit();
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            Db::startTrans();
            $old = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $old->chapter = Chapter::build()->where('course_uuid', $uuid)->where('is_deleted', 1)->select();

            $chapter = $request['chapter'];
            $request['update_time'] = now_time(time());
            $data = Course::build()->where('uuid', $uuid)->findOrFail();
            $new = $request;

            unset($request['chapter']);
            $data->save($request);

            //保存章节信息
            //先删除原来的
            Chapter::build()->where('course_uuid', $uuid)->delete();
            foreach ($chapter as $k=>$v) {
                $res = $v;
                $res['uuid'] = isset($v['uuid'])?$v['uuid']:uuid();
                $res['course_uuid'] = $uuid;
                $res['create_time'] = now_time(time());
                $res['update_time'] = now_time(time());
                $res['sort'] = $k;
                $res['points'] = json_encode($v['points'], JSON_UNESCAPED_UNICODE);
                Chapter::build()->save($res);
            }
            Db::commit();

            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表', Course::build()->logData($old), Course::build()->logData($new), $request['name']);
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Course::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function setVis($request, $userInfo, $uuid)
    {
        try {
            $user = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程列表');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static function recommend($request, $userInfo, $uuid)
    {
        try {
            DB::startTrans();
            $course = Course::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $course->save($request);
            AdminLog::build()->add($userInfo['uuid'], '课程管理', '课程推荐管理');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

}
