<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\mini\ArtLogic;

/**
 * 核心技能-控制器
 */
class Art extends Api
{
    public $restMethodList = 'get';

    public function index()
    {
        $request = $this->selectParam([
            'page_index' => 1,
            'page_size' => 10,
            'title'
        ]);
        $result = ArtLogic::List($request);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }

    }

    public function read($id)
    {
        $result = ArtLogic::Detail($id);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
