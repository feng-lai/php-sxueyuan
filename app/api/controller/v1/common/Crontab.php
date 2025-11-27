<?php

namespace app\api\controller\v1\common;

use app\api\logic\common\MsgPushLogic;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;
use app\api\controller\Api;
use app\api\logic\common\UserMemberLogLogic;
use app\api\logic\common\UpdateTrainStatusLogic;
use app\api\logic\common\AutoMemberLogic;
use app\api\logic\common\ExpireMemberLogic;
use app\api\logic\common\ExpireCourseLogic;
use app\api\logic\common\ExpireCourseEarlyLogic;
use Exception;

class Crontab extends Api
{

    /**
     * 允许访问的方式列表，资源数组如果没有对应的方式列表，请不要把该方法写上，如user这个资源，客户端没有delete操作
     */
    public $restMethodList = 'get|post|options';

    /**
     * @param string $type
     * @throws DataNotFoundException
     * @throws ModelNotFoundException
     * @throws DbException
     * @throws \think\Exception
     */
    public function index($type = "")
    {
        switch ($type) {
            case 'UserMemberLog':
                // 每个月记录用户的会员状态
                // /v1/common/Crontab?type=UserMemberLog
                // 每30秒执行一次
                UserMemberLogLogic::sync();
                break;
            case 'UpdateTrainStatus':
                // 更新培训状态
                // /v1/common/Crontab?type=UpdateTrainStatus
                // 每30秒执行一次
                UpdateTrainStatusLogic::sync();
                break;

            case 'AutoMember':
                // 会员自动续费
                // /v1/common/Crontab?type=AutoMember
                // 每30秒执行一次
                AutoMemberLogic::sync();
                break;

            case 'ExpireMember':
                // 会员到期通知
                // /v1/common/Crontab?type=ExpireMember
                // 每30秒执行一次
                ExpireMemberLogic::sync();
                break;

            case 'ExpireCourse':
                // 课程失效通知
                // /v1/common/Crontab?type=ExpireCourse
                // 每30秒执行一次
                ExpireCourseLogic::sync();
                break;

            case 'ExpireCourseEarly':
                // 课程30天失效通知
                // /v1/common/Crontab?type=ExpireCourseEarly
                // 每30秒执行一次
                ExpireCourseEarlyLogic::sync();
                break;

            case 'MsgPush':
                // 消息推送
                // /v1/common/Crontab?type=MsgPush
                // 每30秒执行一次
                MsgPushLogic::sync();
                break;
        }
    }

    function getCurl($url)
    {
        try {
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $url);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curlHandle, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($curlHandle);
            curl_close($curlHandle);
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }
}
