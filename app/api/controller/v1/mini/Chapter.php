<?php

namespace app\api\controller\v1\mini;

use app\api\controller\Api;
use think\Exception;

/**
 * 章节-控制器
 */
class Chapter extends Api
{
    public $restMethodList = 'get';


    public function read($id)
    {
        $result = \app\api\model\Chapter::build()->alias('c')->join('course co','co.uuid = c.course_uuid','left')->field('co.vis,co.is_deleted as course_is_deleted,c.is_deleted')->where(['c.uuid'=>$id])->findOrFail();
        $this->render(200, ['result' => $result]);
    }
}
