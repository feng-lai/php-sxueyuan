<?php

namespace app\api\logic\common;

use app\api\model\Admin;
use app\api\model\AdminToken;
use think\Exception;

/**
 * 后台登陆-逻辑
 */
class AdminLoginLogic
{

    static public function cmsAdd($request)
    {
        try {
            $result = Admin::build()->login($request['uname'], $request['password']);
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
