<?php
namespace Home\Model;
use Think\Model;

class RouteCityModel extends Model {

    /**
     * 获取路线城市名称
     * @param $id
     * @return mixed
     */
    public function get_city_name($id) {
        $where['id'] = array('eq', $id);
        $name = $this->where($where)->getField('name');
        if ($name) {
            return $name;
        } else {
            return '';
        }
    }

    /**
     * 添加城市
     * @param $name
     * @return mixed
     */
    public function add_city($name) {
        $add = [
            'name' => $name
        ];
        $temp = $this->add($add);
        return $temp;
    }

    /**
     * 获取列表
     * @return mixed
     */
    public function get_lists() {
        $lists = $this->select();
        return $lists;
    }

}