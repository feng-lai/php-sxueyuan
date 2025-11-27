<?php

namespace app\api\logic\mini;

use app\api\model\ServiceLocation;
use think\Exception;
use think\Db;

/**
 * 服务据点-逻辑
 */
class ServiceLocationLogic
{
    static public function List($request)
    {
        $where = ['is_deleted' => 1];
        $result = ServiceLocation::build()
            ->where($where)
            ->order('weight desc')
            ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']]);
        return $result;
    }
}
