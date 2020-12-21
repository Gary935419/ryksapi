<?php
namespace Admin\Model;
use Think\Model;

class AdminUserModel extends Model {

    /**
     * 管理员登录
     * @param $username
     * @param $userpwd
     * @return bool
     */
    public function login($username, $userpwd) {
        $where['username'] = array('eq', $username);
        $where['userpwd'] = array('eq', md5($userpwd));
        $admin_user = $this->where($where)->find();
        if ($admin_user) {
            return $admin_user;
        } else {
            return false;
        }
    }
    
}