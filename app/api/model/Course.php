<?php

namespace app\api\model;

/**
 * 拼课课程-模型
 */
class Course extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function logData($data)
    {
        $chapter = [];
        foreach($data['chapter'] as $k=>$v){
            switch ($v['type']){
                case 1:
                    $type = '视频';
                    break;
                case 2:
                    $type = '音频';
                    break;
                case 3:
                    $type = 'PDF';
                    break;
                case 4:
                    $type = 'PPT';
                    break;
                default:
                    $type = '视频';
            }
            $points = [];
            if($v['points']){
                foreach ($v['points'] as $k1 => $v1){
                    $points[] = [
                        "时间点"=>$v1['part_time'],
                        "名称"=>$v1['name'],
                        "简介"=>$v1['desc']
                    ];
                }
            }
            $chapter[] = [
                "名称"=>$v['name'],
                "描述"=>$v['desc'],
                "类型"=>$type,
                "文件"=>$v['file'],
                "试看文件"=>$v['fee_file'],
                "是否支持试看"=>$v['is_see'] == 1?'是':'否',
                "试看秒数"=>$v['seconds'],
                "章节要点"=>$points
            ];
        }
        return [
            "名称"=>$data['name'],
            "一级分类"=>CourseCate::build()->where('uuid',$data['course_cate_uuid'])->value('name'),
            "二级分类"=>CourseCate::build()->where('uuid',$data['sub_course_cate_uuid'])->value('name'),
            "封面"=>$data['img'],
            "课程介绍"=>$data['desc'],
            "章节"=>$chapter
        ];
    }

}
