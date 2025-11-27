<?php

namespace app\api\logic\cms;

use app\api\model\AdminLog;
use app\api\model\Bill;
use app\api\model\Course;
use app\api\model\CourseOrder;
use app\api\model\Member;
use app\api\model\MemberOrder;
use app\api\model\Order;
use app\api\model\Train;
use app\api\model\TrainOrder;
use app\api\model\User;
use app\api\model\UserLoginDate;
use app\api\model\UserMemberLog;
use think\Exception;
use think\Db;

/**
 * 逻辑
 */
class BillLogic
{
    static public function course_rank($request, $userInfo)
    {
        try {
            $where = '';
            if($request['day']){
                $where = 'and create_time <= \'' . $request['day'] . ' 23:59:59\'';
            }
            $data = Course::build()
                ->alias('c')
                ->field([
                    'c.name',
                    '(select count(1) as c from course_order where course_uuid = c.uuid '.$where.') as num',
                ])
                ->where('is_deleted',1)
                ->order('num desc')
                ->order('create_time desc')
                ->limit('5')
                ->select();
            $score = Course::build()
                ->alias('c')
                ->field([
                    'name',
                    '(select ifnull(sum(score),0) as score from course_order where course_uuid = c.uuid '.$where.') as score',
                ])
                ->where('is_deleted',1)
                ->order('score desc')
                ->order('create_time desc')
                ->limit('5')
                ->select();
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
            return [
                'course_order_num' => [
                    'list' => $data,
                    'total' => CourseOrder::build()->where('is_deleted', 1)->count()
                ],
                'course_score'=>[
                    'list' => $score,
                    'total' => CourseOrder::build()->where('is_deleted', 1)->sum('score')
                ]
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function train_order($request, $userInfo)
    {
        try {
            if ($request['start_time'] && $request['end_time']) {
                $request['start_time'] = $request['start_time'] . '-01 00:00:00';
                $request['end_time'] = get_last_time($request['end_time']);
            } else {
                $request['start_time'] = date('Y-m', strtotime('-4 months')) . '-01 00:00:00';
                $request['end_time'] = get_last_time(date('Y-m'));
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']), 1);
            $where = [
                'is_deleted' => 1,
                'status' => 2,
                'create_time' => [
                    'between', [$request['start_time'], $request['end_time']]
                ]
            ];
            $score = TrainOrder::build()
                ->field([
                    'DATE_FORMAT(create_time, "%Y-%m") as stat_date',
                    'sum(score) as score',
                ])
                ->group('stat_date')
                ->where('pay_type', 1)
                ->where($where)
                ->select();

            $price = TrainOrder::build()
                ->field([
                    'DATE_FORMAT(create_time, "%Y-%m") as stat_date',
                    'sum(price) as price',
                ])
                ->group('stat_date')
                ->where('pay_type', 2)
                ->where($where)
                ->select();

            $score_data = [];
            $price_data = [];
            foreach ($list_date as $v) {
                $score_num = 0;
                $price_num = 0;
                foreach ($score as $val) {
                    if ($v == $val['stat_date']) {
                        $score_num = $val['score'];
                    }
                }
                foreach ($price as $val) {
                    if ($v == $val['stat_date']) {
                        $price_num = $val['price'];
                    }
                }
                $price_data[] = $price_num;
                $score_data[] = $score_num;
            }
            return [
                'score' => $score_data,
                'price' => $price_data,
                'list_date' => $list_date,
            ];
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function train_stat($request, $userInfo)
    {
        try {
            $status = [1, 2, 3];
            $data = Train::build()->field('count(status) as num,status')->where('is_deleted', 1)->group('status')->select();
            $res = [];
            $all = 0;
            foreach ($status as $v) {
                $num = 0;
                foreach ($data as $val) {
                    if ($val['status'] == $v) {
                        $num = $val['num'];
                    }
                }
                $all += $num;
                $res[] = ['num' => $num, 'status' => $v];
            }

            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
            return ['list' => $res, 'all' => $all];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function user_member($request, $userInfo)
    {
        try {

            $month = date('Y-m', time());

            if ($request['m_time']) {
                $month = $request['m_time'];
            }
            $uuid = Member::build()->where('is_deleted', 1)->where('level', 1)->value('uuid');
            $data = UserMemberLog::build()
                ->field([
                    'count(um.uuid) as num',
                    'um.member_uuid',
                    'm.text_color as bg'
                ])
                ->alias('um')
                ->group('um.member_uuid')
                ->join('user u', 'u.uuid = um.user_uuid', 'left')
                ->join('member m', 'm.uuid = um.member_uuid', 'left')
                ->where('um.member_uuid', '<>', $uuid)
                ->where('um.month', 'like', "$month%")
                ->select();
            $member = Member::build()->where('is_deleted', 1)->where('level', '>', 1)->select();
            $res = [];
            $total = 0;
            foreach ($member as $k => $v) {
                $a = [
                    'num' => 0,
                    'name' => $v['name'],
                    'bg' => $v['text_color']
                ];
                foreach ($data as $val) {
                    if ($val['member_uuid'] == $v['uuid']) {
                        $a['num'] = $val['num'];
                        $total += $a['num'];
                    }
                }
                $res[] = $a;
            }
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
            return [
                'member_type' => [
                    'fee' => UserMemberLog::build()->where('member_uuid', $uuid)->where('month', 'like', "$month%")->count(),
                    'pay' => UserMemberLog::build()->where('member_uuid', '<>', $uuid)->where('month', 'like', "$month%")->count(),
                ],
                'member_num' => ['data' => $res, 'total' => $total],
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function course_stat($request, $userInfo)
    {
        try {
            $where = ['c.is_deleted' => 1];
            $where1 = ['co.is_deleted' => 1];
            if ($request['course_cate_uuid']) {
                $where['c.course_cate_uuid'] = $request['course_cate_uuid'];
                $where1['c.course_cate_uuid'] = $request['course_cate_uuid'];
            }
            if ($request['day']) {
                $where['c.create_time'] = ['<=', $request['day'] . ' 23:59:59'];
                $where1['co.create_time'] = ['<=', $request['day'] . ' 23:59:59'];
            }
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
            return [
                'course' => Course::build()->alias('c')->where($where)->count(),
                'course_order' => CourseOrder::build()->alias('co')->join('course c', 'c.uuid = co.course_uuid')->where($where1)->count(),
                'course_score' => CourseOrder::build()->alias('co')->join('course c', 'c.uuid = co.course_uuid')->where($where1)->sum('co.score'),
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function user_rank($request, $userInfo)
    {
        try {
            $where = ['u.is_deleted' => 1];
            if ($request['day']) {
                $where['l.create_time'] = ['like', $request['day'] . '%'];
            }
            $user = User::build()
                ->alias('u')
                ->field('
                    u.uuid,
                    u.name,
                    l.day,
                    l.create_time
                    ')
                ->join('user_login_date l', 'u.uuid = l.user_uuid', 'left')
                ->order('l.day desc')
                ->order('u.create_time desc')
                ->group('u.uuid')
                ->where($where)
                ->limit(5)
                ->select();
            $score = User::build()
                ->alias('u')
                ->field('
                    u.uuid,
                    u.name,
                    l.left_score,
                    l.create_time
                    ')
                ->join('user_score l', 'u.uuid = l.user_uuid', 'left')
                ->order('l.left_score desc')
                ->order('u.create_time desc')
                ->group('u.uuid')
                ->where($where)
                ->limit(5)
                ->select();
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
            return [
                'user' => $user,
                'score' => $score,
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function user_stat($request, $userInfo)
    {
        try {
            $where = ['is_deleted' => 1];
            $today = [date('Y-m-d', time()), date('Y-m-d', time()) . ' 23:59:59'];
            $yesterday = [date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('-1 day')) . ' 23:59:59'];
            if ($request['day']) {
                $today = [date('Y-m-d', strtotime($request['day'])), date('Y-m-d', strtotime($request['day'])) . ' 23:59:59'];
                $newDate = date('Y-m-d', strtotime($request['day'] . ' - 1 day')); // 获取前一天的日期
                $yesterday = [date('Y-m-d', strtotime($newDate)), date('Y-m-d', strtotime($newDate)) . ' 23:59:59'];
            }

            $td_user = User::build()->where($where)->where('create_time', '<=', $today[1])->count();
            $yd_user = User::build()->where($where)->where('create_time', '<=', $yesterday[1])->count();

            $td_register = User::build()->where($where)->where('create_time', 'between', $today)->count();
            $yd_register = User::build()->where($where)->where('create_time', 'between', $yesterday)->count();


            $td_login = UserLoginDate::build()->where('create_time', 'between', $today)->count();
            $yd_login = UserLoginDate::build()->where('create_time', 'between', $yesterday)->count();
            return [
                'user' => [
                    'num' => $td_user,
                    'persent' => calculateIncrease($td_user, $yd_user),
                ],
                'register' => [
                    'num' => $td_register,
                    'persent' => calculateIncrease($td_register, $yd_register),
                ],
                'login' => [
                    'num' => $td_login,
                    'persent' => calculateIncrease($td_login, $yd_login),
                ]
            ];
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '综合数据分析');
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function rank($request, $userInfo)
    {
        try {
            $where = '';
            $map = ['is_deleted' => 1];
            if ($request['start_time'] && $request['end_time']) {
                $where = 'and create_time between \'' . $request['start_time'] . '\' and \'' . $request['end_time'] . ' 23:59:59\'';
                $map['create_time'] = ['between', [$request['start_time'] . ' 00:00:00', $request['end_time'] . ' 23:59:59']];
            }
            $member_uuid = Member::build()->where('is_deleted', 1)->where('level','>',1)->column('uuid');
            $member_price = MemberOrder::build()->where($map)->where('status', 2)->where('pay_type',2)->whereIn('member_uuid',$member_uuid)->sum('price_cost');
            $member = Member::build()
                ->alias('m')
                ->field('
                    m.name,
                    (select ifnull(sum(price_cost),0) as price from member_order where member_uuid = m.uuid and pay_type = 2 ' . $where . ') as m_price
                ')
                ->where('is_deleted', 1)
                ->order('m_price desc')
                ->order('level desc')
                ->select();

            $train_uuid = Train::build()->where('is_deleted', 1)->where('status',3)->column('uuid');
            $train_price = TrainOrder::build()->where($map)->where('status', 2)->where('pay_type',2)->whereIn('train_uuid',$train_uuid)->sum('price');

            $train = Train::build()
                ->alias('t')
                ->field('
                    t.name,
                    (select ifnull(sum(price),0) as price from train_order where train_uuid = t.uuid and pay_type = 2 and t.status =3 ' . $where . ') as price
                ')
                ->where('is_deleted', 1)
                ->order('price desc')
                ->order('create_time desc')
                ->limit(5)
                ->select();

            $course_score = CourseOrder::build()->where($map)->sum('score');
            $course = Course::build()
                ->alias('c')
                ->field('
                    c.name,
                    (select ifnull(sum(score),0) as score from course_order where course_uuid = c.uuid ' . $where . ') as score
                ')
                ->where('is_deleted', 1)
                ->order('score desc')
                ->order('create_time desc')
                ->limit(5)
                ->select();
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '财务指标');
            return [
                'member' => [
                    'list' => $member,
                    'all' => $member_price
                ],
                'train' => [
                    'list' => $train,
                    'all' => $train_price
                ],
                'course' => [
                    'list' => $course,
                    'all' => $course_score
                ]
            ];

        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }


    static public function analyze($request, $userInfo)
    {
        try {
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $request['start_time'] = $request['start_time'] . '-01 00:00:00';
                    $request['end_time'] = get_last_time($request['end_time']);
                } else {
                    $request['start_time'] = $request['start_time'] . ' 00:00:00';
                    $request['end_time'] = $request['end_time'] . ' 23:59:59';
                }
            } else {
                if ($request['type']) {
                    if ($request['type'] == 1) {
                        $request['start_time'] = now_time(strtotime('-6 months'));
                        $request['end_time'] = now_time(time());
                    } else {
                        $request['start_time'] = now_time(strtotime('-6 day'));
                        $request['end_time'] = now_time(time());
                    }
                } else {
                    $request['start_time'] = now_time(strtotime('-6 day'));
                    $request['end_time'] = now_time(time());
                    $request['type'] = 2;
                }
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']), $request['type']);

            $where = [
                'to.is_deleted' => 1,
                'to.pay_type' => 2,
                'to.status' => 2,
                'to.create_time' => ['between', [$request['start_time'], $request['end_time']]],
            ];
            $train_order = TrainOrder::build()->alias('to')->where($where);
            $member_order = MemberOrder::build()->alias('to')->where($where);

            if ($request['type'] == 2) {
                // 使用GROUP_CONNECT和子查询优化
                $train_order = $train_order
                    ->join('train t', 't.uuid = to.train_uuid')
                    ->field([
                        'DATE(to.create_time) as stat_date',
                        'COUNT(*) as num',
                        'SUM(to.price) as price_cost'
                    ])
                    ->group('DATE(to.create_time)')
                    ->where('t.status', 3)
                    ->select();

                $member_order = $member_order->field([
                    'DATE(to.create_time) as stat_date',
                    'COUNT(*) as num',
                    'SUM(to.price_cost) as price_cost'
                ])->group('DATE(create_time)')->select();
            } else {
                // 使用GROUP_CONNECT和子查询优化
                $train_order = $train_order
                    ->join('train t', 't.uuid = to.train_uuid')
                    ->field([
                        'DATE_FORMAT(to.create_time, "%Y-%m") as stat_date',
                        'COUNT(*) as num',
                        'SUM(to.price) as price_cost'
                    ])->group('stat_date')->select();

                $member_order = $member_order
                    ->field([
                        'DATE_FORMAT(to.create_time, "%Y-%m") as stat_date',
                        'COUNT(*) as num',
                        'SUM(to.price_cost) as price_cost'
                    ])->group('stat_date')->select();
            }
            // 格式化结果
            $member_order_num = [];
            $member_order_price = [];
            $train_order_num = [];
            $train_order_price = [];
            foreach ($list_date as $v) {
                $data = [
                    'member_order_num' => 0,
                    'member_order_price' => 0,
                    'train_order_num' => 0,
                    'train_order_price' => 0,
                ];
                foreach ($train_order as $result) {
                    if ($v == $result['stat_date']) {
                        $data['train_order_num'] = $result['num'];
                        $data['train_order_price'] = $result['price_cost'];
                    }
                }


                foreach ($member_order as $result) {
                    if ($v == $result['stat_date']) {
                        $data['member_order_num'] = $result['num'];
                        $data['member_order_price'] = $result['price_cost'];
                    }
                }
                $member_order_num[] = $data['member_order_num'];
                $member_order_price[] = floatval($data['member_order_price']);
                $train_order_num[] = $data['train_order_num'];
                $train_order_price[] = floatval($data['train_order_price']);

            }
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '财务指标');
            return [
                'date' => $list_date,
                'train_order_num' => $train_order_num,
                'member_order_num' => $member_order_num,
                'member_order_price' => $member_order_price,
                'train_order_price' => $train_order_price,
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }



    static public function order_analyze($request, $userInfo)
    {
        try {
            if ($request['start_time'] && $request['end_time']) {
                if ($request['type'] == 1) {
                    $request['start_time'] = $request['start_time'] . '-01 00:00:00';
                    $request['end_time'] = get_last_time($request['end_time']);
                } else {
                    $request['start_time'] = $request['start_time'] . ' 00:00:00';
                    $request['end_time'] = $request['end_time'] . ' 23:59:59';
                }
            } else {
                if ($request['type']) {
                    if ($request['type'] == 1) {
                        $request['start_time'] = now_time(strtotime('-6 months'));
                        $request['end_time'] = now_time(time());
                    } else {
                        $request['start_time'] = now_time(strtotime('-6 day'));
                        $request['end_time'] = now_time(time());
                    }
                } else {
                    $request['start_time'] = now_time(strtotime('-6 day'));
                    $request['end_time'] = now_time(time());
                    $request['type'] = 2;
                }
            }
            $list_date = cut_date(strtotime($request['start_time']), strtotime($request['end_time']), $request['type']);

            $where = [
                'to.is_deleted' => 1,
                'to.status' => 2,
                'to.create_time' => ['between', [$request['start_time'], $request['end_time']]],
            ];
            $train_order = TrainOrder::build()->alias('to')->where($where);
            $member_order = MemberOrder::build()->alias('to')->where($where);
            unset($where['to.status']);
            unset($where['to.pay_type']);
            $course_order = CourseOrder::build()->alias('to')->where($where);

            if ($request['type'] == 2) {
                // 使用GROUP_CONNECT和子查询优化
                $train_order = $train_order
                    ->field([
                        'DATE(to.create_time) as stat_date',
                        'COUNT(*) as num',
                    ])
                    ->group('DATE(to.create_time)')
                    ->select();

                $member_order = $member_order
                    ->field([
                    'DATE(to.create_time) as stat_date',
                    'COUNT(*) as num',
                    ])
                    ->group('DATE(create_time)')
                    ->select();

                $course_order = $course_order
                    ->field([
                        'DATE(to.create_time) as stat_date',
                        'COUNT(*) as num',
                    ])
                    ->group('DATE(create_time)')
                    ->select();
            } else {
                // 使用GROUP_CONNECT和子查询优化
                $train_order = $train_order
                    ->field([
                        'DATE_FORMAT(to.create_time, "%Y-%m") as stat_date',
                        'COUNT(*) as num',
                    ])
                    ->group('stat_date')
                    ->select();

                $member_order = $member_order
                    ->field([
                        'DATE_FORMAT(to.create_time, "%Y-%m") as stat_date',
                        'COUNT(*) as num',
                    ])->group('stat_date')->select();

                $course_order = $course_order
                    ->field([
                        'DATE_FORMAT(to.create_time, "%Y-%m") as stat_date',
                        'COUNT(*) as num',
                    ])->group('stat_date')->select();
            }
            // 格式化结果
            $order_num = [];
            foreach ($list_date as $v) {
                $data = [
                    'order_num' => 0,
                ];
                foreach ($train_order as $result) {
                    if ($v == $result['stat_date']) {
                        $data['order_num'] += $result['num'];
                    }
                }


                foreach ($member_order as $result) {
                    if ($v == $result['stat_date']) {
                        $data['order_num'] += $result['num'];
                    }
                }

                foreach ($course_order as $result) {
                    if ($v == $result['stat_date']) {
                        $data['order_num'] += $result['num'];
                    }
                }
                $order_num[] = $data['order_num'];

            }
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '财务指标');
            return [
                'date' => $list_date,
                'order_num' => $order_num,
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function stat($request, $userInfo)
    {
        try {
            $where = ['to.is_deleted' => 1, 'to.pay_type' => 2];
            if ($request['start_time'] && $request['end_time']) {
                $where['to.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
            }

            $train_order_num = TrainOrder::build()
                ->alias('to')
                ->join('train t', 't.uuid = to.train_uuid', 'left')
                ->where('to.status', 2)
                ->where($where)
                ->count();

            $member_order_num = MemberOrder::build()
                ->alias('to')
                ->where('to.status', 2)
                ->where($where)
                ->count();

            $train_order_price = TrainOrder::build()
                ->alias('to')
                ->join('train t', 't.uuid = to.train_uuid', 'left')->where('to.status', 2)
                ->where('t.status', 3)
                ->where($where)
                ->sum('to.price');

            $member_order_price = MemberOrder::build()
                ->alias('to')
                ->where('to.status', 2)
                ->where($where)
                ->sum('to.price_cost');
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '财务指标');
            return [
                'train_order_num' => $train_order_num,
                'member_order_num' => $member_order_num,
                'train_order_price' => $train_order_price,
                'member_order_price' => $member_order_price,
                'total_price' => $train_order_price + $member_order_price,
                'total_num' => $train_order_num + $member_order_num,
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

    }

    static public function cmsList($request, $userInfo)
    {
        try {
            $where = ['to.is_deleted' => 1, 'to.pay_type' => 2];
            if ($request['start_time'] && $request['end_time']) {
                $where['to.create_time'] = ['between time', [$request['start_time'], $request['end_time']]];
            }
            $data = TrainOrder::build()
                ->alias('to')
                ->field(['to.order_id', '1 as order_type', 'to.price as price_cost,t.name', 'to.create_time'])
                ->join('train t', 't.uuid = to.train_uuid')
                ->where('t.status', 3)
                ->where($where)
                ->union(
                    MemberOrder::build()
                        ->alias('to')
                        ->field(['to.order_id', '2 as order_type', 'Ifnull(to.price_cost,0)', 'm.name', 'to.create_time'])
                        ->join('member m', 'm.uuid = to.member_uuid')
                        ->where($where)
                        ->buildSql()
                    , true)
                ->page($request['page_index'], $request['page_size'])
                ->order('create_time desc')
                ->select();
            $total = TrainOrder::build()
                ->alias('to')
                ->field(['to.order_id', '1 as order_type', 'to.price as price_cost,t.name', 'to.create_time'])
                ->join('train t', 't.uuid = to.train_uuid')
                ->where('t.status', 3)
                ->where($where)->count()+ MemberOrder::build()
                        ->alias('to')
                        ->field(['to.order_id', '2 as order_type', 'Ifnull(to.price_cost,0)', 'm.name', 'to.create_time'])
                        ->join('member m', 'm.uuid = to.member_uuid')
                        ->where($where)->count();
            AdminLog::build()->add($userInfo['uuid'], '数据统计', '财务指标');
            return ['total' => $total, 'data' => $data];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDetail($id, $userInfo)
    {
        $data['income'] = Bill::build()->where('type', 'in', [1, 8])->where('pay_type', 'in', [2, 3, 4])->sum('price');
        $data['month_income'] = Bill::build()->where('type', 'in', [1, 8])->where('pay_type', 'in', [2, 3, 4])->whereTime('create_time', 'month')->sum('price');
        AdminLog::build()->add($userInfo['uuid'], '平台流水管理', '查询统计');
        return $data;
    }

    static public function cmsAdd($request, $userInfo)
    {
        try {
            //最多6个
            if (RechangeSet::build()->count() >= 6) {
                throw new Exception('最多添加6条配置', 500);
            }

            $data = [
                'uuid' => uuid(),
                'price' => $request['price'],
                'coins' => $request['coins'],
                'create_time' => now_time(time()),
                'update_time' => now_time(time()),
            ];
            RechangeSet::build()->insert($data);
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '新增：' . $data['coins']);
            return $data['uuid'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsEdit($request, $userInfo)
    {
        try {
            $user = RechangeSet::build()->where('uuid', $request['uuid'])->findOrFail();
            $user->save($request);
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '更新：' . $user->coins);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    static public function cmsDelete($id, $userInfo)
    {
        try {
            $data = RechangeSet::build()->where('uuid', $id)->findOrFail();
            $data->delete();
            AdminLog::build()->add($userInfo['uuid'], '充值配置管理', '删除：' . $data->coins);
            return true;
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}
