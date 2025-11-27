<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use app\common\tools\AliOss;
use think\Exception;
use app\api\logic\cms\CourseLogic;

/**
 * 拼课课程导出-控制器
 */
class CourseExport2 extends Api
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
            'page_size'=>10,
            'page_index'=>1,
            'start_time',
            'end_time',
            'admin_name',
            'cate_uuid'
        ]);
        $result = CourseLogic::cmsList($request, $this->userInfo);
        $result = $result->toArray();
        $data = [];
        $data[] = ['课程名称', '报名人数', '类型', '拼课状态', '报名时间', '开课时间'];
        foreach ($result['data'] as $k => $v) {
            //1=拼课中 2=待开课 3=已完成 4评课取消
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
                    $text = '评课取消';
                    break;
                default:
                    break;
            }
            $tmp = [
                $v['name'],
                $v['order_num'],
                $v['cate_name'],
                $text,
                $v['begin'],
                $v['class_begin']
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

            $file_name = '课程列表.xlsx';
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
