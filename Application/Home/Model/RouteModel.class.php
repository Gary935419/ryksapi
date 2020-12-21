<?php
namespace Home\Model;
use Think\Model;

class RouteModel extends Model {

    /**
     * 获取线路列表
     * @return mixed
     */
    public function get_lists() {
        $lists = $this->where('status = 1')->order('paixu DESC')->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['route_city_name1'] = M('route_city')->where('id = '.$v['route_city_id1'])->getField('name');
                $lists[$k]['route_city_name2'] = M('route_city')->where('id = '.$v['route_city_id2'])->getField('name');
            }
        } else {
            $lists = [];
        }
        return $lists;
    }

    /**
     * 给该路线管理员发送短信
     * @param $route_city_id1
     * @param $route_city_id2
     * @param $user_id
     * @param $location
     * @param $arrival_position
     * @param $people_num
     * @param $type 1预约 2立即
     * @param $time 下单时间
     * @return bool
     */
    public function send_admin_tel($route_city_id1, $route_city_id2, $user_id, $location, $arrival_position, $people_num, $type, $time) {
        $where = '( route_city_id1 = '.$route_city_id1.' AND route_city_id2 = '.$route_city_id2.' ) OR '.
                 '( route_city_id1 = '.$route_city_id2.' AND route_city_id2 = '.$route_city_id1.')';
        $data = $this->where($where)->find();
        
        if ($data) {
            $route_city = new \Home\Model\RouteCityModel();
            $user = new \Home\Model\UserModel();
            
            $route_city_font1 = $route_city->get_city_name($route_city_id1);
            $route_city_font2 = $route_city->get_city_name($route_city_id2);
            $route = $route_city_font1.'-'.$route_city_font2;
            
            $account = $user->get_info($user_id);
            
            $this->send_code($data['admin_tel'], C('phone_account'), C('phone_psd'), $route, $account['account'], $location, $arrival_position, $people_num, $type, $time);
        }
    }

    /**
     * 互亿短信发送
     * @param $phone 手机
     * @param $cf_username 互亿短信账号
     * @param $cf_userpwd 互亿短信密码
     * @param $route 线路
     * @param $account 客户手机
     * @param $location
     * @param $arrival_position
     * @param $people_num
     * @param $type 1预约 2立即
     * @param $time 下单时间
     * @return bool
     */
    public function send_code($phone, $cf_username, $cf_userpwd, $route, $account, $location, $arrival_position, $people_num, $type, $time) {
        if ($type == 1) { // 预约
            $arr_time = explode(' ', $time);
            $time = $arr_time[0].'/'.$arr_time[1];
            $str = '线路：'.$route.'预约叫车，'.$people_num.'人，时间：'.$time.'，出发地：'.$location.'，目的地：'.$arrival_position.'，请尽快电话回复：'.$account;
            $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".$cf_username."&password=".$cf_userpwd."&mobile=".$phone."&content=".$str;
        } elseif ($type == 2) { // 立即
            $str = '线路：'.$route.'现在出发叫车，'.$people_num.'人，时间：'.$time.'，出发地：'.$location.'，目的地：'.$arrival_position.'，请尽快电话回复：'.$account;
            $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".$cf_username."&password=".$cf_userpwd."&mobile=".$phone."&content=".$str;
        }
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 坐车模式
     * @param $k
     * @return array|mixed
     */
    public function route_nature($k) {
        $arr = array(
            1 => '长途',
            2 => '短途'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 添加线路
     * @param $data
     * @return bool|mixed
     */
    public function add_route($data) {
        if ($data['route_city_id1'] == $data['route_city_id2']) {
            return false;
        }
        $where  = '( route_city_id1 = '.$data['route_city_id1'].' AND route_city_id2 = '.$data['route_city_id2'].' )';
        $where .= ' OR ( route_city_id1 = '.$data['route_city_id2'].' AND route_city_id2 = '.$data['route_city_id1'].' )';
        $temp = $this->where($where)->find();
        if ($temp) {
            return false;
        }
        $temp = $this->add($data);
        return $temp;
    }

    /**
     * 编辑线路
     * @param $id
     * @param $data
     * @return bool
     */
    public function edit_route($id, $data) {
        $temp = $this->where('id = '.$id)->save($data);
        return $temp;
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function get_info($id) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->find();
        return $temp;
    }

    /**
     * 根据城市获取路线
     * @param $route_city_id1
     * @param $route_city_id2
     * @return mixed
     */
    public function to_city_get_route($route_city_id1, $route_city_id2) {
        $where  = '( route_city_id1 = '.$route_city_id1.' AND route_city_id2 = '.$route_city_id2.' )';
        $where .= ' OR ( route_city_id2 = '.$route_city_id1.' AND route_city_id1 = '.$route_city_id2.' )';
        $route = $this->where($where)->find();
        return $route;
    }

    public function send_user_tel($phone, $cf_username, $cf_userpwd, $times) {
        $str = '您选择的城际拼车已下单成功，出行时间'.$times.'，请等待系统派车，详情可在我的订单里查看。';
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".$cf_username."&password=".$cf_userpwd."&mobile=".$phone."&content=".$str;

        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

}