<?php

namespace Home\Controller;

use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;
use Think\Controller;
use Home\Model\OrderModel;
use Home\Model\UserModel;
use Home\Model\BalanceRecordModel;
use Home\Model\TopupModel;
use Home\Model\CouponModel;
use Home\Model\CouponsetModel;
/**
 * Class PayReController
 * @package Home\Controller
 * @property OrderModel $OrderModel
 * @property UserModel $UserModel
 * @property CouponsetModel $CouponsetModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderTownModel $OrderTownModel
 * @property TopupModel $TopupModel
 * @property CouponModel $CouponModel
 */
class PayReController extends Controller
{

    private $OrderModel;
    private $UserModel;
    private $BalanceRecordModel;
    private $OrderTrafficModel;
    private $OrderTownModel;
    private $TopupModel;
    private $CouponModel;
    private $CouponsetModel;

    public function _initialize()
    {
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->TopupModel = new TopupModel();
        $this->CouponModel = new CouponModel();
        $this->CouponsetModel = new CouponsetModel();
    }

    /**
     * 微信支付回调 app下单
     */
    public function wxpay()
    {
        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['pay_number'] =$ordernumber;
            $bigOrderInfo = $this->OrderModel->where($pay_numberWhere)->find();
            $orderInfoWhere['big_order_id'] =$bigOrderInfo['id'];
            $save = [
                'order_status' => 2,
                'pay_type' => 1,
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
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
    }
    /**
     * 微信支付回调 app超时
     */
    public function wxpay_new()
    {
        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['delay_number'] = $ordernumber;
            $save = [
                'delay_state' => 1,
                'pay_type_new' => 1,
            ];
            $orderInfo = $this->OrderTownModel->where($pay_numberWhere)->find();
            $this->OrderTownModel->save_info($orderInfo['id'], $save);
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
    }
    /**
     * 微信app充值支付回调
     */
    public function wxpay_new_top()
    {
        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['paynumber'] =$ordernumber;
            $bigOrderInfo = $this->TopupModel->where($pay_numberWhere)->find();
            $user_info = $this->UserModel->get_user($bigOrderInfo['uid']);
            $moneynew = floatval($bigOrderInfo['money']) + floatval($user_info['money']);
            $save = [
                'status' => 1,
                'pay_type' => 0,
            ];
            $savenew = [
                'money' => $moneynew,
            ];
            $this->TopupModel->save_info($bigOrderInfo['id'], $save);
            $this->UserModel->save_info($bigOrderInfo['uid'], $savenew);
            $CouponsetModel = $this->CouponsetModel->get_user(1);
            if ($user_info['is_merchants'] == 1){
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
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
    }
    /**
     * 支付宝回调 app下单
     */
    public function alipay()
    {
        $data = getRequest();
        file_put_contents("data.txt",$data);
        if (!empty($data['out_trade_no']) && $data['trade_status'] == 'TRADE_SUCCESS') {
            $ordernumber = array('eq', $data['out_trade_no']);
            $pay_numberWhere['pay_number'] =$ordernumber;
            $bigOrderInfo = $this->OrderModel->where($pay_numberWhere)->find();
            $orderInfoWhere['big_order_id'] =$bigOrderInfo['id'];
            $save = [
                'order_status' => 2,
                'pay_type' => 2,
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
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
    }
    /**
     * 支付宝回调 app超时
     */
    public function alipay_new()
    {
        $data = getRequest();
        file_put_contents("data1.txt",$data);
        if (!empty($data['out_trade_no']) && $data['trade_status'] == 'TRADE_SUCCESS') {
            $ordernumber = array('eq', I('out_trade_no'));
            $pay_numberWhere['delay_number'] =$ordernumber;
            $save = [
                'delay_state' => 1,
                'pay_type_new' => 2,
            ];
            $orderInfo = $this->OrderTownModel->where($pay_numberWhere)->find();
            $this->OrderTownModel->save_info($orderInfo['id'], $save);
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
    }
    /**
     * 支付宝充值回调
     */
    public function alipay_new_top()
    {
        $data = getRequest();
        file_put_contents("data2.txt",$data);
        if (!empty($data['out_trade_no']) && $data['trade_status'] == 'TRADE_SUCCESS') {
            $ordernumber = array('eq', I('out_trade_no'));
            $pay_numberWhere['paynumber'] =$ordernumber;
            $bigOrderInfo = $this->TopupModel->where($pay_numberWhere)->find();
            $user_info = $this->UserModel->get_user($bigOrderInfo['uid']);
            $moneynew = floatval($bigOrderInfo['money']) + floatval($user_info['money']);
            $save = [
                'status' => 1,
                'pay_type' => 1,
            ];
            $savenew = [
                'money' => $moneynew,
            ];
            $this->TopupModel->save_info($bigOrderInfo['id'], $save);
            $this->UserModel->save_info($bigOrderInfo['uid'], $savenew);
            $CouponsetModel = $this->CouponsetModel->get_user(1);
            if ($user_info['is_merchants'] == 1){
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
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        }else{
            echo '<xml><return_code><![CDATA[ERROR]]></return_code><return_msg><![CDATA[ERROR]]></return_msg></xml>';
        }
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
    /**
     * 微信支付回调 小程序下单
     */
    function Wx_notify_url()
    {

        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['pay_number'] =$ordernumber;
            $bigOrderInfo = $this->OrderModel->where($pay_numberWhere)->find();
            $orderInfoWhere['big_order_id'] =$bigOrderInfo['id'];
            $save = [
                'order_status' => 2,
                'pay_type' => 1,
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
            echo 'SUCCESS';
        } else {
            echo 'SUCCESS';
            echo '支付失败';
        }
    }
    /**
     * 微信支付回调 小程序超时
     */
    function Wx_notify_url_new()
    {

        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['delay_number'] =$ordernumber;
            $save = [
                'delay_state' => 1,
                'pay_type_new' => 1,
            ];
            $orderInfo = $this->OrderTownModel->where($pay_numberWhere)->find();
            $this->OrderTownModel->save_info($orderInfo['id'], $save);
            echo 'SUCCESS';
        } else {
            echo 'SUCCESS';
            echo '支付失败';
        }
    }
    /**
     * 微信支付回调 小程序充值
     */
    function topup_treatment()
    {

        $receipt = $_REQUEST;
        if ($receipt == null) {
            $receipt = file_get_contents("php://input");
        }
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
        }
        $post_data = $this->xmlToArray($receipt);
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        ksort($post_data);// 对数据进行排序
        $str = $params = http_build_query($post_data);//对数组数据拼接成key=value字符串
        $user_sign = strtoupper(md5($str . "&key=Nruyoukuaisong152326197512071176"));   //再次生成签名，与$postSign比较
        $ordernumber = $post_data['out_trade_no'];// 订单可以查看一下数据库是否有这个订单

        if ($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign) {
            $pay_numberWhere['paynumber'] =$ordernumber;
            $bigOrderInfo = $this->TopupModel->where($pay_numberWhere)->find();
            $user_info = $this->UserModel->get_user($bigOrderInfo['uid']);
            $moneynew = floatval($bigOrderInfo['money']) + floatval($user_info['money']);
            $save = [
                'status' => 1,
                'pay_type' => 0,
            ];
            $savenew = [
                'money' => $moneynew,
            ];
            $this->TopupModel->save_info($bigOrderInfo['id'], $save);
            $this->UserModel->save_info($bigOrderInfo['uid'], $savenew);
            $CouponsetModel = $this->CouponsetModel->get_user(1);
            if ($user_info['is_merchants'] == 1){
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
            echo 'SUCCESS';
        } else {
            echo 'SUCCESS';
            echo '支付失败';
        }
    }
}