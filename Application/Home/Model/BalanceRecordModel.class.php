<?php
namespace Home\Model;
use Think\Model;

class BalanceRecordModel extends Model {

    /**
     * 获取列表
     * @param $con
     * @return array
     */
    public function get_lists($con) {
        $where  = 'driver_id = '.$con['id'];
        $page = $con['page'] ? $con['page']  : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page-1)*$limit.",".$limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['add_time']     = date('Y-m-d H:i:s', $v['add_time']);
            }
            return $lists;
        } else {
            return [];
        }
    }

    /**
     * 余额明细
     * @param $driver_id
     * @param $content
     * @param $type
     * @param $money
     */
    public function balance($driver_id, $content, $type, $money) {
        // 增加记录
        $add = [
            'driver_id' => $driver_id,
            'content' => $content,
            'type' => $type,
            'money' => $money,
            'add_time' => time()
        ];
        $this->add($add);

        // 操作余额
        $UserModel = new \Home\Model\UserModel();
        $user = $UserModel->get_info($driver_id);
        if ($type == 1) { // 加
            $UserModel->save_info($driver_id, array('money' => $user['money'] + $money));
        } elseif ($type == 2) { // 减
            $UserModel->save_info($driver_id, array('money' => $user['money'] - $money));
        }
    }
}