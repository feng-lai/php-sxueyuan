<?php

namespace app\api\controller\v1\common;
use app\api\controller\Api;
use think\Config;

class GetDuration extends Api
{
    public function index()
    {
        try {
            $request = $this->selectParam([
                'file'
            ]);
            if (!$request['file']) {
                $this->returnmsg(400, [], [], '', '', 'file不能为空');
            }
            $command = "ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($request['file']);
            $duration = shell_exec($command);
            $this->render(200, ['result' => intval($duration)]);
        } catch (\Exception $e) {
            $this->returnmsg(403, [], [], 'Forbidden', '', $e->getMessage());
        }
    }
}
