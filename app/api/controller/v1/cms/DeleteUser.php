<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\UserLogic;

/**
 * 删除用户-控制器
 */
class DeleteUser extends Api
{
    public $restMethodList = 'delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function delete($id){
        $is = \app\api\model\User::build()->where('uuid',$id)->update(['is_deleted'=>2]);
        if (!$is) {
            $this->returnmsg(400, [], [], '', '', '失败');
        } else {
            $this->render(200, ['result' => true]);
        }
    }
}
