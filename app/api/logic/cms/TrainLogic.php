<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Member;
use app\api\model\Train;
use app\api\model\TrainCate;
use app\api\model\TrainOrder;
use app\api\model\User;
use think\Exception;
use think\Db;

/**
 *培训逻辑
 */
class TrainLogic
{
    static public function cmsList($request, $userInfo)
    {
        $where = ['c.is_deleted' => 1];
        if ($request['name']) {
            $where['c.name'] = ['like', '%' . $request['name'] . '%'];
        }
        if ($request['is_recommend']) {
            $where['c.is_recommend'] = ['=', $request['is_recommend']];
        }
        if ($request['train_cate_uuid']) {
            $where['c.train_cate_uuid'] = ['=', $request['train_cate_uuid']];
        }
        if ($request['status']) {
            $where['c.status'] = ['=', $request['status']];
        }
        $order = ['c.create_time' , 'desc'];
        if($request['is_recommend'] == 1){
            $order = ['c.weight' , 'desc'];
        }
        $result = Train::build()
            ->alias('c')
            ->field('
                c.uuid,
                c.name,
                ca.name as train_cate_name,
                c.create_time,
                c.weight,
                c.is_recommend,
                c.status,
                c.sign_end_time,
                c.end_time,
                c.begin_time,
                c.sign_begin_time
            ')
            ->join('train_cate ca', 'ca.uuid = c.train_cate_uuid', 'left')
            ->where($where)
            ->order($order[0],$order[1])
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训列表');
        return $result;
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data = Train::build()->where('uuid', $id)->where('is_deleted', 1)->findOrFail();
        $data->train_cate_name = TrainCate::build()->where('uuid', $data->train_cate_uuid)->value('name');
        $data->member_name = Member::build()->where('uuid', $data->member_uuid)->value('name');
        $data->order_num = TrainOrder::build()->where('train_uuid', $id)->where('status',2)->count();
        $data->left = $data->num - $data->order_num;
        AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训列表');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            Db::startTrans();
            if (Train::build()->where('is_deleted', 1)->where('name', $request['name'])->count()) {
                return ['msg' => '当前培训名称已存在，请重新输入'];
            }
            if (Train::build()->where('is_deleted', 1)->where('weight', $request['weight'])->count()) {
                return ['msg' => '当前培训权重已存在，请重新输入'];
            }
            if($request['end_time'] < $request['begin_time']){
                return ['msg'=>'开始时间不能大于结束时间'];
            }
            if($request['sign_begin_time'] > $request['sign_end_time']){
                return ['msg'=>'报名开始时间不能大于报名结束时间'];
            }
            $request['create_time'] = now_time(time());
            $request['update_time'] = now_time(time());
            $request['uuid'] = uuid();
            Train::build()->insert($request);
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训列表', '', Train::build()->logData($request),$request['name']);
            return $request['uuid'];
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo, $uuid)
    {
        try {
            $old = Train::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $request['update_time'] = now_time(time());
            $data = Train::build()->where('uuid', $uuid)->findOrFail();
            $data->save($request);
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训列表', Train::build()->logData($old), Train::build()->logData($request),$request['name']);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            Db::startTrans();
            $data = Train::build()
                ->where('uuid', $id)
                ->where('is_deleted',1)
                ->findOrFail();
            $data->save(['is_deleted' => 2]);
            //已报名的退款退积分
            TrainOrder::build()->where('train_uuid', $id)->where('is_deleted',1)->where('status',2)->select()->each(function ($item) {
                $item->save(['status' => 4]);
                if($item['pay_type'] == 1){
                    //退还积分
                    User::build()->change_score($item['score'],'培训删除退还积分',$item['user_uuid'],['train_order_uuid',$item['uuid']]);
                }
            });
            Db::commit();
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训列表');
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function recommend($request, $userInfo, $uuid)
    {
        try {
            DB::startTrans();
            $course = Train::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $course->save($request);
            AdminLog::build()->add($userInfo['uuid'], '培训管理', '培训推荐管理');
            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function finish($uuid, $userInfo,$info){
        try {
            DB::startTrans();
            $data = Train::build()->where('uuid', $uuid)->findOrFail();
            if($data->status != 2){
                return ['msg'=>'培训中状态才能结束'];
            }
            if($info){
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
                foreach ($excel_array as $k => $v) {
                    $train_order = TrainOrder::build()->where(['user_uuid'=>$v[0],'train_uuid'=>$uuid])->find();
                    if($train_order && $train_order->status == 2){
                        $res = ['is_sign'=>1];
                        if($data['is_get_score'] == 1){
                            $user = User::build()->where('uuid', $v[0])->find();
                            if($user){
                                $score = $data['get_score'] * User::build()->get_double($user);
                                $res['get_score'] = $score;
                                User::build()->change_score($score,'培训完成签到积分奖励',$v[0],['train_order_uuid',$train_order->uuid]);
                            }
                        }
                        $train_order->save($res);
                    }else{
                        return ['msg'=>'失败,存在还没报名的用户'];
                    }
                }
            }
            $data->save(['status' => 3]);
            Db::commit();
            return true;
        }catch (Exception $e) {
            Db::rollback();
            throw new Exception($e->getMessage(), 500);
        }
    }
}
