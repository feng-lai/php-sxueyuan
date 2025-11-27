<?php

namespace app\common\validate;

use think\Validate;

/**
 * 轮播-校验
 */
class Banner extends Validate
{
    protected $rule = [
        'name' => 'require',
        'img' => 'require',
        'type' => 'require|number',
        'weight' => 'require|checkRepeat',
        'link_type' => 'require|number|checkData',
    ];

    protected $field = [
        'sort' => '序号',
        'img' => '图片',
        'type' => '跳转类型',
        'weight' => '权重',
        'link_type' => '跳转链接类型'
    ];

    protected $message = [];

    protected $scene = [
        'list' => [],
        'save' => ['name', 'img', 'type', 'weight', 'link_type'],
        'update' => ['name', 'img', 'type', 'weight', 'link_type'],
    ];

    protected function checkRepeat($value, $rule, $data)
    {
        if(isset($data['uuid'])){
            if(\app\api\model\Banner::where('weight', $value)->where('is_deleted',1)->where('uuid','<>',$data['uuid'])->count() > 0){
                return '当前权重已存在';
            }
        }else{
            if(\app\api\model\Banner::where('weight', $value)->where('is_deleted',1)->count() > 0){
                return '当前权重已存在';
            }
        }

        return true;
    }

    protected function checkData($value, $rule, $data)
    {
        if ($value == 1) {
            if (!isset($data['course_uuid']) || $data['course_uuid'] == '') {
                return '课程uuid不能为空';
            }
        }
        if ($value == 2) {
            if (!isset($data['train_uuid']) || $data['train_uuid'] == '') {
                return '培训uuid不能为空';
            }
        }
        if ($value == 6) {
            if (!isset($data['url']) || $data['url'] == '') {
                return '外部链接url不能为空';
            }
        }
        if ($value == 5) {
            if (!isset($data['art_uuid']) || $data['art_uuid'] == '') {
                return '核心技术uuid不能为空';
            }
        }
        return true;
    }
}
