<?php

namespace app\common\validate;

use think\Validate;

/**
 * 课程-校验
 */
class Course extends Validate
{
    protected $rule = [
        'name' => 'require',
        'course_cate_uuid' => 'require',
        'sub_course_cate_uuid' => 'require',
        'img' => 'require',
        'weight' => 'require|number',
        'desc' => 'require',
        'chapter' => 'require|checkData',
        'is_hot'=>'require|in:1,2',
        'is_quality'=>'require|in:1,2',
        'is_home'=>'require|in:1,2',
        'score' => 'require|number',
    ];

    protected $field = [
        'name' => '课程名称',
        'course_cate_uuid' => '一级分类',
        'sub_course_cate_uuid' => '二级分类',
        'img' => '图片',
        'desc' => '课程介绍',
        'weight' => '权重',
        'chapter' => '章节',
        'is_hot'=>'是否热点课程推荐',
        'is_quality'=>'是否精品课程推荐',
        'is_home'=>'是否首页课程推荐',
        'score'=>'整课购买积分'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'img', 'course_cate_uuid', 'sub_course_cate_uuid', 'desc', 'weight', 'chapter','score'],
        'update' => [],
        'vis' => ['vis'],
        'recommend'=>['is_hot','is_quality','is_home']
    ];
    protected function checkData($value, $rule, $data){
        foreach($value as $v){
            if(!isset($v['name'])|| $v['name'] == ''){
                return false;
            }
            if(!isset($v['desc'])|| $v['desc'] == ''){
                return false;
            }
            if(!isset($v['type'])|| $v['type'] == ''){
                return false;
            }
            if(!isset($v['score'])|| $v['score'] == ''){
                return false;
            }
            if(!isset($v['file'])|| $v['file'] == ''){
                return false;
            }
            if(!isset($v['is_see'])|| $v['is_see'] == ''){
                return false;
            }
            if($v['type'] == 1 && $v['is_see'] == 1){
                if(!isset($v['seconds'])|| $v['seconds'] == ''){
                    return false;
                }
            }
        }
        return true;
    }
}
