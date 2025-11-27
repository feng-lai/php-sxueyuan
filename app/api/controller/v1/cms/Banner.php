<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\BannerLogic;

/**
 * 轮播-控制器
 */
class Banner extends Api
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
      'vis',
      'name',
      'type',
      'page_size'=>10,
      'page_index'=>1,
    ]);
    $result = BannerLogic::cmsList($request,$this->userInfo);

    $this->render(200, ['result' => $result]);
  }

  public function read($id)
  {
    $result = BannerLogic::cmsDetail($id,$this->userInfo);
    $this->render(200, ['result' => $result]);
  }

   public function save()
   {
     $request = $this->selectParam([
         'name',
         'type',
         'img',
         'weight'=>1,
         'link_type',
         'course_uuid',
         'url',
         'train_uuid',
         'art_uuid'
     ]);
     $this->check($request, "Banner.save");
     $result = BannerLogic::cmsAdd($request,$this->userInfo);
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
        'type',
        'img',
        'weight'=>1,
        'link_type',
        'course_uuid',
        'url',
        'train_uuid',
        'art_uuid'
    ]);
    $request['uuid'] = $id;
    $this->check($request, "Banner.save");
    $result = BannerLogic::cmsEdit($request,$this->userInfo);
    if (isset($result['msg'])) {
      $this->returnmsg(400, [], [], '', '', $result['msg']);
    } else {
      $this->render(200, ['result' => $result]);
    }
  }

   public function delete($id)
   {
     $result = BannerLogic::cmsDelete($id,$this->userInfo);
     if (isset($result['msg'])) {
       $this->returnmsg(400, [], [], '', '', $result['msg']);
     } else {
       $this->render(200, ['result' => $result]);
     }
   }

}
