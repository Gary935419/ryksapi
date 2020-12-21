<?php
namespace Home\Model;
use Think\Model;

class IntegralModel extends Model {

    /**
     * 获取兑换列表
     * @return mixed
     */
    public function get_lists() {
        $where['status'] = array('eq', '1');
        $lists = $this->where($where)->order('id desc')->select();
        foreach ($lists as $k => $v) {
            $lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
        }
        return $lists;
    }

    /**
     * 是否兑换
     * @param $integral_id
     * @param $user_id
     * @return string
     */
    public function is_exchange($integral_id, $user_id) {
        $where['integral_id'] = array('eq', $integral_id);
        $where['user_id'] = array('eq', $user_id);
        $temp = M('integral_exchange')->where($where)->find();
        if ($temp) {
            return '1';
        } else {
            return '0';
        }
    }

    /**
     * 兑换
     * @param $user_id
     * @param $integral_id
     * @return bool
     */
    public function exchange($user_id, $integral_id) {
        $UserModel = new \Home\Model\UserModel();
        $user = $UserModel->get_info($user_id);
        $integral = $this->where('id = '.$integral_id)->find();
        if ($user['integral'] >= $integral['number']) {
            $UserModel->save_info($user_id, array('integral' => $user['integral'] - $integral['number']));
            
            $add = [
                'integral_id' => $integral_id,
                'user_id' => $user_id,
                'add_time' => time()
            ];
            M('integral_exchange')->add($add);
            
            return true;
        } else {
            return false;
        }
    }

    /**
     * 兑换状态
     * @param $k
     * @return array|mixed
     */
    public function exchange_status($k) {
        $arr = array(
            1 => '未发',
            2 => '已发'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 添加兑换商品
     * @param $data
     * @return mixed
     */
    public function add_integral($data) {
        $data['add_time'] = time();
        $data['status'] = 1;
        $temp = $this->add($data);
        return $temp;
    }

    /**
     * 编辑兑换商品
     * @param $id
     * @param $data
     */
    public function set_integral($id, $data) {
        $where['id'] = array('eq', $id);
        $this->where($where)->save($data);
    }

    /**
     * 发货
     * @param $id
     */
    public function fahuo($id) {
        $where['id'] = array('eq', $id);
        M('integral_exchange')->where($where)->save(array('status' => '2'));
    }
}