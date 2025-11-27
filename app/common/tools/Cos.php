<?php
namespace app\common\tools;

use think\Config;

use Qcloud\Cos\Client;
class Cos
{
    public static function getClient()
    {
        $config = Config::get('cos');

        return new Client([
            'region' => $config['region'],
            'credentials' => [
                'secretId' => $config['access_id'],
                'secretKey' => $config['access_secret']
            ]
        ]);
    }

    public function upload($localPath, $cosPath)
    {
        $client = self::getClient();

        $bucket = Config::get('cos.bucket');

        try {
            $result = $client->upload(
                $bucket,
                $cosPath,
                fopen($localPath, 'rb')
            );

            return $result;
        } catch (\Exception $e) {
            throw new \Exception('上传失败：' . $e->getMessage());
        }
    }
}