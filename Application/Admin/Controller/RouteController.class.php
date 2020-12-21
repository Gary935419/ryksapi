<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\RouteModel;
use Home\Model\RouteCityModel;
use Home\Model\RoutePriceModel;

/**
 * Class RouteController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property RouteCityModel $RouteCityModel
 * @property RouteModel $RouteModel
 * @property RoutePriceModel $RoutePriceModel
 */
class RouteController extends CommonController {

    private $ListsModel;
    private $RouteCityModel;
    private $RouteModel;
    private $RoutePriceModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->RouteModel = new RouteModel();
        $this->RouteCityModel = new RouteCityModel();
        $this->RoutePriceModel = new RoutePriceModel();
    }

    /**
     * 城际路线
     */
    public function lists() {
        $status_arr = [
            '0' => '下架',
            '1' => '正常'
        ];
        // 列表配置
        $data['model'] = 'route';
        $data['page_size'] = 10;
        $data['order'] = 'paixu desc';

        // 搜索

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $lists[$k]['route_city_font1'] = $this->RouteCityModel->get_city_name($v['route_city_id1']);
            $lists[$k]['route_city_font2'] = $this->RouteCityModel->get_city_name($v['route_city_id2']);
            $lists[$k]['nature'] = $this->RouteModel->route_nature($v['nature']);
            $lists[$k]['status_font'] = $status_arr[$v['status']];
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);

        $this->display();
    }

    /**
     * 添加城市
     */
    public function add_city() {
        $this->display();
    }

    /**
     * 添加城市 - 提交
     */
    public function add_city_submit() {
        if (empty($_POST['name'])) {
            die('没有参数');
        }
        
        $temp = $this->RouteCityModel->add_city($_POST['name']);
        echo $temp;
    }

    /**
     * 添加线路
     */
    public function add_route() {
        $route_city = $this->RouteCityModel->get_lists();
        $this->assign('route_city', $route_city);
        
        $this->display();
    }

    /**
     * 添加线路 - 提交
     */
    public function add_route_submit() {
        if (empty($_POST['route_city_id1']) || empty($_POST['route_city_id2']) || empty($_POST['admin_tel']) || 
            empty($_POST['paixu']) || empty($_POST['nature']) || empty($_POST['nature'])) {
            die('没有参数');
        }
        
        $data = [
            'route_city_id1' => $_POST['route_city_id1'],
            'route_city_id2' => $_POST['route_city_id2'],
            'admin_tel'      => $_POST['admin_tel'],
            'paixu'          => $_POST['paixu'],
            'nature'         => $_POST['nature'],
            'online'         => $_POST['online']
        ];
        
        $temp = $this->RouteModel->add_route($data);
        if ($temp) {
            echo 1;
        } else {
            echo 2;
        }
    }

    /**
     * 编辑路线
     */
    public function edit() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('route', $_GET['id']);
        $info['route_city_font1'] = $this->RouteCityModel->get_city_name($info['route_city_id1']);
        $info['route_city_font2'] = $this->RouteCityModel->get_city_name($info['route_city_id2']);
        $this->assign('data', $info);
        
        $route_city = $this->RouteCityModel->get_lists();
        $this->assign('route_city', $route_city);

        $this->display();
    }


    /**
     * 编辑路线 - 提交
     */
    public function edit_submit() {
        if (empty($_POST['id']) || empty($_POST['admin_tel']) || empty($_POST['nature'])) {
            die('没有参数');
        }

        $data = [
            'admin_tel'      => $_POST['admin_tel'],
            'paixu'          => $_POST['paixu'],
            'nature'         => $_POST['nature'],
            'online'         => $_POST['online']
        ];

        $temp = $this->RouteModel->edit_route($_POST['id'], $data);
        if ($temp) {
            echo 1;
        } else {
            echo 2;
        }
    }

    /**
     * 配置
     */
    public function disposed() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->RouteModel->get_info($_GET['id']);
        $info['route_city_font1'] = $this->RouteCityModel->get_city_name($info['route_city_id1']);
        $info['route_city_font2'] = $this->RouteCityModel->get_city_name($info['route_city_id2']);

        $route_price1 = $this->RoutePriceModel->get_price($_GET['id'], '1'); // 轿车
        $route_price2 = $this->RoutePriceModel->get_price($_GET['id'], '2'); // 七座商务车
        $route_price3 = $this->RoutePriceModel->get_price($_GET['id'], '3'); // 豪华商务车

        $this->assign('data', $info);
        $this->assign('route_price1', $route_price1);
        $this->assign('route_price2', $route_price2);
        $this->assign('route_price3', $route_price3);
        
        $this->display();
    }

    /**
     * 配置 - 提交
     */
    public function disposed_submit() {
        if (empty($_POST['route_id'])) {
            die('没有参数');
        }

        $set_data1 = [
            'route_id' => $_POST['route_id'],
            'car_type_id' => '1',
            'real_price' => $_POST['real_price1'],
            'sale_price' => $_POST['sale_price1'],
            'tishi' => $_POST['tishi1']
        ];
        $this->RoutePriceModel->set_price($_POST['route_id'], '1', $set_data1); // 轿车

        $set_data2 = [
            'route_id' => $_POST['route_id'],
            'car_type_id' => '2',
            'real_price' => $_POST['real_price2'],
            'sale_price' => $_POST['sale_price2'],
            'tishi' => $_POST['tishi2']
        ];
        $this->RoutePriceModel->set_price($_POST['route_id'], '2', $set_data2); // 七座商务车

        $set_data3 = [
            'route_id' => $_POST['route_id'],
            'car_type_id' => '3',
            'real_price' => $_POST['real_price3'],
            'sale_price' => $_POST['sale_price3'],
            'tishi' => $_POST['tishi3']
        ];
        $this->RoutePriceModel->set_price($_POST['route_id'], '3', $set_data3); // 豪华商务车

        echo 1;
    }

    /**
     * 状态
     */
    public function status() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }

        $route = $this->RouteModel->get_info($_POST['uid']);
        if ($route['status'] == 1) {
            $this->RouteModel->edit_route($_POST['uid'], array('status' => '0'));
        } elseif ($route['status'] == 0) {
            $this->RouteModel->edit_route($_POST['uid'], array('status' => '1'));
        }

        echo 1;
    }
    
}