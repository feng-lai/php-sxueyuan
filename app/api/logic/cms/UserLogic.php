<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Business;
use app\api\model\Member;
use app\api\model\Order;
use app\api\model\User;
use app\api\model\Score;
use app\api\model\UserInterrest;
use app\api\model\UserRelation;
use app\common\tools\Sync;
use think\Exception;
use think\Db;

/**
 * 用户管理-用户列表-逻辑
 * User: Yacon
 * Date: 2022-08-11
 * Time: 21:24
 */
class UserLogic
{
    static public function cmsList($request, $userInfo)
    {
        $map['u.is_deleted'] = 1;
        $request['name'] ? $map['u.name'] = ['like', '%' . $request['name'] . '%'] : '';
        $request['disabled'] ? $map['u.disabled'] = ['=', $request['disabled']] : '';
        $request['member_uuid'] ? $map['u.member_uuid'] = ['in', explode(',', $request['member_uuid'])] : '';
        $request['phone'] ? $map['u.phone'] = ['=', $request['phone']] : '';
        $request['start_time'] ? $map['u.create_time'] = ['between', [$request['start_time'], $request['end_time']]] : '';
        if ($request['is_member_user']) {
            //基础会员uuid
            $uuid = Member::build()->where('level', 1)->where('is_deleted', 1)->find();
            if ($request['is_member_user'] == 1) {
                $map['u.member_uuid'] = ['<>', $uuid['uuid']];
                $map['u.member_time'] = ['>=', date('Y-m-d H:i:s')];
                $request['member_uuid'] ? $map['u.member_uuid'] = ['=', $request['member_uuid']] : '';
            }
        }
        $result = User::build()
            ->field('
                u.uuid,
                u.name,
                u.phone,
                b.name as business_name,
                u.business,
                m.name as member_name,
                u.disabled,
                u.create_time,
                u.update_member_time,
                a.name as admin_name,
                u.member_time,
                u.member_uuid
            ')
            ->alias('u')
            ->join('business b', 'b.uuid = u.business_uuid', 'left')
            ->join('member m', 'm.uuid = u.member_uuid', 'left')
            ->join('admin a', 'a.uuid = u.admin_uuid', 'left')
            ->order('u.create_time desc');

        if ($request['is_member_user'] && $request['is_member_user'] == 2) {
            $uuid = Member::build()->where('level', 1)->where('is_deleted', 1)->find();
            $result = $result->whereOr(function ($query) use ($map, $uuid) {
                $map['u.member_uuid'] = $uuid['uuid'];
                $query->where($map);
            });
            $result = $result->whereOr(function ($query) use ($map, $uuid) {
                $map['u.member_uuid'] = ['<>', $uuid['uuid']];
                $map['u.member_time'] = ['<', date('Y-m-d H:i:s')];
                $query->where($map);
            });
        } else {
            $result = $result->where($map);
        }

        $result = $result->order('u.create_time desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function ($item) {

                if (strtotime($item['member_time']) < time()) {
                    $uuid = Member::build()->where('level', 1)->where('is_deleted', 1)->find();
                    $item['member_name'] = $uuid->name;
                    $item['member_uuid'] = $uuid->uuid;
                }
                $item['member_time'] = date('Y-m-d', strtotime($item['member_time']));
            });
        AdminLog::build()->add($userInfo['uuid'], '用户管理', '用户列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $result = User::build()->where('is_deleted', 1)->where('uuid', $id)->find();
        $result->business_name = Business::build()->where('uuid', $result->business_uuid)->value('name');
        $result->member_name = Member::build()->where('uuid', $result->member_uuid)->value('name');
        AdminLog::build()->add($userInfo['uuid'], '用户管理', '用户列表');

        return $result;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            $data = [
                'uuid' => uuid(),
                'name' => $request['name'],
                'phone' => $request['phone'],
                'business_uuid' => $request['business_uuid'],
                'member_uuid' => Member::build()->where(['is_deleted' => 1, 'level' => 1])->value('uuid'),
                'invite_code' => generateRandomString(),
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            User::build()->insert($data);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '用户管理', '用户列表', '', User::build()->logData($data));
            return $data['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            Db::startTrans();
            $old = User::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $user = User::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            Member::build()->where('uuid', $request['member_uuid'])->where('is_deleted', 1)->findOrFail();
            $request['update_member_time'] = now_time(time());
            $request['admin_uuid'] = $userInfo['uuid'];
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '会员管理', '付费会员列表', User::build()->logData_member($old), User::build()->logData_member($user));
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function setDisabled($request, $userInfo)
    {
        try {
            if (!$request['disabled']) {
                return ['msg' => 'disabled不能为空'];
            }
            $data = User::build()->where('uuid', $request['uuid'])->where('is_deleted', 1)->findOrFail();
            $data->save(['disabled' => $request['disabled']]);
            AdminLog::build()->add($userInfo['uuid'], '用户管理', '用户列表');
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function import($info, $userInfo)
    {
        try {
            ini_set('memory_limit', '1G'); // 临时设置为 1GB
            Db::startTrans();
            $obj_PHPExcel = new \PHPExcel();
            $exclePath = $info->getSaveName();  //获取文件名
            $file_name = ROOT_PATH . 'public' . DS . 'upload' . DS . 'excel' . DS . $exclePath;//上传文件的地址
            $name = get_excel_name($file_name);
            if ($name === 'xlsx') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            } else {
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
            }
            $obj_PHPExcel = $objReader->load($file_name, $encode = 'utf-8');  //加载文件内容,编码utf-8
            @unlink($file_name);
            $excel_array = $obj_PHPExcel->getSheet(0)->toArray();   //转换为数组格式
            array_shift($excel_array);  //删除第一个数组(标题);
            array_shift($excel_array);
            array_shift($excel_array);
            $user = [];
            $name = [];
            $phone = [];
            $company = [];
            foreach ($excel_array as $k => $v) {
                if ($v[0]) {
                    if (count($v) != 3) {
                        return ['请上传正确的模板文件'];
                    }
                    $name[] = trim($v[0]);
                    $phone[] = trim($v[1]);
                    $company[] = trim($v[2]);
                    $business_uuid = Business::build()->where(['name' => $v[2]])->where('is_deleted', 1)->value('uuid');
                    $arr = [
                        'name' => trim($v[0]),
                        'phone' => trim($v[1]),
                        'business' => '',
                        'business_uuid' => $business_uuid
                    ];
                    $arr['invite_code'] = generateRandomString();
                    $arr['member_uuid'] = Member::build()->where(['is_deleted' => 1, 'level' => 1])->value('uuid');
                    $arr['uuid'] = uuid();
                    $arr['create_time'] = now_time(time());
                    $arr['update_time'] = now_time(time());
                    $user[$k] = $arr;
                }
            }
            //判断重复
            $data_name = User::build()->where('is_deleted', 1)->whereIn('name', $name)->column('name');
            $data_phone = User::build()->where('is_deleted', 1)->whereIn('phone', $phone)->column('phone');
            $company = array_unique($company);
            $data_company = Business::build()->where('is_deleted', 1)->whereIn('name', $company)->column('name');
            if (count($data_name)) {
                return ['msg' => '失败，昵称已被使用:' . implode(',', $data_name)];
            }
            if (count($data_phone)) {
                return ['msg' => '失败，手机号已被使用:' . implode(',', $data_phone)];
            }
            if (count(array_diff($company,$data_company))) {
                return ['msg' => '失败，企业不存在:' . implode(',', array_diff($company,$data_company))];
            }
            User::build()->insertAll($user);
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($uuid, $userInfo)
    {
        try {
            $data = User::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data->save([
                'admin_uuid' => $userInfo['uuid'],
                'member_uuid' => Member::build()->where(['level' => 1, 'is_deleted' => 1])->value('uuid'),
                'update_member_time' => now_time(time())
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

}
