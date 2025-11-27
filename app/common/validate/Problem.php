<?php

namespace app\common\validate;

use think\Validate;

/**
 * 常见问题-校验
 */
class Problem extends Validate
{
    protected $rule = [
        'problem' => 'require|checkData',
        'answer' => 'require',
        'weight' => 'require|checkRepeat',
    ];

    protected $field = [
        'problem' => '问题',
        'answer' => '答案',
        'weight' => '权重'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['problem', 'answer', 'weight'],
        'edit' => [],
    ];

    protected function checkRepeat($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\Problem::where('weight', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前权重已存在';
            }
        }else{
            if(\app\api\model\Problem::where('weight', $value)->where('is_deleted',1)->count() > 0){
                return '当前权重已存在';
            }
        }

        return true;
    }

    protected function checkData($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\Problem::where('problem', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前问题已存在';
            }
        }else{
            if(\app\api\model\Problem::where('problem', $value)->where('is_deleted',1)->count() > 0){
                return '当前问题已存在';
            }
        }

        return true;
    }
}
