<?php

namespace Home\Model;

use Think\Cache\Driver\Db;
use Think\Model;

class OrderTownModel extends Model
{

    /**
     * 添加订单信息
     * @param $data
     * @return mixed
     */
    public function add_order( $data )
    {
        $data['add_time'] = time();
        $data['status']   = 1;
        $re               = $this->add( $data );
        return $re;
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
     * 线上派单
     * @param $order_id
     * @param $waiting_id
     * @return null
     */
    public function online_send( $order_id , $waiting_id = null )
    {
        // ----- 模型 -----
        $userWorkingModel  = new \Home\Model\UserWorkingModel();
        $orderWaitingModel = new \Home\Model\OrderWaitingModel();

        // ----- 订单详情 -----
        $where['id'] = array ( 'eq' , $order_id );
        $order       = $this->where( $where )->find();

        // ----- 按条件搜索司机 -----
        $condition = [
            'taker_type_id'  => '2' , // 市区出行(2)
            'car_type_id'    => $order['car_type_id'] ,
            'longitude'      => $order['start_longitude'] ,
            'latitude'       => $order['start_latitude'] ,
            'small_order_id' => $order['id']
        ];
        $driver_id = $userWorkingModel->search_working_driver( $condition );

        // ----- 等待订单信息 -----
        $waiting_data = [
            'taker_type_id' => '2' , // 市区出行(2)
            'driver_id'     => $driver_id ? $driver_id : '' ,
            'user_id'       => $order['user_id'] ,
            'order_id'      => $order['id'] ,
            'add_time'      => time()
        ];
        if ($waiting_id) {
            $orderWaitingModel->set_order( $waiting_id , $waiting_data );
        } else {
            $waiting_id = $orderWaitingModel->add_waiting( $waiting_data );
        }

        // ----- 派单 -----
        if ($driver_id != 0) { // 已找到司机
            $working      = $userWorkingModel->get_working( $driver_id );
            $save_working = [
                'status_send' => '1' , // 设置派单状态为正在派单(1)
                'been_order'  => $working['been_order'] . '[' . $order['id'] . ']' // 已推订单ID集
            ];
            $userWorkingModel->set_working( $driver_id , $save_working );

            $title   = '如邮快送';
            $content = '您有一个新的订单';
            $extras  = [
                'taker_type_id' => '2' , // 市区出行(1)
                'waiting_id'    => $waiting_id
            ];
            $JModel  = new \Home\Model\JpushModel();
            $rs      = $JModel->sj_send_alias( $driver_id , $title , $content , $extras ); // 发送司机消息
        }
    }

    public function online_send_new( $order_id , $waiting_id = null )
    {
        // ----- 模型 -----
        $userWorkingModel = new \Home\Model\UserWorkingModel();
//        $orderWaitingModel = new \Home\Model\OrderWaitingModel();

        // ----- 订单详情 -----
        $where['id'] = array ( 'eq' , $order_id );
        $order       = $this->table( 'order_traffic' )->where( $where )->find();

        // ----- 按条件搜索司机 -----
        $condition = [
            'taker_type_id'  => '2' , // 市区出行(2)
            'car_type_id'    => $order['car_type_id'] ,
            'longitude'      => $order['start_longitude'] ,
            'latitude'       => $order['start_latitude'] ,
            'small_order_id' => $order['id']
        ];
        $driver_id = $userWorkingModel->search_working_driver( $condition );


        $waiting_id = $order_id;
        // ----- 派单 -----
        if ($driver_id != 0) { // 已找到司机

            $title   = '如邮快送';
            $content = '您有一个新的订单';
            $extras  = [
                'taker_type_id' => '2' , // 市区出行(1)
                'waiting_id'    => $waiting_id
            ];
            $JModel  = new \Home\Model\JpushModel();

            if (is_array( $driver_id )) {
                foreach ($driver_id as $item) {
                    $res = $JModel->sj_send_alias( $item , $title , $content , $extras ); // 发送司机消息

                    $data['dateline']  = date( 'Y-m-d H:i:s',time() );
                    $data['driver_id'] = $item;
                    $data['order_id']  = $order_id;
                    $data['res']       = json_encode( $res );
                    $this->table( 'driver_send_log' )->add( $data );

                }
            }
        }
    }

    /**
     * 获取该用户正在进行的单子
     * @param $user_id
     * @return mixed
     */
    public function get_user_order( $user_id )
    {
        $where = 'user_id = ' . $user_id;
        $where .= ' AND ( status = 1 OR status = 2 OR status = 3 OR status = 4 )'; // (1)待接单(2)待接驾(3)乘客上车(4)已开始
        $order = $this->where( $where )->find();
        return $order;
    }

    /**
     * 取消订单
     * @param $id
     */
    public function cancel_order( $id )
    {
        $OrderWaitingModel = new \Home\Model\OrderWaitingModel();
        $UserWorkingModel  = new \Home\Model\UserWorkingModel();

        $where['id'] = array ( 'eq' , $id );
        $order       = $this->get_info( $id );

        // ----- 根据状态来取消订单 ----- //
        if ($order['status'] == 1) { // 待接单(1)

            $this->where( $where )->save( array ( 'status' => '7' ) ); // 已取消(7)

            $waiting = $OrderWaitingModel->get_user_info( $order['user_id'] );
            $OrderWaitingModel->user_del_order( $order['user_id'] ); // 删除等待订单

            $UserWorkingModel->set_working( $waiting['driver_id'] , array ( 'status_send' => '0' ) ); // 还原该司机上班推送状态

        } elseif ($order['status'] == 2) { // 待接驾(2)

            $this->where( $where )->save( array ( 'status' => '7' ) ); // 已取消(7)

            $UserWorkingModel->set_working( $order['driver_id'] , array ( 'status' => '1' ) ); // 空闲(1)

        }
    }

    /**
     * 改变状态
     * @param $order_id
     * @param $status
     * @return bool
     */
    public function change_status( $order_id , $status )
    {
        $where['id'] = array ( 'eq' , $order_id );
        $save        = array (
            'status' => $status
        );
        $temp        = $this->where( $where )->save( $save );
        return $temp;
    }

    /**
     * 获取订单详情
     * @param $order_id
     * @return mixed
     */
    public function get_info( $order_id )
    {
        $where['id'] = array ( 'eq' , $order_id );
        $data        = $this->where( $where )->find();
        return $data;
    }

    /**
     * 用户获取订单详情
     * @param $order_id
     * @return mixed
     */
    public function user_get_info( $order_id )
    {
        $where['id'] = array ( 'eq' , $order_id );
        $data        = $this->where( $where )->find();
        return $data;
    }

    /**
     * 更新订单信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function set_order( $id , $data )
    {
        $where['id'] = array ( 'eq' , $id );
        $temp        = $this->where( $where )->save( $data );
        return $temp;
    }

    /**
     * 上班获取订单ID
     * @param $driver_id
     * @return mixed
     */
    public function work_get_id( $driver_id )
    {
        $where = 'driver_id = ' . $driver_id;
        $where .= ' AND ( status = 2 OR status = 3 OR status = 4 OR status = 5 )';
        $id    = $this->where( $where )->getField( 'id' );
        if ($id) {
            return $id;
        } else {
            return '0';
        }
    }

    /**
     * 乘客上车
     * @param $id
     */
    public function user_aboard( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $this->where( $where )->save( array ( 'status' => '3' ) ); // 乘客上车(3)
    }

    /**
     * 开始行程
     * @param $id
     */
    public function start_trip( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $order       = $this->get_info( $id );
        $this->where( $where )->save( array ( 'status' => '4' ) ); // 开始行程

        // ----- 司机上班 -----
        $user_working_model = new \Home\Model\UserWorkingModel();
        $user_working_model->set_working( $order['driver_id'] , array ( 'status' => '3' ) ); // 行程中
    }

    /**
     * 支付
     * @param $id
     * @return string
     */
    public function pay( $id )
    {
        $number      = date( 'YmdHis' ) . rand( 1000 , 9999 );
        $where['id'] = array ( 'eq' , $id );
        $this->where( $where )->save( array ( 'number' => $number ) ); // 支付订单编号
        return $number;
    }

    /**
     * 支付成功
     * @param $number
     */
    public function pay_success( $number )
    {
        $where['number'] = array ( 'eq' , $number );
        $this->where( $where )->save( array ( 'status' => '5' ) ); // 已支付(5)
    }

    /**
     * 完成
     * @param $id
     */
    public function small_ok( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $order       = $this->get_info( $id );
        $this->where( $where )->save( array ( 'status' => '6' ) ); // 已完成(6)

        // 上班状态
        $user_working        = new \Home\Model\UserWorkingModel();
        $working_save_status = [
            'status' => '1' , // 状态:空闲(1)
        ];
        $user_working->set_working( $order['driver_id'] , $working_save_status );
    }

    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_lists( $con )
    {
        $where  = 'user_id = ' . $con['id'];
        $where  .= ' AND driver_id != 0';
        $where  .= ' AND ( status = 5 OR status = 6 OR status = 7 )';
        $page   = $con['page'] ? $con['page'] : 1;
        $limit  = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order  = 'id DESC';
        $lists  = $this->where( $where )->limit( $limit1 )->order( $order )->select();
        $re     = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel                = new \Home\Model\UserModel();
                $user                     = $UserModel->get_info( $v['driver_id'] );
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img']       = $user['head_img'];
                $re[$k]['name']           = $user['name'];
                $re[$k]['account']        = $user['account'] ? $user['account'] : '';
                $re[$k]['attribute']      = $user['attribute'];
                $re[$k]['times']          = date( 'Y-m-d H:i:s' , $v['add_time'] );
                $re[$k]['status']         = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location']   = $v['end_location'];
                $re[$k]['price']          = $v['price'];
                $re[$k]['evaluate']       = $v['evaluate'];
            }
        }
        return $re;
    }
    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_town_order_lists($con)
    {
        $where = 'user_id = ' . $con['id'];
        $where .= ' AND order_status > 1 ';
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
            }
        }
        return $re;
    }
    /**
     * 获取行程列表
     * @param $con
     * @return mixed
     */
    public function get_trip_order_lists( $con )
    {
        $where = 'user_id = ' . $con['id'];
//        $where .= ' AND driver_id = 0';
        $page   = $con['page'] ? $con['page'] : 1;
        $limit  = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order  = 'id DESC';
        $lists  = $this->where( $where )->limit( $limit1 )->order( $order )->select();
        $re     = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel                = new \Home\Model\UserModel();
                $user                     = $UserModel->get_info( $v['user_id'] );
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img']       = $user['head_img'];
                $re[$k]['name']           = $user['name'];
                $re[$k]['attribute']      = $user['attribute'];
                $re[$k]['times']          = date( 'Y-m-d H:i:s' , $v['add_time'] );
                $re[$k]['status']         = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location']   = $v['end_location'];
                $re[$k]['price']          = $v['price'];
                $re[$k]['evaluate']       = $v['evaluate'];
            }
        }
        return $re;
    }

    /**
     * 获取行程详情
     * @param $id
     * @return mixed
     */
    public function get_trip_details( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $order       = $this->where( $where )->find();
        $re          = [];
        if ($order) {
            $UserModel            = new \Home\Model\UserModel();
            $user                 = $UserModel->get_info( $order['driver_id'] );
            $re['order_small_id'] = $order['id'];
            $re['head_img']       = $user['head_img'];
            $re['name']           = $user['name'];
            $re['account']        = $user['account'];
            $re['car_number']     = $user['car_number'];
            $re['attribute']      = $user['attribute'];
            $re['times']          = date( 'Y-m-d H:i:s' , $order['add_time'] );
            $re['status']         = $order['status'];
            $re['start_location'] = $order['start_location'];
            $re['end_location']   = $order['end_location'];
            $re['price']          = $order['price'];
            $re['evaluate']       = $order['evaluate'];
        }
        return $re;
    }

    /**
     * 获取订单列表
     * @param $con
     * @return mixed
     */
    public function get_order_lists( $con )
    {
        $where  = 'driver_id = ' . $con['id'];
        $page   = $con['page'] ? $con['page'] : 1;
        $limit  = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order  = 'id DESC';
        $lists  = $this->where( $where )->limit( $limit1 )->order( $order )->select();
        $re     = [];
        if ($lists) {
            foreach ($lists as $k => $v) {
                $UserModel                = new \Home\Model\UserModel();
                $user                     = $UserModel->get_info( $v['user_id'] );
                $re[$k]['order_small_id'] = $v['id'];
                $re[$k]['head_img']       = $user['head_img'];
                $re[$k]['name']           = $user['name'];
                $re[$k]['account']        = $user['account'];
                $re[$k]['times']          = date( 'Y-m-d H:i:s' , $v['add_time'] );
                $re[$k]['status']         = $v['status'];
                $re[$k]['start_location'] = $v['start_location'];
                $re[$k]['end_location']   = $v['end_location'];
                $re[$k]['price']          = $v['price'];
                $re[$k]['order_status']   = $v['order_status'];

            }
        }
        return $re;
    }

    /**
     * 订单状态
     * @param $k
     * @return array|mixed
     */
    public function status( $k )
    {
        $arr = array (
            1 => '待接单' ,
            2 => '待接驾' ,
            3 => '乘客上车' ,
            4 => '已开始' ,
            5 => '已支付' ,
            6 => '已完成' ,
            7 => '已取消' ,
            8 => '没人接'
        );

        if (!empty( $k )) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 评价教练
     * @param $id
     * @param $content
     */
    public function evaluate( $id , $content )
    {
        $where['id'] = array ( 'eq' , $id );
        $this->where( $where )->save( array ( 'evaluate' => $content ) );
    }
}