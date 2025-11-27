<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\api\controller\v1\mini\TrainOrder;
use app\api\model\CourseOrder;
use app\api\model\MemberOrder;
use app\api\model\MemberOrderLog;
use think\Exception;
use app\api\logic\cms\ArtLogic;

/**
 * 核心技术-控制器
 */
class Desk extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $result = [
            'user'=>\app\api\model\User::build()->whereTime('create_time', 'today')->count(),
            'price'=>MemberOrder::build()->where('status',2)->whereTime('create_time', 'today')->sum('price') +
                \app\api\model\TrainOrder::build()->alias('t')->join('train tr','tr.uuid = t.train_uuid','left')->where('t.status',2)->where('tr.status',3)->whereTime('t.create_time', 'today')->sum('t.price'),
            'order'=>MemberOrder::build()->whereTime('create_time', 'today')->where('status',2)->count() +
                \app\api\model\TrainOrder::build()->whereTime('create_time', 'today')->where('status',2)->count() +
                CourseOrder::build()->whereTime('create_time', 'today')->count(),
            'member'=>MemberOrderLog::build()->whereTime('create_time', 'today')->where('content','like','%会员开通%')->count(),
            'is_read'=>\app\api\model\Feedback::build()->where('is_read',1)->count()?1:2,
        ];

        $this->render(200, ['result' => $result]);
    }
}
