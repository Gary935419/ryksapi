<?php
namespace Home\Model;
use Think\Model;

class PostalModel extends Model {

    /**
     * 新增提现记录
     * @param $data
     * @return mixed
     */
    public function add_postal($data) {
        $data['add_time'] = time();
        $data['status'] = 1;
        $temp = $this->add($data);
        if ($temp) {
            return $temp;
        }
    }

    /**
     * 状态
     * @param $k
     * @return array|mixed
     */
    public function status($k) {
        $arr = array(
            1 => '申请提现',
            2 => '提现成功',
            3 => '提现驳回'
        );

        if (!empty($k)) {
            return $arr[$k];
        } else {
            return $arr;
        }
    }

    /**
     * 设置记录
     * @param $id
     * @param $save
     * @return bool
     */
    public function set_info($id, $save) {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($save);
        return $temp;
    }

}