<?php
namespace Admin\Controller;
use Think\Controller;
use Home\Model\SetConfigModel;
use Home\Model\TelephoneModel;

/**
 * Class SystemController
 * @package Admin\Controller
 * @property SetConfigModel $SetConfigModel
 * @property TelephoneModel $TelephoneModel
 */
class SystemController extends CommonController {

    private $SetConfigModel;
    private $TelephoneModel;

    public function _initialize() {
        parent::_initialize();
        $this->SetConfigModel = new SetConfigModel();
        $this->TelephoneModel = new TelephoneModel();
    }
    
    /**
     * 文本配置
     */
    public function file() {
        $this->assign('driver_deal', $this->SetConfigModel->get_content('driver_deal'));
        $this->assign('user_deal', $this->SetConfigModel->get_content('user_deal'));
        $this->assign('driver_contact_us', $this->SetConfigModel->get_content('driver_contact_us'));
        $this->assign('user_contact_us', $this->SetConfigModel->get_content('user_contact_us'));
        $this->assign('driver_about_us', $this->SetConfigModel->get_content('driver_about_us'));
        $this->assign('user_about_us', $this->SetConfigModel->get_content('user_about_us'));
        $this->assign('cozy_type4', $this->SetConfigModel->get_content('cozy_type4'));
        $this->assign('cozy_type1', $this->SetConfigModel->get_content('cozy_type1'));
        $this->assign('cozy_type2', $this->SetConfigModel->get_content('cozy_type2'));
        $this->assign('cozy_type3', $this->SetConfigModel->get_content('cozy_type3'));
        $this->assign('down_car', $this->TelephoneModel->get_down_car());
        $this->assign('user_jijia', $this->SetConfigModel->get_content('user_jijia'));
        $this->assign('user_kaipiao', $this->SetConfigModel->get_content('user_kaipiao'));
        $this->assign('traffic_car', $this->TelephoneModel->get_traffic_car());
        $this->assign('town_starting_price', $this->SetConfigModel->get_content('town_starting_price'));
        $this->assign('traffic_starting_price', $this->SetConfigModel->get_content('traffic_starting_price'));
        
        $this->display();
    }

    /**
     * 文本配置 - 提交
     */
    public function submit() {

        $this->SetConfigModel->set_content('driver_deal', $_POST['driver_deal']);
        $this->SetConfigModel->set_content('user_deal', $_POST['user_deal']);
        $this->SetConfigModel->set_content('driver_contact_us', $_POST['driver_contact_us']);
        $this->SetConfigModel->set_content('user_contact_us', $_POST['user_contact_us']);
        $this->SetConfigModel->set_content('driver_about_us', $_POST['driver_about_us']);
        $this->SetConfigModel->set_content('user_about_us', $_POST['user_about_us']);
        $this->SetConfigModel->set_content('cozy_type4', $_POST['cozy_type4']);
        $this->SetConfigModel->set_content('cozy_type1', $_POST['cozy_type1']);
        $this->SetConfigModel->set_content('cozy_type2', $_POST['cozy_type2']);
        $this->SetConfigModel->set_content('cozy_type3', $_POST['cozy_type3']);
        $this->SetConfigModel->set_content('user_jijia', $_POST['user_jijia']);
        $this->SetConfigModel->set_content('user_kaipiao', $_POST['user_kaipiao']);
        $this->TelephoneModel->set_down_car($_POST['down_car']);
        $this->TelephoneModel->set_traffic_car($_POST['traffic_car']);
        $this->SetConfigModel->set_content('town_starting_price', $_POST['town_starting_price']);
        $this->SetConfigModel->set_content('traffic_starting_price', $_POST['traffic_starting_price']);

        echo 1;
    }

}