<?php

namespace app\api\logic\mini;

use app\api\model\CourseCate;
use think\Exception;
use think\Db;

/**
 * 分类-逻辑
 */
class CourseCateLogic
{
    static public function List($request)
    {
        $where = ['is_deleted' => 1];
        $where['pid'] = '';
        if($request['pid']){
            $where['pid'] = $request['pid'];
        }
        $result = CourseCate::build()
            ->where($where)
            ->order('weight desc')
            ->select();
        foreach ($result as $v) {
            $child = CourseCate::build()->field('uuid,name,create_time,weight')->where('pid', $v->uuid)->where('is_deleted', 1)->order('weight asc')->order('create_time desc')->select();
            $v->child = $child;
        }
        return $result;
    }
}
