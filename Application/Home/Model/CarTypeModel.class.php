<?php
namespace Home\Model;
use Think\Model;

class CarTypeModel extends Model {

    /**
     * 获取车型座位
     * @param $id
     * @return mixed
     */
    public function get_car_seat($id) {
        $where['id'] = array('eq', $id);
        $seat = $this->where($where)->getField('seat');
        if ($seat) {
            return $seat;
        } else {
            return '0';
        }
    }
    
    /**
     * 获取车型姓名
     * @param $id
     * @return mixed
     */
    public function get_car_name($id) {
        $where['id'] = array('eq', $id);
        $name = $this->where($where)->getField('name');
        return $name;
    }
    
    /**
     * 获取车型列表
     * @param $type
     * @return mixed
     */
    public function get_car_lists($type) {
        $where['type'] = array('eq', $type);
        $lists = $this->where($where)->order('paixu DESC')->select();

        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['head_img'] = $v['head_img'] ? httpImg($v['head_img']) : '';
                if ($type == 2) { // 市区出行
                    $lists[$k]['starting_price'] = M('set_config')->where('name = "town_starting_price"')->getField('content');
                } elseif ($type == 3) { // 同城货运
                    $lists[$k]['starting_price'] = M('set_config')->where('name = "traffic_starting_price"')->getField('content');
                }
            }
        }

        return $lists;
    }

    /**
     * 编辑车型
     * @param $id
     * @param $save
     */
    public function set_car($id, $save) {
        $this->where('id = '.$id)->save($save);
    }


    public function get_car_info($id) {
        $where['id'] = array('eq', $id);
        $info = $this->where($where)->find();;
        if ($info) {
            return $info;
        } else {
            return '0';
        }
    }

}