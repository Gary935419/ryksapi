<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\RouteModel;
use Home\Model\UserWorkingModel;
use Home\Model\OrderWaitingModel;
use Home\Model\OrderIntercityModel;
use Home\Model\OrderTownModel;

/**
 * Class AssignmentController
 * @package Home\Controller
 * @property RouteModel $RouteModel
 * @property UserWorkingModel $UserWorkingModel
 * @property OrderWaitingModel $OrderWaitingModel
 * @property OrderIntercityModel $OrderIntercityModel
 * @property OrderTownModel $OrderTownModel
 */
class AssignmentController extends Controller {

    private $RouteModel;
    private $UserWorkingModel;
    private $OrderWaitingModel;
    private $OrderIntercityModel;
    private $OrderTownModel;

    public function _initialize() {
        $this->RouteModel = new RouteModel();
        $this->UserWorkingModel = new UserWorkingModel();
        $this->OrderWaitingModel = new OrderWaitingModel();
        $this->OrderIntercityModel = new OrderIntercityModel();
        $this->OrderTownModel = new OrderTownModel();
    }

    public function index() {
        $this->no_join_waiting();
        $this->no_look_waiting();
    }

    /**
     * 处理没有司机接的等待订单
     */
    public function no_join_waiting() {
        $where['driver_id'] = array('eq', 0);
        $lists = $this->OrderWaitingModel->get_lists($where);
        if ($lists) {
            foreach ($lists as $k => $v) {
                switch ($v['taker_type_id']) {
                    case 1: // 城际拼车
                        $order = $this->OrderIntercityModel->get_info($v['order_id']);
                        $this->RouteModel->send_admin_tel($order['route_city_id1'], $order['route_city_id2'], $v['user_id'], $order['location'], $order['arrival_position'], $order['people_num'], 2, date('Y-m-d/H:i', time())); // 发短信
                        $this->OrderIntercityModel->set_order($v['order_id'], array('line' => '2')); // 转为线下
                        break;
                    case 2: // 市区出行
                        $this->OrderTownModel->change_status($v['order_id'], '8'); // 没人接
                        break;
                    case 3: // 同城货运
                        break;
                }

                /**
                $title = '戎州行';
                $content = '您有一个新的消息';
                $extras = [
                    'type'               => '2', // 未接消息
                    'taker_type_id'       => $v['taker_type_id'],
                    'msg'                => '该区域暂时没有可用车辆,请等待平台线下派车'
                ];
                $JModel = new \Home\Model\JpushModel();
                $JModel->send_alias($v['user_id'], $title, $content, $extras); // 发送用户消息
                **/

                $this->OrderWaitingModel->del_order($v['id']); // 删除该等待订单信息

                usleep(2000000); // 延时2秒
            }
        }
    }

    /**
     * 处理未查看等待订单
     */
    public function no_look_waiting() {
        $time = time() - 30; // 30秒之前(等待时间31秒至89秒内)
        $where = 'add_time <= '.$time.' AND driver_id != 0';
        $lists = $this->OrderWaitingModel->get_lists($where);
        if ($lists) {
            foreach ($lists as $k => $v) {
                switch ($v['taker_type_id']) {
                    case 1: // 城际拼车
                        $this->OrderIntercityModel->online_send($v['order_id'], $v['id']); // 推给下一个司机
                        break;
                    case 2: // 市区出行
                        $this->OrderTownModel->online_send($v['order_id'], $v['id']); // 推给下一个司机
                        break;
                    case 3: // 同城货运
                        break;
                }
                $this->UserWorkingModel->set_working($v['driver_id'], array('status_send' => '0')); // 设置派单状态为未派单(0)
            }
        }
    }

}