<?php

namespace app\common\validate;

use think\Validate;

/**
 * 消息推送-校验
 */
class MsgPush extends Validate
{
    protected $rule = [
        'title' => 'require',
        'content'=>'require',
        'type'=>'require|checkType',
        'user_type'=>'require|checkUserType',
        'course_uuid' => 'require',
        'train_uuid' => 'require',
        'user_uuid' => 'require',
        'business_uuid' => 'require',
        'push_time'=>'require|checkTime',
    ];

    protected $field = [
        'title' => '推送标题',
        'content'=>'推送内容',
        'type'=>'推送分类',
        'user_type'=>'推送类型',
        'course_uuid' => '课程uuid',
        'train_uuid' => '培训uuid',
        'user_uuid'=>'用户uuid',
        'business_uuid'=>'企业uuid',
        'push_time'=>'推送时间'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['title','content','type','user_type','push_time'],
        'edit' => [],
    ];

    protected function checkTime($value, $rule, $data)
    {
        if($value < now_time(time())){
            return '推送时间不能少于当前时间';
        }
        return true;
    }

    protected function checkUserType($value, $rule, $data)
    {
        if($value == 1){
            if(!isset($data['user_uuid']) || empty($data['user_uuid'])){
                return '用户uuid不能为空';
            }
            if(!is_array($data['user_uuid'])){
                return '用户uuid非法格式';
            }
        }
        if($value == 2){
            if(!isset($data['business_uuid']) || empty($data['business_uuid'])){
                return '企业uuid不能为空';
            }
            if(!\app\api\model\Business::build()->where('uuid', $data['business_uuid'])->where('is_deleted',1)->count()){
                return '企业不存在';
            }
        }
        return true;
    }

    protected function checkType($value, $rule, $data){
        if($value == 2){
            if(!isset($data['course_uuid']) || empty($data['course_uuid'])){
                return '课程uuid不能为空';
            }
            if(!\app\api\model\Course::build()->where('uuid', $data['course_uuid'])->where('is_deleted',1)->count()){
                return '课程不存在';
            }
        }
        if($value == 3){
            if(!isset($data['train_uuid']) || empty($data['train_uuid'])){
                return '培训uuid不能为空';
            }
            if(!\app\api\model\Train::build()->where('uuid', $data['train_uuid'])->where('is_deleted',1)->count()){
                return '培训不存在';
            }
        }
        return true;
    }
}
