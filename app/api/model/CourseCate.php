<?php

namespace app\api\model;
use think\Db;

/**
 * 课程分类-模型
 */
class CourseCate extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data){
        $res = getColums('course_cate');
        return [
            $res['name']=>$data['name'],
            '上级分类'=>$data['pid']?CourseCate::build()->where('uuid',$data['pid'])->value('name'):'',
            $res['weight']=>$data['weight'],
        ];
    }

}
