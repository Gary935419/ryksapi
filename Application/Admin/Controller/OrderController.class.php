<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\UserModel;
use Home\Model\RouteCityModel;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;
use Home\Model\OrderTrafficModel;
use Home\Model\OrderModel;
use Home\Model\CarTypeModel;

/**
 * Class OrderController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property UserModel $UserModel
 * @property RouteCityModel $RouteCityModel
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 * @property OrderTrafficModel $OrderTrafficModel
 * @property OrderModel $OrderModel
 * @property CarTypeModel $CarTypeModel
 */
class OrderController extends CommonController {

    private $ListsModel;
    private $UserModel;
    private $RouteCityModel;
    private $OrderIntercityModel;
    private $OrderTownModel;
    private $OrderTrafficModel;
    private $OrderModel;
    private $CarTypeModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->UserModel = new UserModel();
        $this->RouteCityModel = new RouteCityModel();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
        $this->OrderTrafficModel = new OrderTrafficModel();
        $this->OrderModel = new OrderModel();
        $this->CarTypeModel = new CarTypeModel();
    }
    
    /**
     * 城际拼车
     */
    public function intercity() {
        // 获取所有城市
        $route_city_id1 = $this->RouteCityModel->get_lists();
        
        // 列表配置
        $data['model'] = 'order_intercity';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        if (!empty(I('status'))) {
            $data['where']['status'] = array('eq', I('status'));
            if (I('status') == '6') {
                $data['where']['line'] = array('eq', 1);
            }
        }
        I('route_city_id1') ? $data['where']['route_city_id1'] = array('eq', I('route_city_id1')) : '';
        I('route_city_id2') ? $data['where']['route_city_id2'] = array('eq', I('route_city_id2')) : '';
        if (!empty(I('driver_account'))) {
            $driver_id = M('user')->where('account = "'.I('driver_account').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_id);
        }
        if (!empty(I('driver_car_number'))) {
            $driver_car_number = M('user')->where('car_number = "'.I('driver_car_number').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_car_number);
        }
        if (!empty(I('user_account'))) {
            $user_id = M('user')->where('account = "'.I('user_account').'" AND type = 1')->getField('id');
            $data['where']['user_id'] = array('eq', $user_id);
        }
        
        // 列表
        $status_lists = $this->OrderIntercityModel->status('');
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['user_id']);
            $driver_user = $v['driver_id'] ? $this->UserModel->get_info($v['driver_id']) : '';
            $lists[$k]['user_name'] = $user['name'];
            $lists[$k]['driver_user_name'] = $driver_user['name'];
            $lists[$k]['route_city_font1'] = $this->RouteCityModel->get_city_name($v['route_city_id1']);
            $lists[$k]['route_city_font2'] = $this->RouteCityModel->get_city_name($v['route_city_id2']);
            $lists[$k]['status'] = $this->OrderIntercityModel->status($v['status']);
            $lists[$k]['status_online'] = $this->OrderIntercityModel->status_online($v['status_online']);
            $lists[$k]['car_type_id'] = $this->CarTypeModel->get_car_name($v['car_type_id']);
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('status_lists', $status_lists);
        $this->assign('route_city_id1', $route_city_id1);
        
        $this->display();
    }

    /**
     * 城际拼车支付
     */
    public function intercity_pay_send() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        $number = $this->OrderIntercityModel->pay($_POST['order_id']);
        $this->OrderModel->pay_success($number);

        echo 1;
    }

    /**
     * 城际拼车完成
     */
    public function intercity_line_order() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        M('order_intercity')->where('id = '.$_POST['order_id'])->save(array('status_online' => '6'));

        echo 1;
    }

    /**
     * 城际拼车详情
     */
    public function order_intercity_details() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $data = $this->ListsModel->get_info('order_intercity', $_GET['id']);
        $data['user'] = $this->UserModel->get_info($data['user_id']);
        $data['driver_user'] = $this->UserModel->get_info($data['driver_id']);
        $data['route_city_font1'] = $this->RouteCityModel->get_city_name($data['route_city_id1']);
        $data['route_city_font2'] = $this->RouteCityModel->get_city_name($data['route_city_id2']);
        $data['car_mode'] = $this->OrderIntercityModel->car_mode($data['car_mode']);
        
        if ($data['status'] == '6' && $data['line'] == '2') {
            $data['status'] = $this->OrderIntercityModel->status_online($data['status_online']);
        } else {
            $data['status'] = $this->OrderIntercityModel->status($data['status']);
        }
        
        $data['line'] = $this->OrderIntercityModel->line($data['line']);
        $data['car_type_id'] = $this->CarTypeModel->get_car_name($data['car_type_id']);

        $this->assign('data', $data);

        $this->display();
    }

    /**
     * 城际拼车派单
     */
    public function order_intercity_send() {
        // 列表配置
        $data['model'] = 'user';
        $data['where']['type'] = array('eq', 2);
        $data['where']['check'] = array('eq', 1);
        $data['page_size'] = 5;
        $data['order'] = 'id desc';

        // 搜索
        I('name') ? $data['where']['name'] = array('like', '%'.I('name').'%') : '';
        I('account') ? $data['where']['account'] = array('like', '%'.I('account').'%') : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];

        foreach ($lists as $k => $v) {
            $lists[$k]['car_type_id'] = $this->CarTypeModel->get_car_name($v['car_type_id']);
        }
        
        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('order_id', I('id'));
        
        $this->display();
    }

    /**
     * 城际拼车指派
     */
    public function order_intercity_assign() {
        if (empty($_POST['order_id']) || empty($_POST['driver_id'])) {
            die('没有参数');
        }
        
        $this->OrderIntercityModel->assign_send($_POST['order_id'], $_POST['driver_id']);

        $order = $this->OrderIntercityModel->get_info($_POST['order_id']);
        $user = $this->UserModel->get_info($order['user_id']);
        $driver = $this->UserModel->get_info($_POST['driver_id']);

        if ($order['order_time']) {
            $time_arr = explode(' ', $order['order_time']);
            $time = $time_arr[0].'/'.$time_arr[1];
        } else {
            $time = date('Y-m-d/H:i', $order['add_time']);
        }

        $this->sj_send_code($driver['account'], $user['account'], $time, $order['location'], $order['arrival_position'], $order['people_num']); // 发给司机

        if ($order['order_time']) { // 预约
            $arr_time = explode(' ', $order['order_time']);
            $this->yh_send_code($user['account'], $arr_time[0].'/'.$arr_time[1], $driver['account']); // 发给用户
        } else { // 现在出发
            $this->yh_now_send_code($user['account'], date('Y-m-d', $order['add_time']), $driver['account']); // 发给用户
        }

        echo 1;
    }

    /**
     * 司机短信发送
     * @param $phone
     * @param $user_phone
     * @param $times
     * @param $location
     * @param $arrival_position
     * @param $people_num
     * @return bool
     */
    public function sj_send_code($phone, $user_phone, $times, $location, $arrival_position, $people_num) {
        $str = "您接到乘客".$user_phone."的城际拼车订单，预约时间：".$times."，出发地：".$location."，目的地：".$arrival_position."，人数：".$people_num."，详情可在我的订单查看。";
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".C('phone_account')."&password=".C('phone_psd')."&mobile=".$phone."&content=".$str;
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用户短信发送
     * @param $phone
     * @param $times
     * @param $driver_phone
     * @return bool
     */
    public function yh_send_code($phone, $times, $driver_phone) {
        $str = "您的城际拼车预约".$times."已成功派单，可在行程记录中查看详情，接驾司机".$driver_phone."会在出发前半小时与您联系。";
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".C('phone_account')."&password=".C('phone_psd')."&mobile=".$phone."&content=".$str;
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用户短信发送
     * @param $phone
     * @param $times
     * @param $driver_phone
     * @return bool
     */
    public function yh_now_send_code($phone, $times, $driver_phone) {
        $str = "您的城际拼车".$times."已成功派单，可在行程记录中查看详情，接驾司机".$driver_phone."会及时与您联系。";
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".C('phone_account')."&password=".C('phone_psd')."&mobile=".$phone."&content=".$str;
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 司机短信发送
     * @param $phone
     * @param $user_phone
     * @param $times
     * @param $location
     * @param $arrival_position
     * @return bool
     */
    public function tc_sj_send_code($phone, $user_phone, $times, $location, $arrival_position) {
        $str = "您接到乘客".$user_phone."的同城货运订单，预约时间：".$times."，出发地：".$location."，目的地：".$arrival_position."，详情可在我的订单查看。";
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".C('phone_account')."&password=".C('phone_psd')."&mobile=".$phone."&content=".$str;
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 用户短信发送
     * @param $phone
     * @param $times
     * @param $driver_phone
     * @return bool
     */
    public function tc_yh_send_code($phone, $times, $driver_phone) {
        $str = "您的同城货运预约".$times."已成功派单，可在行程记录中查看详情，接货司机".$driver_phone."会在出发前半小时与您联系。";
        $url = "http://106.ihuyi.cn/webservice/sms.php?method=Submit&account=".C('phone_account')."&password=".C('phone_psd')."&mobile=".$phone."&content=".$str;
        $html = file_get_contents($url);
        if ($html) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 城际拼车撤销
     */
    public function order_intercity_revoke() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        $info = $data = $this->ListsModel->get_info('order_intercity', $_POST['order_id']);
        if (time() - $info['add_time'] <= 86400) {
            $this->OrderIntercityModel->change_status($_POST['order_id'], '8');
        }

        echo 1;
    }

    /**
     * 市区出行
     */
    public function down() {
        // 列表配置
        $data['model'] = 'order_town';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('status') ? $data['where']['status'] = array('eq', I('status')) : '';
        if (!empty(I('driver_account'))) {
            $driver_id = M('user')->where('account = "'.I('driver_account').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_id);
        }
        if (!empty(I('driver_car_number'))) {
            $driver_car_number = M('user')->where('car_number = "'.I('driver_car_number').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_car_number);
        }
        if (!empty(I('user_account'))) {
            $user_id = M('user')->where('account = "'.I('user_account').'" AND type = 1')->getField('id');
            $data['where']['user_id'] = array('eq', $user_id);
        }

        // 列表
        $status_lists = $this->OrderTownModel->status('');
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['user_id']);
            $driver_user = $v['driver_id'] ? $this->UserModel->get_info($v['driver_id']) : '';
            $lists[$k]['user_name'] = $user['name'];
            $lists[$k]['driver_user_name'] = $driver_user['name'];
            $lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $lists[$k]['status'] = $this->OrderTownModel->status($v['status']);
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('status_lists', $status_lists);

        $this->display();
    }

    /**
     * 市区出行支付
     */
    public function down_pay_send() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        $number = $this->OrderTownModel->pay($_POST['order_id']);
        $this->OrderModel->pay_success($number);
        
        echo 1;
    }

    /**
     * 市区出行详情
     */
    public function order_down_details() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $data = $this->ListsModel->get_info('order_town', $_GET['id']);
        $data['user'] = $this->UserModel->get_info($data['user_id']);
        $data['driver_user'] = $this->UserModel->get_info($data['driver_id']);
        $data['status'] = $this->OrderTownModel->status($data['status']);

        $this->assign('data', $data);

        $this->display();
    }

    /**
     * 同城货运
     */
    public function traffic() {
        // 列表配置
        $data['model'] = 'order_traffic';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('status') ? $data['where']['status'] = array('eq', I('status')) : '';
        if (!empty(I('driver_account'))) {
            $driver_id = M('user')->where('account = "'.I('driver_account').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_id);
        }
        if (!empty(I('driver_car_number'))) {
            $driver_car_number = M('user')->where('car_number = "'.I('driver_car_number').'" AND type = 2')->getField('id');
            $data['where']['driver_id'] = array('eq', $driver_car_number);
        }
        if (!empty(I('user_account'))) {
            $user_id = M('user')->where('account = "'.I('user_account').'" AND type = 1')->getField('id');
            $data['where']['user_id'] = array('eq', $user_id);
        }

        // 列表
        $status_lists = $this->OrderTrafficModel->status('');
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['user_id']);
            $driver_user = $v['driver_id'] ? $this->UserModel->get_info($v['driver_id']) : '';
            $lists[$k]['user_name'] = $user['name'];
            $lists[$k]['driver_user_name'] = $driver_user['name'];
            $lists[$k]['add_times'] = date('Y-m-d H:i:s', $v['add_time']);
            $lists[$k]['add_time'] = $v['add_time'];
            $lists[$k]['status'] = $this->OrderTrafficModel->status($v['status']);
        }
        
        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('status_lists', $status_lists);

        $this->display();
    }

    /**
     * 同城货运详情
     */
    public function order_traffic_details() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $data = $this->ListsModel->get_info('order_traffic', $_GET['id']);
        $data['user'] = $this->UserModel->get_info($data['user_id']);
        $data['driver_user'] = $this->UserModel->get_info($data['driver_id']);
        $data['status'] = $this->OrderTrafficModel->status($data['status']);
        $data['car_type_id'] = $this->CarTypeModel->get_car_name($data['car_type_id']);

        $this->assign('data', $data);

        $this->display();
    }

    /**
     * 同城货运派单
     */
    public function order_traffic_send() {
        // 列表配置
        $data['model'] = 'user';
        $data['where']['type'] = array('eq', 2);
        $data['where']['check'] = array('eq', 1);
        $data['page_size'] = 5;
        $data['order'] = 'id desc';

        // 搜索
        I('name') ? $data['where']['name'] = array('like', '%'.I('name').'%') : '';
        I('account') ? $data['where']['account'] = array('like', '%'.I('account').'%') : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];

        foreach ($lists as $k => $v) {
            $lists[$k]['car_type_id'] = $this->CarTypeModel->get_car_name($v['car_type_id']);
        }
        
        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('order_id', I('id'));

        $this->display();
    }

    /**
     * 同城货运指派
     */
    public function order_traffic_assign() {
        if (empty($_POST['order_id']) || empty($_POST['driver_id'])) {
            die('没有参数');
        }

        $this->OrderTrafficModel->assign_send($_POST['order_id'], $_POST['driver_id']);

        $order = $this->OrderTrafficModel->where('id = '.$_POST['order_id'])->find();
        $user = $this->UserModel->get_info($order['user_id']);
        $driver = $this->UserModel->get_info($_POST['driver_id']);
        $this->tc_sj_send_code($driver['account'], $user['account'], date('Y-m-d', $order['add_time']), $order['start_location'], $order['end_location']); // 发给司机
        $this->tc_yh_send_code($user['account'], date('Y-m-d', $order['add_time']), $driver['account']); // 发给用户

        echo 1;
    }

    /**
     * 城际拼车撤销
     */
    public function order_traffic_revoke() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        $info = $data = $this->ListsModel->get_info('order_traffic', $_POST['order_id']);
        if (time() - $info['add_time'] <= 86400) {
            $this->OrderTrafficModel->change_status($_POST['order_id'], '3');
        }

        echo 1;
    }

    /**
     * 城际拼车完成
     */
    public function order_traffic_ok() {
        if (empty($_POST['order_id'])) {
            die('没有参数');
        }

        M('order_traffic')->where('id = '.$_POST['order_id'])->save(array('status' => '2'));

        echo 1;
    }
}