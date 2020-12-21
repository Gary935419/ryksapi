<?php
namespace Home\Model;
use Think\Model;

class OrderWaitingModel extends Model {

    /**
     * 添加等待订单信息
     * @param $data
     * @return mixed
     */
    public function add_waiting($data) {
        $id = $this->add($data);
        return $id;
    }

    /**
     * 获取等待订单列表
     * @param $where
     * @return mixed
     */
    public function get_lists($where) {
        $lists = $this->where($where)->order('id ASC')->select();
        return $lists;
    }
    
    /**
     * 删除等待订单信息
     * @param $id
     * @return bool
     */
    public function del_order($id) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->delete();
        return $temp;
    }

    /**
     * 用户删除订单
     * @param $user_id
     * @return mixed
     */
    public function user_del_order($user_id) {
        $where['user_id'] = array('eq', $user_id);
        $temp = $this->where($where)->delete();
        return $temp;
    }

    /**
     * 更新等待订单信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function set_order($id, $data) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }

    /**
     * 获取等待订单信息
     * @param $id
     * @return bool
     */
    public function get_info($id) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->find();
        return $temp;
    }

    /**
     * 用户获取等待订单信息
     * @param $user_id
     * @return mixed
     */
    public function get_user_info($user_id) {
        $where['user_id'] = array('eq', $user_id);
        $temp = $this->where($where)->find();
        return $temp;
    }

}