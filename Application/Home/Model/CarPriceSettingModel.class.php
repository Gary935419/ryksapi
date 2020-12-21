<?php
namespace Home\Model;
use Think\Model;

class CarPriceSettingModel extends Model {

    public function get_car_price_setting_info($id) {
        $where['sid'] = array('eq', $id);
        $info = $this->where($where)->find();;
        if ($info) {
            return $info;
        } else {
            return '0';
        }
    }

}