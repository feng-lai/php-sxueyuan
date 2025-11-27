<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\AdminLogic;

/**
 * 后台用户-控制器
 */
class Admin extends Api
{
    public $restMethodList = 'get|put|post|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_index' => 1,
            'page_size' => 10
        ]);
        $result = AdminLogic::cmsList($request,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $result = AdminLogic::cmsDetail($id,$this->userInfo);
        $this->render(200, ['result' => $result]);
    }

    public function save()
    {
        $request = $this->selectParam([
            'name',
            'uname',
            'password',
            'role_uuid'
        ]);
        $this->check($request, "Admin.save");
        $result = AdminLogic::cmsAdd($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'name',
            'uname',
            'password',
            'role_uuid'
        ]);
        $request['uuid'] = $id;
        $result = AdminLogic::cmsEdit($request,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = AdminLogic::cmsDelete($id,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
