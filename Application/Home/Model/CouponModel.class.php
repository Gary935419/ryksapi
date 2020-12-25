<?php
namespace Home\Model;
use Think\Model;

class CouponModel extends Model {

    /**
     * 获取优惠券列表
     * @param $con
     * @return array
     */
    public function get_lists($con) {
        $where = 'user_id = ' . $con['id'];
        $where .= ' AND end_time > '.time();
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 1000;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'money DESC , end_time ASC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $lists[$k]['end_time'] = date('Y-m-d H:i:s', $v['end_time']);
                $lists[$k]['title'] = '通用券';
            }
            return $lists;
        } else {
            return [];
        }
    }
    /**
     * 修改优惠券状态
     * @param $id
     * @param $data
     * @return bool
     */
    public function saveCoupon($id, $data)
    {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }
    /**
     * 获取优惠券
     * @param $user_id
     * @param $type
     * @return mixed|string
     */
    public function get_coupon($user_id, $type) {
        $where = 'user_id = "'.$user_id.'"';
        $where .= ' AND type = "'.$type.'"';
        $where .= ' AND end_time > '.time();
        $coupon = $this->where($where)->find();
        if ($coupon) {
            return $coupon;
        } else {
            return '';
        }
    }

    /**
     * 满10次发送优惠券
     * @param $user_id
     */
    public function send_coupon($user_id) {
        $OrderIntercityModel = new \Home\Model\OrderIntercityModel();
        $where['user_id'] = array('eq', $user_id);
        $where['status'] = array('eq', '6');
        $count = $OrderIntercityModel->where($where)->count();
        if ($count%10==0) {
            $coupon10 = [
                'user_id' => $user_id,
                'money' => 10,
                'type' => 2,
                'add_time' => time(),
                'end_time' => time() + 7776000
            ];
            $this->add($coupon10);
        }
    }

    /**
     * 推荐用户下单 上级获得优惠券
     * @param $user_id
     */
    public function addCoupon($coupon) {
        $this->add($coupon);
    }
    /**
     * 获取优惠券信息
     * @param $id
     * @return mixed|string
     */
    public function get_coupon_by_id($id) {
        $where = 'id = "'.$id.'"';
//        $where .= ' AND end_time > '.time();
        $coupon = $this->where($where)->find();
        if ($coupon) {
            return $coupon;
        } else {
            return '';
        }
    }

}