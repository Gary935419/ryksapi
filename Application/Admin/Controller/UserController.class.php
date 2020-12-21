<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\TakerTypeModel;
use Home\Model\RouteCityModel;
use Home\Model\CarTypeModel;
use Home\Model\UserModel;

/**
 * Class UserController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property TakerTypeModel $TakerTypeModel
 * @property RouteCityModel $RouteCityModel
 * @property CarTypeModel $CarTypeModel
 * @property UserModel $UserModel
 */
class UserController extends CommonController {

    private $ListsModel;
    private $TakerTypeModel;
    private $RouteCityModel;
    private $CarTypeModel;
    private $UserModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->TakerTypeModel = new TakerTypeModel();
        $this->RouteCityModel = new RouteCityModel();
        $this->CarTypeModel = new CarTypeModel();
        $this->UserModel = new UserModel();
    }

    /**
     * 司机列表
     */
    public function driver_lists() {
        $is_check = array(0 => '<span class="label label-defaunt radius">未认证</span>', 1 => '<span class="label label-success radius">已通过</span>', 2 => '<span class="label label-danger radius">未通过</span>', 3 => '<span class="label label-warning radius">认证中</span>');

        // 列表配置
        $data['model'] = 'user';
        $data['where']['type'] = array('eq', 2);
//        $data['where']['is_logoff'] = array('eq', 0);
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('name') ? $data['where']['name'] = array('like', '%'.I('name').'%') : '';
        I('account') ? $data['where']['account'] = array('like', '%'.I('account').'%') : '';
        I('check') ? $data['where']['check'] = array('eq', I('check')-1) : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];


        foreach ($lists as $k => $v) {
            $lists[$k]['check_font'] = $is_check[$v['check']];
            $lists[$k]['driving_check_font'] = $is_check[$v['driving_check']];
        }
        
        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        
        $this->display();
    }

    /**
     * 乘客列表
     */
    public function passenger_lists() {
        // 列表配置
        $data['model'] = 'user';
        $data['where']['type'] = array('eq', 1);
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('name') ? $data['where']['name'] = array('like', '%'.I('name').'%') : '';
        I('account') ? $data['where']['account'] = array('like', '%'.I('account').'%') : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        
        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        
        $this->display();
    }
    
    /**
     * 认证审核
     */
    public function check() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }
        
        $info = $this->ListsModel->get_info('user', $_GET['id']);
        
        $taker_type = $this->TakerTypeModel->get_lists();
        $info['taker_type_font'] = $taker_type[$info['taker_type_id']-1]['name'];
        $info['car_type_font'] = $this->CarTypeModel->get_car_name($info['car_type_id']);

        $this->assign('data', $info);
        
        $this->display();
    }
    /**
     * 代驾认证审核
     */
    public function driving_check() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('user', $_GET['id']);

        $taker_type = $this->TakerTypeModel->get_lists();
        $info['taker_type_font'] = $taker_type[$info['taker_type_id']-1]['name'];
        $info['car_type_font'] = $this->CarTypeModel->get_car_name($info['car_type_id']);

        $this->assign('data', $info);

        $this->display();
    }

    /**
     * 处理认证审核
     */
    public function driving_ajax_check() {
        if (empty($_POST['uid']) || empty($_POST['ucheck'])) {
            die('没有参数');
        }

        switch ($_POST['ucheck']) {
            case 1:
                $save = array('driving_check' => '1');
                break;
            case 2:
                $save = array('driving_check' => '2', 'driving_reason' => $_POST['liyou']);
                break;
        }

        $temp = $this->UserModel->save_info($_POST['uid'], $save);
        echo $temp;
    }


    /**
     * 处理认证审核
     */
    public function ajax_check() {
        if (empty($_POST['uid']) || empty($_POST['ucheck'])) {
            die('没有参数');
        }

        switch ($_POST['ucheck']) {
            case 1:
                $save = array('check' => '1');
                break;
            case 2:
                $save = array('check' => '2', 'reason' => $_POST['liyou']);
                break;
        }
        
        $temp = $this->UserModel->save_info($_POST['uid'], $save);
        echo $temp;
    }

    /**
     * 不通过理由
     */
    public function reason() {
        $this->display();
    }
    /**
     * 不通过理由
     */
    public function driving_reason() {
        $this->display();
    }

    /**
     * 详情
     */
    public function details() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('user', $_GET['id']);

        $taker_type = $this->TakerTypeModel->get_lists();
        $info['taker_type_font'] = $taker_type[$info['taker_type_id']-1]['name'];
        $info['route_city_font1'] = $this->RouteCityModel->get_city_name($info['route_city_id1']);
        $info['route_city_font2'] = $this->RouteCityModel->get_city_name($info['route_city_id2']);
        $info['car_type_font'] = $this->CarTypeModel->get_car_name($info['car_type_id']);

        $this->assign('data', $info);
        
        $this->display();
    }
    /**
     * 代驾详情
     */
    public function driving_details() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('user', $_GET['id']);

        $taker_type = $this->TakerTypeModel->get_lists();
        $info['taker_type_font'] = $taker_type[$info['taker_type_id']-1]['name'];
        $info['car_type_font'] = $this->CarTypeModel->get_car_name($info['car_type_id']);

        $this->assign('data', $info);

        $this->display();
    }


}