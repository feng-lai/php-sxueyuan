<?php

namespace app\api\logic\common;

use app\api\controller\v1\common\UploadBase64;
use app\api\model\FetchQRCode;
use app\common\tools\Image;
use think\Exception;
use think\Config;
use think\Db;

/**
 * 获取小程序二维码-逻辑
 * User: Yacon
 * Date: 2022-04-17
 * Time: 09:59
 */
class FetchQRCodeLogic
{
    static public function commonList($request, $userInfo)
    {
        $result = '';

        $config = Config::get('wechat');
        $appid = $config['MinAppID'];
        $appSecret = $config['MinAppSecret'];

        // 获取AccessToken
        $accessURL = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$appSecret}";
        $access = curlSend($accessURL);
        $access = objToArray(json_decode($access));
        $accessToken = $access['access_token'];

        // 获取小程序码
        $codeURL = "https://api.weixin.qq.com/wxa/getwxacode?access_token={$accessToken}";
        // 构造line_color参数
        $line_color = [];
        if (isset($request['line_color'])) {
            if (is_string($request['line_color'])) {
                $line_color = json_decode($request['line_color'], true);
            } else {
                $line_color = $request['line_color'];
            }
        }

        $params = json_encode([
            'path' => $request['path'],
            'width' => intval($request['width']), // 确保为整数
            'env_version' => $request['env_version']?$request['env_version']:'release',
            'auto_color' => boolval($request['auto_color']), // 确保为布尔值
            'line_color' => $line_color, // 直接使用，不要嵌套json_decode
            'is_hyaline' => boolval($request['is_hyaline'])
        ]);
        $result = curl_post($codeURL, $params);
        print_r($result);exit;

        $result = base64_encode($result);

        // Base64文件上传并返回文件路径
        $result = UploadBase64Logic::commonAdd(['img' => $result, 'type' => 'png'], $userInfo);
        return $result;

        // 返回Base64字符串
        // return "data:image/png;base64,{$result}";
    }
}
