<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;

/**
 * 操作日志菜单-控制器
 */
class AdminLogMenu extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = \app\api\model\AdminLog::where('is_deleted',1)->whereNotNull('menu')->group('menu')->column('menu');
        $this->render(200, ['result' => $result]);
    }


}
