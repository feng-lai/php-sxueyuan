<?php

namespace app\api\model;

/**
 * 常见问题-模型
 * User:
 * Date:
 * Time:
 */
class Problem extends BaseModel
{
    public static function build()
    {
        return new self();
    }
    public function logData($data){
        return [
            '问题'=>$data['problem'],
            '答案'=>$data['answer'],
            '权重'=>$data['weight'],
        ];
    }

}
