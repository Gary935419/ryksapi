<?php

namespace Home\Model;

use Think\Cache\Driver\Db;
use Think\Model;

class TopupModel extends Model
{

    /**
     * 添加大单
     * @param $data
     * @return mixed
     */
    public function add_order( $data )
    {
        $re = $this->add( $data );
        return $re;
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function get_info( $id )
    {
        $where['id'] = array ( 'eq' , $id );
        $re          = $this->where( $where )->find();
        return $re;
    }
    public function save_info($id, $data)
    {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }
}