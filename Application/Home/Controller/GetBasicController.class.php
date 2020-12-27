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
        $set_config['zhizhao']         = 'http://ryks.ychlkj.cn/Public/images/system/DOC_2020062817292200161_000.jpg';
        $set_config['zhaomu_text']     = 'XXXX招募文案';
        $set_config['zhaomu_code']     = 'http://ryks.ychlkj.cn/Public/images/system/code.png';

        echoOk( 200 , '获取成功' , $set_config );
    }

    public function get_agreement_list()
    {

        $data[] = [ 'title' => '司机服务协议' , 'url' => 'www.baidu.com' ];
        $data[] = [ 'title' => '计费说明' , 'url' => 'www.baidu.com' ];

        echoOk( 200 , '获取成功' , $data );

    }
}