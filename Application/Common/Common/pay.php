<?php

/**
 * Class AliPay
 * 支付宝支付
 */
class aliPay {
    static $NOTIFY_URL;
    static $REFUND_URL;
    private $aliPayApi;

    public function __construct(){
        vendor("alipay.AliPayApi");
        //self::$NOTIFY_URL = C("webaddress")."control.php/Calipay/aliPayCallBack";
        //self::$REFUND_URL = C("webaddress")."index.php/Admin/Pay/aliPayRefundCallBack";
        $this->aliPayApi = new AliPayApi();
    }

    /**
     * 验证支付宝支付回调消息是否合法
     * @param $data
     * @return bool
     */
    public function verifyCallBack($data){
        if(empty($data)) {
            return false;
        }
        else {
            //生成签名结果
            $isSign = $this->aliPayApi->getSignVeryfy($data, $data["sign"]);
            //获取支付宝远程服务器ATN结果（验证是否是支付宝发来的消息）
            $responseTxt = 'false';
            if (!empty($data["notify_id"])){
                $responseTxt = $this->aliPayApi->getResponse($data["notify_id"]);
            }
            //验证
            //$responsetTxt的结果不是true，与服务器设置问题、合作身份者ID、notify_id一分钟失效有关
            //isSign的结果不是true，与安全校验码、请求时的参数格式（如：带自定义参数等）、编码格式有关
            if (preg_match("/true$/i",$responseTxt) && $isSign) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取带签名的请求体
     * @param string $order_no   订单号
     * @param float $money       交易金额 单位：元
     * @param string $notify_url 服务器异步通知页面路径
     * @return string
     */
    public function getRequestParam($order_no, $money, $notify_url){
        $request = [
            "partner"=>PARTNER,
            "seller_id"=>SELLER_ID,
            "out_trade_no"=> $order_no,
            "subject" => "购买商品",
            "body" => "购买商品",
            "total_fee" => $money,
            "notify_url" => C('web_address').'index.php/'.$notify_url,
            "service" => "mobile.securitypay.pay",
            "payment_type" => "1",
            "_input_charset" => "utf-8",
            "it_b_pay" => "30m",
            // "return_url" => "m.alipay.com"
        ];
        $request_str = $this->aliPayApi->getRequestStr(($request));
        $sign = $this->aliPayApi->rsaSign($request_str);
        $request['sign'] = urlencode($sign);
        $request['sign_type'] = "RSA";
        $result = $this->aliPayApi->getRequestStr($request);
        return $result;
    }

    /**
     * 支付宝退款
     * @param string $batch_no 退款批次号
     * @param $trade_no
     * @param $money
     * @param $notify_url
     * @return 支付宝退款URL
     */
    public function refund($batch_no,$trade_no, $money, $notify_url) {
        $result = $this->aliPayApi->refund($batch_no, $trade_no, $money, C('web_address').'index.php/'.$notify_url);
        return $result;
    }

}

/**
 * Class WxPay
 * 微信支付
 *
 */
class WxPay{
    static $NOTIFY_URL;         //支付回调地址
    static $TRADE_STATE = [
        "SUCCESS" => "支付成功",
        "REFUND" => "转入退款",
        "NOTPAY" => "未支付",
        "CLOSED" => "已关闭",
        "REVOKED" => "已撤销（刷卡支付）",
        "USERPAYING" => "用户支付中",
        "PAYERROR" => "支付失败"
    ];

    public function __construct()
    {
        vendor("wxpay.lib.WxPay#Api");
        self::$NOTIFY_URL = C('web_address').'index.php/Home/PayRe/wxpay';
    }

    /**
     * 微信下单
     * @param string $order_no 商户订单号
     * @param string $txnTime 交易时间 date("YmdHis")
     * @param int $txnAmt 交易金额 单位为分
     * @return array 成功时返回
     * @throws Exception
     * @throws WxPayException
     */
    public function payOrder($order_no, $txnTime, $txnAmt){
        $input = new WxPayUnifiedOrder();
        $input->SetBody($order_no);
        $input->SetAttach("");
        $input->SetOut_trade_no($order_no);
        $input->SetTotal_fee($txnAmt);
        $input->SetTime_start($txnTime);
        $input->SetTime_expire(date("YmdHis", strtotime($txnTime) + 600));
        $input->SetNotify_url(self::$NOTIFY_URL);
        $input->SetTrade_type("APP");

        $result = WxPayApi::unifiedOrder($input);
        if($result['return_code']!="SUCCESS"){
            throw new \Exception($result['return_msg']);
        }
        if($result['result_code']!="SUCCESS"){
            throw new \Exception($result['err_code_des']);
        }

        return $result;
    }

    /**
     * 获取APP端的请求参数
     * @param $prepayId
     * @return array
     */
    public function getAppRequest($prepayId){
        $input = new WxPayRequest();
        $input->setPrepayid($prepayId);
        $result = WxPayApi::getSignRequest($input);
        return $result;
    }

    /**
     * 微信支付退款
     * @param string $transaction_id    微信订单号
     * @param int $money    交易金额 单位为分
     * @throws WxPayException
     * @return array
     */
    public function refund($transaction_id, $money){
        $input = new WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($money|0);
        $input->SetRefund_fee($money|0);
        $input->SetOut_refund_no(date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);

        $result = WxPayApi::refund($input);
        return $result;
    }

    /**
     * 微信订单查询
     * @param string $order_no 商户订单号
     * @param string $transaction_id 微信订单号
     * @return array 成功时返回
     * @throws Exception
     * @throws WxPayException
     */
    public function orderQuery($order_no="", $transaction_id=""){
        $input = new WxPayOrderQuery();
        if(!empty($order_no)){
            $input->SetOut_trade_no($order_no);
        }elseif(!empty($transaction_id)){
            $input->SetTransaction_id($transaction_id);
        }else{
            throw new \Exception("商户订单号或微信订单号必须！");
        }
        $response = WxPayApi::orderQuery($input);
        $result = [];
        if($response['return_code']!='SUCCESS'){
            if(!empty($response['return_msg'])){
                $result['msg'] = $response['return_msg'];
            }else{
                $result['msg'] = "查询失败，请稍后再试";
            }
        }elseif($response['result_code']!='SUCCESS'){
            $result['msg'] = !empty($response['err_code_des'])?$response['err_code_des']:"请稍后再试";
        }else{
            $result['msg'] = self::$TRADE_STATE[$response['trade_state']];
        }

        return $result;
    }
}