<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

//use app\api\logic\common\UploadBase64Logic;
use app\api\model\Contestant;
use app\api\model\ContestantImg;
use app\api\model\ContestantPoster;
use think\Db;
use think\Exception;
use think\Request;
use think\Route;

/**
Route::get('export',function (){
    $data = \app\api\model\CourseOrder::build()->field(['uuid'])->limit(1000)->order('create_time desc')->where('is_deleted',1)->select();
    $res = [];
    $res[] = ['uuid'];
    foreach ($data as $v){
        $res[] = [$v['uuid']];
    }
    $excel = new \PHPExcel();
    $excel_sheet = $excel->getActiveSheet();
    $excel_sheet->fromArray($res);
    $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'CSV');

    $file_name = '课程订单uuid.csv';
    $file_path = ROOT_PATH . 'public/upload/'.$file_name;

    $excel_writer->save($file_path);
});**/
// 公共
Route::group(':version/common', function () {
    //定时器用
    Route::resource('Crontab', 'api/:version.common.Crontab');
    // 获取小程序二维码
    Route::resource('FetchQRCode', 'api/:version.common.FetchQRCode');
    // 获取小程序码
    Route::resource('GetQRCode', 'api/:version.common.GetQRCode');
    //上传文件
    Route::resource('Upload', 'api/:version.common.Upload');
    //上传Base64文件
    Route::resource('UploadBase64', 'api/:version.common.UploadBase64');
    // UE富文本编辑器文件上传
    Route::resource('UEUpload', 'api/:version.common.UEUpload');
    // 统一下单
    Route::resource('UnionOrderPayment', 'api/:version.common.UnionOrderPayment');
    // 统一下单-支付回调-用户端
    Route::resource('UnionOrderPaymentNotify', 'api/:version.common.UnionOrderPaymentNotify');
    // 统一下单-订单查询
    Route::resource('UnionOrderQuery', 'api/:version.common.UnionOrderQuery');
    // 统一下单-订单退款
    Route::resource('UnionOrderRefund', 'api/:version.common.UnionOrderRefund');
    // 微信登录
    Route::resource('WechatLogin', 'api/:version.common.WechatLogin');
    // 苹果登录
    Route::resource('IosLogin', 'api/:version.common.IosLogin');
    // 皮卡图片处理
    Route::resource('PicupShop', 'api/:version.common.PicupShop');
    // 腾讯电子签-回调
    Route::resource('EssCallback', 'api/:version.common.EssCallback');
    // 【临时接口】根据手机号清理用户信息
    Route::resource('ClearUser', 'api/:version.common.ClearUser');
    //密码登录
    Route::resource('LoginByPassword', 'api/:version.common.LoginByPassword');
    //验证码登录
    Route::resource('LoginByCode', 'api/:version.common.LoginByCode');
    //获取手机号
    Route::resource('GetMobile', 'api/:version.common.GetMobile');
    //获取手机号
    Route::resource('log', 'api/:version.common.Log');
    //后台登录
    Route::resource('AdminLogin', 'api/:version.common.AdminLogin');
    //cas单点登出
    Route::resource('LoginOut', 'api/:version.common.LoginOut');
    //获取视频时长
    Route::resource('GetDuration', 'api/:version.common.GetDuration');
    //发送短信验证码
    Route::resource('SendSmsCode', 'api/:version.common.SendSmsCode');
});

// 用户端
Route::group(':version/mini', function () {
    //章节
    Route::resource('Chapter', 'api/:version.mini.Chapter');
    //分享课程
    Route::resource('CourseShare', 'api/:version.mini.CourseShare');
    //7天签到情况
    Route::resource('Sign', 'api/:version.mini.Sign');
    //课程订单评价点赞
    Route::resource('CourseOrderEvaluateAgree', 'api/:version.mini.CourseOrderEvaluateAgree');
    //课程订单评价
    Route::resource('CourseOrderEvaluate', 'api/:version.mini.CourseOrderEvaluate');
    //我的章节
    Route::resource('UserChapter', 'api/:version.mini.UserChapter');
    //我的课程
    Route::resource('UserCourseChapter', 'api/:version.mini.UserCourseChapter');
    //课程订单
    Route::resource('CourseOrder', 'api/:version.mini.CourseOrder');
    //会员订单
    Route::resource('MemberOrder', 'api/:version.mini.MemberOrder');
    //会员开通记录
    Route::resource('MemberOrderLog', 'api/:version.mini.MemberOrderLog');
    //会员
    Route::resource('Member', 'api/:version.mini.Member');
    //培训
    Route::resource('Train', 'api/:version.mini.Train');
    //购物车
    Route::resource('Cart', 'api/:version.mini.Cart');
    //意见反馈
    Route::resource('Feedback', 'api/:version.mini.Feedback');
    //服务据点
    Route::resource('ServiceLocation', 'api/:version.mini.ServiceLocation');
    //核心技术
    Route::resource('Art', 'api/:version.mini.Art');
    //配置
    Route::resource('Config', 'api/:version.mini.Config');
    //常见问题
    Route::resource('Problem', 'api/:version.mini.Problem');
    // 用户信息
    Route::resource('User', 'api/:version.mini.User');
    // 我的拼课列表
    Route::resource('UserCourse', 'api/:version.mini.UserCourse');
    // 课程一级分类
    Route::resource('CourseCate', 'api/:version.mini.CourseCate');
    //培训分类
    Route::resource('TrainCate', 'api/:version.mini.TrainCate');
    //邀请码填写
    Route::resource('Invite', 'api/:version.mini.Invite');
    // 投诉建议
    Route::resource('Complaint', 'api/:version.mini.Complaint');
    // 拼团课程
    Route::resource('Course', 'api/:version.mini.Course');
    // 公告
    Route::resource('Notification', 'api/:version.mini.Notification');
    //签到
    Route::resource('Sign', 'api/:version.mini.Sign');
    //配置
    Route::resource('Config', 'api/:version.mini.Config');
    //订单
    Route::resource('Order', 'api/:version.mini.Order');
    //收藏课程
    Route::resource('Collect', 'api/:version.mini.Collect');
    //评价
    Route::resource('Evaluate', 'api/:version.mini.Evaluate');
    //签到
    Route::resource('Sign', 'api/:version.mini.Sign');
    //心得
    Route::resource('Feel', 'api/:version.mini.Feel');
    //消息
    Route::resource('Message', 'api/:version.mini.Message');
    //轮播
    Route::resource('Banner', 'api/:version.mini.Banner');
    //浏览记录
    Route::resource('Footprint', 'api/:version.mini.Footprint');
    //标签
    Route::resource('Tag', 'api/:version.mini.Tag');
    //培训订单
    Route::resource('TrainOrder', 'api/:version.mini.TrainOrder');
    //用户积分明细
    Route::resource('UserScore', 'api/:version.mini.UserScore');
    //更新首次登录
    Route::resource('UserSetFirstLogin', 'api/:version.mini.UserSetFirstLogin');
});

// 管理端
Route::group(':version/cms', function () {
    //消息推送
    Route::resource('MsgPush', 'api/:version.cms.MsgPush');
    //课程评论
    Route::resource('CourseOrderEvaluate', 'api/:version.cms.CourseOrderEvaluate');
    //财务
    Route::resource('Bill', 'api/:version.cms.Bill');
    //拉新情况
    Route::resource('Invite', 'api/:version.cms.Invite');
    //会员订单
    Route::resource('memberOrder', 'api/:version.cms.MemberOrder');
    //培训订单
    Route::resource('TrainOrder', 'api/:version.cms.TrainOrder');
    //课程订单
    Route::resource('CourseOrder', 'api/:version.cms.CourseOrder');
    //工作台
    Route::resource('Desk', 'api/:version.cms.Desk');
    //问题反馈
    Route::resource('Feedback', 'api/:version.cms.Feedback');
    //积分下发
    Route::resource('SendScore', 'api/:version.cms.SendScore');
    //常见问题
    Route::resource('Problem', 'api/:version.cms.Problem');
    //服务据点
    Route::resource('ServiceLocation', 'api/:version.cms.ServiceLocation');
    // 核心技术
    Route::resource('Art', 'api/:version.cms.Art');
    //核心技术上架/下架
    Route::resource('ArtVis', 'api/:version.cms.ArtVis');
    // 培训
    Route::resource('Train', 'api/:version.cms.Train');
    //培训分类
    Route::resource('TrainCate', 'api/:version.cms.TrainCate');
    //培训推荐
    Route::resource('TrainRecommend', 'api/:version.cms.TrainRecommend');
    //结束培训
    Route::resource('TrainFinish', 'api/:version.cms.TrainFinish');
    // 企业
    Route::resource('Business', 'api/:version.cms.Business');
    //会员设置
    Route::resource('Member', 'api/:version.cms.Member');
    //积分配置
    Route::resource('Config', 'api/:version.cms.Config');
    // 课程分类
    Route::resource('CourseCate', 'api/:version.cms.CourseCate');
    // 课程
    Route::resource('Course', 'api/:version.cms.Course');
    //章节
    Route::resource('Chapter', 'api/:version.cms.Chapter');
    //课程下发
    Route::resource('SendCourse', 'api/:version.cms.SendCourse');
    // 课程上架下架
    Route::resource('CourseVis', 'api/:version.cms.CourseVis');
    // 课程推荐
    Route::resource('CourseRecommend', 'api/:version.cms.CourseRecommend');
    // 用户
    Route::resource('User', 'api/:version.cms.User');
    //批量导入
    Route::resource('UserImport', 'api/:version.cms.UserImport');
    // 用户禁用/启用
    Route::resource('UserDisabled', 'api/:version.cms.UserDisabled');
    // 轮播
    Route::resource('Banner', 'api/:version.cms.Banner');
    // 轮播上架/下架
    Route::resource('BannerVis', 'api/:version.cms.BannerVis');
    // 管理员
    Route::resource('Admin', 'api/:version.cms.Admin');
    // 管理员权限设置
    Route::resource('AdminSetPermission', 'api/:version.cms.AdminSetPermission');
    // 管理员当前用户信息
    Route::resource('AdminInfo', 'api/:version.cms.AdminInfo');
    // 菜单
    Route::resource('AdminMenu', 'api/:version.cms.AdminMenu');
    // 菜单设置是否启用
    Route::resource('AdminMenuVis', 'api/:version.cms.AdminMenuVis');
    // 角色
    Route::resource('AdminRole', 'api/:version.cms.AdminRole');
    // 日志
    Route::resource('AdminLog', 'api/:version.cms.AdminLog');
    // 日志一级菜单
    Route::resource('AdminLogMenu', 'api/:version.cms.AdminLogMenu');
    // 日志二级菜单
    Route::resource('AdminLogSubMenu', 'api/:version.cms.AdminLogSubMenu');
    //日志导出
    Route::resource('AdminLogExport', 'api/:version.cms.AdminLogExport');
    // 管理员/教师导出
    Route::resource('AdminExport', 'api/:version.cms.AdminExport');
    // 管理员/教师CAS同步
    Route::resource('AdminSync', 'api/:version.cms.AdminSync');
    // 报名信息
    Route::resource('Order', 'api/:version.cms.Order');
    // 学生详情报名信息导出
    Route::resource('OrderExport', 'api/:version.cms.OrderExport');
    // 课程详情报名信息导出
    Route::resource('OrderExport2', 'api/:version.cms.OrderExport2');
    // 课程详情签到信息导出
    Route::resource('OrderExport3', 'api/:version.cms.OrderExport3');
    // 签到信息
    Route::resource('Sign', 'api/:version.cms.Sign');
    // 签到信息导出
    Route::resource('SignExport', 'api/:version.cms.SignExport');
    // 学生详情心得
    Route::resource('Feel', 'api/:version.cms.Feel');
    // 学生详情心得导出
    Route::resource('FeelExport', 'api/:version.cms.FeelExport');
    // 课程详情心得
    Route::resource('CourseFeel', 'api/:version.cms.CourseFeel');
    // 课程详情心得导出
    Route::resource('CourseFeelExport', 'api/:version.cms.CourseFeelExport');
    // 评价
    Route::resource('Evaluate', 'api/:version.cms.Evaluate');
    // 评价导出
    Route::resource('EvaluateExport', 'api/:version.cms.EvaluateExport');
    // 评价导出
    Route::resource('Analysis', 'api/:version.cms.Analysis');
    // 年级
    Route::resource('Grade', 'api/:version.cms.Grade');
    // 班级
    Route::resource('Class', 'api/:version.cms.UserClass');
    // 需求建议
    Route::resource('Complaint', 'api/:version.cms.Complaint');
});

Route::miss('Error/index');
$request = Request::instance();
if ($request->method() === "OPTIONS") {
    exit(json_encode(array('error' => 200, 'message' => 'option true.')));
} elseif ($request->method() === "HEAD") {
    exit(json_encode(array('error' => 200, 'message' => 'option true.')));
}
return [
    '__pattern__' => [
        'name' => '\w+',
    ],


];
