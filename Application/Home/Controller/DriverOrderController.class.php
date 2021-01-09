<?php

namespace Home\Controller;

use Think\Controller;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;

/**
 * Class DriverOrderController
 * @package Home\Controller
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderTrafficModel $OrderTrafficModel
 */
class DriverOrderController extends CommonController
{

    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderTrafficModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
    }

    /**
     * 记录列表
     */
    public function lists()
    {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['taker_type_id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        switch ($data['taker_type_id']) {
            case 1: // 专车  顺风
                $lists = $this->OrderTrafficModel->get_order_lists($con,1);
                break;
            case 2: // 代买
                $lists = $this->OrderTrafficModel->get_order_lists($con,2);
                break;
            case 3: // 代驾
                $lists = $this->OrderTownModel->get_order_lists($con);
                break;
        }

        echoOk(200, '获取成功', $lists);
    }

    /**
     * 代驾 - 乘客上车
     */
    public function line_on_car()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['order_small_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $order = $this->OrderTownModel->get_info($data['order_small_id']);
        if ($order['status'] == '2' && $order['order_status'] == '3') {
            $this->OrderTownModel->where('id = "' . $data['order_small_id'] . '"')->save(array('status' => '3','order_status' => '7','takeup_time' => time()));
            echoOk(200, '操作成功');
        } else {
            echoOk(301, '该订单状态不符合乘客上车条件');
        }
    }

    /**
     * 代驾 - 完成订单
     */
    public function line_on_car_ok()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['order_small_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $order = $this->OrderTownModel->get_info($data['order_small_id']);
        if ($order['order_status'] == '7') {
            $this->OrderTownModel->where('id = "' . $data['order_small_id'] . '"')->save(array('status' => '6','order_status' => '8','complete_time' => time()));
            echoOk(200, '操作成功');
        } else {
            echoOk(301, '该订单状态不符合乘客完成条件');
        }
    }

    public function get_order_info()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['order_id']) || empty( $data['taker_type_id'] )) {
            echoOk(301, '必填项不能为空', []);
        }
        $orderInfoWhere['id'] = $data['order_id'];
        $orderInfoWhere['driver_id'] = $data['id'];
        if ($data['taker_type_id'] == 1){
            $OrderTraffic = $this->OrderTrafficModel->getWhereInfo($orderInfoWhere);
            $OrderTown = array();
        }else{
            $OrderTraffic = array();
            $OrderTown = $this->OrderTownModel->getWhereInfo($orderInfoWhere);
        }
        if (!empty($OrderTraffic)){
            $orderInfo = $OrderTraffic;
        }elseif (!empty($OrderTown)){
            $orderInfo = $OrderTown;
        }else{
            echoOk(301, '数据错误！', []);
        }
        if ($orderInfo) {
            echoOk(200, '获取成功', $orderInfo);
        } else {
            echoOk(301, '订单不存在', []);
        }
    }
    /**
     * 未接订单池列表
     */
    public function order_lists()
    {
        $data = self::$_DATA;
        if (empty($data['taker_type_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $con = [
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        switch ($data['taker_type_id']) {
            case 1: // 专车  顺风  代买
                $lists = $this->OrderTrafficModel->get_order_lists1($con);
                break;
            case 2: // 代驾
                $lists = $this->OrderTownModel->get_order_lists1($con);
                break;
        }
        echoOk(200, '获取成功', $lists);
    }
}