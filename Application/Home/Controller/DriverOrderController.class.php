<?php

namespace Home\Controller;

use Think\Controller;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderModel;
use Home\Model\UserModel;
use Home\Model\UserRecommendedModel;
use Home\Model\OrderTrafficModel;

/**
 * Class DriverOrderController
 * @package Home\Controller
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderModel $OrderModel
 * @property UserModel $UserModel
 * @property UserRecommendedModel $UserRecommendedModel
 * @property OrderTrafficModel $OrderTrafficModel
 */
class DriverOrderController extends CommonController
{

    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderModel;
    private $UserModel;
    private $OrderTrafficModel;
    private $UserRecommendedModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->UserRecommendedModel    = new UserRecommendedModel();
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
            case 1: // 专车
                $lists = $this->OrderTrafficModel->get_order_lists($con,1);
                break;
            case 2: // 代买
                $lists = $this->OrderTrafficModel->get_order_lists($con,2);
                break;
            case 3: // 代驾
                $lists = $this->OrderTownModel->get_order_lists($con);
                break;
            case 4: // 顺路
                $lists = $this->OrderTrafficModel->get_order_lists($con,3);
                break;
        }

        echoOk(200, '获取成功', $lists);
    }

    /**
     * 代驾 - 用户上车
     */
    public function line_on_car()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['order_small_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $order = $this->OrderTownModel->get_info($data['order_small_id']);
        if($order['appointment_time'] < time()){
            $dalay_s = floatval(time()) - floatval($order['appointment_time']);
            //延时分钟
            $delay_time = floatval($dalay_s) / 60;
            //延时计算单位价格
            $delay_num = 0.1;
            //状态修正  0：未支付 1：已支付 2：未生效
            $delay_state = 0;
            //延时费
            $delay_price = floatval($delay_time) * floatval($delay_num);
            //延时支付订单号
            $delay_number = "PAYD".time().$data['id'];
            //超时处理
            if ($order['status'] == '4' && $order['order_status'] == '4') {
                $this->OrderTownModel->where('id = "' . $data['order_small_id'] . '"')->save(array('delay_number' => $delay_number,'delay_price' => $delay_price,'delay_state' => $delay_state,'delay_num' => $delay_num,'delay_time' => $delay_time,'status' => '3','order_status' => '7','takeup_time' => time()));
                echoOk(200, '操作成功');
            } else {
                echoOk(301, '该订单状态不符合用户上车条件');
            }
        }else{
            if ($order['status'] == '4' && $order['order_status'] == '4') {
                $this->OrderTownModel->where('id = "' . $data['order_small_id'] . '"')->save(array('status' => '3','order_status' => '7','takeup_time' => time()));
                echoOk(200, '操作成功');
            } else {
                echoOk(301, '该订单状态不符合用户上车条件');
            }
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
        $driverInfo = $this->UserModel->get_info( $order['driver_id'] );
        $pricenow = floatval($driverInfo['money']) + floatval($order['order_driver_price']) + floatval($order['tip_price']);
        if ($order['order_status'] == '7') {
            $this->OrderTownModel->where('id = "' . $data['order_small_id'] . '"')->save(array('status' => '6','order_status' => '8','complete_time' => time()));
            $this->OrderModel->order_ok( $order['big_order_id'] , $order['driver_id'] ); // 完成大单

            if (empty($driverInfo)){
                echoOk( 301 , '数据错误' );
            }
            if ($driverInfo['credit_num'] == 9){
                $credit_pointsnew = floatval($driverInfo['credit_points']) + 5;
                if ($credit_pointsnew > 100){
                    $credit_pointsnew = 100;
                }
            }
            if ($driverInfo['credit_points'] < 100){
                //更新信誉分
                $credit_numnew = floatval($driverInfo['credit_num']) + 1;
                if ($driverInfo['credit_num'] == 9){
                    $credit_numnew = 0;
                }
                $this->UserModel->save_info( $order['driver_id'] , array ( 'credit_points' => $credit_pointsnew,'credit_num' => $credit_numnew ) );
            }
            //是否有推荐人处理
            if (!empty($driverInfo['invitation_code2_up']) && empty($driverInfo['is_invitation'])){
                $invitation_code2_up = $driverInfo['invitation_code2_up'];
                $where['invitation_code2'] = $invitation_code2_up;
                $user_info_up = $this->UserModel->getWhereInfo($where);
                if (!empty($user_info_up)){
                    $moneynew = floatval($user_info_up['money']) + 20;
                    $this->UserModel->save_info($user_info_up['id'],array('money' => $moneynew));
                    $this->UserModel->save_info($order['driver_id'],array('is_invitation' => 1));
                    $insert = [
                        'user_id' => $user_info_up['id'],
                        'user_id_up' => $order['driver_id'],
                        'price' => 20,
                        'add_time' => time(),
                    ];
                    //推荐记录插入
                    $this->UserRecommendedModel->recommended_insert($insert);
                }
            }
            $this->UserModel->save_info($order['driver_id'],array('money' => $pricenow));
            echoOk(200, '操作成功');
        } else {
            echoOk(301, '该订单状态不符合用户完成条件');
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
    /**
     * 进行中的订单
     */
    public function order_lists_ing()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['taker_type_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $con = [
            'driver_id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        switch ($data['taker_type_id']) {
            case 1: // 专车  顺风  代买
                $lists = $this->OrderTrafficModel->get_order_lists_ing($con);
                break;
            case 2: // 代驾
                $lists = $this->OrderTownModel->get_order_lists_ing($con);
                break;
        }
        echoOk(200, '获取成功', $lists);
    }
}