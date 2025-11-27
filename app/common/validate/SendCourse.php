<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程下发-校验
 */
class SendCourse extends Validate
{
    protected $rule = [
        'course_uuid' => 'require|checkRepeat',
        'chapter_uuid' => 'require',
        'type' => 'require|in:1,2,3|checkType',//类型 1=个人用户 2=全体用户 3=企业用户
        'user_uuid' => 'require',
        'business_uuid' => 'require',
        'status' => 'require|in:1,2',
    ];

    protected $field = [
        'course_uuid' => '课程',
        'chapter_uuid' => '章节',
        'type' => '类型',
        'user_uuid' => '用户',
        'business_uuid' => '企业用户',
        'status' => '状态'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['course_uuid', 'chapter_uuid', 'type'],
        'edit' => [],
    ];

    protected function checkType($value, $rule, $data)
    {
        switch ($value) {
            case 1:
                if (!$data['user_uuid'] || count($data['user_uuid']) == 0) {
                    return '请选择用户';
                }
                break;
            case 3:
                if (!$data['business_uuid'] || count($data['business_uuid']) == 0) {
                    return '请选择企业';
                }
                break;
        }
        return true;
    }

    protected function checkRepeat($value, $rule, $data)
    {
        if (\app\api\model\SendCourse::where('course_uuid', $value)->where('status', 1)->where('is_deleted', 1)->count() > 0) {
            return '当前课程已下发，如需重新下发，请撤回再进行下发';
        }
        return true;
    }

}
