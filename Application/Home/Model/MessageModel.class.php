<?php
namespace Home\Model;
use Think\Model;

class MessageModel extends Model {

    /**
     * 该用户是否有未读消息
     * @param $type
     * @param $id
     * @return bool
     */
    public function is_unread($type, $id) {
        $where  = 'type = '.$type;
        $where .= ' AND read_user NOT LIKE "%['.$id.']%" AND del_user NOT LIKE "%['.$id.']%"';
        $count = $this->where($where)->count();
        if ($count != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断该条消息是否已读
     * @param $id
     * @param $user_id
     * @return string 0未读 1已读
     */
    public function is_this_unread($id, $user_id) {
        $where  = 'id = '.$id;
        $where .= ' AND read_user NOT LIKE "%['.$user_id.']%"';
        $temp = $this->where($where)->find();
        if ($temp) {
            return '0';
        } else {
            return '1';
        }
    }

    /**
     * 获取消息列表
     * @param $con
     * @return array
     */
    public function get_lists($con) {
//        $where  = 'type = '.$con['type'];
//        $where .= ' AND del_user NOT LIKE "%['.$con['id'].']%"';
        $where = 'del_user NOT LIKE "%['.$con['id'].']%"';
        $page = $con['page'] ? $con['page']  : 1;
        $limit = $con['limit'] ? $con['limit'] : 10;
        $limit1 = ($page-1)*$limit.",".$limit;
        $order = 'id DESC';
        $lists = $this->where($where)->limit($limit1)->order($order)->select();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
                $lists[$k]['is_read'] = $this->is_this_unread($v['id'], $con['id']);
                unset($lists[$k]['read_user']);
                unset($lists[$k]['del_user']);
            }

            return $lists;
        } else {
            return [];
        }
    }

    /**
     * 已读该条消息
     * @param $id
     * @param $user_id
     */
    public function read_message($id, $user_id) {
        $read_temp = $this->is_this_unread($id, $user_id);
        if ($read_temp == 0) {
            $where['id'] = array('eq', $id);
            $read_user = $this->where($where)->getField('read_user');
            $this->where($where)->save(array('read_user' => $read_user.'['.$user_id.']'));
        }
    }

    /**
     * 获取详情
     * @param $id
     * @return mixed
     */
    public function get_info($id) {
        $where['id'] = array('eq', $id);
        $info = $this->where($where)->find();
        $info['add_time'] = date('Y-m-d H:i:s', $info['add_time']);
        unset($info['read_user']);
        unset($info['del_user']);
        return $info;
    }

    /**
     * 删除消息
     * @param $id
     * @param $user_id
     */
    public function del_message($id, $user_id) {
        $where  = 'id = '.$id;
        $where .= ' AND del_user NOT LIKE "%['.$user_id.']%"';
        $temp = $this->where($where)->find();
        if ($temp) {
            $where1['id'] = array('eq', $id);
            $del_user = $this->where($where1)->getField('del_user');
            $this->where($where1)->save(array('del_user' => $del_user.'['.$user_id.']'));
        }
    }
    
}