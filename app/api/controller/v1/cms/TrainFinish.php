<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\TrainLogic;

/**
 * 结束培训-控制器
 */
class TrainFinish extends Api
{
    public $restMethodList = 'post';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function save()
    {
        $file = request()->file('file');
        $uuid = request()->post('train_uuid');
        if(!$uuid){
            $this->returnmsg(400, [], [], '', '', '培训uuid不能为空');
        }
        $info = '';
        if($file){
            $info = $file->validate(['ext' => 'xlsx,xls'])->move(ROOT_PATH . 'public/upload/excel');
        }
        $result = TrainLogic::finish($uuid, $this->userInfo,$info);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

}
