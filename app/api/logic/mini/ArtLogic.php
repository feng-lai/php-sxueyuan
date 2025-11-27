<?php

namespace app\api\logic\mini;

use app\api\model\Art;
use think\Exception;
use think\Db;

/**
 * 核心技术-逻辑
 */
class ArtLogic
{
    static public function List($request)
    {
        $where = ['is_deleted' => 1,'vis'=>1];
        if($request['title']){
            $where['title'] = ['like','%'.$request['title'].'%'];
        }
        $result = Art::build()
            ->where($where)
            ->order('weight desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        return $result;
    }

    static public function Detail($uuid)
    {
        $where = ['is_deleted' => 1,'uuid' => $uuid,'vis'=>1];
        $result = Art::build()
            ->where($where)
            ->findOrFail();
        return $result;
    }
}
