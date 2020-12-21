<?php

namespace Home\Controller;

use Home\Model\OrderExtendModel;
use Home\Model\OrderTrafficModel;
use Think\Controller;
use Home\Model\UserModel;
use Home\Model\UserWorkingModel;
use Home\Model\RouteCityModel;
use Home\Model\CarTypeModel;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderWaitingModel;
use Home\Model\OrderModel;
use Home\Model\CouponModel;

/**
 * Class DriverPickController
 * @package Home\Controller
 * @property UserModel $UserModel
 * @property UserWorkingModel $UserWorkingModel
 * @property RouteCityModel $RouteCityModel
 * @property CarTypeModel $CarTypeModel
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderWaitingModel $OrderWaitingModel
 * @property OrderModel $OrderModel
 * @property CouponModel $CouponModel
 * @property OrderExtendModel $OrderExtendModel
 */
class DriverPickController extends CommonController
{

    private $UserModel;
    private $UserWorkingModel;
    private $RouteCityModel;
    private $CarTypeModel;
    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderWaitingModel;
    private $OrderModel;
    private $CouponModel;
    private $OrderTrafficModel;
    private $OrderExtendModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->UserModel           = new UserModel();
        $this->UserWorkingModel    = new UserWorkingModel();
        $this->RouteCityModel      = new RouteCityModel();
        $this->CarTypeModel        = new CarTypeModel();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel      = new OrderTownModel();
        $this->OrderWaitingModel   = new OrderWaitingModel();
        $this->OrderModel          = new OrderModel();
        $this->CouponModel         = new CouponModel();
        $this->OrderTrafficModel   = new OrderTrafficModel();
        $this->OrderExtendModel    = new OrderExtendModel();
    }

    /**
     * 司机接单信息
     */
    public function get_basic()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        $data = $this->UserModel->get_user( $data['id'] );
        if ($data) {
            $working                     = $this->UserWorkingModel->get_working( $data['id'] );
            $user['seat']                = $this->CarTypeModel->get_car_seat( $data['car_type_id'] );
            $user['route_city_id1']      = $data['route_city_id1'] ?: '';
            $user['route_city_id2']      = $data['route_city_id2'] ?: '';
            $user['route_city_font1']    = $this->RouteCityModel->get_city_name( $data['route_city_id1'] ) ?: '';
            $user['route_city_font2']    = $this->RouteCityModel->get_city_name( $data['route_city_id2'] ) ?: '';
            $user['taker_type_id']       = '2';
            $user['car_type_id']         = $data['car_type_id'];
            $user['working_status']      = $this->UserWorkingModel->get_working_status( $data['id'] );
            $user['working_status_type'] = $working['taker_type_id'] ? $working['taker_type_id'] : '0';
            $user['order_id']            = $this->OrderTrafficModel->work_get_id( $data['id'] ) ?: '0';

            echoOk( 200 , '获取成功' , $user );
        } else {
            echoOk( 301 , '没有数据' , [] );
        }
    }

    /**
     * 上班
     */
    public function working()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['taker_type_id'] ) || empty( $data['longitude'] ) ||
            empty( $data['latitude'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        $status = $this->UserWorkingModel->get_working_status( $data['id'] ); // 上班状态
        if ($status != '0') {
            echoOk( 301 , '操作失败,您已上班' );
        }

        $add  = [
            'driver_id'      => $data['id'] ,
            'taker_type_id'  => $data['taker_type_id'] ,
            'car_type_id'    => $data['car_type_id'] ,
            'status'         => '1' ,
            'longitude'      => $data['longitude'] ,
            'latitude'       => $data['latitude'] ,
            'surplus_seat'   => $data['surplus_seat'] ? $data['surplus_seat'] : '' ,
            'route_city_id1' => $data['route_city_id1'] ? $data['route_city_id1'] : '' ,
            'route_city_id2' => $data['route_city_id2'] ? $data['route_city_id2'] : '' ,
            'update_time'    => time()
        ];
        $temp = $this->UserWorkingModel->add_working( $add );
        if ($temp) {
            echoOk( 200 , '操作成功' );
        } else {
            echoOk( 301 , '操作失败' );
        }
    }

    /**
     * 更新坐标
     */
    public function update_coordinate()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['longitude'] ) || empty( $data['latitude'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        $save = [
            'longitude'   => $data['longitude'] ,
            'latitude'    => $data['latitude'] ,
            'update_time' => time()
        ];
        $temp = $this->UserWorkingModel->set_working( $data['id'] , $save );
        if ($temp) {
            echoOk( 200 , '操作成功' );
        } else {
            echoOk( 301 , '操作失败' );
        }
    }

    /**
     * 下班
     */
    public function off_duty()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        $temp = $this->UserWorkingModel->del_working( $data['id'] );
        if ($temp) {
            echoOk( 200 , '操作成功' );
        } else {
            echoOk( 301 , '操作失败' );
        }
    }

    /**
     * 获取弹窗消息
     */
    public function get_popup()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['waiting_id'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        // 获取显示信息
        $user  = $this->UserModel->get_info( $data['id'] );
        $order = $this->OrderTrafficModel->get_info( $data['waiting_id'] );
        $re    = [
            'head_img'       => $user['head_img'] ,
            'name'           => $user['name'] ,
            'start_location' => $order['start_location'] ,
            'end_location'   => $order['end_location'] ,
            'order_type'     => '1' ,
        ];

        echoOk( 200 , '获取成功' , $re );

    }

    /**
     * 操作弹窗订单
     */
    public function handle_popup()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['waiting_id'] ) || empty( $data['handle'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        switch ($data['handle']) {
            case 1: // 接单

                sleep( 0.5 );
                $orderInfo = $this->OrderTrafficModel->where( [ 'id' => $data['waiting_id'] ] )->find();

                if (!empty( $orderInfo['driver_id'] )) {
                    echoOk( 301 , '已经接单' );
                    break;
                }

                $this->OrderWaitingModel->startTrans();

                // 2) ----- 改变小单状态 -----
                $order_save = [
                    'driver_id'    => $data['id'] ,
                    'status'       => '2' ,// 小单状态: 2 订单开始
                    'order_status' => '3' // 3 已接单
                ];
                $this->OrderTrafficModel->set_order( $data['waiting_id'] , $order_save );

                // 3) ----- 改变司机派送状态、上班状态 -----
                $working_save = [
                    'status_send' => '0' ,
                    'status'      => '3' , // 状态:行程中(3)
                ];
                $this->UserWorkingModel->set_working( $data['id'] , $working_save );

                $this->OrderWaitingModel->commit();


                //发送取货码
                $driverInfo = $this->UserModel->get_info( $data['id'] );

                if ($driverInfo) {
                    $orderExtendInfo = $this->OrderExtendModel->where( [ 'order_id' => $data['waiting_id'] ] )->find();
                    $text            = '您所接订单的取货码为:' . $orderExtendInfo['pick_up_code'];
                    $a               = $this->send_code( $driverInfo['account'] , $text );

                }

                //绑定虚拟号码
//                $bingMobileData = $this->bingMobile();

                echoOk( 200 , '接单成功' , $data['waiting_id'] );
                break;
            case 2: // 拒单
                echoOk( 200 , '操作成功' );
                break;
        }
    }
//    }

    /**
     * 获取订单信息_不知道干啥
     */
    public function get_order()
    {
        $data = self::$_DATA;

        if (empty( $data['order_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        $order = $this->OrderModel->get_info( $data['order_id'] );
        if (!$order) {
            echoOk( 301 , '没有数据' , [] );
        }

        $user_working = $this->UserWorkingModel->get_working( $order['driver_id'] );
        if (!$user_working) {
            echoOk( 301 , '没有数据' , [] );
        }

        // 路线
        $route_city_id1 = $this->RouteCityModel->get_city_name( $user_working['route_city_id1'] );
        $route_city_id2 = $this->RouteCityModel->get_city_name( $user_working['route_city_id2'] );

        // 小单列表
        $order_lists = [];
        $lists       = $this->OrderIntercityModel->get_connected_lists( $data['order_id'] );
        if ($lists) {
            foreach ($lists as $k => $v) {
                $user                                = $this->UserModel->get_info( $v['user_id'] );
                $order_lists[$k]['order_small_id']   = $v['id'];
                $order_lists[$k]['head_img']         = $user['head_img'];
                $order_lists[$k]['name']             = $user['name'];
                $order_lists[$k]['account']          = $v['other'] ? $v['other'] : $user['account'];
                $order_lists[$k]['people_num']       = $v['people_num'];
                $order_lists[$k]['location']         = $v['location'];
                $order_lists[$k]['arrival_position'] = $v['arrival_position'];
                $order_lists[$k]['longitude']        = $user['longitude'];
                $order_lists[$k]['latitude']         = $user['latitude'];
                $order_lists[$k]['route']            = $route_city_id1 . ' - ' . $route_city_id2;
                $order_lists[$k]['status']           = $v['status'];
                $order_lists[$k]['order_status']     = $v['order_status'];
            }
        }

        $re = [
            'route'        => $route_city_id1 . ' - ' . $route_city_id2 ,
            'connected'    => $this->OrderIntercityModel->get_connected_num( $data['order_id'] ) ,
            'surplus_seat' => $user_working['surplus_seat'] ,
            'status'       => $order['status'] ,
            'lists'        => $order_lists
        ];

        echoOk( 200 , '获取成功' , $re );
    }

    /**
         * 获取订单信息_跑腿订单
     */
    public function town_get_order()
    {
        $data = self::$_DATA;

        if (empty( $data['order_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        $order = $this->OrderTrafficModel->get_info( $data['order_id'] );
        if (!$order) {
            echoOk( 301 , '没有数据' , [] );
        }

        $user_working = $this->UserWorkingModel->get_working( $order['driver_id'] );
        if (!$user_working) {
            echoOk( 301 , '没有数据' , [] );
        }

        $user = $this->UserModel->get_info( $order['user_id'] );

        $re = [
            'status'          => $order['status'] ,
            'order_small_id'  => $order['id'] ,
            'head_img'        => $user['head_img'] ,
            'name'            => $user['name'] ,
            'account'         => $user['account'] ,
            'start_location'  => $order['start_location'] ,
            'start_longitude' => $order['start_longitude'] ,
            'start_latitude'  => $order['start_latitude'] ,
            'end_location'    => $order['end_location'] ,
            'end_longitude'   => $order['end_longitude'] ,
            'end_latitude'    => $order['end_latitude'] ,
            'longitude'       => $user['longitude'] ,
            'latitude'        => $user['latitude'] ,
            'order_status'    => $order['order_status'] ,
        ];

        echoOk( 200 , '获取成功' , $re );
    }

    /**
     * 取消订单
     */
    public function cancel()
    {
        $data = self::$_DATA;

        if (empty( $data['taker_type_id'] ) || empty( $data['order_small_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }
//
//        switch ($data['taker_type_id']) {
//            case 1: // 城际拼车

        $orderInfo = $this->OrderTrafficModel->where( [ 'id' => $data['order_small_id'] ] )->find();

        if ($orderInfo['order_status'] != 9) {
            $status = $this->OrderTrafficModel->cancel_order( $data['order_small_id'] ); // 取消订单

            if ($status) {
                echoOk( 200 , '操作成功' );

            } else {
                echoOk( 301 , '操作失败' );

            }
        } else {
            echoOk( 301 , '订单已经取消' );

        }
//                break;
//            case 2: // 市区出行
//                $this->OrderTownModel->cancel_order($data['order_small_id']);
//                break;
//            case 3: // 同城货运
//                break;
//        }

    }

    /**
     * 乘客上车
     */
    public function aboard()
    {
        $data = self::$_DATA;

        if (empty( $data['taker_type_id'] ) || empty( $data['order_small_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        switch ($data['taker_type_id']) {
            case 1: // 城际拼车
                $this->OrderIntercityModel->user_aboard( $data['order_small_id'] ); // 乘客上车
                break;
            case 2: // 市区出行
                $this->OrderTownModel->user_aboard( $data['order_small_id'] ); // 乘客上车
                break;
            case 3: // 同城货运
                break;
        }

        echoOk( 200 , '操作成功' );
    }

    /**
     * 开始行程
     */
    public function start_trip()
    {
        $data = self::$_DATA;

        if (empty( $data['taker_type_id'] ) || empty( $data['order_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        switch ($data['taker_type_id']) {
            case 1: // 城际拼车
                $this->OrderIntercityModel->start_trip( $data['order_id'] ); // 开始行程
                break;
            case 2: // 市区出行
                $this->OrderTrafficModel->start_trip( $data['order_id'] ); // 开始行程
                break;
            case 3: // 同城货运
                break;
        }

        echoOk( 200 , '操作成功' );
    }

    /**
     * 完成小单
     */
    public function small_ok()
    {
        $data = self::$_DATA;

        if (empty( $data['taker_type_id'] ) || empty( $data['order_small_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        switch ($data['taker_type_id']) {
            case 1: // 城际拼车
                $this->OrderIntercityModel->small_ok( $data['order_small_id'] ); // 完成小单
                $order = $this->OrderIntercityModel->get_info( $data['order_small_id'] );

                // 发优惠券
                $this->CouponModel->send_coupon( $order['user_id'] );
                break;
            case 2: // 市区出行
                $this->OrderTrafficModel->small_ok( $data['order_small_id'] ); // 完成小单
                break;
            case 3: // 同城货运
                break;
        }

        echoOk( 200 , '操作成功' );
    }

    /**
     * 完成大单
     */
    public function order_ok()
    {
        $data = self::$_DATA;

        if (empty( $data['taker_type_id'] ) || empty( $data['order_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        switch ($data['taker_type_id']) {
            case 1: // 城际拼车
                $this->OrderModel->order_ok( $data['order_id'] ); // 完成大单

                break;
            case 2: // 市区出行
                break;
            case 3: // 同城货运
                break;
        }

        echoOk( 200 , '操作成功' );
    }

    //绑定关系号
    public function AxB( $fm , $tm )
    {
        //C.保密号模式接口 1. 关系虚号呼转接口_AxB模式绑定
        $url       = "http://sandbox.teleii.com/testApi/autoCallTransferForSp.do";
        $spId      = "705"; //teleii平台分配的商户id
        $spKey     = "GJKVBNHBLEUEKO3VQVK2MYZO"; //teleii平台分配的商户key
        $timestamp = time() . "000"; //毫秒级的时间戳
        $seqId     = $timestamp;
//        $fm            = "15140565551"; //主叫号码
//        $tm            = "18642852237"; //被叫号码
        $virtualMobile = ""; //虚拟号码.创建新关系时，虚拟号码为空,系统自行分配虚拟号码。
        $bindTime      = 10;//关系绑定10分钟

//Md5(key+id+seqId+timestamp+fm+tm)； 生成签名
        $sign_source = $spKey . $spId . $seqId . $timestamp . $fm . $tm;
        $sign        = md5( $sign_source );

//拼接HTTP请求参数
        $curlparams = "id=" . $spId . "&timestamp=" . $timestamp . "&seqId=" . $seqId . "&fm=" . $fm . "&tm=" . $tm . "&bindTime=" . $bindTime . "&virtualMobile=" . $virtualMobile . "&sign=" . $sign;

//打印HTTP的参数
//        echo "<br />".$sign_source;
//        echo "<br/>sign:".$sign;
//        echo "<br/>".$curlparams."<br/>";

//发起HTTP请求
        $result = $this->postUrlForCalling( $url , $curlparams );
        echo "result:" . $result; //打印HTTP请求结果
    }

    private function postUrlForCalling( $url , $reqParams )
    {
        $ch = curl_init();
        curl_setopt( $ch , CURLOPT_URL , $url );
        curl_setopt( $ch , CURLOPT_HEADER , 0 );
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt( $ch , CURLOPT_POST , 1 );
        curl_setopt( $ch , CURLOPT_POSTFIELDS , $reqParams );
        $data = curl_exec( $ch );
        curl_close( $ch );
        return $data;
    }

    public function bingMobile( $orderId )
    {
//        $orderInfo = $this->OrderTrafficModel->


    }

    //司机取货，上传取货照片
    public function pickUp()
    {
        $data = self::$_DATA;
        $imgInfo                   = uploadImg( '' );

        if (empty( $data['order_id'] )) {
            echoOk( 301 , '字段不能为空' );
        }

        if (empty( $imgInfo['goods_image'] )) {
            echoOk( 301 , '图片上传失败' );
        } else {
            $extendData['goods_image'] = $imgInfo['goods_image']['path'];
            $this->OrderExtendModel->where( [ 'order_id' => $data['order_id'] ] )->save( $extendData );
        }

        $orderData['order_status'] = 7;//7前往目的地
        $this->OrderTrafficModel->where( [ 'id' => $data['order_id'] ] )->save( $orderData );

        echoOk( 200 , '提交成功' );

    }

    /**
     * 变更订单状态
     */
    public function change_order_status()
    {
        $data = self::$_DATA;

        if (empty( $data['order_status'] ) || empty( $data['order_id'] )) {
            echoOk( 301 , '必填项不能为空' , [] );
        }

        $where['id']               = $data['order_id'];
        $orderData['order_status'] = $data['order_status'];
        $status                    = $this->OrderTrafficModel->where( $where )->save( $orderData );

        if ($status) {
            echoOk( 200 , '修改成功' );
        } else {
            echoOk( 301 , '修改失败' );
        }


    }

    public function get_info()
    {


        $re['company_address'] = '大连市甘井子区姚胜街14号1-1-5';
        $re['company_name']    = '爱度网络信息科技（大连）有限公司';
        echoOk( 200 , '获取成功' , $re );

    }

    public function check_code()
    {

        $data = self::$_DATA;
        if (empty( $data['pick_up_code'] ) || empty( $data['order_id'] )) {
            echoOk( 301 , '字段不能为空' );
        }

        $orderExtendInfoWhere['order_id']     = $data['order_id'];
        $orderExtendInfoWhere['pick_up_code'] = $data['pick_up_code'];
        $orderExtendInfo                      = $this->OrderExtendModel->where( $orderExtendInfoWhere )->find();

        if ($orderExtendInfo) {

            $orderData['order_status'] = 8;//7前往目的地
            $this->OrderTrafficModel->where( [ 'id' => $data['order_id'] ] )->save( $orderData );

            $extendData['pick_up_time'] = time();
            $this->OrderExtendModel->where( [ 'order_id' => $data['order_id'] ] )->save( $extendData );


            $orderInfo = $this->OrderTrafficModel->getWhereInfo( [ 'id' => $data['order_id'] ] );

            $this->OrderModel->order_ok( $orderExtendInfo['big_order_id'] , $orderInfo['driver_id'] ); // 完成大单

            echoOk( 200 , '提交成功' );
//            echoOk( 301 , json_encode($data));
        } else {
            echoOk( 301 , '验证码错误' );
        }
    }
}