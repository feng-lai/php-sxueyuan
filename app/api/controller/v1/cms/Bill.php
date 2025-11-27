<?php

namespace app\api\controller\v1\cms;

use app\api\controller\Api;
use think\Exception;
use app\api\logic\cms\BillLogic;

/**
 * 财务数据统计-控制器
 */
class Bill extends Api
{
    public $restMethodList = 'get|post|put|delete';


    public function _initialize()
    {
        parent::_initialize();
        $this->userInfo = $this->cmsValidateToken();
    }

    public function index()
    {
        $request = $this->selectParam([
            'page_size' => 10,
            'page_index' => 1,
            'start_time',
            'end_time'
        ]);
        $result = BillLogic::cmsList($request, $this->userInfo);

        $this->render(200, ['result' => $result]);
    }

    public function read($id)
    {
        $request = $this->selectParam([
            'start_time',
            'end_time',
            'type' => 2,
            'day',
            'course_cate_uuid',
            'm_time'

        ]);
        switch ($id) {
            case 'stat':
                $result = BillLogic::stat($request, $this->userInfo);
                break;
            case 'analyze':
                $result = BillLogic::analyze($request, $this->userInfo);
                break;
            case 'rank':
                $result = BillLogic::rank($request, $this->userInfo);
                break;
            case 'user_stat':
                $result = BillLogic::user_stat($request, $this->userInfo);
                break;
            case 'user_rank':
                $result = BillLogic::user_rank($request, $this->userInfo);
                break;
            case 'course_stat':
                $result = BillLogic::course_stat($request, $this->userInfo);
                break;
            case 'user_member':
                $result = BillLogic::user_member($request, $this->userInfo);
                break;
            case 'train_stat':
                $result = BillLogic::train_stat($request, $this->userInfo);
                break;
            case 'train_order':
                $result = BillLogic::train_order($request, $this->userInfo);
                break;
            case 'course_rank':
                $result = BillLogic::course_rank($request, $this->userInfo);
                break;
            case 'order_analyze':
                $result = BillLogic::order_analyze($request, $this->userInfo);
                    break;
        }

        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function save()
    {
        $request = $this->selectParam([]);
        $this->check($request, "RechangeSet.save");
        $result = RechangeSetLogic::cmsAdd($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function update($id)
    {
        $request = $this->selectParam([
            'coins',
            'price'
        ]);
        $request['uuid'] = $id;
        $result = RechangeSetLogic::cmsEdit($request, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }

    public function delete($id)
    {
        $result = RechangeSetLogic::cmsDelete($id, $this->userInfo);
        if (isset($result['msg'])) {
            $this->returnmsg(400, [], [], '', '', $result['msg']);
        } else {
            $this->render(200, ['result' => $result]);
        }
    }
}
