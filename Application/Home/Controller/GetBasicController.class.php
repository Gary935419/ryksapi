<?php

namespace Home\Controller;

use Think\Controller;
use Home\Model\TakerTypeModel;
use Home\Model\RouteModel;
use Home\Model\RoutePriceModel;
use Home\Model\CarTypeModel;
use Home\Model\SetConfigModel;
use Home\Model\UserModel;

/**
 * Class GetBasicController
 * @package Home\Controller
 * @property TakerTypeModel $TakerTypeModel
 * @property RouteModel $RouteModel
 * @property RoutePriceModel $RoutePriceModel
 * @property CarTypeModel $CarTypeModel
 * @property SetConfigModel $SetConfigModel
 * @property UserModel $UserModel
 */
class GetBasicController extends CommonController
{

    private $UserModel;
    private $TakerTypeModel;
    private $RouteModel;
    private $RoutePriceModel;
    private $CarTypeModel;
    private $SetConfigModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->UserModel       = new UserModel();
        $this->TakerTypeModel  = new TakerTypeModel();
        $this->RouteModel      = new RouteModel();
        $this->RoutePriceModel = new RoutePriceModel();
        $this->CarTypeModel    = new CarTypeModel();
        $this->SetConfigModel  = new SetConfigModel();
    }

    /**
     * 获取接单模式
     */
    public function taker_type()
    {
        $data = self::$_DATA;

        $lists = $this->TakerTypeModel->get_lists();

        if (!empty( $data['id'] )) {
            $user_info = $this->UserModel->get_info( $data['id'] );
            if ($user_info['taker_type_id'] == 3) {
                $arr[] = $lists[2];
                echoOk( 200 , '获取成功' , $arr );
            } elseif ($user_info['taker_type_id'] == 1 || $user_info['taker_type_id'] == 2) {
                unset( $lists[2] );
            }
        }

        echoOk( 200 , '获取成功' , $lists );
    }

    /**
     * 获取车型
     */
    public function car_type()
    {
//        $data = self::$_DATA;

        $lists = $this->CarTypeModel->get_car_lists( 1 );

        if ($lists) {
            echoOk( 200 , '获取成功' , $lists );
        } else {
            echoOk( 301 , '没有数据' , $lists );
        }
    }

    /**
     * 获取线路
     */
    public function route()
    {
        $data = self::$_DATA;

        $lists = $this->RouteModel->get_lists();

        if ($lists) {
            if (!empty( $data['car_type_id'] )) { // 车型ID
                $name = $this->CarTypeModel->get_car_name( $data['car_type_id'] );
                if (!empty( $name )) {
                    $cozy_type4 = $this->SetConfigModel->get_content( 'cozy_type4' );
                    foreach ($lists as $k => $v) {
                        $route_price             = $this->RoutePriceModel->get_price( $v['id'] , $data['car_type_id'] );
                        $lists[$k]['real_price'] = $route_price['real_price'];
                        $lists[$k]['sale_price'] = $route_price['sale_price'];
                        $lists[$k]['tishi']      = $route_price['tishi'];
                        $lists[$k]['cozy_type4'] = $cozy_type4;
                    }
                }
                foreach ($lists as $kk => $vv) {
                    if (empty( $vv['sale_price'] ) || $vv['sale_price'] == '0') {
                        unset( $lists[$kk] );
                    }
                }
            }
            echoOk( 200 , '获取成功' , array_values( $lists ) );
        } else {
            echoOk( 301 , '没有数据' , $lists );
        }
    }

    /**
     * 获取协议
     */
    public function get_deal()
    {
        $data = self::$_DATA;

        $set_config = '';
        if ($data['type'] == 1 || empty($data['type'])) { // 用户端 专车送  顺风送
            $set_config = $this->SetConfigModel->get_content( 'user_mini12' );
        } elseif ($data['type'] == 4) { // 司机端
            $set_config = $this->SetConfigModel->get_content( 'driver_deal' );
        } elseif ($data['type'] == 2) { // 用户端 代买
            $set_config = $this->SetConfigModel->get_content( 'user_mini3' );
        } elseif ($data['type'] == 3) { // 用户端 代驾
            $set_config = $this->SetConfigModel->get_content( 'user_mini4' );
        }elseif ($data['type'] == 5) { // 用户端 个人信息保护与隐私政策及法律声明
            $set_config = $this->SetConfigModel->get_content( 'agreement1' );
        }elseif ($data['type'] == 6) { // 用户端 注册协议
            $set_config = $this->SetConfigModel->get_content( 'agreement2' );
        } elseif ($data['type'] == 7) { // 用户端 特别声明
            $set_config = $this->SetConfigModel->get_content( 'agreement3' );
        } elseif ($data['type'] == 8) { // 用户端 代买协议
            $set_config = $this->SetConfigModel->get_content( 'agreement4' );
        } elseif ($data['type'] == 9) { // 用户端 代价协议
            $set_config = $this->SetConfigModel->get_content( 'agreement5' );
        } elseif ($data['type'] == 10) { // 用户端 商城协议
            $set_config = $this->SetConfigModel->get_content( 'agreement6' );
        } elseif ($data['type'] == 11) { // 用户端 商城入驻协议
            $set_config = $this->SetConfigModel->get_content( 'agreement7' );
        } elseif ($data['type'] == 12) { // 用户端 计费说明
            $set_config = $this->SetConfigModel->get_content( 'agreement8' );
        }

        echoOk( 200 , '获取成功' , $set_config );
    }

    /**
     * 获取文本内容
     */
    public function get_text_info()
    {
        $data = self::$_DATA;
        
        $jijiaText         = $this->SetConfigModel->get_content( 'user_jijia' );
        $kaipiaoText       = $this->SetConfigModel->get_content( 'user_kaipiao' );
        $zhinanText        = $this->SetConfigModel->get_content( 'user_zhinan' );
        $wentiText         = $this->SetConfigModel->get_content( 'user_wenti' );
        $phoneText         = $this->SetConfigModel->get_content( 'user_contact_us' );
        $user_about_usText = $this->SetConfigModel->get_content( 'user_about_us' );
//        $user_yingyezhizhao = $this->SetConfigModel->get_content('user_yingyezhizhao');
        $user_info         = $this->UserModel->get_user($data['user_id']);
        $set_config['jijia']           = $jijiaText;
        $set_config['kaipiao']         = $kaipiaoText;
        $set_config['zhinan']          = $zhinanText;
        $set_config['wenti']           = $wentiText;
        $set_config['user_contact_us'] = $phoneText;
        $set_config['user_info'] = empty($user_info['invitation_code1_up'])?'1':'2';
//        $set_config['user_about_us'] = $user_about_usText;
//        $set_config['user_yingyezhizhao'] = $user_yingyezhizhao; //营业执照
        $set_config['wx_gongzhonghao'] = '如邮快运';
        $set_config['email']           = 'rysy@163.com';
        $set_config['zhizhao']         = 'https://ryks.dltqwy.com/Public/images/system/DOC_2020062817292200161_000.jpg';
        $set_config['zhaomu_text']     = 'XXXX招募文案';
        $set_config['zhaomu_code']     = 'https://ryks.dltqwy.com/Public/images/system/code.png';

        echoOk( 200 , '获取成功' , $set_config );
    }

    public function get_agreement_list()
    {

        $data[] = [ 'title' => '个人信息保护与隐私政策及法律声明' , 'content' => '1' ];
        $data[] = [ 'title' => '软件使用协议' , 'content' => '2' ];
        $data[] = [ 'title' => '注册协议' , 'content' => '3' ];
        $data[] = [ 'title' => '合作协议' , 'content' => '4' ];
        $data[] = [ 'title' => '服务规则' , 'content' => '5' ];
        $data[] = [ 'title' => '安全规则' , 'content' => '6' ];
        $data[] = [ 'title' => '计价规则' , 'content' => '7' ];
        $data[] = [ 'title' => '平台监督规则' , 'content' => '8' ];
        $data[] = [ 'title' => '用户取消规则' , 'content' => '9' ];
        $data[] = [ 'title' => '快送员取消规则' , 'content' => '10' ];
        $data[] = [ 'title' => '司机接单不服务' , 'content' => '11' ];
        $data[] = [ 'title' => '司机诱导取消' , 'content' => '12' ];
        $data[] = [ 'title' => '诱导用户线下交易' , 'content' => '13' ];
        $data[] = [ 'title' => '车辆准入标准' , 'content' => '14' ];
        $data[] = [ 'title' => '附加费收取方式' , 'content' => '15' ];

        echoOk( 200 , '获取成功' , $data );

    }
}