<?php

namespace app\api\model;

use \think\Request;

/**
 * 管理员日志-模型
 * User: Yacon
 * Date: 2022-08-11
 * Time: 20:43
 */
class AdminLog extends BaseModel
{
    public static function build()
    {
        return new self();
    }

    public function getOldContentAttr($value)
    {
        return json_decode($value);
    }

    public function setOldContentAttr($value)
    {
        return json_encode($value);
    }

    public function getNewContentAttr($value)
    {
        return json_decode($value);
    }

    public function setNewContentAttr($value)
    {
        return json_encode($value);
    }    /**
     * 添加日志
     * @param {stirng} $admin_uuid 管理员UUID
     * @param {string} $menu 一级菜单
     * @param {string} $sub_menu 二级菜单
     * @param {string} $old_content 修改前的数据
     * @param {string} $new_content 修改后的数据
     */
    public function add($admin_uuid, $menu, $sub_menu, $old_content = '', $new_content = '',$name = '')
    {
        $method = \request()->method();
        switch (strtolower($method)) {
            case 'get':
                $text = '查询';
                break;
            case 'post':
                $text = '新增';
                break;
            case 'delete':
                $text = '删除';
                break;
            case 'put':
                $text = '编辑';
                break;
        }
        $this->insert([
            'uuid' => uuid(),
            "create_time" => now_time(time()),
            "update_time" => now_time(time()),
            "menu" => $menu,
            "explain" => $text,
            "admin_uuid" => $admin_uuid,
            "sub_menu" => $sub_menu,
            "old_content"=>json_encode($old_content,JSON_UNESCAPED_UNICODE),
            "new_content"=>json_encode($new_content,JSON_UNESCAPED_UNICODE),
            "name" => $name,
        ]);
    }

}
