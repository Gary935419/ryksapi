<?php

namespace Home\Controller;

use Home\Model\OrderExtend;
use Home\Model\OrderExtendModel;
use Think\Controller;
use Home\Model\TakerTypeModel;
use Home\Model\SetConfigModel;
use Home\Model\CarTypeModel;
use Home\Model\CarPriceSettingModel;
use Home\Model\UserAddressModel;
use Home\Model\TimeSlotModel;
use Home\Model\TelephoneModel;
use Home\Model\RouteModel;
use Home\Model\RouteCityModel;
use Home\Model\UserModel;
use Home\Model\UserWorkingModel;
use Home\Model\OrderModel;
use Home\Model\MerchantsApplyModel;
use Home\Model\MerchantsImgModel;
use Home\Model\TopupModel;
use Home\Model\CouponsetModel;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderInvoiceModel;
use Home\Model\OrderTrafficModel;
use Home\Model\CouponModel;
use Home\Model\BalanceRecordModel;

/**
 * Class UserCallController
 * @package Home\Controller
 * @property MerchantsImgModel $MerchantsImgModel
 * @property TakerTypeModel $TakerTypeModel
 * @property SetConfigModel $SetConfigModel
 * @property CarTypeModel $CarTypeModel
 * @property UserAddressModel $UserAddressModel
 * @property CarPriceSettingModel $CarPriceSettingModel
 * @property TimeSlotModel $TimeSlotModel
 * @property TelephoneModel $TelephoneModel
 * @property RouteModel $RouteModel
 * @property UserModel $UserModel
 * @property UserWorkingModel $UserWorkingModel
 * @property OrderModel $OrderModel
 * @property MerchantsApplyModel $MerchantsApplyModel
 * @property TopupModel $TopupModel
 * @property CouponsetModel $CouponsetModel
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderInvoiceModel $OrderInvoiceModel
 * @property CouponModel $CouponModel
 * @property RouteCityModel $RouteCityModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property OrderExtendModel $OrderExtendModel
 */
class UserCallController extends CommonController
{

    private $TakerTypeModel;
    private $SetConfigModel;
    private $CarTypeModel;
    private $CarPriceSettingModel;
    private $UserAddressModel;
    private $TimeSlotModel;
    private $TelephoneModel;
    private $RouteModel;
    private $UserModel;
    private $UserWorkingModel;
    private $OrderModel;
    private $MerchantsApplyModel;
    private $MerchantsImgModel;
    private $TopupModel;
    private $CouponsetModel;
    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderTrafficModel;
    private $OrderInvoiceModel;
    private $CouponModel;
    private $RouteCityModel;
    private $BalanceRecordModel;
    private $OrderExtendModel;

    public $KEY = "Nruyoukuaisong152326197512071176";
    //应用ID
    public $appid="wx9b94176ce775269c";
    //商户号
    public $mch_id="1580673321";
    //设备号
    public $device_info="WEB";
    //随机字符串
    public $nonce_str="";
    //签名
    public $sign="";
    //签名类型
    public $sign_type="MD5";
    //商品描述
    public $body="下单";
    //商品详情  否
    public $detail="";
    //附加数据  否
    public $attach="";
    //商户订单号
    public $out_trade_no="";
    //货币类型
    public $fee_type="CNY";
    //总金额
    public $total_fee=0;
    //终端IP
    public $spbill_create_ip="";
    //交易起始时间    否
    public $time_start="";
    //交易结束时间    否
    public $time_expire="";
    //商品标记  否
    public $goods_tag="";
    //通知地址
    public $notify_url="https://ryks.dltqwy.com/index.php/home/PayRe/wxpay";
    public $notify_url_new="https://ryks.dltqwy.com/index.php/home/PayRe/wxpay_new";
    public $notify_url_new_top="https://ryks.dltqwy.com/index.php/home/PayRe/wxpay_new_top";
    //交易类型
    public $trade_type="APP";
    //指定支付方式
    public $limit_pay="";//no_credit

    public $openid="";

    public function _initialize()
    {
        parent::_initialize();
        $this->TakerTypeModel = new TakerTypeModel();
        $this->SetConfigModel = new SetConfigModel();
        $this->CarTypeModel = new CarTypeModel();
        $this->CarPriceSettingModel = new CarPriceSettingModel();
        $this->UserAddressModel = new UserAddressModel();
        $this->TimeSlotModel = new TimeSlotModel();
        $this->TelephoneModel = new TelephoneModel();
        $this->RouteModel = new RouteModel();
        $this->UserModel = new UserModel();
        $this->UserWorkingModel = new UserWorkingModel();
        $this->OrderModel = new OrderModel();
        $this->MerchantsApplyModel = new MerchantsApplyModel();
        $this->MerchantsImgModel = new MerchantsImgModel();
        $this->TopupModel = new TopupModel();
        $this->CouponsetModel = new CouponsetModel();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->OrderInvoiceModel = new OrderInvoiceModel();
        $this->CouponModel = new CouponModel();
        $this->RouteCityModel = new RouteCityModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->OrderExtendModel = new OrderExtendModel();
    }
    /**
     * 获取叫车基本信息
     */
    public function get_info()
    {
        $lists = $this->TakerTypeModel->get_lists();

        foreach ($lists as $k => $v) {
            $lists[$k]['car_type'] = $this->CarTypeModel->get_car_lists($v['id']);
            $lists[$k]['cozy'] = $this->SetConfigModel->get_content('cozy_type' . $v['id']);
            $lists[$k]['telephone'] = $this->TelephoneModel->get_lists($v['id']);
        }

        echoOk(200, '获取成功', $lists);
    }
    /**
     * 获取时间段
     */
    public function get_time_slot()
    {
        $lists = $this->TimeSlotModel->get_lists();
        echoOk(200, '获取成功', $lists);
    }
    /**
     * 城际拼车-我要叫车
     */
    public function call_intercity()
    {

        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['route_city_id1']) || empty($data['route_city_id2']) || empty($data['location']) ||
            empty($data['arrival_position']) || empty($data['longitude']) || empty($data['latitude']) || empty($data['price']) ||
            empty($data['car_mode']) || empty($data['car_type_id']) || empty($data['people_num'])) {
            echoOk(301, '必填项不能为空');
        }

        // ----- 添加订单 ----- //
        $add_data = [
            'line' => $data['is_immediate'] == 1 ? '1' : '2',
            'user_id' => $data['id'],
            'route_city_id1' => $data['route_city_id1'],
            'route_city_id2' => $data['route_city_id2'],
            'location' => $data['location'],
            'arrival_position' => $data['arrival_position'],
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
            'price' => $data['price'],
            'car_mode' => $data['car_mode'],
            'car_type_id' => $data['car_type_id'],
            'people_num' => $data['people_num'],
            'other' => $data['other'] ? $data['other'] : '',
            'order_time' => $data['order_time'] ? $data['order_time'] : '',
        ];
        $order_id = $this->OrderIntercityModel->add_order($add_data);

        // ----- 叫车 ----- //
        if ($data['is_immediate'] == 1) { // 即时出发
            $route = $this->RouteModel->to_city_get_route($data['route_city_id1'], $data['route_city_id2']);
            if ($route['online'] == 1) { // 线上
                $this->OrderIntercityModel->online_send($order_id);
            } elseif ($route['online'] == 2) { // 线下
                $this->OrderIntercityModel->where('id = ' . $order_id)->save(['line' => '2']);
                $this->RouteModel->send_admin_tel($data['route_city_id1'], $data['route_city_id2'], $data['id'], $data['location'], $data['arrival_position'], $data['people_num'], 2, date('Y-m-d/H:i', time())); // 发短信
            }
        } elseif ($data['is_immediate'] == 2) { // 预约模式
            $this->RouteModel->send_admin_tel($data['route_city_id1'], $data['route_city_id2'], $data['id'], $data['location'], $data['arrival_position'], $data['people_num'], 1, $add_data['order_time']); // 发短信
        }

        $user_phone = M('user')->where('id = "' . $data['id'] . '"')->getField('account');
        if (!empty($data['order_time'])) {
            $arr_order_time = explode(' ', $data['order_time']);
        }
        $times = $data['order_time'] ? $arr_order_time[0] . '-' . $arr_order_time[1] : date('Y-m-d-H:i:s');
        $this->RouteModel->send_user_tel($user_phone, C('phone_account'), C('phone_psd'), $times);

        echoOk(200, '正在为您叫车,请耐心等待...', $order_id);
    }
    /**
     * 发票申请
     */
    public function insert_invoice(){
        $data = self::$_DATA;
        if (empty($data['type']) || empty($data['id']) || empty($data['price'])) {
            echoOk(301, '必填项不能为空');
        }
        $invoice = array();
        $invoice['user_id'] = $data['user_id'];
        $invoice['order_id'] = $data['id'];
        $invoice['intype'] = $data['type'];
        $invoice['add_time'] = time();
        $invoice['price'] = sprintf("%.2f", $data['price']);
        $invoice['tabChooseValue'] = $data['tabChooseValue'];
        $invoice['tabChooseTypeValue'] = $data['tabChooseTypeValue'];
        $invoice['tabChangeNameValue'] = $data['tabChangeNameValue'];
        $invoice['tabChangeNumberValue'] = $data['tabChangeNumberValue'];
        $invoice['tabChangeContentValue'] = $data['tabChangeContentValue'];
        $invoice['tabChangePnameValue'] = $data['tabChangePnameValue'];
        $invoice['tabChangeMarksValue'] = $data['tabChangeMarksValue'];
        $invoice['tabChangePtelValue'] = $data['tabChangePtelValue'];
        $invoice['tabChangePemailValue'] = $data['tabChangePemailValue'];
        $invoice['tabChangePaddressValue'] = $data['tabChangePaddressValue'];

        $save = [
            'is_invoice' => 1,
        ];
        if ($data['type'] == 4){
            $this->OrderTownModel->save_info($data['id'], $save);
        }else{
            $this->OrderTrafficModel->save_info($data['id'], $save);
        }
        $result = $this->OrderInvoiceModel->add($invoice);
        echoOk(200, '操作成功', $result);
    }
    /**
     * 商户申请 小程序
     */
    public function insert_merchants(){
        $data = self::$_DATA;

        if (empty($data['tabChangePcardValue']) || empty($data['tabChangePtel1Value']) || empty($data['tabChangePname1Value']) || empty($data['tabChangePtelValue']) || empty($data['tabChangePnameValue']) || empty($data['tabChangeContentValue']) || empty($data['user_id']) || empty($data['upload_picture_list']) || empty($data['tabChangeNameValue'])) {
            echoOk(301, '必填项不能为空');
        }
        $id = $this->MerchantsApplyModel->get_id($data['user_id']);
        if (!empty($id)){
            echoOk(301, '您已经申请了，请耐心等待！');
        }
        $imgs = json_decode($data['upload_picture_list'],true);
        $invoice = array();
        $invoice['user_id'] = $data['user_id'];
        $invoice['status'] = 1;
        $invoice['rejected'] = '';
        $invoice['addtime'] = time();
        $invoice['tabChangeNameValue'] = $data['tabChangeNameValue'];
        $invoice['tabChangeNumberValue'] = $data['tabChangeNumberValue'];
        $invoice['tabChangeContentValue'] = $data['tabChangeContentValue'];
        $invoice['tabChangePnameValue'] = $data['tabChangePnameValue'];
        $invoice['tabChangePtelValue'] = $data['tabChangePtelValue'];
        $invoice['tabChangePname1Value'] = $data['tabChangePname1Value'];
        $invoice['tabChangePtel1Value'] = $data['tabChangePtel1Value'];
        $invoice['tabChangePcardValue'] = $data['tabChangePcardValue'];
        $invoice['tabChangeNumber1Value'] = empty($data['tabChangeNumber1Value'])?'':$data['tabChangeNumber1Value'];

        $id = $this->MerchantsApplyModel->add_order($invoice);
        foreach ($imgs as $k => $v){
            $invoicenew = array();
            $invoicenew['mid'] = $id;
            $invoicenew['path_server'] = $v['path_server'];
            $invoicenew['addtime'] = time();
            $this->MerchantsImgModel->add_order($invoicenew);
        }
        echoOk(200, '申请成功，2s后自动跳转', $id);
    }
    /**
     * 商户申请 app
     */
    public function insert_merchants_app()
    {
        $data = self::$_DATA;

        if (empty($data['tabChangePcardValue']) || empty($data['tabChangePtel1Value']) || empty($data['tabChangePname1Value']) || empty($data['tabChangePtelValue']) || empty($data['tabChangePnameValue']) || empty($data['tabChangeContentValue']) || empty($data['user_id']) || empty($data['upload_picture_list']) || empty($data['tabChangeNameValue'])) {
            echoOk(301, '必填项不能为空');
        }

        $id = $this->MerchantsApplyModel->get_id($data['user_id']);
        if (!empty($id)){
            echoOk(301, '您已经申请了，请耐心等待！');
        }

        $imgInfo = uploadImg( '' );
        if (empty($imgInfo['image1']) || empty($imgInfo['image2']) || empty($imgInfo['image3'])) {
            echoOk( 301 , '请上传完整三张图片。' );
        } else {
            $complaintData[] = $imgInfo['image1']['path'];
            $complaintData[] = $imgInfo['image2']['path'];
            $complaintData[] = $imgInfo['image3']['path'];
        }

        $invoice = array();
        $invoice['user_id'] = $data['user_id'];
        $invoice['status'] = 1;
        $invoice['rejected'] = '';
        $invoice['addtime'] = time();
        $invoice['tabChangeNameValue'] = $data['tabChangeNameValue'];
        $invoice['tabChangeNumberValue'] = $data['tabChangeNumberValue'];
        $invoice['tabChangeContentValue'] = $data['tabChangeContentValue'];
        $invoice['tabChangePnameValue'] = $data['tabChangePnameValue'];
        $invoice['tabChangePtelValue'] = $data['tabChangePtelValue'];
        $invoice['tabChangePname1Value'] = $data['tabChangePname1Value'];
        $invoice['tabChangePtel1Value'] = $data['tabChangePtel1Value'];
        $invoice['tabChangePcardValue'] = $data['tabChangePcardValue'];
        $invoice['tabChangeNumber1Value'] = empty($data['tabChangeNumber1Value'])?'':$data['tabChangeNumber1Value'];

        $id = $this->MerchantsApplyModel->add_order($invoice);
        foreach ($complaintData as $k => $v){
            $invoicenew = array();
            $invoicenew['mid'] = $id;
            $invoicenew['path_server'] = $v;
            $invoicenew['addtime'] = time();
            $this->MerchantsImgModel->add_order($invoicenew);
        }

        echoOk( 200 , '申请成功' );
    }
    /**
     * 下单处理 代驾订单
     */
    public function call_town()
    {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['car_type_id']) || empty($data['start_location']) || empty($data['start_longitude']) ||
            empty($data['start_latitude']) || empty($data['end_location']) || empty($data['end_longitude']) ||
            empty($data['end_latitude']) || empty($data['price'])) {
            echoOk(301, '必填项不能为空');
        }
        $data['price'] = sprintf("%.2f", $data['price']);
        $orderData = array();
        $orderData['status'] = 1;
        $orderData['add_time'] = time();
        $orderData['user_id'] = $data['id'];
        $orderData['order_type'] = 4;
        $orderData['pay_number'] = "PAY".time().$data['id'];
        $orderData['order_number'] = "ORD".rand(100000000000,999999999999);
        $order_id = $this->OrderModel->add($orderData);

        if ($data['couponId']) {
            $couponInfo = $this->CouponModel->get_coupon_by_id($data['couponId']);
            if (!$couponInfo) {
                echoOk('301', '优惠券信息错误');
            }
            if ($couponInfo['end_time'] < time()){
                echoOk('301', '优惠券已经过期！');
            }
        } else {
            $couponInfo['price'] = 0;
        }
        //获得预约时间
        $data_now = strtotime($data['pickerDate']." ".$data['pickerTime']);

        $carPriceSettingInfo = $this->CarPriceSettingModel->get_car_price_setting_info(4);
        $order_price1 = floatval($data['price']) - floatval($data['tip_price']);
        //抽成金额
        $cost_price = floatval($order_price1) * floatval($carPriceSettingInfo['kg1']) * 0.01;
        //司机金额
        $order_driver_price = floatval($order_price1) - floatval($cost_price);
        //抽成比例
        $cost_num = $carPriceSettingInfo['kg1'];
        // ----- 添加订单 ----- //
        $add_data = [
            'cost_price' => empty($cost_price) || $cost_price <= 0 ? 0.00 : $cost_price,
            'order_driver_price' => empty($order_driver_price) || $order_driver_price <= 0 ? 0.00 : $order_driver_price,
            'cost_num' => empty($cost_num) || $cost_num <= 0 ? 0.00 : $cost_num,
            'user_id' => $data['id'],
            'car_type_id' => $data['car_type_id'],
            'start_location' => $data['start_location'],
            'start_longitude' => $data['start_longitude'],
            'start_latitude' => $data['start_latitude'],
            'end_location' => $data['end_location'],
            'end_longitude' => $data['end_longitude'],
            'end_latitude' => $data['end_latitude'],
            'price' => $data['price'],
            'coupon_id' => $data['couponId'],
            'preferential_price' => $couponInfo['price'],
            'number' => "PAY".time().$data['id'],
            'order_number' => "ORD".rand(100000000000,999999999999),
            'appointment_time' => $data_now,
            'remarks' => $data['remarks'],
            'distribution_km' => $data['distribution_km'],
            'tip_price' => $data['tip_price'],
            'name' => $data['name'],
            'tel' => $data['tel'],
            'name1' => $data['name1'],
            'tel1' => $data['tel1'],
            'address1' => $data['address1'],
            'address2' => $data['address2'],
            'order_status' => 1,
            'order_type' => 4,
            'big_order_id' => $order_id
        ];
        $this->OrderTownModel->add_order($add_data);

        if (!empty($data['start_latitude']) && !empty($data['start_longitude'])){
            //页面展现起点地址
            $user_address_start = $this->UserAddressModel->get_user_address_start($data);
            if (empty($user_address_start)){
                $this->UserAddressModel->user_address_insert($data,1);
            }
        }
        if (!empty($data['end_latitude']) && !empty($data['end_longitude'])){
            //页面展现结束地址
            $user_address_end = $this->UserAddressModel->get_user_address_end($data);
            if (empty($user_address_end)){
                $this->UserAddressModel->user_address_insert($data,2);
            }
        }

        //验证是否有邀请人
        $user_info = $this->UserModel->get_user($data['id']);
        if (!empty($user_info['invitation_code1_up'])){
            $invitation_code1_up = $user_info['invitation_code1_up'];
            $where['invitation_code1'] = $invitation_code1_up;
            $user_info_up = $this->UserModel->getWhereInfo($where);
            $CouponsetModel = $this->CouponsetModel->get_user(1);
            $coupon = [
                'user_id' => $user_info_up['id'],
                'money' => $CouponsetModel['price'],
                'type' => 1,
                'add_time' => time(),
                'end_time' => $CouponsetModel['days'] * 86400 + time(),
            ];
            for ($i=1; $i<=5; $i++)
            {
                $this->CouponModel->addCoupon($coupon);
            }
        }
        //优惠券状态变更
        if (!empty($couponInfo['price'])){
            $set['is_use'] = 1;
            $this->CouponModel->saveCoupon($data['couponId'],$set);
        }
        // ----- 叫车 ----- //
//        $this->OrderTownModel->online_send($order_id);

        echoOk(200, '下单成功', $order_id);
    }
    /**
     * 下单处理  专车 顺风 代买
     */
    public function call_traffic()
    {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['end_location']) || empty($data['end_longitude'])
            || empty($data['end_latitude']) || empty($data['price']) || empty($data['category_type'])) {
            echoOk(301, '必填项不能为空');
        }
        $data['price'] = sprintf("%.2f", $data['price']);
        $orderData = array();
        $orderData['status'] = 1;
        $orderData['add_time'] = time();
        $orderData['user_id'] = $data['id'];
        $orderData['order_type'] = $data['category_type'];
        $orderData['pay_number'] = "PAY".time().$data['id'];
        $orderData['order_number'] = "ORD".rand(100000000000,999999999999);
        $order_id = $this->OrderModel->add($orderData);

        if ($data['couponId']) {
            $couponInfo = $this->CouponModel->get_coupon_by_id($data['couponId']);
            if (!$couponInfo) {
                echoOk('301', '优惠券信息错误');
            }
            if ($couponInfo['end_time'] < time()){
                echoOk('301', '优惠券已经过期！');
            }
        } else {
            $couponInfo['price'] = 0;
        }
        //获得预约时间
        $data_now = strtotime($data['pickerDate']." ".$data['pickerTime']);
        $carPriceSettingInfo = $this->CarPriceSettingModel->get_car_price_setting_info(4);
        $order_price1 = floatval($data['price']) - floatval($data['protect_price']) - floatval($data['tip_price']);
        //抽成金额
        $cost_price = floatval($order_price1) * floatval($carPriceSettingInfo['kg1']) * 0.01;
        //司机金额
        $order_driver_price = floatval($order_price1) - floatval($cost_price);
        //抽成比例
        $cost_num = $carPriceSettingInfo['kg1'];
        // ----- 添加订单 ----- //
        $add_data = [
            'cost_price' => empty($cost_price) || $cost_price <= 0 ? 0.00 : $cost_price,
            'order_driver_price' => empty($order_driver_price) || $order_driver_price <= 0 ? 0.00 : $order_driver_price,
            'cost_num' => empty($cost_num) || $cost_num <= 0 ? 0.00 : $cost_num,
            'user_id' => $data['id'],
            'car_type_id' => 1,
            'file_type' => 2,
            'start_location' => $data['start_location'],
            'start_longitude' => $data['start_longitude'],
            'start_latitude' => $data['start_latitude'],
            'end_location' => $data['end_location'],
            'end_longitude' => $data['end_longitude'],
            'end_latitude' => $data['end_latitude'],
            'order_type' => $data['category_type'],
            'price' => $data['price'],
            'coupon_id' => $data['couponId'],
            'preferential_price' => $couponInfo['price'],
            'number' => $orderData['pay_number'],
            'appointment_time' => $data_now,
            'goods_name' => $data['goods_name'],
            'goods_remarks' => $data['goods_remarks'],
            'distribution_km' => empty($data['distribution_km'])?3:$data['distribution_km'],
            'protect_price' => $data['protect_price'],
            'tip_price' => $data['tip_price'],
            'name' => $data['name'],
            'tel' => $data['tel'],
            'name1' => $data['name1'],
            'tel1' => $data['tel1'],
            'address1' => $data['address1'],
            'address2' => $data['address2'],
            'order_status' => 1,
            'big_order_id' => $order_id
        ];
        $order_traffic_id = $this->OrderTrafficModel->add_order($add_data);

        if ($order_traffic_id) {
            $extendData['pick_up_code'] = rand(1000,9999);
            $extendData['dateline'] = time();
            $extendData['big_order_id'] = $order_id;
            $extendData['order_id'] = $order_traffic_id;
            $this->OrderExtendModel->add($extendData);
        }

//        $this->OrderTownModel->online_send_new($order_id);

        if (!empty($data['start_latitude']) && !empty($data['start_longitude'])){
            //页面展现起点地址
            $user_address_start = $this->UserAddressModel->get_user_address_start($data);
            if (empty($user_address_start)){
                $this->UserAddressModel->user_address_insert($data,1);
            }
        }
        if (!empty($data['end_latitude']) && !empty($data['end_longitude'])){
            //页面展现结束地址
            $user_address_end = $this->UserAddressModel->get_user_address_end($data);
            if (empty($user_address_end)){
                $this->UserAddressModel->user_address_insert($data,2);
            }
        }

        //验证是否有邀请人
        $user_info = $this->UserModel->get_user($data['id']);
        if (!empty($user_info['invitation_code1_up'])){
            $invitation_code1_up = $user_info['invitation_code1_up'];
            $where['invitation_code1'] = $invitation_code1_up;
            $user_info_up = $this->UserModel->getWhereInfo($where);
            $CouponsetModel = $this->CouponsetModel->get_user(1);
            $coupon = [
                'user_id' => $user_info_up['id'],
                'money' => $CouponsetModel['price'],
                'type' => 1,
                'add_time' => time(),
                'end_time' => $CouponsetModel['days'] * 86400 + time(),
            ];
            for ($i=1; $i<=5; $i++)
            {
                $this->CouponModel->addCoupon($coupon);
            }
        }
        //优惠券状态变更
        if (!empty($couponInfo['price'])){
            $set['is_use'] = 1;
            $this->CouponModel->saveCoupon($data['couponId'],$set);
        }
        echoOk(200, '下单成功', $order_id);
    }
    /**
     * 更新坐标
     */
    public function update_coordinate()
    {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['longitude']) || empty($data['latitude'])) {
            echoOk(301, '必填项不能为空');
        }

        $save = [
            'longitude' => $data['longitude'],
            'latitude' => $data['latitude'],
        ];
        $temp = $this->UserModel->save_info($data['id'], $save);
        if ($temp) {
            echoOk(200, '操作成功');
        } else {
            echoOk(301, '操作失败');
        }
    }
    /**
     * 发送通知
     */
    public function order_send()
    {
        $data = self::$_DATA;
        if (empty($data['id']) || empty($data['taker_type_id']) || empty($data['waiting_id'])) {
            echoOk(301, '必填项不能为空');
        }
        $driverInfo = $this->UserModel->get_info( $data['id'] );
        if (empty($driverInfo)){
            echoOk( 301 , '数据错误' );
        }
        if ($driverInfo['credit_points'] < 20){
            echoOk( 301 , '信誉分低于20分、不可接单，请您联系客服处理分数问题！' );
        }
        if ($data['taker_type_id'] == 1){
            if ($driverInfo['user_check'] != 1){
                echoOk( 301 , '你还没有认证！请先去认证！' );
            }
            $orderInfo = $this->OrderTrafficModel->where( [ 'id' => $data['waiting_id'] ] )->find();
            $orderInfotown = array();
        }elseif ($data['taker_type_id'] == 2){
            if ($driverInfo['driving_check'] != 1){
                echoOk( 301 , '你还没有认证！请先去认证！' );
            }
            $orderInfo = array();
            $orderInfotown = $this->OrderTownModel->where( [ 'id' => $data['waiting_id'] ] )->find();
        }else{
            echoOk( 301 , '数据错误！' );
        }
        $todayStart= strtotime(date('Y-m-d 00:00:00', time()));
        $todayEnd= strtotime(date('Y-m-d 23:59:59', time()));
        $where_order  = 'driver_id = '.$data['id'];
        $where_order .= ' AND getorder_time BETWEEN '.$todayStart.' AND '.$todayEnd;
        $carPriceSettingInfo = $this->CarPriceSettingModel->get_car_price_setting_info(4);
        $OrderTrafficCount = $this->OrderTrafficModel->where($where_order)->count();
        $OrderTownCount = $this->OrderTownModel->where($where_order)->count();
        $OrderCount = floatval($OrderTrafficCount) + floatval($OrderTownCount);
        if ($OrderCount >= $carPriceSettingInfo['km2']){
            echoOk( 301 , '当天已到最大接单量！请明天再次接单！' );
            return false;
        }
        if (!empty($orderInfo)){
            if (!empty( $orderInfo['driver_id'] )) {
                echoOk( 301 , '已被接单' );
                return false;
            }
            $this->OrderTrafficModel->startTrans();
            if (empty($orderInfo['start_longitude']) || empty($orderInfo['start_latitude'])){
                $userwork = $this->UserWorkingModel->get_working($data['id']);
                // 2) ----- 改变小单状态 -----
                $order_save = [
                    'start_longitude'    => $userwork['longitude'] ,
                    'start_latitude'    => $userwork['latitude'] ,
                    'start_location'    => "附近地址购买" ,
                    'address1'    => "附近地址购买" ,
                    'driver_id'    => $data['id'] ,
                    'status'       => '2' ,// 小单状态: 2 订单开始
                    'order_status' => '3', // 3 已接单
                    'getorder_time' => time() // 接单时间
                ];
            }else{
                // 2) ----- 改变小单状态 -----
                $order_save = [
                    'driver_id'    => $data['id'] ,
                    'status'       => '2' ,// 小单状态: 2 订单开始
                    'order_status' => '3', // 3 已接单
                    'getorder_time' => time() // 接单时间
                ];
            }

            $this->OrderTrafficModel->set_order( $data['waiting_id'] , $order_save );
            // 3) ----- 改变司机派送状态、上班状态 -----
            $working_save = [
                'status_send' => '0' ,
                'status'      => '3' , // 状态:行程中(3)
            ];
            $this->UserWorkingModel->set_working( $data['id'] , $working_save );
            $this->OrderTrafficModel->commit();
        }elseif (!empty($orderInfotown)){
            if (!empty( $orderInfotown['driver_id'] )) {
                echoOk( 301 , '已被接单' );
                return false;
            }
            $this->OrderTownModel->startTrans();
            // 2) ----- 改变小单状态 -----
            $order_save = [
                'driver_id'    => $data['id'] ,
                'status'       => '2' ,// 小单状态: 2 待接驾
                'order_status' => '3', // 3 已接单
                'getorder_time' => time() // 接单时间
            ];
            $this->OrderTownModel->save_info( $data['waiting_id'] , $order_save );
            // 3) ----- 改变司机派送状态、上班状态 -----
            $working_save = [
                'status_send' => '0' ,
                'status'      => '3' , // 状态:行程中(3)
            ];
            $this->UserWorkingModel->set_working( $data['id'] , $working_save );
            $this->OrderTownModel->commit();
        }else{
            echoOk( 301 , '数据错误' );
        }
        echoOk(200, '操作成功');
    }
    /**
     * 获取用户叫单信息
     */
    public function get_user_order()
    {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        // 城际拼车
        $order = $this->OrderIntercityModel->get_user_order($data['id']);
        if ($order) {
            $order['taker_type_id'] = '1'; // 城际拼车(1)
        } else {
            $order = $this->OrderTownModel->get_user_order($data['id']);
            if ($order) {
                $order['taker_type_id'] = '2'; // 市区出行(2)
            }
        }

        $re = [
            'status' => '0',
            'taker_type_id' => '',
            'order_small_id' => '',
            'price' => '',
            'times' => '',
            'coupon' => '',
            'longitude' => '',
            'latitude' => '',
            'head_img' => '',
            'name' => '',
            'attribute' => '',
            'account' => '',
            'car_number' => '',
            'start_location' => '',
            'end_location' => '',
        ];

        if ($order) {
            $user = $this->UserModel->get_info($order['driver_id']);
            $user_working = $this->UserWorkingModel->get_working($order['driver_id']);

            if ($order['taker_type_id'] == '1') { // 城际拼车
                // 优惠券
                $route = $this->RouteModel->to_city_get_route($order['route_city_id1'], $order['route_city_id2']);
                if ($route['nature'] == '1') { // 长途
                    $coupon = $this->CouponModel->get_coupon($order['user_id'], '2');
                } elseif ($route['nature'] == '2') { // 短途
                    $coupon = $this->CouponModel->get_coupon($order['user_id'], '1');
                }
                $start_location = $order['location']; // $this->RouteCityModel->get_city_name($order['route_city_id1']);
                $end_location = $order['arrival_position']; // $this->RouteCityModel->get_city_name($order['route_city_id2']);
            } elseif ($order['taker_type_id'] == '2') { // 市区出行
                $start_location = $order['start_location'];
                $start_longitude = $order['start_longitude'];
                $start_latitude = $order['start_latitude'];
                $end_location = $order['end_location'];
                $end_longitude = $order['end_longitude'];
                $end_latitude = $order['end_latitude'];
            }

            $re = [
                'status' => $order['status'],
                'taker_type_id' => $order['taker_type_id'],
                'order_small_id' => $order['id'],
                'price' => $order['price'],
                'times' => date('Y-m-d H:i:s', $order['add_time']),
                'coupon' => $coupon['money'] ? $coupon['money'] : '',
                'longitude' => $user_working['longitude'] ? $user_working['longitude'] : '',
                'latitude' => $user_working['latitude'] ? $user_working['latitude'] : '',
                'head_img' => $user['head_img'],
                'name' => $user['name'],
                'attribute' => $user['attribute'],
                'account' => $user['account'],
                'car_number' => $user['car_number'],
                'start_location' => $start_location ? $start_location : '',
                'start_longitude' => $start_longitude ? $start_longitude : '',
                'start_latitude' => $start_latitude ? $start_latitude : '',
                'end_location' => $end_location ? $end_location : '',
                'end_longitude' => $end_longitude ? $end_longitude : '',
                'end_latitude' => $end_latitude ? $end_latitude : '',
            ];
        }

        echoOk(200, '获取成功', $re);
    }
    /**
     * 删除常用地址
     */
    public function cancel_delete()
    {
        $data = self::$_DATA;
        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $this->UserAddressModel->del_address($data['id']);

        echoOk(200, '操作成功');
    }
    /**
     * 取消订单
     */
    public function cancel()
    {
        $data = self::$_DATA;
        if (empty($data['type']) || empty($data['order_small_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        switch ($data['type']) {
            case 1: // 专车 顺风 代买
                $this->OrderTrafficModel->cancel_order($data['order_small_id']);
                $order_info = $this->OrderTrafficModel->get_info($data['order_small_id']);
                break;
            case 2: // 代驾
                $this->OrderTownModel->cancel_order($data['order_small_id']);
                $order_info =$this->OrderTownModel->get_info($data['order_small_id']);
                break;
            default:
                break;
        }
        if (!empty($order_info['driver_id'])){
            $this->OrderTownModel->online_send_cancel($order_info['driver_id']);
        }

        echoOk(200, '操作成功');
    }
    /**
     * 确认支付（app下单）
     */
    public function pay()
    {
        $data = self::$_DATA;

        if (empty($data['order_id']) ) {
            echoOk(301, '必填项不能为空', []);
        }
        $bigOrderInfo  =  $this->OrderModel->where('id = '.$data['order_id'])->find();
        if ($bigOrderInfo['order_type'] == 4){
            $orderInfo = $this->OrderTownModel->where('big_order_id = '. $data['order_id'])->find();
            if (!$orderInfo) {
                echoOk(301, '订单错误', []);
            }
            // 判断该订单是否已支付
            if ($orderInfo['order_status'] == 2) {
                echoOk(301, '该订单已支付');
            }
        }else{
            $orderInfo = $this->OrderTrafficModel->where('big_order_id = '. $data['order_id'])->find();
            if (!$orderInfo) {
                echoOk(301, '订单错误', []);
            }
            // 判断该订单是否已支付
            if ($orderInfo['order_status'] == 2) {
                echoOk(301, '该订单已支付');
            }
        }
        $number = $bigOrderInfo['pay_number'];
        $money = $orderInfo['price'];

        switch ($data['pay_type']) {
            case 1: // 支付宝
                $aop = new \AopClient;
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
//                $aop->appId = "2021002135667785";
                $aop->appId = "2021002148666951";
//                $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAk9eSc0xh3ZHC/FNMh9vd6WVGK5J7DO1RAt1g9oLf7EF5bzPytbxTfVbXp7NlUbJQ6jQR2eDNJLRa/piJpUQFnbW1JIf2KW+wH5KOztDBs3DLUoKpAk+s7QW0MCZEtzXwUGKiiv5AeLATcRLzV7galuHCSq6y/55f4rj5Qley9OaH/lk5K7Nmr/tdTVAhIOkskr2DIR9RVkN1vfn0Sgiv06bpkgDIe6qP7jP31FAqr9+W8VhmogMCBsvopOerLhXyEfhZj9b3Q5OdtzdSloyF5FRpGOkdUNZ5QQuJXFA/vPOEf5VtYxH/PfEautUdGPAg491FzMdNKMM8zkoYdim+3QIDAQABAoIBAFO4u1WRxsYLumjb3zX8m7GFSXR5Ujei2MKPjU5sFNScy2DfAS5LhNgw6pUscU8PdOT8MbO5q1KC1BjjVueBGtERyo/ycsNKXWmzvC2AaIDueiWymnUVm67qEP1HXpiF8h4DjVq62tW9mHTrL2TYfC4/kKBsSC+bh19EMtuQAGbQlffQlx8syTlbzYsV2zqebm7dZOFNxMuNHHb0H36T0F50o8bYuln2r9Fpp9ZFNxHbwTT6sy0tKborYmChVEIKc4Cj5i5OlcNRVO6w6EfrGRexokTknxy9km0vVQGRkMx0fgL414OafM0ToF58a3jL3+fNo5PM8+dqqYx7eOSiff0CgYEA/MqccDZLV7R7JKM+xjLNecN3WsXcRSnGgDY4oys/JMhfUo69PsG9MULBYzXMewN8EhiFCiyaVgIs7hzo0us4R5rRL+GvxgslEWgxwRA/gUciD1VvTMz5AWVZTw2T7okpKG6zpRE7lh4AU1GrGA16RXVox9QeYu97/GFGmNU077sCgYEAlbfzndBtf4iftC2UWaMOFO34fxlcvAaCWmWrNot+zGKz9eAktAaqYuI01ElxXPl6+J9TZeVPdgFtMWybK+w1zYpXDIdOVkwMdXoEZWEqxuoIo7uF+3cs3H/ha+rn7EJNMeOnXHm/ngqMH1ZCMmybeue3LYoR3ignaVJ8AF6YpkcCgYEAiIDsGtfmtVR7DpysICB8vhyjzbt8J0jYrJEa4llO/pDIkG8Nl97MR9ZxUR5S7bZDAA6HgKf3Kyx/kJDB1Gs598iM3xsybHHGMXEfNM6OTEPaC4ep00H30B4nLL37PNqKBBBLJ353u6c1q8g06IcWmH0++HD9e5UwUWdB1POEY1MCgYA7A+SSe3r9/3O8SQ8l+iDt3RPLvcqRDxvI2DSR7pCPrgRz0eOF1u7IxZO/bieyV2DCqcLs8mPNByjsh3vu51AZ8gL4HEmjuJxcnjknYRLEj7HSIHPiuIpFbF8F8/Vxan0VQkBRMmh2un+bRpwXVjo/SyEoYTlD0Z4fZPUeDCuttwKBgQC9LpPWblPpJIdn+ACTAkHcxQbPCLn1TJt8LjOU0N22eBRF3CtngDLSvHcEtMZYmQCrTJqC3BBHmxuAcizXznl5QUztJcGcvZ9Db7TezMu4K/0bnqASbBZUNZTtlb6c6KILeLfz/1lSH5yjg186MxL3QH+huiJ7SyCVZi9+suDvAQ==';
                $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEAx0Nyl0p4U1vVNPzhzjO3tpIOUk3FKa/SzGoSbkrLlxN4rbIiWj/iojciBTpzdS9TYlrLx2KhQP761T93pAMELJeb6Dfz60rsseBmxAexWsDGunm1IBGe6GDiwkZSzFhppj46OK6d5MR6Aa49r+JzEvkYF+m5y5Kn/7zgYLlY9xiJ2pLTfaMw7wqG5F9AzFIOfGDwPA2ZVDlv6ebA7WiP2ty/pa+LKSzaq/Hi6TYTFYlrFS7sIVOxvaXQy2Y2yCKgo6XIuNH7KaUTifFXMpHHyx0T26M4Ep7LNW8MxozGhU+DnNWWOLOIN9u4pmcpio5Mpkb2AVjt/HV1NJlDtOIY1QIDAQABAoIBAQC7eC9lO0D9XBa5JXnkIDAlakBjGpv2Xl76xojUjzX/yufssv0y34v/hdNSC+34QDA5BqdVzOtdVQb0fiO3gMb6lSCvV4T6x6DssOy+XWZ0tY3CfyLzKuDQS+bLVJItgrjTGRR5o38VbexDjISo52nQXd5XESQl7LJ2HmMTXQ3gWeD8oixfSR94Hne8t1MKk25lAQ0GDQ1KCyfjzuVy8Tvi+Wl+bOKI+VLPvCpw1bpYVcRyAcWm2CFbOwhBitzadxRM6nuQ+KaPhumh4+kHHFnwwJZqFLrGcLwy6sxEfgsW45SZUz2Cy7uaGN15YwqiS+64wUDg/Yt1q5bRowcosQ15AoGBAPex028qJyiFaA0/VWj+vY8dLYKR0KdKZXZowSi13Jl8KiQg7vEVvTXiW+tUCp+k3aeUKQu6/v4YhnWsxPc4+0F7z5xIGi3FO3+B3DWhmwjTazKVazpFfZOWRCjhMG8aGNW/lbEYs8Wwq+R9QakYcVJ6h6Ytw9B60VJvOaYcaOeLAoGBAM3x5U++OXuGJoHecE4xFVg74we6/JW8gCOXwUl0mAyj2M/IpsoZCJOztZiITmwEJa+on4qEv7Revt36WpsJk/mOywT9qr4FYpcCY1MpOec+u3UyV8IqlWLA5MrWepgeYibsMF0CABUC2JtPt0BNFXl/4/mU3JF04+6zXPA+xA0fAoGBAM8XhJmq+rZsY8S/sR9p7P8c/BHw7C0SjKZliyoIc3p4T5uJVX2IGXxV11h4GbHEW/Zib7b5D31O8Ayu+Ldikt/7LmeR+SFPiCFhZ8YHaeIFMdlHCDHeK9Ibjr1HGgGqJh2AYx2Ac73TqsQM3bsNTqIRBB0yokdKiHyNtftmQWdlAoGBAK2QvWMcfZOWNuPi1SGBCm0nY6pDM9b6W8AjiJngsriMahCMGc1flbPtlBFuZzFvuVc2tOboBCz+YLvx3+SkCNsdNgsJwpmNLk74iz8uEofKffBCsmWIwAfPa8NrSTzk9nG5QvVP10Pd9zHF2DpP/NY+K6NPiyjnq6Wj8qLjMxWfAoGAFHrFd3w7l0FWqf27tocWBkrPbk+Nue6zsW3CJ/oEI3YT6Ij7GZ2mUJavB22iUI+iXZLqy0KW0tRmKaJOxxQZAQV8Zwwikmlv0g39SzkXGisWQEoL3N9GY4KTP8vP9e5b+NP62loHDJegt/wLtKvC49Bx94ghhx5N34UbXocpQ/c=';
                $aop->format = "json";
                $aop->charset = "UTF-8";
//                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnk/JVGyl9hmYx6S241Vp0lixFqfwBg038zgrNyVjQlF8ecktULWktYDeHQ8TTwx8d4JJWNb5MF+ytbdlI0aWvg0oQVO09UhLE1CCSQ0uHlxbcdkLjuYNO0LTjHJtPuRdh1WcE27JcHmKX8MYPKooaUTmz0e5oEh3t9TolkWr8gaYFrP0DrE8jT5yg3IJEUnDxvWK46sFj4XRpIXuBE5CcJ5eRM0DstLk5LGwTZBl7Nu1bxK9YSYLH0p0LZXlsfQPca2DUUwrrvYwJAkVtQUkteFvGaRyqjVfT+BeUMNM48yaaXfWhIcMzX9nCpjNRnrt7IKeynv5xcccsXq9zXRUvQIDAQAB';
                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8gy/Z3GG1wakg1n7I+c3WD+8V6MMoLAe4AXCMM7AA4nz6fYF1X61PqieXsP+MBM0LZ9+c/P3TEwZmm2Ec2r5w8JY3PPzqb3DPi0zYL30YKnR0/b+c9mrtcv6uzIKw2SjD9r02oB134kIkzgTaKo46MeL/HkuPC+llo1i7L+k9kgQDRVfQM4lX2LTkb5zcgKY/N7V3TJobg9ZavZJl+ZSh19j8mapcrqjsDIlIdC1X4t9OnkeX3cGxGZEK586oExUH0PsIjvKP/BE7lb4oVG6gFlbnLbB9TkO/DGkOSw5yWsEihM8UzLBnvUdg7WfPzW0ho8JMg0o4J28SmoYBoY8MwIDAQAB';
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $bizcontent = "{\"body\":\"sell\","
                    . "\"subject\": \"sell\","
                    . "\"out_trade_no\": \"$number\","
                    . "\"timeout_express\": \"30m\","
                    . "\"total_amount\": \"$money\","
                    . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                    . "}";
                $request->setNotifyUrl("https://ryks.dltqwy.com/index.php/home/PayRe/alipay");
                $request->setBizContent($bizcontent);

                //这里和普通的接口调用不同，使用的是sdkExecute
                $alipay_sign = $aop->sdkExecute($request);
                break;
            case 2: // 微信
                $timeOut = 6;
                $this->out_trade_no=$number;
                $this->total_fee=floatval($money)*100;
                $this->spbill_create_ip=$_SERVER['REMOTE_ADDR'];
                $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
                $this->time_start=date("YmdHis");
                $this->time_expire=date("YmdHis", time() + 600);
                $data["appid"]=$this->appid;
                $data["mch_id"]=$this->mch_id;
                $data["device_info"]=$this->device_info;
                $data["nonce_str"]=$this->getNonceStr();
                $data["sign_type"]=$this->sign_type;
                $data["body"]=$this->body;
                $data["detail"]=$this->detail;
                $data["attach"]=$this->attach;
                $data["out_trade_no"]=$this->out_trade_no;
                $data["fee_type"]=$this->fee_type;
                $data["total_fee"]=$this->total_fee;
                $data["spbill_create_ip"]=$this->spbill_create_ip;
                $data["time_start"]=$this->time_start;
                $data["time_expire"]=$this->time_expire;
                $data["goods_tag"]=$this->goods_tag;
                $data["notify_url"]=$this->notify_url;
                $data["trade_type"]=$this->trade_type;
                $data["limit_pay"]=$this->limit_pay;
                $data["sign"]=$this->MakeSign($data);

                $xml=$this->ToXml($data);

                $response = $this->postXmlCurl($xml, $url, false,$timeOut);

                $values=$this->FromXml($response);
                if(!$values) {
                    echoOk(301, 'xml数据异常');
                }

                if($values['return_code'] != 'SUCCESS'){
                    echoOk(301, $values['return_msg']);
                }

                if($values["result_code"]!='SUCCESS'){
                    echoOk(301, $values['err_code_des']);
                }
                $sign = $this->MakeSign($values);
                $weixin_sign = array();
                if($values["sign"] == $sign){
                    $weixin_sign["appid"]=$this->appid;
                    $weixin_sign["partnerid"]=$this->mch_id;
                    $weixin_sign["prepayid"]=$values["prepay_id"];
                    $weixin_sign["package"]="Sign=WXPay";
                    $weixin_sign["noncestr"]=$this->getNonceStr();
                    $weixin_sign["timestamp"]="".time();
                    $weixin_sign["sign"]=$this->MakeSign($weixin_sign);
                    break;
                } else {
                    echoOk(301, '签名验证失败');
                }
        }

        // 积分
//        $user = $this->UserModel->get_info($order['user_id']);
//        $this->UserModel->save_info($order['user_id'], ['integral' => $user['integral'] + intval($money)]);

        $re = [
            'alipay_sign' => $alipay_sign ? $alipay_sign : '',
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * 确认支付（app超时下单）
     */
    public function pay_new()
    {
        $data = self::$_DATA;

        if (empty($data['order_id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $orderInfo = $this->OrderTownModel->where('id = '. $data['order_id'])->find();
        if (!$orderInfo) {
            echoOk(301, '订单错误', []);
        }

        $number = $orderInfo['delay_number'];
        $money = $orderInfo['delay_price'];

        switch ($data['pay_type']) {
            case 1: // 支付宝
                $aop = new \AopClient;
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
//                $aop->appId = "2021002135667785";
                $aop->appId = "2021002148666951";
//                $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAk9eSc0xh3ZHC/FNMh9vd6WVGK5J7DO1RAt1g9oLf7EF5bzPytbxTfVbXp7NlUbJQ6jQR2eDNJLRa/piJpUQFnbW1JIf2KW+wH5KOztDBs3DLUoKpAk+s7QW0MCZEtzXwUGKiiv5AeLATcRLzV7galuHCSq6y/55f4rj5Qley9OaH/lk5K7Nmr/tdTVAhIOkskr2DIR9RVkN1vfn0Sgiv06bpkgDIe6qP7jP31FAqr9+W8VhmogMCBsvopOerLhXyEfhZj9b3Q5OdtzdSloyF5FRpGOkdUNZ5QQuJXFA/vPOEf5VtYxH/PfEautUdGPAg491FzMdNKMM8zkoYdim+3QIDAQABAoIBAFO4u1WRxsYLumjb3zX8m7GFSXR5Ujei2MKPjU5sFNScy2DfAS5LhNgw6pUscU8PdOT8MbO5q1KC1BjjVueBGtERyo/ycsNKXWmzvC2AaIDueiWymnUVm67qEP1HXpiF8h4DjVq62tW9mHTrL2TYfC4/kKBsSC+bh19EMtuQAGbQlffQlx8syTlbzYsV2zqebm7dZOFNxMuNHHb0H36T0F50o8bYuln2r9Fpp9ZFNxHbwTT6sy0tKborYmChVEIKc4Cj5i5OlcNRVO6w6EfrGRexokTknxy9km0vVQGRkMx0fgL414OafM0ToF58a3jL3+fNo5PM8+dqqYx7eOSiff0CgYEA/MqccDZLV7R7JKM+xjLNecN3WsXcRSnGgDY4oys/JMhfUo69PsG9MULBYzXMewN8EhiFCiyaVgIs7hzo0us4R5rRL+GvxgslEWgxwRA/gUciD1VvTMz5AWVZTw2T7okpKG6zpRE7lh4AU1GrGA16RXVox9QeYu97/GFGmNU077sCgYEAlbfzndBtf4iftC2UWaMOFO34fxlcvAaCWmWrNot+zGKz9eAktAaqYuI01ElxXPl6+J9TZeVPdgFtMWybK+w1zYpXDIdOVkwMdXoEZWEqxuoIo7uF+3cs3H/ha+rn7EJNMeOnXHm/ngqMH1ZCMmybeue3LYoR3ignaVJ8AF6YpkcCgYEAiIDsGtfmtVR7DpysICB8vhyjzbt8J0jYrJEa4llO/pDIkG8Nl97MR9ZxUR5S7bZDAA6HgKf3Kyx/kJDB1Gs598iM3xsybHHGMXEfNM6OTEPaC4ep00H30B4nLL37PNqKBBBLJ353u6c1q8g06IcWmH0++HD9e5UwUWdB1POEY1MCgYA7A+SSe3r9/3O8SQ8l+iDt3RPLvcqRDxvI2DSR7pCPrgRz0eOF1u7IxZO/bieyV2DCqcLs8mPNByjsh3vu51AZ8gL4HEmjuJxcnjknYRLEj7HSIHPiuIpFbF8F8/Vxan0VQkBRMmh2un+bRpwXVjo/SyEoYTlD0Z4fZPUeDCuttwKBgQC9LpPWblPpJIdn+ACTAkHcxQbPCLn1TJt8LjOU0N22eBRF3CtngDLSvHcEtMZYmQCrTJqC3BBHmxuAcizXznl5QUztJcGcvZ9Db7TezMu4K/0bnqASbBZUNZTtlb6c6KILeLfz/1lSH5yjg186MxL3QH+huiJ7SyCVZi9+suDvAQ==';
                $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEAx0Nyl0p4U1vVNPzhzjO3tpIOUk3FKa/SzGoSbkrLlxN4rbIiWj/iojciBTpzdS9TYlrLx2KhQP761T93pAMELJeb6Dfz60rsseBmxAexWsDGunm1IBGe6GDiwkZSzFhppj46OK6d5MR6Aa49r+JzEvkYF+m5y5Kn/7zgYLlY9xiJ2pLTfaMw7wqG5F9AzFIOfGDwPA2ZVDlv6ebA7WiP2ty/pa+LKSzaq/Hi6TYTFYlrFS7sIVOxvaXQy2Y2yCKgo6XIuNH7KaUTifFXMpHHyx0T26M4Ep7LNW8MxozGhU+DnNWWOLOIN9u4pmcpio5Mpkb2AVjt/HV1NJlDtOIY1QIDAQABAoIBAQC7eC9lO0D9XBa5JXnkIDAlakBjGpv2Xl76xojUjzX/yufssv0y34v/hdNSC+34QDA5BqdVzOtdVQb0fiO3gMb6lSCvV4T6x6DssOy+XWZ0tY3CfyLzKuDQS+bLVJItgrjTGRR5o38VbexDjISo52nQXd5XESQl7LJ2HmMTXQ3gWeD8oixfSR94Hne8t1MKk25lAQ0GDQ1KCyfjzuVy8Tvi+Wl+bOKI+VLPvCpw1bpYVcRyAcWm2CFbOwhBitzadxRM6nuQ+KaPhumh4+kHHFnwwJZqFLrGcLwy6sxEfgsW45SZUz2Cy7uaGN15YwqiS+64wUDg/Yt1q5bRowcosQ15AoGBAPex028qJyiFaA0/VWj+vY8dLYKR0KdKZXZowSi13Jl8KiQg7vEVvTXiW+tUCp+k3aeUKQu6/v4YhnWsxPc4+0F7z5xIGi3FO3+B3DWhmwjTazKVazpFfZOWRCjhMG8aGNW/lbEYs8Wwq+R9QakYcVJ6h6Ytw9B60VJvOaYcaOeLAoGBAM3x5U++OXuGJoHecE4xFVg74we6/JW8gCOXwUl0mAyj2M/IpsoZCJOztZiITmwEJa+on4qEv7Revt36WpsJk/mOywT9qr4FYpcCY1MpOec+u3UyV8IqlWLA5MrWepgeYibsMF0CABUC2JtPt0BNFXl/4/mU3JF04+6zXPA+xA0fAoGBAM8XhJmq+rZsY8S/sR9p7P8c/BHw7C0SjKZliyoIc3p4T5uJVX2IGXxV11h4GbHEW/Zib7b5D31O8Ayu+Ldikt/7LmeR+SFPiCFhZ8YHaeIFMdlHCDHeK9Ibjr1HGgGqJh2AYx2Ac73TqsQM3bsNTqIRBB0yokdKiHyNtftmQWdlAoGBAK2QvWMcfZOWNuPi1SGBCm0nY6pDM9b6W8AjiJngsriMahCMGc1flbPtlBFuZzFvuVc2tOboBCz+YLvx3+SkCNsdNgsJwpmNLk74iz8uEofKffBCsmWIwAfPa8NrSTzk9nG5QvVP10Pd9zHF2DpP/NY+K6NPiyjnq6Wj8qLjMxWfAoGAFHrFd3w7l0FWqf27tocWBkrPbk+Nue6zsW3CJ/oEI3YT6Ij7GZ2mUJavB22iUI+iXZLqy0KW0tRmKaJOxxQZAQV8Zwwikmlv0g39SzkXGisWQEoL3N9GY4KTP8vP9e5b+NP62loHDJegt/wLtKvC49Bx94ghhx5N34UbXocpQ/c=';
                $aop->format = "json";
                $aop->charset = "UTF-8";
//                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnk/JVGyl9hmYx6S241Vp0lixFqfwBg038zgrNyVjQlF8ecktULWktYDeHQ8TTwx8d4JJWNb5MF+ytbdlI0aWvg0oQVO09UhLE1CCSQ0uHlxbcdkLjuYNO0LTjHJtPuRdh1WcE27JcHmKX8MYPKooaUTmz0e5oEh3t9TolkWr8gaYFrP0DrE8jT5yg3IJEUnDxvWK46sFj4XRpIXuBE5CcJ5eRM0DstLk5LGwTZBl7Nu1bxK9YSYLH0p0LZXlsfQPca2DUUwrrvYwJAkVtQUkteFvGaRyqjVfT+BeUMNM48yaaXfWhIcMzX9nCpjNRnrt7IKeynv5xcccsXq9zXRUvQIDAQAB';
                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8gy/Z3GG1wakg1n7I+c3WD+8V6MMoLAe4AXCMM7AA4nz6fYF1X61PqieXsP+MBM0LZ9+c/P3TEwZmm2Ec2r5w8JY3PPzqb3DPi0zYL30YKnR0/b+c9mrtcv6uzIKw2SjD9r02oB134kIkzgTaKo46MeL/HkuPC+llo1i7L+k9kgQDRVfQM4lX2LTkb5zcgKY/N7V3TJobg9ZavZJl+ZSh19j8mapcrqjsDIlIdC1X4t9OnkeX3cGxGZEK586oExUH0PsIjvKP/BE7lb4oVG6gFlbnLbB9TkO/DGkOSw5yWsEihM8UzLBnvUdg7WfPzW0ho8JMg0o4J28SmoYBoY8MwIDAQAB';
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $bizcontent = "{\"body\":\"sell\","
                    . "\"subject\": \"sell\","
                    . "\"out_trade_no\": \"$number\","
                    . "\"timeout_express\": \"30m\","
                    . "\"total_amount\": \"$money\","
                    . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                    . "}";
                $request->setNotifyUrl("https://ryks.dltqwy.com/index.php/home/PayRe/alipay_new");
                $request->setBizContent($bizcontent);

                //这里和普通的接口调用不同，使用的是sdkExecute
                $alipay_sign = $aop->sdkExecute($request);
                break;
            case 2: // 微信
                $timeOut = 6;
                $this->out_trade_no=$number;
                $this->total_fee=floatval($money)*100;
                $this->spbill_create_ip=$_SERVER['REMOTE_ADDR'];
                $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
                $this->time_start=date("YmdHis");
                $this->time_expire=date("YmdHis", time() + 600);
                $data["appid"]=$this->appid;
                $data["mch_id"]=$this->mch_id;
                $data["device_info"]=$this->device_info;
                $data["nonce_str"]=$this->getNonceStr();
                $data["sign_type"]=$this->sign_type;
                $data["body"]=$this->body;
                $data["detail"]=$this->detail;
                $data["attach"]=$this->attach;
                $data["out_trade_no"]=$this->out_trade_no;
                $data["fee_type"]=$this->fee_type;
                $data["total_fee"]=$this->total_fee;
                $data["spbill_create_ip"]=$this->spbill_create_ip;
                $data["time_start"]=$this->time_start;
                $data["time_expire"]=$this->time_expire;
                $data["goods_tag"]=$this->goods_tag;
                $data["notify_url"]=$this->notify_url_new;
                $data["trade_type"]=$this->trade_type;
                $data["limit_pay"]=$this->limit_pay;
                $data["sign"]=$this->MakeSign($data);

                $xml=$this->ToXml($data);

                $response = $this->postXmlCurl($xml, $url, false,$timeOut);

                $values=$this->FromXml($response);
                if(!$values) {
                    echoOk(301, 'xml数据异常');
                }

                if($values['return_code'] != 'SUCCESS'){
                    echoOk(301, $values['return_msg']);
                }

                if($values["result_code"]!='SUCCESS'){
                    echoOk(301, $values['err_code_des']);
                }
                $sign = $this->MakeSign($values);
                $weixin_sign = array();
                if($values["sign"] == $sign){
                    $weixin_sign["appid"]=$this->appid;
                    $weixin_sign["partnerid"]=$this->mch_id;
                    $weixin_sign["prepayid"]=$values["prepay_id"];
                    $weixin_sign["package"]="Sign=WXPay";
                    $weixin_sign["noncestr"]=$this->getNonceStr();
                    $weixin_sign["timestamp"]="".time();
                    $weixin_sign["sign"]=$this->MakeSign($weixin_sign);
                    break;
                } else {
                    echoOk(301, '签名验证失败');
                }
        }

        // 积分
//        $user = $this->UserModel->get_info($order['user_id']);
//        $this->UserModel->save_info($order['user_id'], ['integral' => $user['integral'] + intval($money)]);

        $re = [
            'alipay_sign' => $alipay_sign ? $alipay_sign : '',
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * 确认支付(小程序下单)
     */
    public function traffic_order_pay()
    {
        $data = self::$_DATA;

        if (empty($data['order_id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $bigOrderInfo  =  $this->OrderModel->where('id = '.$data['order_id'])->find();
        if ($bigOrderInfo['order_type'] == 4){
            $orderInfo = $this->OrderTownModel->where('big_order_id = '. $data['order_id'])->find();
            if (!$orderInfo) {
                echoOk(301, '订单错误', []);
            }
        }else{
            $orderInfo = $this->OrderTrafficModel->where('big_order_id = '. $data['order_id'])->find();
            if (!$orderInfo) {
                echoOk(301, '订单错误', []);
            }
        }


        $number = $bigOrderInfo['pay_number'];
        $userInfo = $this->UserModel->where('id = ' . $orderInfo['user_id'])->find();
        $openid = $userInfo['open_id'];

        $appid = 'wx95ff8ddda8027413';
        $key = "Nruyoukuaisong152326197512071176";
        $mch_id = "1580673321";

//        $openid = "osb5a5EK208TUOfOfHWS-zEgEmRE";

        $money = $orderInfo['price'];

        $orderCode = $number;   //  订单号
//        随机字符串
        $str = "QWERTYUIPADGHJKLZXCVNM1234567890";
        $nonce = str_shuffle($str);

        $pay['appid'] = $appid;
        $pay['body'] = '订单支付';               //商品描述
        $pay['mch_id'] = $mch_id;            //商户号
        $pay['nonce_str'] = $nonce;        //随机字符串
        $pay['notify_url'] = 'https://ryks.dltqwy.com/index.php/Home/PayRe/Wx_notify_url';
        $pay['openid'] = $openid;
        $pay['out_trade_no'] = $orderCode;       //订单号
        $pay['spbill_create_ip'] = $_SERVER['SERVER_ADDR']; // 终端IP
        $pay['total_fee'] = 100 * $money; //支付金额
        $pay['trade_type'] = 'JSAPI';    //交易类型
//        组建签名（不可换行 空格  否则哭吧）
        $stringA = "appid=" . $pay['appid'] . "&body=" . $pay['body'] . "&mch_id=" . $pay['mch_id'] . "&nonce_str=" . $pay['nonce_str'] . "&notify_url=" . $pay['notify_url'] . "&openid=" . $pay['openid'] . "&out_trade_no=" . $pay['out_trade_no'] . "&spbill_create_ip=" . $pay['spbill_create_ip'] . "&total_fee=" . $pay['total_fee'] . "&trade_type=" . $pay['trade_type'];
        $stringSignTemp = $stringA . "&key=" . $key; //注：key为商户平台设置的密钥key(这个还需要再确认一下)
        $sign = strtoupper(md5($stringSignTemp)); //注：MD5签名方式
        $pay['sign'] = $sign;              //签名
//        统一下单请求
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = $this->arrayToXml($pay);
        $res = $this->wxpost($url, $data);
//        对 统一下单返回得参数进行处理
        $pay_arr = $this->xmlToArray($res);  //这里是数组

        if ($pay_arr['return_code'] == 'FAIL' || $pay_arr['result_code'] == 'FAIL') {
            echo json_encode($res);
            exit;
        }
//        调起支付数据签名字段
        $timeStamp = time();
        $nonce_pay = str_shuffle($str);
        $package = $pay_arr['prepay_id'];
        $signType = "MD5";
        $stringPay = "appId=" . $appid . "&nonceStr=" . $nonce_pay . "&package=prepay_id=" . $package . "&signType=" . $signType . "&timeStamp=" . $timeStamp . "&key=" . $key;
        $paySign = strtoupper(md5($stringPay));
        $rpay['timeStamp'] = (string)$timeStamp;
        $rpay['nonceStr'] = $nonce_pay;
        $rpay['_package'] = "prepay_id=" . $package;
        $rpay['signType'] = $signType;
        $rpay['paySign'] = $paySign;
        $rpay['orders'] = $orderCode;

        $weixin_sign = [
            'order_no' => $number,
            'money' => $money,
            'app_request' => $rpay,
        ];

        $re = [
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * 余额支付
     */
    public function balance_payment()
    {
        $data = self::$_DATA;

        if (empty($data['order_id']) || empty($data['type'])) {
            echoOk(301, '必填项不能为空', []);
        }
        if ($data['type'] == 1){
            $bigOrderInfo  =  $this->OrderModel->where('id = '.$data['order_id'])->find();
            if ($bigOrderInfo['order_type'] == 4){
                $orderInfo = $this->OrderTownModel->where('big_order_id = '. $data['order_id'])->find();
                if (!$orderInfo) {
                    echoOk(301, '订单错误', []);
                }
                // 判断该订单是否已支付
                if ($orderInfo['order_status'] == 2) {
                    echoOk(301, '该订单已支付');
                }
            }else{
                $orderInfo = $this->OrderTrafficModel->where('big_order_id = '. $data['order_id'])->find();
                if (!$orderInfo) {
                    echoOk(301, '订单错误', []);
                }
                // 判断该订单是否已支付
                if ($orderInfo['order_status'] == 2) {
                    echoOk(301, '该订单已支付');
                }
            }
            $number = $bigOrderInfo['pay_number'];
            $money = $orderInfo['price'];
            $user = $this->UserModel->get_info($bigOrderInfo['user_id']);
            if ($user['money'] < $money) {
                echoOk(301, '余额不足');
            }
            $money_new = floatval($user['money']) - floatval($money);
            $pay_numberWhere['pay_number'] =$number;
            $bigOrderInfo = $this->OrderModel->where($pay_numberWhere)->find();
            $orderInfoWhere['big_order_id'] =$bigOrderInfo['id'];
            $save = [
                'order_status' => 2,
                'pay_type' => 3,
            ];
            if ($bigOrderInfo['order_type'] == 4){
                $orderInfo = $this->OrderTownModel->where($orderInfoWhere)->find();
                $this->OrderTownModel->save_info($orderInfo['id'], $save);
                //叫车
                $this->OrderTownModel->online_send($orderInfo['id']);
            }else{
                $orderInfo = $this->OrderTrafficModel->where($orderInfoWhere)->find();
                $this->OrderTrafficModel->save_info($orderInfo['id'], $save);
                //叫车
                $this->OrderTownModel->online_send_new($orderInfo['id']);
            }
            $save_data = [
                'money'                    => $money_new ,
            ];
            $this->UserModel->save_info($bigOrderInfo['user_id'],$save_data);
        }else{
            $orderInfo = $this->OrderTownModel->where('id = '. $data['order_id'])->find();
            if (!$orderInfo) {
                echoOk(301, '订单错误', []);
            }

            $number = $orderInfo['delay_number'];
            $money = $orderInfo['delay_price'];

            $user = $this->UserModel->get_info($orderInfo['user_id']);
            if ($user['money'] < $money) {
                echoOk(301, '余额不足');
            }
            $money_new = floatval($user['money']) - floatval($money);
            $pay_numberWhere['delay_number'] =$number;
            $save = [
                'delay_state' => 1,
                'pay_type_new' => 3,
            ];
            $orderInfo = $this->OrderTownModel->where($pay_numberWhere)->find();
            $this->OrderTownModel->save_info($orderInfo['id'], $save);
            $save_data = [
                'money'                    => $money_new ,
            ];
            $this->UserModel->save_info($orderInfo['user_id'],$save_data);
        }


        echoOk(200, '支付成功，2s后自动跳转');
    }
    /**
     * 确认支付(小程序超时下单)
     */
    public function traffic_order_pay_new()
    {
        $data = self::$_DATA;

        if (empty($data['order_id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $orderInfo = $this->OrderTownModel->where('id = '. $data['order_id'])->find();
        if (!$orderInfo) {
            echoOk(301, '订单错误', []);
        }

        $number = $orderInfo['delay_number'];
        $userInfo = $this->UserModel->where('id = ' . $orderInfo['user_id'])->find();
        $openid = $userInfo['open_id'];

        $appid = 'wx95ff8ddda8027413';
        $key = "Nruyoukuaisong152326197512071176";
        $mch_id = "1580673321";

//        $openid = "osb5a5EK208TUOfOfHWS-zEgEmRE";

        $money = $orderInfo['delay_price'];

        $orderCode = $number;   //  订单号
//        随机字符串
        $str = "QWERTYUIPADGHJKLZXCVNM1234567890";
        $nonce = str_shuffle($str);

        $pay['appid'] = $appid;
        $pay['body'] = '超时订单支付';               //商品描述
        $pay['mch_id'] = $mch_id;            //商户号
        $pay['nonce_str'] = $nonce;        //随机字符串
        $pay['notify_url'] = 'https://ryks.dltqwy.com/index.php/Home/PayRe/Wx_notify_url_new';
        $pay['openid'] = $openid;
        $pay['out_trade_no'] = $orderCode;       //订单号
        $pay['spbill_create_ip'] = $_SERVER['SERVER_ADDR']; // 终端IP
        $pay['total_fee'] = 100 * $money; //支付金额
        $pay['trade_type'] = 'JSAPI';    //交易类型
//        组建签名（不可换行 空格  否则哭吧）
        $stringA = "appid=" . $pay['appid'] . "&body=" . $pay['body'] . "&mch_id=" . $pay['mch_id'] . "&nonce_str=" . $pay['nonce_str'] . "&notify_url=" . $pay['notify_url'] . "&openid=" . $pay['openid'] . "&out_trade_no=" . $pay['out_trade_no'] . "&spbill_create_ip=" . $pay['spbill_create_ip'] . "&total_fee=" . $pay['total_fee'] . "&trade_type=" . $pay['trade_type'];
        $stringSignTemp = $stringA . "&key=" . $key; //注：key为商户平台设置的密钥key(这个还需要再确认一下)
        $sign = strtoupper(md5($stringSignTemp)); //注：MD5签名方式
        $pay['sign'] = $sign;              //签名
//        统一下单请求
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";

        $data = $this->arrayToXml($pay);
        $res = $this->wxpost($url, $data);

//        对 统一下单返回得参数进行处理
        $pay_arr = $this->xmlToArray($res);  //这里是数组

        if ($pay_arr['return_code'] == 'FAIL' || $pay_arr['result_code'] == 'FAIL') {
            echo json_encode($res);
            exit;
        }
//        调起支付数据签名字段
        $timeStamp = time();
        $nonce_pay = str_shuffle($str);
        $package = $pay_arr['prepay_id'];
        $signType = "MD5";
        $stringPay = "appId=" . $appid . "&nonceStr=" . $nonce_pay . "&package=prepay_id=" . $package . "&signType=" . $signType . "&timeStamp=" . $timeStamp . "&key=" . $key;
        $paySign = strtoupper(md5($stringPay));
        $rpay['timeStamp'] = (string)$timeStamp;
        $rpay['nonceStr'] = $nonce_pay;
        $rpay['_package'] = "prepay_id=" . $package;
        $rpay['signType'] = $signType;
        $rpay['paySign'] = $paySign;
        $rpay['orders'] = $orderCode;

        $weixin_sign = [
            'order_no' => $number,
            'money' => $money,
            'app_request' => $rpay,
        ];

        $re = [
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * app充值
     */
    public function topup_pay()
    {
        $data = self::$_DATA;

        if (empty($data['user_id']) || empty($data['money']) || empty($data['pay_type'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $orderData = array();
        $orderData['status'] = 0;
        $orderData['addtime'] = time();
        $orderData['money'] = $data['money'];
        $orderData['uid'] = $data['user_id'];
        $orderData['paynumber'] = "PAY".time().$data['user_id'];
        $order_id = $this->TopupModel->add($orderData);
        $number = $orderData['paynumber'];
        $money = $orderData['money'];

        switch ($data['pay_type']) {
            case 1: // 支付宝
                $aop = new \AopClient;
                $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
//                $aop->appId = "2021002135667785";
                $aop->appId = "2021002148666951";
//                $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAk9eSc0xh3ZHC/FNMh9vd6WVGK5J7DO1RAt1g9oLf7EF5bzPytbxTfVbXp7NlUbJQ6jQR2eDNJLRa/piJpUQFnbW1JIf2KW+wH5KOztDBs3DLUoKpAk+s7QW0MCZEtzXwUGKiiv5AeLATcRLzV7galuHCSq6y/55f4rj5Qley9OaH/lk5K7Nmr/tdTVAhIOkskr2DIR9RVkN1vfn0Sgiv06bpkgDIe6qP7jP31FAqr9+W8VhmogMCBsvopOerLhXyEfhZj9b3Q5OdtzdSloyF5FRpGOkdUNZ5QQuJXFA/vPOEf5VtYxH/PfEautUdGPAg491FzMdNKMM8zkoYdim+3QIDAQABAoIBAFO4u1WRxsYLumjb3zX8m7GFSXR5Ujei2MKPjU5sFNScy2DfAS5LhNgw6pUscU8PdOT8MbO5q1KC1BjjVueBGtERyo/ycsNKXWmzvC2AaIDueiWymnUVm67qEP1HXpiF8h4DjVq62tW9mHTrL2TYfC4/kKBsSC+bh19EMtuQAGbQlffQlx8syTlbzYsV2zqebm7dZOFNxMuNHHb0H36T0F50o8bYuln2r9Fpp9ZFNxHbwTT6sy0tKborYmChVEIKc4Cj5i5OlcNRVO6w6EfrGRexokTknxy9km0vVQGRkMx0fgL414OafM0ToF58a3jL3+fNo5PM8+dqqYx7eOSiff0CgYEA/MqccDZLV7R7JKM+xjLNecN3WsXcRSnGgDY4oys/JMhfUo69PsG9MULBYzXMewN8EhiFCiyaVgIs7hzo0us4R5rRL+GvxgslEWgxwRA/gUciD1VvTMz5AWVZTw2T7okpKG6zpRE7lh4AU1GrGA16RXVox9QeYu97/GFGmNU077sCgYEAlbfzndBtf4iftC2UWaMOFO34fxlcvAaCWmWrNot+zGKz9eAktAaqYuI01ElxXPl6+J9TZeVPdgFtMWybK+w1zYpXDIdOVkwMdXoEZWEqxuoIo7uF+3cs3H/ha+rn7EJNMeOnXHm/ngqMH1ZCMmybeue3LYoR3ignaVJ8AF6YpkcCgYEAiIDsGtfmtVR7DpysICB8vhyjzbt8J0jYrJEa4llO/pDIkG8Nl97MR9ZxUR5S7bZDAA6HgKf3Kyx/kJDB1Gs598iM3xsybHHGMXEfNM6OTEPaC4ep00H30B4nLL37PNqKBBBLJ353u6c1q8g06IcWmH0++HD9e5UwUWdB1POEY1MCgYA7A+SSe3r9/3O8SQ8l+iDt3RPLvcqRDxvI2DSR7pCPrgRz0eOF1u7IxZO/bieyV2DCqcLs8mPNByjsh3vu51AZ8gL4HEmjuJxcnjknYRLEj7HSIHPiuIpFbF8F8/Vxan0VQkBRMmh2un+bRpwXVjo/SyEoYTlD0Z4fZPUeDCuttwKBgQC9LpPWblPpJIdn+ACTAkHcxQbPCLn1TJt8LjOU0N22eBRF3CtngDLSvHcEtMZYmQCrTJqC3BBHmxuAcizXznl5QUztJcGcvZ9Db7TezMu4K/0bnqASbBZUNZTtlb6c6KILeLfz/1lSH5yjg186MxL3QH+huiJ7SyCVZi9+suDvAQ==';
                $aop->rsaPrivateKey = 'MIIEpQIBAAKCAQEAx0Nyl0p4U1vVNPzhzjO3tpIOUk3FKa/SzGoSbkrLlxN4rbIiWj/iojciBTpzdS9TYlrLx2KhQP761T93pAMELJeb6Dfz60rsseBmxAexWsDGunm1IBGe6GDiwkZSzFhppj46OK6d5MR6Aa49r+JzEvkYF+m5y5Kn/7zgYLlY9xiJ2pLTfaMw7wqG5F9AzFIOfGDwPA2ZVDlv6ebA7WiP2ty/pa+LKSzaq/Hi6TYTFYlrFS7sIVOxvaXQy2Y2yCKgo6XIuNH7KaUTifFXMpHHyx0T26M4Ep7LNW8MxozGhU+DnNWWOLOIN9u4pmcpio5Mpkb2AVjt/HV1NJlDtOIY1QIDAQABAoIBAQC7eC9lO0D9XBa5JXnkIDAlakBjGpv2Xl76xojUjzX/yufssv0y34v/hdNSC+34QDA5BqdVzOtdVQb0fiO3gMb6lSCvV4T6x6DssOy+XWZ0tY3CfyLzKuDQS+bLVJItgrjTGRR5o38VbexDjISo52nQXd5XESQl7LJ2HmMTXQ3gWeD8oixfSR94Hne8t1MKk25lAQ0GDQ1KCyfjzuVy8Tvi+Wl+bOKI+VLPvCpw1bpYVcRyAcWm2CFbOwhBitzadxRM6nuQ+KaPhumh4+kHHFnwwJZqFLrGcLwy6sxEfgsW45SZUz2Cy7uaGN15YwqiS+64wUDg/Yt1q5bRowcosQ15AoGBAPex028qJyiFaA0/VWj+vY8dLYKR0KdKZXZowSi13Jl8KiQg7vEVvTXiW+tUCp+k3aeUKQu6/v4YhnWsxPc4+0F7z5xIGi3FO3+B3DWhmwjTazKVazpFfZOWRCjhMG8aGNW/lbEYs8Wwq+R9QakYcVJ6h6Ytw9B60VJvOaYcaOeLAoGBAM3x5U++OXuGJoHecE4xFVg74we6/JW8gCOXwUl0mAyj2M/IpsoZCJOztZiITmwEJa+on4qEv7Revt36WpsJk/mOywT9qr4FYpcCY1MpOec+u3UyV8IqlWLA5MrWepgeYibsMF0CABUC2JtPt0BNFXl/4/mU3JF04+6zXPA+xA0fAoGBAM8XhJmq+rZsY8S/sR9p7P8c/BHw7C0SjKZliyoIc3p4T5uJVX2IGXxV11h4GbHEW/Zib7b5D31O8Ayu+Ldikt/7LmeR+SFPiCFhZ8YHaeIFMdlHCDHeK9Ibjr1HGgGqJh2AYx2Ac73TqsQM3bsNTqIRBB0yokdKiHyNtftmQWdlAoGBAK2QvWMcfZOWNuPi1SGBCm0nY6pDM9b6W8AjiJngsriMahCMGc1flbPtlBFuZzFvuVc2tOboBCz+YLvx3+SkCNsdNgsJwpmNLk74iz8uEofKffBCsmWIwAfPa8NrSTzk9nG5QvVP10Pd9zHF2DpP/NY+K6NPiyjnq6Wj8qLjMxWfAoGAFHrFd3w7l0FWqf27tocWBkrPbk+Nue6zsW3CJ/oEI3YT6Ij7GZ2mUJavB22iUI+iXZLqy0KW0tRmKaJOxxQZAQV8Zwwikmlv0g39SzkXGisWQEoL3N9GY4KTP8vP9e5b+NP62loHDJegt/wLtKvC49Bx94ghhx5N34UbXocpQ/c=';
                $aop->format = "json";
                $aop->charset = "UTF-8";
//                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAnk/JVGyl9hmYx6S241Vp0lixFqfwBg038zgrNyVjQlF8ecktULWktYDeHQ8TTwx8d4JJWNb5MF+ytbdlI0aWvg0oQVO09UhLE1CCSQ0uHlxbcdkLjuYNO0LTjHJtPuRdh1WcE27JcHmKX8MYPKooaUTmz0e5oEh3t9TolkWr8gaYFrP0DrE8jT5yg3IJEUnDxvWK46sFj4XRpIXuBE5CcJ5eRM0DstLk5LGwTZBl7Nu1bxK9YSYLH0p0LZXlsfQPca2DUUwrrvYwJAkVtQUkteFvGaRyqjVfT+BeUMNM48yaaXfWhIcMzX9nCpjNRnrt7IKeynv5xcccsXq9zXRUvQIDAQAB';
                $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA8gy/Z3GG1wakg1n7I+c3WD+8V6MMoLAe4AXCMM7AA4nz6fYF1X61PqieXsP+MBM0LZ9+c/P3TEwZmm2Ec2r5w8JY3PPzqb3DPi0zYL30YKnR0/b+c9mrtcv6uzIKw2SjD9r02oB134kIkzgTaKo46MeL/HkuPC+llo1i7L+k9kgQDRVfQM4lX2LTkb5zcgKY/N7V3TJobg9ZavZJl+ZSh19j8mapcrqjsDIlIdC1X4t9OnkeX3cGxGZEK586oExUH0PsIjvKP/BE7lb4oVG6gFlbnLbB9TkO/DGkOSw5yWsEihM8UzLBnvUdg7WfPzW0ho8JMg0o4J28SmoYBoY8MwIDAQAB';
                //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
                $request = new \AlipayTradeAppPayRequest();
                //SDK已经封装掉了公共参数，这里只需要传入业务参数
                $bizcontent = "{\"body\":\"sell\","
                    . "\"subject\": \"sell\","
                    . "\"out_trade_no\": \"$number\","
                    . "\"timeout_express\": \"30m\","
                    . "\"total_amount\": \"$money\","
                    . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                    . "}";
                $request->setNotifyUrl("https://ryks.dltqwy.com/index.php/home/PayRe/alipay_new_top");
                $request->setBizContent($bizcontent);

                //这里和普通的接口调用不同，使用的是sdkExecute
                $alipay_sign = $aop->sdkExecute($request);
                break;
            case 2: // 微信
                $timeOut = 6;
                $this->out_trade_no=$number;
                $this->total_fee=floatval($money)*100;
                $this->spbill_create_ip=$_SERVER['REMOTE_ADDR'];
                $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
                $this->time_start=date("YmdHis");
                $this->time_expire=date("YmdHis", time() + 600);
                $data["appid"]=$this->appid;
                $data["mch_id"]=$this->mch_id;
                $data["device_info"]=$this->device_info;
                $data["nonce_str"]=$this->getNonceStr();
                $data["sign_type"]=$this->sign_type;
                $data["body"]=$this->body;
                $data["detail"]=$this->detail;
                $data["attach"]=$this->attach;
                $data["out_trade_no"]=$this->out_trade_no;
                $data["fee_type"]=$this->fee_type;
                $data["total_fee"]=$this->total_fee;
                $data["spbill_create_ip"]=$this->spbill_create_ip;
                $data["time_start"]=$this->time_start;
                $data["time_expire"]=$this->time_expire;
                $data["goods_tag"]=$this->goods_tag;
                $data["notify_url"]=$this->notify_url_new_top;
                $data["trade_type"]=$this->trade_type;
                $data["limit_pay"]=$this->limit_pay;
                $data["sign"]=$this->MakeSign($data);

                $xml=$this->ToXml($data);

                $response = $this->postXmlCurl($xml, $url, false,$timeOut);

                $values=$this->FromXml($response);
                if(!$values) {
                    echoOk(301, 'xml数据异常');
                }

                if($values['return_code'] != 'SUCCESS'){
                    echoOk(301, $values['return_msg']);
                }

                if($values["result_code"]!='SUCCESS'){
                    echoOk(301, $values['err_code_des']);
                }
                $sign = $this->MakeSign($values);
                $weixin_sign = array();
                if($values["sign"] == $sign){
                    $weixin_sign["appid"]=$this->appid;
                    $weixin_sign["partnerid"]=$this->mch_id;
                    $weixin_sign["prepayid"]=$values["prepay_id"];
                    $weixin_sign["package"]="Sign=WXPay";
                    $weixin_sign["noncestr"]=$this->getNonceStr();
                    $weixin_sign["timestamp"]="".time();
                    $weixin_sign["sign"]=$this->MakeSign($weixin_sign);
                    break;
                } else {
                    echoOk(301, '签名验证失败');
                }
        }
        $re = [
            'alipay_sign' => $alipay_sign ? $alipay_sign : '',
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * 小程序充值
     */
    public function topup_treatment()
    {
        $data = self::$_DATA;

        if (empty($data['user_id']) || empty($data['money'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $orderData = array();
        $orderData['status'] = 0;
        $orderData['addtime'] = time();
        $orderData['money'] = $data['money'];
        $orderData['uid'] = $data['user_id'];
        $orderData['paynumber'] = "PAY".time().$data['user_id'];
        $order_id = $this->TopupModel->add($orderData);

        $number = $orderData['paynumber'];
        $userInfo = $this->UserModel->where('id = ' . $data['user_id'])->find();
        $openid = $userInfo['open_id'];

        $appid = 'wx95ff8ddda8027413';
        $key = "Nruyoukuaisong152326197512071176";
        $mch_id = "1580673321";

//        $openid = "osb5a5EK208TUOfOfHWS-zEgEmRE";

        $money = $data['money'];

        $orderCode = $number;   //  订单号
//        随机字符串
        $str = "QWERTYUIPADGHJKLZXCVNM1234567890";
        $nonce = str_shuffle($str);

        $pay['appid'] = $appid;
        $pay['body'] = '订单支付';               //商品描述
        $pay['mch_id'] = $mch_id;            //商户号
        $pay['nonce_str'] = $nonce;        //随机字符串
        $pay['notify_url'] = 'https://ryks.dltqwy.com/index.php/Home/PayRe/topup_treatment';
        $pay['openid'] = $openid;
        $pay['out_trade_no'] = $orderCode;       //订单号
        $pay['spbill_create_ip'] = $_SERVER['SERVER_ADDR']; // 终端IP
        $pay['total_fee'] = 100 * $money; //支付金额
        $pay['trade_type'] = 'JSAPI';    //交易类型
//        组建签名（不可换行 空格  否则哭吧）
        $stringA = "appid=" . $pay['appid'] . "&body=" . $pay['body'] . "&mch_id=" . $pay['mch_id'] . "&nonce_str=" . $pay['nonce_str'] . "&notify_url=" . $pay['notify_url'] . "&openid=" . $pay['openid'] . "&out_trade_no=" . $pay['out_trade_no'] . "&spbill_create_ip=" . $pay['spbill_create_ip'] . "&total_fee=" . $pay['total_fee'] . "&trade_type=" . $pay['trade_type'];
        $stringSignTemp = $stringA . "&key=" . $key; //注：key为商户平台设置的密钥key(这个还需要再确认一下)
        $sign = strtoupper(md5($stringSignTemp)); //注：MD5签名方式
        $pay['sign'] = $sign;              //签名
//        统一下单请求
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data = $this->arrayToXml($pay);
        $res = $this->wxpost($url, $data);
//        对 统一下单返回得参数进行处理
        $pay_arr = $this->xmlToArray($res);  //这里是数组

        if ($pay_arr['return_code'] == 'FAIL' || $pay_arr['result_code'] == 'FAIL') {
            echo json_encode($res);
            exit;
        }
//        调起支付数据签名字段
        $timeStamp = time();
        $nonce_pay = str_shuffle($str);
        $package = $pay_arr['prepay_id'];
        $signType = "MD5";
        $stringPay = "appId=" . $appid . "&nonceStr=" . $nonce_pay . "&package=prepay_id=" . $package . "&signType=" . $signType . "&timeStamp=" . $timeStamp . "&key=" . $key;
        $paySign = strtoupper(md5($stringPay));
        $rpay['timeStamp'] = (string)$timeStamp;
        $rpay['nonceStr'] = $nonce_pay;
        $rpay['_package'] = "prepay_id=" . $package;
        $rpay['signType'] = $signType;
        $rpay['paySign'] = $paySign;
        $rpay['orders'] = $orderCode;

        $weixin_sign = [
            'order_no' => $number,
            'money' => $money,
            'app_request' => $rpay,
        ];

        $re = [
            'weixin_sign' => $weixin_sign ? $weixin_sign : (object)[],
        ];

        echoOk(200, '操作成功', $re);
    }
    /**
     * 评价订单
     */
    public function evaluate()
    {
        $data = self::$_DATA;
        if (empty($data['star']) || empty($data['type']) || empty($data['order_small_id']) || empty($data['evaluate'])) {
            echoOk(301, '必填项不能为空', []);
        }
        switch ($data['type']) {
            case 1: // 专车 顺风 代买
                $this->OrderTrafficModel->evaluate($data['order_small_id'], $data['evaluate'], $data['star']);
                break;
            case 2: // 代驾
                $this->OrderTownModel->evaluate($data['order_small_id'], $data['evaluate'], $data['star']);
                break;
            default:
                break;
        }
        echoOk(200, '操作成功');
    }
    // 我的常用地址
    public function my_address()
    {
        $data = self::$_DATA;
        if (empty($data['userId']) || empty($data['user_type'])) {
            echoOk(301, '请您先去登录！', []);
        }
        $con = [
            'user_id' => $data['userId'],
            'user_type' => $data['user_type'],
            'page' => $data['page'] ?: 1,
            'limit' => $data['limit'] ?: 10000,
        ];
        $lists = $this->UserAddressModel->get_address_lists($con);
        echoOk(200, '获取成功', $lists);
    }
    // 设置邀请码
    public function invitation()
    {
        $data = self::$_DATA;
        if (empty($data['user_id']) || empty($data['invitation'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $conwhere = [
            'invitation_code1' => $data['invitation'],
        ];
        $resultwhere = $this->UserModel->getWhereInfo($conwhere);
        if (empty($resultwhere)){
            echoOk(301, '邀请码不存在！', []);
        }
        $con = [
            'invitation_code1_up' => $data['invitation'],
        ];
        $result = $this->UserModel->save_info($data['user_id'],$con);
        echoOk(200, '设置成功', $result);
    }
    // 我的订单
    public function my_order()
    {
        $data = self::$_DATA;

        //category_id 1 2 3 4 待接单 已接单 已完成 代驾订单
        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $con = [
            'id' => $data['id'],
            'page' => $data['page']?'':1,
            'limit' => $data['limit']?'':10,
        ];

        if ($data['category_id'] == 4) {
            //代驾订单
            $lists = $this->OrderTownModel->get_town_order_lists($con);
        } else {
            //跑腿订单
            $con['order_type'] = $data['category_id'];
            $lists = $this->OrderTrafficModel->get_trip_order_lists($con);
        }

        echoOk(200, '获取成功', $lists);
    }
    // 我的订单详情
    public function my_order_info()
    {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        if ($data['type'] == 2){
            //代驾
            $lists_one = $this->OrderTownModel->get_trip_details($data['id']);
        }else{
            //专车 顺风 代买
            $lists_one = $this->OrderTrafficModel->get_trip_details($data['id']);
        }
        echoOk(200, '获取成功', $lists_one);
    }
    //计算订单金额.
    public function order_money()
    {
        $data = self::$_DATA;
        if (empty($data['pickerTime']) || empty($data['pickerDate']) || empty($data['end_longitude']) || empty($data['end_latitude']) || empty($data['category_type'])) {
            echoOk(301, '必填项不能为空');
        }

        if ($data['couponId']) {
            $couponInfo = $this->CouponModel->get_coupon_by_id($data['couponId']);
            if (!$couponInfo) {
                echoOk('301', '优惠券信息错误');
            }
        } else {
            $couponInfo['price'] = 0;
        }

        $from = $data['start_latitude'] . ',' . $data['start_longitude'];
        $to = $data['end_latitude'] . ',' . $data['end_longitude'];

        $carTypeInfo = $this->CarTypeModel->get_car_info(7);
        //获得计费规则
        $carPriceSettingInfo = $this->CarPriceSettingModel->get_car_price_setting_info($data['category_type']);

        //腾讯lbskey
        $key = 'JF5BZ-ZPE33-ILI3C-YIMB2-4EOB2-7XBJ3';
        $distanceInfo = file_get_contents("http://apis.map.qq.com/ws/distance/v1/?mode=driving&from=$from&to=$to&key=$key");
        $distanceInfo = json_decode($distanceInfo, true);

        $distance = $distanceInfo['result']['elements'][0]['distance']; //距离
        $distance_now = $distanceInfo['result']['elements'][0]['distance']; //距离
//        $duration = $distanceInfo['result']['elements'][0]['duration']; //用时

        //获得预约时间
        $data_now = strtotime($data['pickerDate']." ".$data['pickerTime']);
        //判断是否是夜间单
        $zero_time = strtotime(date('Y-m-d',strtotime('+1 day')));
        $zero_time_now = strtotime(date('Y-m-d',$data_now));
        $zero_time_now7 = floatval($zero_time_now) + 25200;
        $time_start = floatval($zero_time) - 7200;
        //费用计算
        if ($data['category_type']==1){
            //专车送
            $km1 = $carPriceSettingInfo['km1'] * 1000;
            $km2 = $carPriceSettingInfo['km2'] * 1000;
            if ($distance > $km1) {
                if ($distance > $km2){
                    $distance = $distance - $km1; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price6'];
                }else{
                    $distance = $distance - $km1; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price2'];
                }
                $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price1']; //公里单价乘以 每公里价格+起步价
            } else {
                $money = $carPriceSettingInfo['price1']; //起步价
            }
        }elseif ($data['category_type']==2){
            //顺路送
            $km1 = $carPriceSettingInfo['km1'] * 1000;
            $km2 = $carPriceSettingInfo['km2'] * 1000;
            $km3 = $carPriceSettingInfo['km3'] * 1000;
            $km4 = $carPriceSettingInfo['km4'] * 1000;
            $km5 = $carPriceSettingInfo['km5'] * 1000;
            if ($distance > $km1){
                if ($distance > $km2){
                    if ($distance > $km3){
                        if ($distance > $km4){
                            if ($distance > $km5){
                                $distance = $distance - $km1; // 实际公里-起步公里
                                $km_price = $carPriceSettingInfo['price6'];
                            }else{
                                $distance = $distance - $km1; // 实际公里-起步公里
                                $km_price = $carPriceSettingInfo['price5'];
                            }
                        }else{
                            $distance = $distance - $km1; // 实际公里-起步公里
                            $km_price = $carPriceSettingInfo['price4'];
                        }
                    }else{
                        $distance = $distance - $km1; // 实际公里-起步公里
                        $km_price = $carPriceSettingInfo['price3'];
                    }
                }else{
                    $distance = $distance - $km1; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price2'];
                }
                $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price1']; //公里单价乘以 每公里价格+起步价
            } else {
                $money = $carPriceSettingInfo['price1']; //起步价
            }
        }elseif ($data['category_type']==3){
            //代买
            if ($data['category_type_buy'] != 1){
                $km1 = $carPriceSettingInfo['km1'] * 1000;
                if ($distance > $km1){
                    $distance = $distance - $km1; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price6'];
                    $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price1']; //公里单价乘以 每公里价格+起步价
                }else{
                    $money = $carPriceSettingInfo['price1']; //起步价
                }
            }else{
                $money = $carPriceSettingInfo['price2']; //起步价
            }
        }elseif ($data['category_type']==4){
            //代驾
            $km6 = $carPriceSettingInfo['km6'] * 1000;
            $km7 = $carPriceSettingInfo['km7'] * 1000;
            if (($data_now>=$zero_time_now) && ($data_now<=$zero_time_now7)){
                if ($distance > $km6){
                    $distance = $distance - $km6; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price8'];
                    $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price10']; //公里单价乘以 每公里价格+起步价
                }else{
                    $money = $carPriceSettingInfo['price10'];
                }
            }elseif (($data_now>=$time_start) && ($data_now<=$zero_time)){
                if ($distance > $km6){
                    $distance = $distance - $km6; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price8'];
                    $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price10']; //公里单价乘以 每公里价格+起步价
                }else{
                    $money = $carPriceSettingInfo['price10'];
                }
            }else{
                if ($distance > $km7){
                    $distance = $distance - $km7; // 实际公里-起步公里
                    $km_price = $carPriceSettingInfo['price9'];
                    $money = $km_price * ($distance / 1000) + $carPriceSettingInfo['price11']; //公里单价乘以 每公里价格+起步价
                }else{
                    $money = $carPriceSettingInfo['price11'];
                }
            }
        }else{
            echoOk('301', '数据错误');
        }
        if ($data['category_type'] < 4){
            if (($data_now>=$zero_time_now) && ($data_now<=$zero_time_now7)){
                $money = $money + $carPriceSettingInfo['price7'];
            } elseif (($data_now>=$time_start) && ($data_now<=$zero_time)){
                $money = $money + $carPriceSettingInfo['price7'];
            }
        }
        //计算优惠券后价格
        if ($money > $couponInfo['money']) {
            $money = $money - $couponInfo['money'];
        } else {
            $money = 0;
        }
        //获取小费
        $tip = empty($data['tip'])?0:$data['tip'];
        //获取保价费
        $protect_price = empty($data['protect_price'])?0:$data['protect_price'];
        $info['money'] = sprintf("%.2f", floatval($money) + floatval($tip) + floatval($protect_price));
        // $info['money'] = 0.01;
        $info['distance'] = sprintf("%.2f",floatval($distance_now) / 1000);
        $info['tip_price'] = sprintf("%.2f",$tip);
        echoOk(200, '获取成功', $info);
    }
    /**
     * @param $arr
     * @return string
     *
     */
    function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . $this->arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
    function xmlToArray($xml, $type = '')
    {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        //simplexml_load_string()解析读取xml数据，然后转成json格式
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($type == "json") {
            $json = json_encode($xmlstring);
            return $json;
        }
        $arr = json_decode(json_encode($xmlstring), true);
        return $arr;
    }
    function wxpost($url, $post)
    {
        //初始化
        $curl = curl_init();
        $header[] = "Content-type: text/xml";//定义content-type为xml
        //设置抓取的url
        curl_setopt($curl, CURLOPT_URL, $url);
        //设置头文件的信息作为数据流输出
//        curl_setopt($curl, CURLOPT_HEADER, 1);
        //定义请求类型
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        //设置获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //设置post方式提交
        curl_setopt($curl, CURLOPT_POST, 1);
        //设置post数据
        $post_data = $post;
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        //执行命令
        $data = curl_exec($curl);
        //关闭URL请求
        //显示获得的数据
//        print_r($data);
        if ($data) {
            curl_close($curl);
            return $data;
        } else {
            $res = curl_error($curl);
            curl_close($curl);
            return $res;
        }
    }
    /**
     * 单图片上传
     */
    public function pushFIle(){
        $src="";
        $_swap = time();
        $number=$this->GetRandStr(2);
        $_swap = $_swap.$number;
        $fileName = $_swap.".".substr(strrchr($_FILES['file']['name'], '.'), 1);
        move_uploaded_file($_FILES['file']["tmp_name"], "./Public/images/".$fileName);
        if (file_exists("./Public/images/".$fileName)) {
            $src="/Public/images/".$fileName;
        }
        echo json_encode(array('code' => 200,'src' => "https://ryks.dltqwy.com".$src, 'msg' => "上传成功"));
        return;
    }
    function GetRandStr($length){
        $str='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $len=strlen($str)-1;
        $randstr='';
        for($i=0;$i<$length;$i++){
            $num=mt_rand(0,$len);
            $randstr .= $str[$num];
        }
        return $randstr;
    }
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32) {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    /**
     * 生成签名
     * @return 签名，
     */
    public function MakeSign($data) {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->KEY;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public function ToXml($data) {
        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30) {
        $isdir = $_SERVER['DOCUMENT_ROOT']."/cert/";//证书位置;绝对路径
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        /*if(WxPayConfig::CURL_PROXY_HOST != "0.0.0.0"
            && WxPayConfig::CURL_PROXY_PORT != 0){
            curl_setopt($ch,CURLOPT_PROXY, "0.0.0.0");
            curl_setopt($ch,CURLOPT_PROXYPORT, 0);
        }*/
        curl_setopt($ch,CURLOPT_URL, $url);
        //curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,true);
        curl_setopt($ch,CURLOPT_CAINFO,$isdir . '/rootca.pem');

        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);//严格校验2

        //curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $isdir . "/apiclient_cert.pem");
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $isdir . "/apiclient_key.pem");
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            file_put_contents("error.txt", "curl出错，错误码:$error");
            return false;
        }
    }
    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public function FromXml($xml) {
        if(!$xml){
            return false;
            //throw new WxPayException("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
    /**
     * 格式化参数格式化成url参数
     */
    public function ToUrlParams($data) {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
}