<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 轮播-控制器
 */
class Banner extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $request = $this->selectParam([
            'type'
        ]);
        $where = ['is_deleted'=>1,'vis'=>1];
        if($request['type']){
            $where['type'] = $request['type'];
        }
        $result = \app\api\model\Banner::build()->where($where)->order('weight','asc')->select()->each(function($item,$key){
            $is_course = 2;
            $is_train = 2;
            $is_art = 2;
            if($item['course_uuid'] && \app\api\model\Course::build()->where(['uuid'=>$item['course_uuid']])->where('is_deleted',1)->where('vis',1)->count()){
                $is_course = 1;
            }
            if($item['train_uuid'] && \app\api\model\Train::build()->where('uuid',$item['train_uuid'])->where('is_deleted',1)->count()){
                $is_train = 1;
            }
            if($item['art_uuid'] && \app\api\model\Art::build()->where('uuid',$item['art_uuid'])->where('is_deleted',1)->where('vis',1)->count()){
                $is_art = 1;
            }
            $item['is_course'] = $is_course;
            $item['is_train'] = $is_train;
            $item['is_art'] = $is_art;
        });
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = \app\api\model\Banner::build()->where(['uuid'=>$id])->find();
        $this->render(200, ['result' => $result]);
    }
}
