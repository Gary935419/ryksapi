<?php
namespace Home\Model;
use Think\Model;

class TelephoneModel extends Model {

    /**
     * 获取电话列表
     * @param $type
     * @return mixed
     */
    public function get_lists($type) {
        $where['type'] = array('eq', $type);
        $lists = $this->where($where)->select();
        return $lists;
    }

    /**
     * 市区出行
     * @return mixed
     */
    public function get_down_car() {
        $where['type'] = array('eq', 2);
        $content = $this->where($where)->getField('content');
        return $content;
    }

    /**
     * 同城货运
     * @return mixed
     */
    public function get_traffic_car() {
        $where['type'] = array('eq', 3);
        $content = $this->where($where)->getField('content');
        return $content;
    }

    /**
     * 设置市区出行
     * @param $content
     */
    public function set_down_car($content) {
        $where['type'] = array('eq', 2);
        $this->where($where)->save(array('content' => $content));
    }

    /**
     * 设置同城货运
     * @param $content
     */
    public function set_traffic_car($content) {
        $where['type'] = array('eq', 3);
        $this->where($where)->save(array('content' => $content));
    }
    
}