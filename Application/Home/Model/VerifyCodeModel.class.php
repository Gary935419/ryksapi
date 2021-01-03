<?php
namespace Home\Model;
use Think\Model;

class VerifyCodeModel extends Model{

    /**
     * 逻辑:添加验证码
     * @param $phone 手机
     * @return int 验证码
     */
    public function add_code($phone,$code) {
        
        $where['account'] = array('eq', $phone);
        $data = $this->where($where)->find();
        if (!$data) {
            $add = [
                'account' => $phone,
                'code' => $code,
                'add_time' => time()
            ];
            $this->add($add); // 写入验证码
        } else {
            $is_forging = $this->is_forging(C('phone_time'), $data['add_time']); // 验证码发送时效
            if ($is_forging) {
                echoOk(301, '时效未过,发送失败');
            }
            
            $save = [
                'id' => $data['id'],
                'code' => $code,
                'add_time' => time()
            ];
            $this->save($save); // 更新验证码
        }

        $this->send_code($phone, $code, C('phone_account'), C('phone_psd'));
        return strval($code);
    }

    /**
     * 逻辑:验证码是否正确
     * @param $phone 手机
     * @param $code 验证码
     * 无返回值
     */
    public function is_code($phone, $code) {
        $where['account'] = array('eq', $phone);
        $where['code'] = array('eq', $code);
        $data = $this->where($where)->find();
        if (!$data) {
            echoOk(301, '验证码错误');
        }

        $is_forging = $this->is_forging(C('phone_time_verify'), $data['add_time']); // 验证码验证时效
        if (!$is_forging) {
            echoOk(301, '验证码已过期');
        }
    }

    /**
     * 判断时效
     * @param $phone_time 时效时长
     * @param $time 时间
     * @return bool false已过 true未过
     */
    public function is_forging($phone_time, $time) {
        if (time() - $time <= $phone_time) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 互亿短信发送
     * @param $phone 手机
     * @param $code 验证码
     * @param $cf_username 互亿短信账号
     * @param $cf_userpwd 互亿短信密码
     * @return bool
     */
    public function send_code($phone, $code, $cf_username, $cf_userpwd) {
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".$cf_username."&password=".$cf_userpwd."&mobile=".$phone."&content=您的验证码是：".$code."。请不要把验证码泄露给其他人。";
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

}