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
    /**
     * 充值列表
     * @param $con
     * @return array
     */
    public function get_lists($con) {
        $where = 'status = 1 and uid = ' . $con['id'];
        $page = $con['page'] ? $con['page'] : 1;
        $limit = $con['limit'] ? $con['limit'] : 10000;
        $limit1 = ($page - 1) * $limit . "," . $limit;
        $order = 'addtime DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['addtime'] = date('Y-m-d H:i:s', $v['addtime']);
                if ($v['pay_type'] == 1){
                    $lists[$k]['title'] = '支付宝';
                }else{
                    $lists[$k]['title'] = '微信';
                }
            }
            return $lists;
        } else {
            return [];
        }
    }
}