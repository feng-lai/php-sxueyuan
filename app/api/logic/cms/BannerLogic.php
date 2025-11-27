<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Art;
use app\api\model\Banner;
use app\api\model\Course;
use app\api\model\Train;
use think\Exception;
use think\Db;

/**
 * 轮播逻辑
 */
class BannerLogic
{
    static public function cmsList($request, $userInfo)
    {
        $result = Banner::build();
        if ($request['vis']) $result = $result->where('vis', '=', $request['vis']);
        if ($request['name']) $result = $result->where('name', 'like', '%' . $request['name'] . '%');
        if ($request['type']) $result = $result->where('type', '=', $request['type']);
        $result = $result->where('is_deleted', 1)->order('weight asc')->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Banner::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            $data = [
                'uuid' => uuid(),
                'img' => $request['img'],
                'course_uuid' => $request['course_uuid'],
                'train_uuid' => $request['train_uuid'],
                'art_uuid' => $request['art_uuid'],
                'name' => $request['name'],
                'url' => $request['url'],
                'type' => $request['type'],
                'link_type' => $request['link_type'],
                'weight' => $request['weight'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            Banner::build()->insert($data);

            AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理', '', Banner::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $old = Banner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $user = Banner::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理', Banner::build()->logData($old), Banner::build()->logData($user));
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = Banner::build()->where('uuid', $id)->findOrFail();
            $data->save(['is_deleted' => 2]);
            AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function vis($request, $userInfo)
    {
        if (!$request['vis']) {
            return ['msg' => 'vis不能为空'];
        }
        $old = Banner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner = Banner::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
        $banner->save(['vis' => $request['vis']]);
        AdminLog::build()->add($userInfo['uuid'], '展示管理', 'banner管理', Banner::build()->logData($old), Banner::build()->logData($banner));
        return true;
    }
}
