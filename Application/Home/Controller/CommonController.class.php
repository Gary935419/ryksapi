<?php

namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller
{

    static $_DATA;

    public function _initialize()
    {
        header('Access-Control-Allow-Origin: *');
//        $md5 = '789487D70BCB07DB9CAD424B26179E9F';
        $md5      = '4EF82E3603825745124695977A46E8C2';
        $post_md5 = I('md5');
        if (empty($post_md5)) {
            echoOk(301, '没有密匙');
            exit;
        } else {
            if (I('md5') != $md5) {
                echoOk(301, '密匙错误');
                exit;
            }
        }
        self::$_DATA = getRequest();
    }

    /**
     * 互亿短信发送
     * @param $phone 手机
     * @param $code 验证码
     * @param $cf_username 互亿短信账号
     * @param $cf_userpwd 互亿短信密码
     * @return bool
     */
    public function send_code($phone, $text)
    {
        $url  = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=" . C('phone_account') . "&password=" . C('phone_psd') . "&mobile=" . $phone . "&content=$text";
        $html = file_get_contents($url);
        if ($html) {
            return $html;
        } else {
            return false;
        }
    }

}