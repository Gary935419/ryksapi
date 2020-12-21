<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;
use Home\Model\BalanceRecordModel;
use Home\Model\OrderModel;
use Home\Model\UserModel;

/**
 * Class UserTripController
 * @package Home\Controller
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property OrderModel $OrderModel
 * @property UserModel $UserModel
 */
class UserTripController extends CommonController {

    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderTrafficModel;
    private $BalanceRecordModel;
    private $OrderModel;
    private $UserModel;

    public function _initialize() {
        parent::_initialize();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
    }

    /**
     * 记录列表
     */
    public function lists() {
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
            case 1: // 城际拼车
                $lists = $this->OrderIntercityModel->get_trip_lists($con);
                break;
            case 2: // 市区出行
                $lists = $this->OrderTownModel->get_trip_lists($con);
                break;
            case 3: // 同城货运
                $lists = $this->OrderTrafficModel->get_trip_lists($con);
                break;
        }

        echoOk(200, '获取成功', $lists);
    }

    /**
     * 记录详情
     */
    public function details() {
        $data = self::$_DATA;

        if (empty($data['taker_type_id']) || empty($data['order_small_id'])) {
            echoOk(301, '必填项不能为空' , []);
        }

        switch ($data['taker_type_id']) {
            case 1: // 城际拼车
                $this->OrderIntercityModel->pay($data['order_small_id']);
                $lists = $this->OrderIntercityModel->get_trip_details($data['order_small_id']);
                break;
            case 2: // 市区出行
                $lists = $this->OrderTownModel->get_trip_details($data['order_small_id']);
                break;
            case 3: // 同城货运
                $lists = $this->OrderTrafficModel->get_trip_details($data['order_small_id']);
                break;
        }

        if ($lists) {
            $lists['taker_type_id'] = $data['taker_type_id'];
            echoOk(200, '获取成功', $lists);
        } else {
            echoOk(301, '获取失败', []);
        }
    }

    /**
     * 城际拼车 - 线下乘客上车 - 支付
     */
    public function pay() {
        $data = self::$_DATA;

        if (empty($data['order_small_id']) || empty($data['pay_type'])) {
            echoOk(301, '必填项不能为空', []);
        }

        $order = $this->OrderIntercityModel->get_info($data['order_small_id']);

        // 判断该订单是否已支付
        if ($order['status'] == '5') {
            echoOk(301, '该订单已支付');
        }

        if ($order['status'] == '6' && $order['line'] == '2' && $order['status_online'] == '10') {
            $number = $this->OrderIntercityModel->pay($data['order_small_id']);
            $money = $order['price'] - $order['coupon'];
            $money = (ceil($money*1000))/1000;

            switch ($data['pay_type']) {
                case 1: // 支付宝
                    $aliPay = new \aliPay();
                    $alipay_sign = $aliPay->getRequestParam($number, $money, 'Home/PayRe/alipay'); // 生成签名
                    break;
                case 2: // 微信
                    $wxPay = new \WxPay();
                    $txnAmt = intval($money * 100);
                    $txnTime = date("YmdHis");
                    $result = $wxPay->payOrder($number, $txnTime, $txnAmt);
                    $appRequest = $wxPay->getAppRequest($result['prepay_id']);
                    $appRequest['_package'] = $appRequest['package'];
                    unset($appRequest['package']);
                    $weixin_sign = [
                        'order_no' => $number,
                        'prepay_id' => $result['prepay_id'],
                        'money' => $txnAmt,
                        'txntime' => $txnTime,
                        'app_request' => $appRequest
                    ];
                    break;
                case 3: // 现金
                    $this->OrderModel->pay_success($number);
                    break;
            }

            // 积分
            $user = $this->UserModel->get_info($order['user_id']);
            $this->UserModel->save_info($order['user_id'], array('integral' => $user['integral'] + intval($money)));

            $re = [
                'alipay_sign' => $alipay_sign ? $alipay_sign : '',
                'weixin_sign' => $weixin_sign ? $weixin_sign : (object)array()
            ];

            echoOk(200, '操作成功', $re);
        } else {
            echoOk(301, '该订单状态不符合支付条件');
        }
    }

}