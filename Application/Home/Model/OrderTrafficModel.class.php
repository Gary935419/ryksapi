<?php

namespace Home\Model;

use Think\Db;
use Think\Model;

class OrderTrafficModel extends Model
{

    public function cancel_order($id)
    {
        $UserWorkingModel = new \Home\Model\UserWorkingModel();
        $UserModel  = new \Home\Model\UserModel();
        $where['id'] = $id;
        $order = $this->get_info($id);
        if ($order['order_status'] == 7){
            echoOk(301, '当前订单无法取消', []);
        }
        $userinfo = $UserModel->get_info( $order['user_id'] );
        $order_price = $order['price'];
        $getorder_time = $order['getorder_time'];
        $now_time = time();
        $getorder_time_now = floatval($getorder_time) + 300;
        // ----- 根据状态来取消订单 ----- //
        if ($order['status'] == 1) { // 待接单(1)
            $money_new = floatval($order_price) + floatval($userinfo['money']);
        } else {
            if ($getorder_time_now >= $now_time){
                $money_new = floatval($order_price) + floatval($userinfo['money']);
            }else{
                $pricenow = floatval($order_price) + floatval($userinfo['money']);
                if ($pricenow < 5){
                    $money_new = 0;
                }else{
                    $money_new = floatval($order_price) + floatval($userinfo['money']) - 5;
                }
            }
        }
        $this->where( $where )->save( array ( 'status' => '7') ); // 已取消(7)
        $UserModel->save_info( $order['user_id'] , array ( 'money' => $money_new ) );
        $UserWorkingModel->set_working($order['driver_id'], array('status_send' => '0','status'=>'1')); // 还原该司机上班推送状态

        // ----- 根据状态来取消订单 ----- //
//        if ($order['status'] == 1) { // 待接单(1)
//
//            $this->where($where)->save(array('status' => '7')); // 已取消(7)
//
//            $waiting = $OrderWaitingModel->get_user_info($order['user_id']);
//            $OrderWaitingModel->user_del_order($order['user_id']); // 删除等待订单
//
//            $UserWorkingModel->set_working($waiting['driver_id'], array('status_send' => '0')); // 还原该司机上班推送状态
//
//        } elseif ($order['status'] == 2) { // 待接驾(2)
//
//            $this->where($where)->save(array('status' => '7')); // 已取消(7)
//
//            $UserWorkingModel->set_working($order['driver_id'], array('status' => '1')); // 空闲(1)
//
//        }
    }
    public function cancel_order_driver($id)
    {
        $UserWorkingModel = new \Home\Model\UserWorkingModel();
        $UserModel  = new \Home\Model\UserModel();
        $where['id'] = $id;
        $order = $this->get_info($id);
        $userinfo = $UserModel->get_info( $order['driver_id'] );
        $getorder_time = $order['getorder_time'];
        $now_time = time();
        $getorder_time_now = floatval($getorder_time) + 300;
        $credit_points_old = $userinfo['credit_points'];
        $credit_points_now = 0;
        if ($getorder_time_now < $now_time){
            if ($credit_points_old < 5){
                $credit_points_now = 0;
            }else{
                $credit_points_now = floatval($credit_points_old) - 5;
            }
            $UserModel->save_info( $order['driver_id'] , array ( 'credit_points' => $credit_points_now ) );
        }
        $result = array();
        $result['credit_points_old'] = $credit_points_old;
        $result['credit_points_now'] = empty($credit_points_now)?$credit_points_old:$credit_points_now;
        $result['credit_points_change'] = floatval($credit_points_old) - floatval($credit_points_now);
        // 还原该司机上班推送状态
        $UserWorkingModel->set_working($order['driver_id'], array('status_send' => '0','status'=>1));
        $this->where( $where )->save( array ( 'getorder_time' => '','driver_id' => '','status' => '1','order_status' => '2' ) );

        return $result;
    }
    /**
     * 修改订单支付状态
     * @param $id
     * @param $data
     * @return bool
     */
    public function save_info($id, $data)
    {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }
    /**
     * 完成
     * @param $id
     */
    public function small_ok($id)
    {
        $where['id'] = array('eq', $id);
        $order = $this->get_info($id);
        $this->where($where)->save(array('status' => '6','order_status'=>'8')); // 已完成(6)

        // 上班状态
        $user_working = new \Home\Model\UserWorkingModel();
        $working_save_status = [
            'status' => '1', // 状态:空闲(1)
        ];
        $user_working->set_working($order['driver_id'], $working_save_status);
    }


    /**
     * 开始行程
     * @param $id
     */
    public function start_trip($id)
    {
        $where['id'] = array('eq', $id);
        $order = $this->get_info($id);
        $this->where($where)->save(['status' => 5, 'order_status' => 4]); // 开始行程

        // ----- 司机上班 -----
        $user_working_model = new \Home\Model\UserWorkingModel();
        $user_working_model->set_working($order['driver_id'], array('status' => '3')); // 行程中
    }

    /**
     * 更新订单信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function set_order($id, $data)
    {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }

    /**
     * 上班获取订单ID
     * @param $driver_id
     * @return mixed
     */
    public function work_get_id($driver_id)
    {
        $where = 'driver_id = ' . $driver_id;
//        $where .= ' AND ( status = 1 OR status = 2 OR status = 4 OR status =5 )';
        $where .= ' AND ( order_status = 3 OR order_status = 4 OR order_status = 5 OR order_status = 6 OR order_status = 7  )';
        $id = $this->where($where)->getField('id');
        if ($id) {
            return $id;
        } else {
            return '0';
        }
    }
    /**
     * 获得进行中的专车 顺风 代买 订单
     * @param $driver_id
     * @return mixed
     */
    public function work_get_order_ing($driver_id)
    {
        $where = 'driver_id = ' . $driver_id;
        $where .= ' AND ( order_status = 3 OR order_status = 4 OR order_status = 5 OR order_status = 6 OR order_status = 7  )';
        $lists = $this->where($where)->select();
        return $lists;
    }
    /**
     * 获取订单详情
     * @param $order_id
     * @return mixed
     */
    public function get_info($order_id)
    {
        $where['id'] = array('eq', $order_id);
        $data = $this->where($where)->find();
        return $data;
    }

    /**
     * 添加订单信息
     * @param $data
     * @return mixed
     */
    public function add_order($data)
    {
        $data['add_time'] = time();
        $data['status'] = 1;
        $re = $this->add($data);
        return $re;
    }

    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_lists($con)
    {
        $where = 'user_id = ' . $con['id'];
        $where .= ' AND driver_id != 0';
        $where .= ' AND ( status = 2 OR status = 3 OR status = 4 )';
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $user = $UserModel->get_info($v['driver_id']);
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img'] = $user['head_img'];
                $re[$k]['name'] = $user['name'];
                $re[$k]['account'] = $user['account'] ? $user['account'] : '';
                $re[$k]['attribute'] = $user['attribute'];
                $re[$k]['times'] = date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status'] = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location'] = $v['end_location'];
                $re[$k]['price'] = $v['price'];
                $re[$k]['evaluate'] = $v['evaluate'];
            }
        }
        return $re;
    }

    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_order_lists($con)
    {
        $where = 'user_id = ' . $con['id'];
        $where .= ' AND order_status > 1 AND order_type = ' . $con['order_type'];
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img'] = $user['head_img'];
                $re[$k]['name'] = $user['name'];
                $re[$k]['attribute'] = $user['attribute'];
                $re[$k]['times'] = date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status'] = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location'] = $v['end_location'];
                $re[$k]['price'] = $v['price'];
                $re[$k]['evaluate'] = $v['evaluate'];
                $re[$k]['order_type'] = $v['order_type'];
                switch ($v['order_status']){
                    case 2:
                        $re[$k]['status_msg'] = "待接单";
                        break;
                    case 3:
                        $re[$k]['status_msg'] = "已接单";
                        break;
                    case 4:
                        $re[$k]['status_msg'] = "前往出发地";
                        break;
                    case 5:
                        $re[$k]['status_msg'] = "到达出发地";
                        break;
                    case 6:
                        $re[$k]['status_msg'] = "验证提货码";
                        break;
                    case 7:
                        $re[$k]['status_msg'] = "前往目的地";
                        break;
                    case 8:
                        $re[$k]['status_msg'] = "已完成";
                        break;
                    default:
                        $re[$k]['status_msg'] = "没人接";
                }
                if ($v['status'] == 7){
                    $re[$k]['status_msg'] = "已取消";
                }
            }
        }
        return $re;
    }

    /**
     * 获取行程详情
     * @param $id
     * @return mixed
     */
    public function get_trip_details($id)
    {
        $where['id'] = array('eq', $id);
        $order = $this->where($where)->find();
        $re = [];
        if ($order) {
            $UserModel = new \Home\Model\UserModel();
            $user = $UserModel->get_info($order['driver_id']);
            $frist = mb_substr($user['name'],0,1,'utf-8');
            $re['head_img'] = $user['head_img'];
            $re['name'] = empty($user['name'])?'':$frist."师傅";
            $re['account'] = $user['account'];
            $re['car_number'] = $user['car_number'];
            $re['attribute'] = $user['attribute'];
            $re['brand'] = $user['brand'];
            $re['order_small_id'] = $order['id'];
            $re['times'] = date('Y-m-d H:i:s', $order['add_time']);
            $re['appointment_time'] = empty($order['appointment_time'])?'':date('Y-m-d H:i:s', $order['appointment_time']);
            $re['getorder_time'] = empty($order['getorder_time'])?'':date('Y-m-d H:i:s', $order['getorder_time']);
            $re['takegoods_time'] = empty($order['takegoods_time'])?'':date('Y-m-d H:i:s', $order['takegoods_time']);
            $re['complete_time'] = empty($order['complete_time'])?'':date('Y-m-d H:i:s', $order['complete_time']);
            $re['status'] = $order['status'];
            $re['start_location'] = $order['start_location'];
            $re['end_location'] = $order['end_location'];
            $re['price'] = sprintf("%.2f",$order['price']);
            $re['protect_price'] = sprintf("%.2f",$order['protect_price']);
            $re['tip_price'] = sprintf("%.2f",$order['tip_price']);
            $re['preferential_price'] = sprintf("%.2f",$order['preferential_price']);
            $re['distribution_km'] = sprintf("%.2f",$order['distribution_km']);
            $re['evaluate'] = empty($order['evaluate'])?'':$order['evaluate'];
            $re['user_id'] = $order['user_id'];
            $re['goods_remarks'] = $order['goods_remarks'];
            $re['goods_name'] = $order['goods_name'];
            $re['number'] = $order['number'];
            $re['order_status'] = $order['order_status'];
            $re['order_type'] = $order['order_type'];
            $re['is_invoice'] = $order['is_invoice'];
            switch ($order['order_status']){
                case 2:
                    $re['status_msg'] = "待接单";
                    break;
                case 3:
                    $re['status_msg'] = "已接单";
                    break;
                case 4:
                    $re['status_msg'] = "前往出发地";
                    break;
                case 5:
                    $re['status_msg'] = "到达出发地";
                    break;
                case 6:
                    $re['status_msg'] = "验证提货码";
                    break;
                case 7:
                    $re['status_msg'] = "前往目的地";
                    break;
                case 8:
                    $re['status_msg'] = "已完成";
                    break;
                default:
                    $re['status_msg'] = "没人接";
            }
            if ($order['status'] == 7){
                $re['status_msg'] = "已取消";
            }
        }
        return $re;
    }

    /**
     * 获取订单列表
     * @param $con
     * @return mixed
     */
    public function get_order_lists($con,$type)
    {
        if ($type == 1){
            $where = 'order_type = 1 and driver_id = ' . $con['id'];
        }elseif ($type == 2){
            $where = 'order_type = 3 and driver_id = ' . $con['id'];
        }else{
            $where = 'order_type = 2 and driver_id = ' . $con['id'];
        }
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img'] = $user['head_img'];
                $re[$k]['name'] = $user['name'];
                $re[$k]['account'] = $user['account'];
                $re[$k]['times'] = date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status'] = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location'] = $v['end_location'];
                $re[$k]['price'] = $v['price'];
                $re[$k]['order_driver_price'] = $v['order_driver_price'];
                $re[$k]['tip_price'] = $v['tip_price'];
            }
        }
        return $re;
    }
    /**
     * 获取订单列表 订单池
     * @param $con
     * @return mixed
     */
    public function get_order_lists1($con)
    {
        $where = 'status = 1 and order_status = 2';
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img'] = $user['head_img'];
                $re[$k]['name'] = $user['name'];
                $re[$k]['account'] = $user['account'];
                $re[$k]['times'] = date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status'] = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location'] = $v['end_location'];
                $re[$k]['price'] = $v['price'];
                $re[$k]['order_type'] = $v['order_type'];
                $re[$k]['order_status'] = $v['order_status'];
                $re[$k]['status'] = $v['status'];
                $re[$k]['number'] = $v['number'];
                $re[$k]['tip_price'] = $v['tip_price'];
                $re[$k]['protect_price'] = $v['protect_price'];
                $re[$k]['distribution_km'] = $v['distribution_km'];
                $re[$k]['start_longitude'] = $v['start_longitude'];
                $re[$k]['start_latitude'] = $v['start_latitude'];
                $re[$k]['end_longitude'] = $v['end_longitude'];
                $re[$k]['end_latitude'] = $v['end_latitude'];
            }
        }
        return $re;
    }
    /**
     * 获取订单列表 ing
     * @param $con
     * @return mixed
     */
    public function get_order_lists_ing($con)
    {
        $where = 'driver_id = '.$con['driver_id'].' and order_status = 3 or order_status = 4 or order_status = 5 or order_status = 6 or order_status = 7';
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img'] = $user['head_img'];
                $re[$k]['name'] = $user['name'];
                $re[$k]['account'] = $user['account'];
                $re[$k]['times'] = date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status'] = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location'] = $v['end_location'];
                $re[$k]['price'] = $v['price'];
                $re[$k]['order_type'] = $v['order_type'];
                $re[$k]['order_status'] = $v['order_status'];
                $re[$k]['status'] = $v['status'];
                $re[$k]['number'] = $v['number'];
                $re[$k]['tip_price'] = $v['tip_price'];
                $re[$k]['protect_price'] = $v['protect_price'];
                $re[$k]['distribution_km'] = $v['distribution_km'];
                $re[$k]['start_longitude'] = $v['start_longitude'];
                $re[$k]['start_latitude'] = $v['start_latitude'];
                $re[$k]['end_longitude'] = $v['end_longitude'];
                $re[$k]['end_latitude'] = $v['end_latitude'];
            }
        }
        return $re;
    }
    /**
     * 评价订单
     * @param $id
     * @param $content
     */
    public function evaluate( $id , $content )
    {
        $where['id'] = array ( 'eq' , $id );
        $this->where( $where )->save( array ( 'evaluate' => $content ) );
    }
    /**
     * 订单状态
     * @param $k
     * @return array|mixed
     */
    public function status($k)
    {
        $arr = array(
            1 => '待派单',
            2 => '已完成',
            3 => '已撤销',
            4 => '未完成'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 线下派单
     * @param $order_id
     * @param $driver_id
     * @return bool
     */
    public function assign_send($order_id, $driver_id)
    {
        $where['id'] = array('eq', $order_id);

        $save = [
            'driver_id' => $driver_id,
            'status' => '4', // 未完成
        ];
        $temp = $this->where($where)->save($save);

        return $temp;
    }

    /**
     * 改变状态
     * @param $order_intercity_id
     * @param $status
     * @return bool
     */
    public function change_status($order_intercity_id, $status)
    {
        $where['id'] = array('eq', $order_intercity_id);
        $save = array(
            'status' => $status
        );
        $temp = $this->where($where)->save($save);
        return $temp;
    }

    /**
     * @param $where
     * @return array|bool|mixed|string|null
     *
     */

    public function getWhereInfo($where)
    {

        return $this->where($where)->find();
    }
}