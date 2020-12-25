<?php

namespace Home\Model;

use Think\Model;

class UserAddressModel extends Model
{

    /**
     * 地址录入
     * @return bool
     */
    public function user_address_insert($data,$user_type)
    {
        if ($user_type == 1){
            $add = [
                'user_id' => $data['id'],
                'user_type' => $user_type,
                'latitude' => $data['start_latitude'],
                'longitude' => $data['start_longitude'],
                'addressName' => $data['start_location'],
                'is_default' => 0,
                'add_time' => time(),
                'address' => $data['start_address'],
                'address_details' => $data['address1'],
            ];
        }else{
            $add = [
                'user_id' => $data['id'],
                'user_type' => $user_type,
                'latitude' => $data['end_latitude'],
                'longitude' => $data['end_longitude'],
                'addressName' => $data['end_location'],
                'is_default' => 0,
                'add_time' => time(),
                'address' => $data['end_address'],
                'address_details' => $data['address2'],
            ];
        }
        $re = $this->add($add);
        return $re;
    }

    /**
     * 判断起点地址是否存在
     */
    public function get_user_address_start($data)
    {
        $where['latitude'] = array('eq', $data['start_latitude']);
        $where['longitude'] = array('eq', $data['start_longitude']);
        $where['addressName'] = array('eq', $data['start_location']);
        $where['address'] = array('eq', $data['start_address']);
        $where['address_details'] = array('eq', $data['address1']);
        $where['user_type'] = array('eq', 1);
        $result = $this->where($where)->find();
        return $result;
    }
    /**
     * 判断终点地址是否存在
     */
    public function get_user_address_end($data)
    {
        $where['latitude'] = array('eq', $data['end_latitude']);
        $where['longitude'] = array('eq', $data['end_longitude']);
        $where['addressName'] = array('eq', $data['end_location']);
        $where['address'] = array('eq', $data['end_address']);
        $where['address_details'] = array('eq', $data['address2']);
        $where['user_type'] = array('eq', 2);
        $result = $this->where($where)->find();
        return $result;
    }

    /**
     * 获取地址列表
     * @param $where
     * @return mixed
     */
    public function get_address_lists($con)
    {
        $where = 'user_id = ' . $con['user_id'];
        $where .= ' AND user_type = ' . $con['user_type'];
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        return $lists;
    }

}