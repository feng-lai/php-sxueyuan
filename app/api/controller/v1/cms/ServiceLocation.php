<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\ServiceLocationLogic;

/**
 * 服务据点-控制器
 */
class ServiceLocation extends Api
{
  public $restMethodList = 'get|post|put|delete';


  public function _initialize()
  {
    parent::_initialize();
    $this->userInfo = $this->cmsValidateToken();
  }

  public function index()
  {
    $request = $this->selectParam([
      'page_size'=>10,
      'page_index'=>1,
    ]);
    $result = ServiceLocationLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = ServiceLocationLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
         'name',
         'address',
         'phone',
         'weight',
     ]);
     $this->check($request, "ServiceLocation.save");
     $result = ServiceLocationLogic::cmsAdd($request,$this->userInfo);
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
        'address',
        'phone',
        'weight',
    ]);
    $request['uuid'] = $id;
    $this->check($request, "ServiceLocation.save");
    $result = ServiceLocationLogic::cmsEdit($request,$this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = ServiceLocationLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

}
