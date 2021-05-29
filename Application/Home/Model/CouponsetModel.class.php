<?php

namespace Home\Model;

use Think\Model;

class CouponsetModel extends Model
{

    /**
     * 获取用户信息
     * @param $id
     * @return mixed
     * @return array|mixed
     */
    public function get_user($id)
    {
        $where['id'] = array('eq', $id);
        $data = $this->where($where)->find();
        return $data;
    }

}