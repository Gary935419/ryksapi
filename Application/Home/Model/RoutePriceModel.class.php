<?php
namespace Home\Model;
use Think\Model;

class RoutePriceModel extends Model {

    /**
     * 获取线路价格详情
     * @param $route_id
     * @param $car_type_id
     * @return mixed|string
     */
    public function get_price($route_id, $car_type_id) {
        $where['route_id'] = array('eq', $route_id);
        $where['car_type_id'] = array('eq', $car_type_id);
        $data = $this->where($where)->find();
        if ($data) {
            return $data;
        } else {
            return '';
        }
    }

    /**
     * 设置线路价格
     * @param $route_id
     * @param $car_type_id
     * @param $set_data
     */
    public function set_price($route_id, $car_type_id, $set_data) {
        $where['route_id'] = array('eq', $route_id);
        $where['car_type_id'] = array('eq', $car_type_id);
        $data = $this->where($where)->find();
        if ($data) { // 修改
            $this->where($where)->save($set_data);
        } else { // 增加
            $this->where($where)->add($set_data);
        }
    }

}