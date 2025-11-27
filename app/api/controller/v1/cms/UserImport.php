<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\UserLogic;

/**
 * 用户批量导入-控制器
 */
class UserImport extends Api
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
        if(!$file){
            $this->returnmsg(400, [], [], '', '', '请选择文件');
        }
        $info = $file->validate(['ext' => 'xlsx,xls'])->move(ROOT_PATH . 'public/upload/excel');
        $result = UserLogic::import($info,$this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }


}
