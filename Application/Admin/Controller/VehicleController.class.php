<?php
namespace Admin\Controller;
use Think\Controller;
use Home\Model\CarTypeModel;

/**
 * Class VehicleController
 * @package Admin\Controller
 * @property CarTypeModel $CarTypeModel
 */
class VehicleController extends CommonController {

    private $CarTypeModel;

    public function _initialize() {
        parent::_initialize();
        $this->CarTypeModel = new CarTypeModel();
    }

    /**
     * 车辆设置
     */
    public function edit() {
        $lists = $this->CarTypeModel->get_car_lists('1');

        $this->assign('lists', $lists);
        $this->display();
    }

    /**
     * 车辆设置 - 提交
     */
    public function edit_submit() {
//        if (!empty($_POST['car_cj1_seat'])) { $this->CarTypeModel->set_car('1', array('seat' => $_POST['car_cj1_seat'])); }
//        if (!empty($_POST['car_cj2_seat'])) { $this->CarTypeModel->set_car('2', array('seat' => $_POST['car_cj2_seat'])); }
//        if (!empty($_POST['car_cj3_seat'])) { $this->CarTypeModel->set_car('3', array('seat' => $_POST['car_cj3_seat'])); }
//
//        if (!empty($_POST['car_sq1_price'])) { $this->CarTypeModel->set_car('4', array('price' => $_POST['car_sq1_price'])); }
//        if (!empty($_POST['car_sq2_price'])) { $this->CarTypeModel->set_car('5', array('price' => $_POST['car_sq2_price'])); }
//        if (!empty($_POST['car_sq3_price'])) { $this->CarTypeModel->set_car('6', array('price' => $_POST['car_sq3_price'])); }
//
//        if (!empty($_POST['car_hy1_price'])) { $this->CarTypeModel->set_car('7', array('price' => $_POST['car_hy1_price'])); }
//        if (!empty($_POST['car_hy2_price'])) { $this->CarTypeModel->set_car('8', array('price' => $_POST['car_hy2_price'])); }
//        if (!empty($_POST['car_hy3_price'])) { $this->CarTypeModel->set_car('9', array('price' => $_POST['car_hy3_price'])); }
//
//        echo 1;
    }

}