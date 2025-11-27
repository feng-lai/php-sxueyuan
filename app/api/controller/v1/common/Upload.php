<?php

namespace app\api\controller\v1\common;

use app\api\controller\Api;
use app\common\tools\Cos;

class Upload extends Api
{
    public function save()
    {
        $file = request()->file('file');

        empty($file) ? $file = request()->file('upload') : '';
        empty($file) ? $this->returnmsg(400, [], [], "", "param error", "请传入文件") : '';

        $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
        empty($info) ? $this->returnmsg(403, [], [], 'Forbidden', '', $file->getError()) : '';

        $filePath = str_replace('\\', '/', 'upload'.DS.$info->getSaveName());
        $photo = 'academy/' . uuid();
        $photo = $photo . strrchr($file->getInfo()['name'], '.');
        try {
            $oss = new Cos();
            $oss->upload($filePath, $photo);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $this->render(200, ['result' => $photo]);
        } catch (\Exception $e) {
            unlink($filePath);
            $this->returnmsg(403, [], [], 'Forbidden', '', $e->getMessage());
        }
    }
}
