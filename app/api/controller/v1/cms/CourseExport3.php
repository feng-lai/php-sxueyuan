<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\CourseLogic;

/**
 * 拼课课程导出-控制器
 */
class CourseExport3 extends Api
{
    public $restMethodList = 'get';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'name',
            'admin_uuid',
            'begin',
            'status',
            'college_uuid',
            'vis',
            'start_time',
            'end_time',
            'page_size'=>10,
            'page_index'=>1,
            'admin_name',
            'cate_uuid'
        ]);
        $result = CourseLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        $data = [];
        $data[] = ['活动ID', '活动名称', '老师', '工号', '参与学生数', '活动开始时间', '学生评分','拼团状态'];
        foreach ($result['data'] as $k => $v) {
            switch ($v['status']){
                case 1:
                    $text = '拼课中';
                    break;
                case 2:
                    $text = '待开课';
                    break;
                case 3:
                    $text = '已完成';
                    break;
                case 4:
                    $text = '已取消';
                    break;
            }
            $tmp = [
                $v['uuid'],
                $v['name'],
                $v['admin_name'],
                $v['admin_number'],
                $v['order_num'],
                $v['create_time'],
                $v['score'],
                $text
            ];

            foreach ($tmp as $tmp_k => $tmp_v) {
                $tmp[$tmp_k] = $tmp_v . '';
            }
            $data[] = $tmp;
        }

        try {
            $excel = new \PHPExcel();
            $excel_sheet = $excel->getActiveSheet();
            $excel_sheet->fromArray($data);
            $excel_writer = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');

            $file_name = '数据分析-课程列表.xlsx';
            $file_path = ROOT_PATH . 'public/upload/'.$file_name;

            $excel_writer->save($file_path);

            if (!file_exists($file_path)) {
                throw new \Exception("Excel生成失败");
            }
            $this->render(200, ['result' => 'upload/' . $file_name]);
        } catch (\Exception $e) {
            unlink($file_path);
            throw new Exception($e->getMessage(), 500);
        }
    }
}
