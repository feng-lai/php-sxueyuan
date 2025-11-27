<?php

namespace app\api\controller;

use app\api\model\Captcha;
use think\Config;
use think\Exception;

/**
 * 短信接口平台
 */
class Sms
{

	function __construct()
	{
		# code...
	}

	/**
	 * 短信发送
	 * @param $mobile
	 * @param array $arr ["变量"=>"值"]
	 * @param string $model_code
	 * @return bool|mixed
	 * @throws \think\db\exception\DataNotFoundException
	 * @throws \think\db\exception\ModelNotFoundException
	 * @throws \think\exception\DbException
	 */
	public function send_notice($mobile)
	{



		if (!$mobile) return false;

        // 生成6位随机验证码
        $code = rand(100000, 999999);

        $config = Config::get('cos');

        // 腾讯云短信配置
        $cred = new \TencentCloud\Common\Credential(
            $config['access_id'],  // 替换为实际SecretId
            $config['access_secret']  // 替换为实际SecretKey
        );

        $client = new \TencentCloud\Sms\V20190711\SmsClient($cred, "ap-guangzhou");

        try {
            $templateCode = $config['login']['templateCode'];
            if(preg_match("/\+86/", $mobile)){
                $templateCode = $config['login_cn']['templateCode'];
            }
            $req = new \TencentCloud\Sms\V20190711\Models\SendSmsRequest();
            $req->PhoneNumberSet = array($mobile);
            $req->TemplateID = $templateCode;  // 替换为审核通过的模板ID
            $req->Sign = $config['login']['sign'];  // 替换为审核通过的签名
            $req->TemplateParamSet = array(strval($code));
            $req->SmsSdkAppid = $config['login']['AppID'];  // 替换为SDK AppID

            $resp = $client->SendSms($req);


            if ($resp->SendStatusSet[0]->Code == "Ok") {
                $captchaInfo = Captcha::build()->where('phone', $mobile)->find();
                if (!empty($captchaInfo['uuid'])) {
                    $data['code'] = $code;
                    $data['create_time'] = now_time(time());
                    Captcha::build()->where("phone", $mobile)->update($data);
                } else {
                    $data['uuid'] = uuid();
                    $data['code'] = $code;
                    $data['create_time'] = now_time(time());
                    $data['phone'] = $mobile;
                    Captcha::build()->insert($data);
                }
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
	}
}
