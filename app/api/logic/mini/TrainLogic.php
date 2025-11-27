<?php

namespace app\api\logic\mini;

use app\api\model\Member;
use app\api\model\Train;
use app\api\model\TrainCate;
use app\api\model\TrainOrder;
use think\Exception;
use think\Db;

/**
 * 培训-逻辑
 */
class TrainLogic
{
    static public function List($request)
    {
        try {
            $where = ['is_deleted' => 1];
            if ($request['is_recommend']) {
                $where['is_recommend'] = $request['is_recommend'];
            }
            if ($request['train_cate_uuid']) {
                $where['train_cate_uuid'] = $request['train_cate_uuid'];
            }
            if ($request['status']) {
                $where['status'] = $request['status'];
            }
            if ($request['keyword']) {
                $where['name'] = ['like', '%' . $request['keyword'] . '%'];
            }
            $result = Train::build()
                ->where($where)
                ->order('weight desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
            foreach ($result as $k=>$v){
                $result[$k]->train_cate_name = TrainCate::build()->where('uuid', $v['train_cate_uuid'])->value('name');
            }
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function Detail($uuid, $userInfo)
    {
        try {

            $data = Train::build()->where('uuid', $uuid)->where('is_deleted', 1)->findOrFail();
            $data->train_cate_name = TrainCate::build()->where('uuid', $data['train_cate_uuid'])->value('name');
            $data->member = Member::build()->field('name,level')->where('uuid', $data['member_uuid'])->find();
            $data->order_num = TrainOrder::build()->where('train_uuid',$uuid)->where('status',2)->count();
            $data->is_order = 2;
            $data->btn = 1;
            $data->text = '立即报名';
            if($userInfo){
                if(TrainOrder::build()->where('train_uuid',$uuid)->where('user_uuid',$userInfo['uuid'])->where('status',2)->count()){
                    $data->is_order = 1;
                }
                $level = 1;
                if($userInfo['member_time'] && $userInfo['member_time'] > now_time(time())){
                    $level = Member::build()->where(['uuid' => $userInfo['member_uuid']])->where('is_deleted',1)->value('level');
                }
                if($level < $data->member['level']){
                    $data->text = '未达到要求等级';
                    $data->btn = 2;
                }
            }
            if($data->order_num == $data->num){
                $data->text = '人数已满';
                $data->btn = 2;
            }
            if($data->sign_begin_time > now_time(time()) || $data->sign_end_time < now_time(time())){
                $data->text = '非报名时间';
                $data->btn = 2;
            }
            if($data->end_time < now_time(time()) || $data->status == 3){
                $data->text = '培训已结束';
                $data->btn = 2;
            }
            return $data;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
