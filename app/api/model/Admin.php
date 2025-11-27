<?php

namespace app\api\model;

use Exception;

/**
 * 后台管理员用户-模型
 * User: Yacon
 * Date: 2022-07-20
 * Time: 13:19
 */
class Admin extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    /**
     * 生成ID号
     */
    public function createID()
    {
        $number = $this->max('serial_number');
        $number++;
        $count = strlen($number);
        $pre = 'AM';
        for ($i = 0; $i < 7 - $count; $i++) {
            $pre .= '0';
        }
        $result = $pre .  $number;
        return [$number, $result];
    }

    /**
     * 用户登陆
     * @param {String} $mobile 账号
     * @param {String} $password 密码
     */
    public function login($uname, $password)
    {
        // 加密密码
        $password = md6($password);

        // 用户登陆
        $user = self::field('*')
            ->where(['uname' => $uname, 'password' => $password, 'is_deleted' => 1])
            ->find();


        // 如果用户不存在，则报错
        if (empty($user)) {
            AdminLog::build()->add($user['uuid'], '权限管理','管理员管理');
            throw new Exception('登陆失败', 403);
        }

        $user->save();

        AdminLog::build()->add($user['uuid'], '权限管理','管理员管理');

        $user = objToArray($user);

        unset($user['password']);

        // 查询角色
        if($user['name'] == '超级管理员') {
            $menu = AdminMenu::build()->where('is_deleted',1)->column('uuid');
            $adminRole['menus'] = $menu;
            $adminRole['name'] = '超级管理员';
            // 获取用户权限
            $user['menus'] = $adminRole['menus'];
        }else{
            $adminRole = AdminRole::build()->where('is_deleted',1)->where(['uuid' => $user['role_uuid']])->find();
            // 获取用户权限
            $user['menus'] = $adminRole['menus'];
        }


        if($user['name'] == '超级管理员'){
            $menus = AdminMenu::build()->field('uuid id,name,pid,level')->where([ 'is_deleted' => 1])->order('weight','desc')->field('uuid,name,url,pid')->select();
        }else{
            $menus = AdminMenu::build()->field('uuid id,name,pid,level')->where(['uuid' => ['in', $adminRole['menus']], 'is_deleted' => 1])->order('weight','desc')->field('uuid,name,url,pid')->select();
        }


        // 角色名
        $user['role_name'] = $adminRole['name'];

        $menus = objToArray($menus);
        $user['menus_all'] = getTreeList($menus, null);

        // 记录用户信息
        $result['user'] = $user;

        // 更新Token
        $token = AdminToken::build()->where(['admin_uuid' => $user['uuid']])->find();
        if (empty($token)) {
            throw new Exception('非法登陆', 403);
        }

        // 如果Token已过期，则更新Token
        if ($token->expiry_time < now_time(time())) {
            // 生成Token
            $result['token'] = uuid();
            $token->token = $result['token'];
            $token->expiry_time = date("Y-m-d H:i:s", time() + 604800);
            $token->save();
        }

        $result['token'] = $token->token;


        return $result;
    }
}
