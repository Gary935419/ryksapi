<?php

namespace Home\Controller;

use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;
use Think\Controller;
use Home\Model\OrderModel;
use Home\Model\UserModel;
use Home\Model\BalanceRecordModel;
use Home\Model\UserWithdrawalModel;
/**
 * Class PayMerchantsController
 * @package Home\Controller
 * @property OrderModel $OrderModel
 * @property UserModel $UserModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderTownModel $OrderTownModel
 * @property UserWithdrawalModel $UserWithdrawalModel
 */
class PayMerchantsController extends CommonController
{

    private $OrderModel;
    private $UserModel;
    private $BalanceRecordModel;
    private $OrderTrafficModel;
    private $OrderTownModel;
    private $UserWithdrawalModel;

    public function _initialize()
    {
        $this->OrderModel = new OrderModel();
        $this->UserModel = new UserModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->UserWithdrawalModel = new UserWithdrawalModel();
    }

    /**
     * 用户提现
     */
    /**
     * [sendMoney 企业付款到零钱]
     * @param [type] $amount  [发送的金额（分）目前发送金额不能少于1元]
     * @param [type] $re_openid [发送人的 openid]
     * @param string $desc  [企业付款描述信息 (必填)]
     * @param string $check_name [收款用户姓名 (选填)]
     * @return [type]    [description]
     */
    public function sendMoney(){
        $params = $_POST;
        if (empty($params['withdrawal_price'])) {
            echoOk(301, '提现金额不能为空', []);
        }
        if (empty($params['user_id'])) {
            echoOk(301, '请先登录', []);
        }
        $user_info = $this->UserModel->get_user($params['user_id']);
        $money = $user_info['money'];
        $re_openid = $user_info['open_id'];
        $total_amount = (100) * $params['withdrawal_price'];

        $data=array(
            'mch_appid'=>'wx95ff8ddda8027413',                            //商户账号appid
            'mchid'=> '1580673321',                                       //商户号
            'nonce_str'=>$this->createNoncestr(),                         //随机字符串
            'partner_trade_no'=> date('YmdHis').rand(1000, 9999),  //商户订单号
            'openid'=> $re_openid,                                        //用户openid
            'check_name'=>'NO_CHECK',                                     //校验用户姓名选项,
            'amount'=>$total_amount,                                      //金额
            'desc'=> "用户提现",                                           //企业付款描述信息
        );

        //生成签名算法
        $secrect_key="Nruyoukuaisong152326197512071176";                  //这个就是个API密码。
        $data=array_filter($data);
        ksort($data);
        $str='';
        foreach($data as $k=>$v) {
            $str.=$k.'='.$v.'&';
        }
        $str.='key='.$secrect_key;
        $data['sign']=md5($str);
        //生成签名算法

        $xml=$this->arraytoxml($data);
        $url='https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers'; //调用接口
        $res=$this->curl_post_ssl($url,$xml);
        $return=$this->xmltoarray($res);
        $res = $return['result_code'];
//        $res = 'SUCCESS';
//        $responseObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
//        $res= $responseObj->result_code; //SUCCESS 如果返回来SUCCESS,则发生成功，处理自己的逻辑
        if ($res === 'SUCCESS'){
            $money_new = floatval($money) - floatval($params['withdrawal_price']);
            $set['money'] = $money_new;
            $this->UserModel->save_info($params['user_id'],$set);
            $insert = [
                'user_id' => $params['user_id'],
                'user_type' => 1,
                'price' => $params['withdrawal_price'],
                'add_time' => time(),
                'withdrawal_type' => 1,
            ];
            //提现记录插入
            $this->UserWithdrawalModel->withdrawal_insert($insert);
            echoOk(200, '提现成功', $res);
        }else{
            echoOk(301, '提现失败', $res);
        }
    }


    /**
     * [xmltoarray xml格式转换为数组]
     * @param [type] $xml [xml]
     * @return [type]  [xml 转化为array]
     */
    function xmltoarray($xml) {
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring),true);
        return $val;
    }

    /**
     * [arraytoxml 将数组转换成xml格式（简单方法）:]
     * @param [type] $data [数组]
     * @return [type]  [array 转 xml]
     */
    function arraytoxml($data){
        $str='<xml>';
        foreach($data as $k=>$v) {
            $str.='<'.$k.'>'.$v.'</'.$k.'>';
        }
        $str.='</xml>';
        return $str;
    }

    /**
     * [createNoncestr 生成随机字符串]
     * @param integer $length [长度]
     * @return [type]   [字母大小写加数字]
     */
    function createNoncestr($length =32){
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYabcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";

        for($i=0;$i<$length;$i++){
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param [type] $url  [发送地址]
     * @param [type] $xmldata [发送文件格式]
     * @param [type] $second [设置执行最长秒数]
     * @param [type] $aHeader [设置头部]
     * @return [type]   [description]
     */
    function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array()){
        $isdir = $_SERVER['DOCUMENT_ROOT']."/cert/";//证书位置;绝对路径

        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $isdir . 'apiclient_cert.pem');//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $isdir . 'apiclient_key.pem');//证书位置
        curl_setopt($ch, CURLOPT_CAINFO, 'PEM');
        curl_setopt($ch, CURLOPT_CAINFO, $isdir . 'rootca.pem');
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }
}