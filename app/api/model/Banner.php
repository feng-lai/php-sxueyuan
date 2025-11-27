<?php

namespace app\api\model;

/**
 * 轮播-模型
 * User:
 * Date:
 * Time:
 */
class Banner extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getType($types)
    {
        switch ($types) {
            case 1:
                $type = '首页-顶部';
                break;
            case 2:
                $type = '首页-腰部';
                break;
            case 3:
                $type = '个人中心-腰部';
                break;
            default:
                $type = '首页-顶部';
        }
        return $type;
    }

    //跳转链接类型 1=指定课程 2=指定培训 3=固定页面-关于我们 4=固定页面-服务据点 5=核心技术 6=外部链接 7=不关联跳转
    public function getLinkType($types)
    {
        switch ($types) {
            case 1:
                $type = '指定课程';
                break;
            case 2:
                $type = '指定培训';
                break;
            case 3:
                $type = '固定页面-关于我们';
                break;
            case 4:
                $type = '固定页面-服务据点';
                break;
            case 5:
                $type = '核心技术';
                break;
            case 6:
                $type = '外部链接';
                break;
            case 7:
                $type = '不关联跳转';
                break;
            default:
                $type = '不关联跳转';
        }
        return $type;
    }

    public function logData($data)
    {
        if(isset($data['vis'])){
            return [
                '图片' => $data['img'],
                '课程' => Course::build()->where('uuid', $data['course_uuid'])->value('name'),
                '培训' => Train::build()->where('uuid', $data['train_uuid'])->value('name'),
                '核心技术' => Art::build()->where('uuid', $data['art_uuid'])->value('title'),
                '名称' => $data['name'],
                'url' => $data['url'],
                '位置' => $this->getType($data['type']),
                '跳转链接类型' => $this->getType($data['link_type']),
                '权重' => $data['weight'],
                '状态'=>$data['vis'] == 1?'上架':'下架'
            ];
        }else{
            return [
                '图片' => $data['img'],
                '课程' => Course::build()->where('uuid', $data['course_uuid'])->value('name'),
                '培训' => Train::build()->where('uuid', $data['train_uuid'])->value('name'),
                '核心技术' => Art::build()->where('uuid', $data['art_uuid'])->value('title'),
                '名称' => $data['name'],
                'url' => $data['url'],
                '位置' => $this->getType($data['type']),
                '跳转链接类型' => $this->getType($data['link_type']),
                '权重' => $data['weight']
            ];
        }
    }

}
