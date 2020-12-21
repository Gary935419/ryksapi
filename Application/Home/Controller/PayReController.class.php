<?php

namespace Home\Controller;

use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;
use Think\Controller;
use Home\Model\OrderModel;
use Home\Model\UserModel;
use Home\Model\BalanceRecordModel;

/**
 * Class PayReController
 * @package Home\Controller
 * @property OrderModel $OrderModel
 * @property UserModel $UserModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderTownModel $OrderTownModel
 */
class PayReController extends Controller
{

    private $OrderModel;
    private $UserModel;
    private $BalanceRecordModel;
    private $OrderTrafficModel;
    private $OrderTownModel;

    public function _initialize()
    {
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->OrderTownModel = new OrderTownModel();

    }

    /**
     * 微信支付回调
     */
    public function wxpay()
    {
        $request_body = file_get_contents("php://input");
        $xml = simplexml_load_string($request_body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if (strval($xml->return_code) == 'SUCCESS' && strval($xml->out_trade_no)) {
            $OrderIntercityModel = new \Home\Model\OrderIntercityModel();
            $where['number'] = array('eq', $xml->out_trade_no);
            $order = $OrderIntercityModel->where($where)->find();

            if ($order) {
                // 判断该订单是否已经支付成功
                if (!($order['status'] == '5')) {
                    $money = $order['price'] - $order['coupon'];

                    $CouponModel = new \Home\Model\CouponModel();
                    if (!empty($order['coupon_id'])) {
                        $CouponModel->where('id = "' . $order['coupon_id'] . '"')->delete();
                    }

                    $this->OrderModel->pay_success($xml->out_trade_no);
                    $this->BalanceRecordModel->balance($order['driver_id'], '收到车费', 1, $money);

                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }
            } else {
                // 判断该订单是否已经支付成功
                if (!($order['status'] == '5')) {
                    $OrderTownModel = new \Home\Model\OrderTownModel();
                    $order = $OrderTownModel->where($where)->find();
                    $money = $order['price'];

                    $this->OrderModel->pay_success($xml->out_trade_no);
                    $this->BalanceRecordModel->balance($order['driver_id'], '收到车费', 1, $money);

                    echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                }
            }
        }
    }

    /**
     * 支付宝回调
     */
    public function alipay()
    {
        if (I('out_trade_no') && I('trade_status') == 'TRADE_SUCCESS') {
            $OrderIntercityModel = new \Home\Model\OrderIntercityModel();
            $where['number'] = array('eq', I('out_trade_no'));
            $order = $OrderIntercityModel->where($where)->find();

            if ($order) {
                // 判断该订单是否已经支付成功
                if (!($order['status'] == '5')) {
                    $money = $order['price'] - $order['coupon'];

                    $CouponModel = new \Home\Model\CouponModel();
                    if (!empty($order['coupon_id'])) {
                        $CouponModel->where('id = "' . $order['coupon_id'] . '"')->delete();
                    }

                    $this->OrderModel->pay_success(I('out_trade_no'));
                    $this->BalanceRecordModel->balance($order['driver_id'], '收到车费', 1, $money);

                    echo 'success';
                }
            } else {
                // 判断该订单是否已经支付成功
                if (!($order['status'] == '5')) {
                    $OrderTownModel = new \Home\Model\OrderTownModel();
                    $order = $OrderTownModel->where($where)->find();
                    $money = $order['price'];

                    $this->OrderModel->pay_success(I('out_trade_no'));
                    $this->BalanceRecordModel->balance($order['driver_id'], '收到车费', 1, $money);

                    echo 'success';
                }
            }
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

}