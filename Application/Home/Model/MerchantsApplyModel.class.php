<?php

namespace Home\Model;

use Think\Cache\Driver\Db;
use Think\Model;

class MerchantsApplyModel extends Model
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
     * 获取大单ID
     * @param $user_id
     * @return mixed
     */
    public function get_id($user_id)
    {
        $where['user_id'] = array ( 'eq' , $user_id );
        $where['status']    = array ( 'eq' , '1' );
        $id                 = $this->where( $where )->getField( 'id' );
        return $id;
    }
    /**
     * 申请列表
     * @param $con
     * @return array
     */
    public function get_lists($con) {
        $where = 'user_id = ' . $con['id'];
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10000;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'addtime DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
                if ($v['status'] == 1){
                    $lists[$k]['title'] = '申请中';
                }elseif ($v['status'] == 2){
                    $lists[$k]['title'] = '已通过';
                }else{
                    $lists[$k]['title'] = '已驳回';
                }
            }
            return $lists;
        } else {
            return [];
        }
    }
}