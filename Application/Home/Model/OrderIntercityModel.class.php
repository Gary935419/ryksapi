<?php
namespace Home\Model;
use Think\Model;

class OrderIntercityModel extends Model {

    /**
     * 坐车模式
     * @param $k
     * @return array|mixed
     */
    public function car_mode($k) {
        $arr = array(
            1 => '拼车',
            2 => '包车',
            3 => '带货'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 订单状态
     * @param $k
     * @return array|mixed
     */
    public function status($k) {
        $arr = array(
            1 => '待接单',
            2 => '待接驾',
            3 => '乘客上车',
            4 => '已开始',
            5 => '已支付',
            6 => '已完成',
            7 => '已取消',
            8 => '已撤销',
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 订单状态
     * @param $k
     * @return array|mixed
     */
    public function status_online($k) {
        $arr = array(
            6 => '已完成',
            9 => '未完成',
            10 => '线下乘客上车'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 线上线下
     * @param $k
     * @return array|mixed
     */
    public function line($k) {
        $arr = array(
            1 => '线上',
            2 => '线下'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 添加订单信息
     * @param $data
     * @return mixed
     */
    public function add_order($data) {
        $data['add_time'] = time();
        $data['status'] = 1;
        $re = $this->add($data);
        return $re;
    }

    /**
     * 更新订单信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function set_order($id, $data) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }

    /**
     * 获取订单详情
     * @param $order_id
     * @return mixed
     */
    public function get_info($order_id) {
        $where['id'] = array('eq', $order_id);
        $data = $this->where($where)->find();
        return $data;
    }

    /**
     * 获取司机ID
     * @param $order_id
     * @return mixed
     */
    public function get_driver_id($order_id) {
        $where['id'] = array('eq', $order_id);
        $driver_id = $this->where($where)->getField('driver_id');
        return $driver_id;
    }

    /**
     * 线下派单
     * @param $order_id
     * @param $driver_id
     * @return bool
     */
    public function assign_send($order_id, $driver_id) {
        $where['id'] = array('eq', $order_id);

        $save = [
            'driver_id' => $driver_id,
            'status' => '6', // 已完成
        ];
        $temp = $this->where($where)->save($save);

        return $temp;
    }

    /**
     * 线上派单
     * @param $order_id
     * @param $waiting_id
     * @return null
     */
    public function online_send($order_id, $waiting_id = null) {
        // ----- 模型 -----
        $userWorkingModel  = new \Home\Model\UserWorkingModel();
        $orderWaitingModel = new \Home\Model\OrderWaitingModel();

        // ----- 订单详情 -----
        $where['id'] = array('eq', $order_id);
        $order = $this->where($where)->find();

        // ----- 按条件搜索司机 -----
        $condition = [
            'taker_type_id'  => '1', // 城际拼车(1)
            'car_type_id'    => $order['car_type_id'],
            'longitude'      => $order['longitude'],
            'latitude'       => $order['latitude'],
            'surplus_seat'   => $order['people_num'],
            'route_city_id1' => $order['route_city_id1'],
            'route_city_id2' => $order['route_city_id2'],
            'small_order_id' => $order['id']
        ];
        $driver_id = $userWorkingModel->search_working_driver($condition);

        // ----- 等待订单信息 -----
        $waiting_data = [
            'taker_type_id'  => '1', // 城际拼车(1)
            'driver_id'      => $driver_id ? $driver_id : '',
            'user_id'        => $order['user_id'],
            'order_id'       => $order['id'],
            'add_time'       => time()
        ];
        if ($waiting_id) {
            $orderWaitingModel->set_order($waiting_id, $waiting_data);
        } else {
            $waiting_id = $orderWaitingModel->add_waiting($waiting_data);
        }

        // ----- 派单 -----
        if ($driver_id != 0) { // 已找到司机
            $working = $userWorkingModel->get_working($driver_id);
            $save_working = [
                'status_send' => '1', // 设置派单状态为正在派单(1)
                'been_order' => $working['been_order'].'['.$order['id'].']' // 已推订单ID集
            ];
            $userWorkingModel->set_working($driver_id, $save_working);

            $title = '如邮快送宝';
            $content = '您有一个新的订单';
            $extras = [
                'taker_type_id' => '1', // 城际拼车(1)
                'waiting_id'    => $waiting_id
            ];
            $JModel = new \Home\Model\JpushModel();
            $JModel->sj_send_alias($driver_id, $title, $content, $extras); // 发送司机消息
        }
    }

    /**
     * 返回人数
     * @param $id
     * @return mixed
     */
    public function get_people_num($id) {
        $where['id'] = array('eq', $id);
        $people_num = $this->where($where)->getField('people_num');
        return $people_num;
    }

    /**
     * 改变状态
     * @param $order_intercity_id
     * @param $status
     * @return bool
     */
    public function change_status($order_intercity_id, $status) {
        $where['id'] = array('eq', $order_intercity_id);
        $save = array(
            'status' => $status
        );
        $temp = $this->where($where)->save($save);
        return $temp;
    }

    /**
     * 返回上车人数
     * @param $order_id
     * @return mixed
     */
    public function get_connected_num($order_id) {
        $where  = 'order_id = '.$order_id;
        $where .= ' AND ( status = 2 OR status = 3 OR status = 4 OR status = 5 OR status = 6 )'; // (2)待接驾(3)乘客上车(4)已开始(5)已支付(6)已完成
        $people_num = $this->where($where)->sum('people_num');
        if ($people_num) {
            return $people_num;
        } else {
            return '0';
        }
    }

    /**
     * 获取上车列表
     * @param $order_id
     * @return mixed
     */
    public function get_connected_lists($order_id) {
        $where  = 'order_id = '.$order_id;
        $where .= ' AND ( status = 2 OR status = 3 OR status = 4 OR status = 5 OR status = 6 )'; // (2)待接驾(3)乘客上车(4)已开始(5)已支付(6)已完成
        $lists = $this->where($where)->order('id ASC')->select();
        return $lists;
    }

    /**
     * 取消订单
     * @param $id
     */
    public function cancel_order($id) {
        $OrderWaitingModel = new \Home\Model\OrderWaitingModel();
        $UserWorkingModel = new \Home\Model\UserWorkingModel();

        $where['id'] = array('eq', $id);
        $order = $this->get_info($id);

        // ----- 根据状态来取消订单 ----- //
        if ($order['status'] == 1) { // 待接单(1)

            $this->where($where)->save(array('status' => '7')); // 已取消(7)

            $waiting = $OrderWaitingModel->get_user_info($order['user_id']);
            $OrderWaitingModel->user_del_order($order['user_id']); // 删除等待订单

            $UserWorkingModel->set_working($waiting['driver_id'], array('status_send' => '0')); // 还原该司机上班推送状态

        } elseif ($order['status'] == 2) { // 待接驾(2)

            $this->where($where)->save(array('status' => '7')); // 已取消(7)

            $surplus_seat = $UserWorkingModel->get_surplus_seat($order['driver_id']);
            $UserWorkingModel->set_working($order['driver_id'], array('surplus_seat' => $surplus_seat + $order['people_num'])); // 加回剩余座位

            // 是否是大单里最后一个小单
            $where_order  = 'order_id = '.$order['order_id'];
            $where_order .= ' AND ( status = 2 OR status = 3 )'; // (2)待接驾(3)乘客上车
            $count = $this->where($where_order)->count();
            if ($count == 0) {
                $UserWorkingModel->set_working($order['driver_id'], array('status' => '1')); // 空闲(1)

                M('order')->where('id = '.$order['order_id'])->save(array('status' => '4')); // 已取消(4)
            }
            
        }
    }

    /**
     * 获取该用户正在进行的单子
     * @param $user_id
     * @return mixed
     */
    public function get_user_order($user_id) {
        $where  = 'user_id = '.$user_id;
        $where .= ' AND ( status = 1 OR status = 2 OR status = 3 OR status = 4 )'; // (1)待接单(2)待接驾(3)乘客上车(4)已开始
        $where .= ' AND line = 1'; // 线上
        $order = $this->where($where)->find();
        return $order;
    }

    /**
     * 乘客上车
     * @param $id
     */
    public function user_aboard($id) {
        $where['id'] = array('eq', $id);
        $this->where($where)->save(array('status' => '3')); // 乘客上车(3)
    }

    /**
     * 开始行程
     * @param $order_id
     */
    public function start_trip($order_id) {
        $order_model = new \Home\Model\OrderModel();
        $order = $order_model->get_info($order_id);

        // ----- 小单 -----
        $lists = $this->get_connected_lists($order_id);
        if ($lists) {
            foreach ($lists as $k => $v) {
                $save_status = [
                    'status' => '4'
                ];
                $this->where('id = '.$v['id'])->save($save_status); // 开始行程
            }
        }

        // ----- 大单 -----
        M('order')->where('id = '.$order_id)->save(array('status' => '2')); // 开始行程

        // ----- 司机上班 -----
        $user_working_model = new \Home\Model\UserWorkingModel();
        $user_working_model->set_working($order['driver_id'], array('status' => '3')); // 行程中
    }

    /**
     * 支付
     * @param $id
     * @return string
     */
    public function pay($id) {
        $RouteModel = new \Home\Model\RouteModel();
        $CouponModel = new \Home\Model\CouponModel();

        $where['id'] = array('eq', $id);
        
        // 优惠券
        $order = $this->get_info($id);
        $route = $RouteModel->to_city_get_route($order['route_city_id1'], $order['route_city_id2']);
        if ($route['nature'] == '1') { // 长途
            $coupon = $CouponModel->get_coupon($order['user_id'], '2');
        } elseif ($route['nature'] == '2') { // 短途
            $coupon = $CouponModel->get_coupon($order['user_id'], '1');
        }
        if (!empty($coupon['money'])) {
            $set_coupon = $coupon['money'];
        }

        // 使用优惠券
        $number = date('YmdHis').rand(1000, 9999);
        $this->where($where)->save(array('number' => $number, 'coupon' => $set_coupon, 'coupon_id' => $coupon['id'])); // 支付订单编号
        
        return $number;
    }

    /**
     * 支付成功
     * @param $number
     */
    public function pay_success($number) {
        $where['number'] = array('eq', $number);
        $this->where($where)->save(array('status' => '5')); // 已支付(5)
    }

    /**
     * 评价教练
     * @param $id
     * @param $content
     */
    public function evaluate($id, $content) {
        $where['id'] = array('eq', $id);
        $this->where($where)->save(array('evaluate' => $content));
    }

    /**
     * 完成
     * @param $id
     */
    public function small_ok($id) {
        $where['id'] = array('eq', $id);
        $this->where($where)->save(array('status' => '6')); // 已完成(6)
    }

    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_lists($con) {
        $where  = 'user_id = '.$con['id'];
        $where .= ' AND driver_id != 0';
        $where .= ' AND ( status = 5 OR status = 6 OR status = 7 OR status = 8 )';
        $page = $con['page'] ? $con['page']  : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page-1)*$limit.",".$limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $RouteCityModel = new \Home\Model\RouteCityModel();
                $user = $UserModel->get_info($v['driver_id']);
                $re[$k]['order_small_id']   = $v['id'];
                $re[$k]['head_img']         = $user['head_img'];
                $re[$k]['name']             = $user['name'];
                $re[$k]['account']          = $user['account'] ? $user['account'] : '';
                $re[$k]['attribute']        = $user['attribute'];
                $re[$k]['times']            = $v['order_time'] ? $v['order_time'] : date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status']           = ($v['status'] == '6' && $v['line'] == '2') ? $v['status_online'] : $v['status'];
                $re[$k]['people_num']       = $v['people_num'];
                $re[$k]['route_city_font1'] = $v['location']; // $RouteCityModel->get_city_name($v['route_city_id1']);
                $re[$k]['route_city_font2'] = $v['arrival_position']; // $RouteCityModel->get_city_name($v['route_city_id2']);
                $re[$k]['price']            = $v['price'];
                $re[$k]['evaluate']         = $v['evaluate'];
                $re[$k]['is_appointment']   = $v['order_time'] ? '1' : '2';
            }
        }
        return $re;
    }

    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_order_lists($con) {
        $where  = 'user_id = '.$con['id'];
        $where .= ' AND driver_id = 0';
        $page = $con['page'] ? $con['page']  : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page-1)*$limit.",".$limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $RouteCityModel = new \Home\Model\RouteCityModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id']   = $v['id'];
                $re[$k]['head_img']         = $user['head_img'];
                $re[$k]['name']             = $user['name'];
                $re[$k]['attribute']        = $user['attribute'];
                $re[$k]['times']            = $v['order_time'] ? $v['order_time'] : date('Y-m-d H:i:s', $v['add_time']);
                $re[$k]['status']           = ($v['status'] == '6' && $v['line'] == '2') ? $v['status_online'] : $v['status'];
                $re[$k]['people_num']       = $v['people_num'];
                $re[$k]['route_city_font1'] = $v['location']; // $RouteCityModel->get_city_name($v['route_city_id1']);
                $re[$k]['route_city_font2'] = $v['arrival_position']; // $RouteCityModel->get_city_name($v['route_city_id2']);
                $re[$k]['price']            = $v['price'];
                $re[$k]['evaluate']         = $v['evaluate'];
                $re[$k]['is_appointment']   = $v['order_time'] ? '1' : '2';
            }
        }
        return $re;
    }

    /**
     * 获取行程详情
     * @param $id
     * @return mixed
     */
    public function get_trip_details($id) {
        $where['id'] = array('eq', $id);
        $order = $this->where($where)->find();
        $re = [];
        if ($order) {
            $UserModel = new \Home\Model\UserModel();
            $RouteCityModel = new \Home\Model\RouteCityModel();
            $user = $UserModel->get_info($order['driver_id']);
            $re['order_small_id']   = $order['id'];
            $re['head_img']         = $user['head_img'];
            $re['name']             = $user['name'];
            $re['account']          = $user['account'];
            $re['car_number']       = $user['car_number'];
            $re['attribute']        = $user['attribute'];
            $re['times']            = $order['order_time'] ? $order['order_time'] : date('Y-m-d H:i:s', $order['add_time']);
            $re['status']           = ($order['status'] == '6' && $order['line'] == '2') ? $order['status_online'] : $order['status'];
            $re['people_num']       = $order['people_num'];
            $re['route_city_font1'] = $order['location']; // $RouteCityModel->get_city_name($order['route_city_id1']);
            $re['route_city_font2'] = $order['arrival_position']; // $RouteCityModel->get_city_name($order['route_city_id2']);
            $re['price']            = $order['price'];
            $re['coupon']           = $order['coupon'];
            $re['evaluate']         = $order['evaluate'];
            $re['is_appointment']   = $order['order_time'] ? '1' : '2';
        }
        return $re;
    }

    /**
     * 获取订单列表
     * @param $con
     * @return mixed
     */
    public function get_order_lists($con) {
        $where  = 'driver_id = '.$con['id'];
        $page = $con['page'] ? $con['page']  : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page-1)*$limit.",".$limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        $re = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel = new \Home\Model\UserModel();
                $RouteCityModel = new \Home\Model\RouteCityModel();
                $user = $UserModel->get_info($v['user_id']);
                $re[$k]['order_small_id']   = $v['id'];
                $re[$k]['head_img']         = $user['head_img'];
                $re[$k]['name']             = $user['name'];
                $re[$k]['account']          = $user['account'];
                $re[$k]['times']            = $v['order_time'] ? $v['order_time'] : date('Y-m-d H:i', $v['add_time']);
                
                if ($v['status'] == '6' && $v['line'] == '2') {
                    $re[$k]['status'] = $v['status_online'];
                } elseif ($v['status'] == '5' && $v['line'] == '2' && $v['status_online'] == '10') {
                    $re[$k]['status'] = '11';
                } else {
                    $re[$k]['status'] = $v['status'];
                }
                
                $re[$k]['people_num']       = $v['people_num'];
                $re[$k]['route_city_font1'] = $v['location']; // $RouteCityModel->get_city_name($v['route_city_id1']);
                $re[$k]['route_city_font2'] = $v['arrival_position']; // $RouteCityModel->get_city_name($v['route_city_id2']);
                $re[$k]['price']            = $v['price'];
                $re[$k]['is_appointment']   = $v['order_time'] ? '1' : '2';
            }
        }
        return $re;
    }

    /**
     * 根据大订单获取车型ID
     * @param $order_id
     * @return mixed
     */
    public function to_order_get_car($order_id) {
        $where['order_id'] = array('eq', $order_id);
        $where['status'] = array('eq', '6');
        $car_type_id = $this->where($where)->getField('car_type_id');
        return $car_type_id;
    }
}