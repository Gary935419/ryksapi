<?php

namespace Home\Model;

use Think\Cache\Driver\Db;
use Think\Model;

class OrderModel extends Model
{

    /**
     * 添加大单
     * @param $data
     * @return mixed
     */
    public function add_order( $data )
    {
        $re = $this->add( $data );
        return $re;
    }

    /**
     * 获取大单ID
     * @param $driver_id
     * @return mixed
     */
    public function get_id( $driver_id )
    {
        $where['driver_id'] = array ( 'eq' , $driver_id );
        $where['status']    = array ( 'eq' , '1' );
        $id                 = $this->where( $where )->getField( 'id' );
        return $id;
    }

    /**
     * 上班获取订单ID
     * @param $driver_id
     * @return mixed
     */
    public function work_get_id( $driver_id )
    {
        $where = 'driver_id = ' . $driver_id;
        $where .= ' AND ( status = 1 OR status = 2 )';
        $id    = $this->where( $where )->getField( 'id' );
        if ($id) {
            return $id;
        } else {
            return '0';
        }
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function get_info( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $re          = $this->where( $where )->find();
        return $re;
    }

    /**
     * 订单支付成功
     * @param $number
     */
    public function pay_success( $number )
    {
        $OrderIntercityModel = new \Home\Model\OrderIntercityModel();
        $OrderTownModel      = new \Home\Model\OrderTownModel();
        $OrderIntercityModel->pay_success( $number );
        $OrderTownModel->pay_success( $number );
    }

    /**
     * 完成
     * @param $id
     */
    public function order_ok( $id ,$driver_id = 0)
    {

        $where['id'] = array ( 'eq' , $id );
        $order       = $this->where( $where )->find();

        if($driver_id ==0){
            $driver_id = $order['driver_id'];
        }

        $this->where( $where )->save( array ( 'status' => '3' ) );

        // 上班状态
        $user_working        = new \Home\Model\UserWorkingModel();
        $working_save_status = [
            'status'       => '1' , // 状态:空闲(1)
            'surplus_seat' => '0'
        ];
        $user_working->set_working( $driver_id , $working_save_status );
    }

}