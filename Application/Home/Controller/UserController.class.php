<?php

namespace Home\Controller;

use Home\Model\OrderComplaint;
use Home\Model\OrderComplaintModel;
use Think\Controller;
use Home\Model\UserModel;
use Home\Model\VerifyCodeModel;
use Home\Model\TakerTypeModel;
use Home\Model\RouteCityModel;

/**
 * Class UserController
 * @package Home\Controller
 * @property UserModel $UserModel
 * @property VerifyCodeModel $VerifyCodeModel
 * @property TakerTypeModel $TakerTypeModel
 * @property RouteCityModel $RouteCityModel
 * @property OrderComplaintModel $OrderComplaintModel
 */
class UserController extends CommonController
{

    private $UserModel;
    private $VerifyCodeModel;
    private $TakerTypeModel;
    private $RouteCityModel;
    private $OrderComplaintModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->UserModel           = new UserModel();
        $this->VerifyCodeModel     = new VerifyCodeModel();
        $this->TakerTypeModel      = new TakerTypeModel();
        $this->RouteCityModel      = new RouteCityModel();
        $this->OrderComplaintModel = new OrderComplaintModel();
    }

    /**
     * 获取验证码
     */
    public function get_verify_code()
    {
        $data = self::$_DATA;

        if (empty( $data['account'] )) {
            echoOk( 301 , '必填项不能为空' );
        }
        $code = rand(100000,999999); // 随机验证码
        $this->VerifyCodeModel->add_code($data['account'],$code); // 添加验证码

        echoOk( 200 , '发送成功', $code);
    }

    /**
     * 登录
     */
    public function login()
    {
        $data = self::$_DATA;
        if (empty( $data['type'] ) || empty( $data['account'] ) || empty( $data['code'] )) {
            echoOk( 301 , '必填项不能为空' );
        }
        $this->VerifyCodeModel->is_code( $data['account'] , $data['code'] ); // 判断验证码
        $user_id_flg = $this->UserModel->is_account_flg( $data['type'] , $data['account'] );
        if ($user_id_flg) {
            echoOk( 301 , '该账号已经锁定,目前无法登录！' );
        }
        if ($data['type'] == 1) { // 用户端
            if ($data['loginCode']) {
                $wxUserInfo = $this->get_user_open_id( $data['loginCode'] );
                if ($wxUserInfo) {
                    $openId = $wxUserInfo['openid'];
                }
            }
            $user_id = $this->UserModel->is_account( $data['type'] , $data['account'] , $openId ); // 登录
            if ($user_id) {
                echoOk( 200 , '登录成功' , $user_id );
            }
            $user_id = $this->UserModel->user_register( '1' , $data['account'] , $openId ); // 注册
            echoOk( 200 , '登录成功' , $user_id );
        } elseif ($data['type'] == 2) { // 司机端
            $user_id = $this->UserModel->is_account( $data['type'] , $data['account'] ); // 登录
            if ($user_id) {
                echoOk( 200 , '登录成功' , $user_id );
            }
            $user_id = $this->UserModel->user_register( '2' , $data['account'] ,''); // 注册
            echoOk( 200 , '登录成功' , $user_id );
        }
    }

    /**
     * 用户所有信息
     */
    public function info()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        $data = $this->UserModel->get_info( $data['id'] );
        if ($data) {
//            $taker_type = $this->TakerTypeModel->get_lists();
//            $data['taker_type_font']  = $data['taker_type_id'] ? $taker_type[$data['taker_type_id'] - 1]['name'] : '';
//            $data['route_city_font1'] = $this->RouteCityModel->get_city_name($data['route_city_id1']);
//            $data['route_city_font2'] = $this->RouteCityModel->get_city_name($data['route_city_id2']);
            $data['taker_type_id'] = '2';
            $data['invitation_code1_up'] = empty($data['invitation_code1_up'])?'无邀请':$data['invitation_code1_up'];
            echoOk( 200 , '获取成功' , $data );
        } else {
            echoOk( 301 , '没有数据' , [] );
        }
    }

    /**
     * 司机认证
     */
    public function probate()
    {
        $data = self::$_DATA;

        //check_type 认证模式1跑腿 2代驾
        $imgInfo = uploadImg( '' );
        if (empty( $imgInfo['img_cards_face'] ) || empty( $imgInfo['img_cards_side'] )) {
            echoOk( 301 , '图片上传失败' );
        } else {
            //正面
            $url['img_cards_face'] = $imgInfo['img_cards_face']['path'];
            //反面
            $url['img_cards_side'] = $imgInfo['img_cards_side']['path'];
            //驾驶证
            $url['img_drivers']    = $imgInfo['img_drivers']['path'];
            //行驶证
            $url['img_vehicle']    = $imgInfo['img_vehicle']['path'];
            //人车合影
            $url['img_car_user']   = $imgInfo['img_car_user']['path'];
            //工作照
            $url['img_worker']     = $imgInfo['img_worker']['path'];
        }

        if (empty( $data['id'] ) || empty( $data['name'] ) || empty( $data['cards'] || empty( $data['sex'] ) )) {
            echoOk( 301 , '必填项不能为空' );
        }


        if ($data['check_type'] == 1) {
//          跑腿认证
            $check     = 3;
            $save_data = [
                'sex'            => $data['sex'] ,
                'name'           => $data['name'] ,
                'brand'          => $data['brand'] ,
                'cards'          => $data['cards'] ,
                'times'          => $data['times'] ,
                'car_number'     => $data['car_number'] ,
                'img_cards_face' => $url['img_cards_face'] ,
                'img_cards_side' => $url['img_cards_side'] ,
                'img_drivers'    => $url['img_drivers'] ,
                'img_vehicle'    => $url['img_vehicle'] ,
                'img_car_user'   => $url['img_car_user'] ,
                'img_worker'       => $url['img_worker'] ,
                'car_type_id'    => $data['car_type_id'] ,
                'attribute'      => $data['attribute'] ,
                'head_img'       => $url['img_worker'] ,
                'user_check'     => $check , //跑腿认证
            ];
        } else {

            $userInfo = $this->UserModel->get_info( $data['id'] );

            //  代驾认证
            $drivingCheck = 3;

            if ($userInfo['user_check'] != 1) {
                $save_data = [
                    'sex'                    => $data['sex'] ,
                    'head_img'               => $url['img_worker'] ,
                    'driving_name'           => $data['name'] ,
                    'driving_cards'          => $data['cards'] ,
                    'driving_times'          => $data['times'] ,
                    'driving_car_number'     => $data['car_number'] ,
                    'driving_img_cards_face' => $url['img_cards_face'] ,
                    'driving_img_cards_side' => $url['img_cards_side'] ,
                    'driving_img_drivers'    => $url['img_drivers'] ,
                    'driving_img_worker'    =>  $url['img_worker'] ,
                    'driving_car_type_id'    => $data['car_type_id'] ,
                    'driving_attribute'      => $data['attribute'] ,
                    'driving_check'          => $drivingCheck ,//代驾认证
                ];
            } else {
                $save_data = [
                    'sex'                    => $data['sex'] ,
                    'driving_name'           => $data['name'] ,
                    'driving_cards'          => $data['cards'] ,
                    'driving_times'          => $data['times'] ,
                    'driving_car_number'     => $data['car_number'] ,
                    'driving_img_cards_face' => $url['img_cards_face'] ,
                    'driving_img_cards_side' => $url['img_cards_side'] ,
                    'driving_img_drivers'    => $url['img_drivers'] ,
                    'driving_img_worker'    =>  $url['img_worker'] ,
                    'driving_car_type_id'    => $data['car_type_id'] ,
                    'driving_attribute'      => $data['attribute'] ,
                    'driving_check'          => $drivingCheck ,//代驾认证
                ];
            }
        }

        $temp = $this->UserModel->save_info( $data['id'] , $save_data );

        if ($temp) {
            echoOk( 200 , '提交成功' );
        } else {
            echoOk( 301 , '提交失败' );
        }
    }
    /**
     * 司机认证修改 后台
     */
    public function probate_updata()
    {
        $data = self::$_DATA;

        //check_type 认证模式1跑腿 2代驾

        if (empty( $data['img_cards_face'] ) || empty( $data['img_cards_side'] )) {
            echoOk( 301 , '图片上传失败' );
        } else {
            //正面
            $url['img_cards_face'] = $data['img_cards_face'];
            //反面
            $url['img_cards_side'] = $data['img_cards_side'];
            //驾驶证
            $url['img_drivers']    = $data['img_drivers'];
            //行驶证
            $url['img_vehicle']    = $data['img_vehicle'];
            //人车合影
            $url['img_car_user']   = $data['img_car_user'];
            //工作照
            $url['img_worker']     = $data['img_worker'];
        }

        if (empty( $data['id'] )) {
            echoOk( 301 , '用户ID不能为空' );
        }

        if ($data['check_type'] == 1) {
//          跑腿认证
            $save_data = [
                'sex'            => $data['sex'] ,
                'name'           => $data['name'] ,
                'brand'          => $data['brand'] ,
                'cards'          => $data['cards'] ,
                'times'          => $data['times'] ,
                'car_number'     => $data['car_number'] ,
                'car_type_id'    => $data['car_type_id'] ,
                'attribute'      => $data['attribute'] ,
                'img_cards_face' => $url['img_cards_face'] ,
                'img_cards_side' => $url['img_cards_side'] ,
                'img_drivers'    => $url['img_drivers'] ,
                'img_vehicle'    => $url['img_vehicle'] ,
                'img_car_user'   => $url['img_car_user'] ,
                'img_worker'     => $url['img_worker'] ,
//                'head_img'       => $url['img_worker'] ,
                'user_check'     => 1 , //跑腿认证
            ];
        } else {
            //  代驾认证
            $save_data = [
                'sex'                    => $data['sex'] ,
                'driving_name'           => $data['name'] ,
                'driving_cards'          => $data['cards'] ,
                'driving_times'          => $data['times'] ,
                'driving_car_number'     => $data['car_number'] ,
                'driving_car_type_id'    => $data['car_type_id'] ,
                'driving_attribute'      => $data['attribute'] ,
                'driving_img_cards_face' => $url['img_cards_face'] ,
                'driving_img_cards_side' => $url['img_cards_side'] ,
                'driving_img_drivers'    => $url['img_drivers'] ,
                'driving_img_worker'     => $url['img_worker'] ,
                'driving_check'          => 1 ,//代驾认证
            ];
        }
        $temp = $this->UserModel->save_info( $data['id'] , $save_data );
        if ($temp) {
            echoOk( 200 , '操作成功' );
        } else {
            echoOk( 301 , '操作失败' );
        }
    }
    /**
     * 修改个人资料
     */
    public function personal()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        if ($_FILES) {
            $imgInfo = uploadImg( '' );
            if (!empty( $imgInfo['head_img'] )) {
                $save_data['head_img'] = $imgInfo['head_img']['path'];
            }
        }

        if ($data['name']) {
            $save_data['name'] = $data['name'];
        }

        if ($data['invitation_code']) {
            $save_data['invitation_code2_up'] = $data['invitation_code'];
        }
        $temp = $this->UserModel->save_info( $data['id'] , $save_data );

        if ($temp) {
            echoOk( 200 , '修改成功' );
        } else {
            echoOk( 301 , '修改失败' );
        }
    }

    private function get_user_open_id( $code )
    {
        //换key
        $secret = "c586ce22143c00638c75ac5bf81907e1";
        $appid  = "wx95ff8ddda8027413";

        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $appid . "&secret=" . $secret . "&js_code=" . $code . "&grant_type=authorization_code";

        $ch = curl_init();
        curl_setopt( $ch , CURLOPT_URL , $url );
        curl_setopt( $ch , CURLOPT_RETURNTRANSFER , 1 );
        curl_setopt( $ch , CURLOPT_TIMEOUT , 30 );

        $content = curl_exec( $ch );
        $status  = (int)curl_getinfo( $ch , CURLINFO_HTTP_CODE );
        if ($status == 404) {
            return $status;
        }
        curl_close( $ch );

        return json_decode( $content , true );
    }

    //修改手机号
    public function edit_phone()
    {

        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['phone'] ) || empty( $data['code'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        if ($data['account'] != '13388889999') {
            $this->VerifyCodeModel->is_code( $data['phone'] , $data['code'] ); // 判断验证码
        }

        $where    = 'account = ' . $data['phone'] . ' AND type =2';
        $userdata = $this->UserModel->get_user_by_account( $where );

        if ($userdata) {
            echoOk( 301 , '手机号已注册' );
        }

        if ($data['phone']) {
            $save_data['account'] = $data['phone'];
        }

        $temp = $this->UserModel->save_info( $data['id'] , $save_data );

        if ($temp) {
            echoOk( 200 , '修改成功' );
        } else {
            echoOk( 301 , '修改失败' );
        }

    }

    //注销
    public function logoff()
    {
        $data = self::$_DATA;

        $uid = $data['id'];

        $changeData['is_logoff'] = 1;
        $this->UserModel->where( [ 'id' => $uid ] )->save( $changeData );

        echoOk( 200 , '操作成功' );
    }


    //获取认证信息
    public function get_probate()
    {
        $data = self::$_DATA;

        if (empty( $data['id'] ) || empty( $data['type'] )) {
            echoOk( 301 , '必填项不能为空' );
        }
        $where['id'] = $data['id'];

        //跑腿认证
        if ($data['type'] == 1) {
            $where['user_check'] = 1;

        } elseif ($data['type'] == 2) {
            $where['driving_check'] = 1;
        }

        $info = $this->UserModel->getWhereInfo( $where );

        echoOk( 200 , '获取成功' , $info );

    }

    //报备处理
    public function complaint()
    {
        $data = self::$_DATA;
//        id 司机id orderId 订单id content 报备内容 type 2司机 1乘客
        if (empty( $data['order_type'] ) || empty( $data['id'] ) || empty( $data['orderId'] ) || empty( $data['content'] ) || empty( $data['type'] )) {
            echoOk( 301 , '必填项不能为空' );
        }

        $infoWhere['uid']      = $data['id'];
        $infoWhere['order_id'] = $data['orderId'];
        $infoWhere['order_type'] = $data['order_type'];
        $info                  = $this->OrderComplaintModel->where( $infoWhere )->find();

        if ($info) {
            echoOk( 301 , '订单已经报备了,请重新选择!' );
        }

        $complaintData['uid']      = $data['id'];
        $complaintData['order_id'] = $data['orderId'];
        $complaintData['content']  = $data['content'];
        $complaintData['type']     = $data['type'];
        $complaintData['order_type']     = $data['order_type'];
        $complaintData['dateline'] = time();
        $status                    = $this->OrderComplaintModel->add( $complaintData );

        if ($status) {
            echoOk( 200 , '报备成功' );
        } else {
            echoOk( 301 , '报备失败' );
        }
    }


}