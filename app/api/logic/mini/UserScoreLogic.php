<?php

namespace app\api\logic\mini;

use app\api\model\Config;
use app\api\model\CourseOrder;
use app\api\model\CourseOrderDetail;
use app\api\model\CourseOrderEvaluate;
use app\api\model\CourseShare;
use app\api\model\Evaluate;
use app\api\model\Invite;
use app\api\model\TrainSign;
use app\api\model\User;
use app\api\model\UserCourseChapter;
use app\api\model\UserScore;
use think\Exception;

/**
 * 用户积分明细-逻辑
 */
class UserScoreLogic
{
    static public function miniList($request, $userInfo)
    {
        try {
            $where['user_uuid'] = $userInfo['uuid'];
            $score = $userInfo['score'];
            $result = UserScore::build()
                ->field('score,content,course_order_uuid,create_time,left_score')
                ->where($where)
                ->order('create_time desc')
                ->paginate(['list_rows' => $request['page_size'], 'page' => $request['page_index']])->each(function($item,$key) use (&$score){
                    if ($item['course_order_uuid']) {
                        $courseInfo = CourseOrder::build()
                            ->alias('co')
                            ->join('course c', 'c.uuid = co.course_uuid')
                            ->where(['co.uuid' => $item['course_order_uuid']])
                            ->field('c.name, COUNT(od.uuid) as chapter_num')
                            ->join('course_order_detail od', 'od.course_order_uuid = co.uuid', 'LEFT')
                            ->find();

                        $item['course_name'] = $courseInfo['name'] ?? '';
                        $item['chapter_num'] = $courseInfo['chapter_num'] ?? 0;
                    }
                    unset($item['course_order_uuid']);
                });
            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function miniDetail($userInfo){
        try {
            $double = User::build()->get_double($userInfo);
            $config = Config::build()->column('value','key');
            $where = ['user_uuid' => $userInfo['uuid'],'is_deleted'=>1];
            return [
                'login'=>[
                    'score'=>'积分 +'.$config['SIGN']*$double,
                    'is'=>1
                ],
                'invite'=>[
                    'score'=>'积分 +'.$config['INVITE']*$double,
                    'is'=>Invite::build()->where($where)->count()?1:2
                ],
                'chapter_study'=>[
                    'score'=>'积分 +'.$config['CHAPTER_STUDY']*$double,
                    'is'=>UserCourseChapter::build()->where($where)->where('persent',100)->count()?1:2
                ],
                'share_course'=>[
                    'score'=>'积分 +'.$config['SHARE_COURSE']*$double,
                    'is'=>CourseShare::build()->where($where)->count()?1:2
                ],
                'evaluate'=>[
                    'score'=>'积分 +'.$config['EAVLUATE']*$double,
                    'is'=>CourseOrderEvaluate::build()->where($where)->where('status',2)->count()?1:2
                ],
                'train_sign'=>[
                    'score'=>'积分 根据培训变动',
                    'is'=>TrainSign::build()->where($where)->count()?1:2
                ],
                'user_info'=>[
                    'score'=>'积分 +'.$config['IMPROVE_INFO']*$double,
                    'is'=>($userInfo['name'] && $userInfo['img'] && ($userInfo['business'] || $userInfo['business_uuid']) && $userInfo['phone_type'] && $userInfo['gender'])?1:2
                ]
            ];
        }catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
