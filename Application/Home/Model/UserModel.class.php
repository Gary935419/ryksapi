<?php

namespace Home\Model;

use Think\Model;

class UserModel extends Model
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

    /**
     * 用户注册
     * @param $type 1用户端 2司机端
     * @param $account 手机
     * @return bool
     */
    public function user_register($type, $account, $openId)
    {

        $is_account = $this->is_account($type, $account,$openId);
        if ($is_account) {
            echoOk(301, '该账号已存在');
        }
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < 6; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }
        if ($type == 1){
            $invitation_code1 = $str;
            $add = [
                'type' => $type,
                'account' => $account,
                'name' => $type == 1 ? '用户' . substr($account, 7) : '',
                'head_img' => 'Public/photo_default.png',
                'add_time' => time(),
                'open_id' => $openId,
                'invitation_code1' => $invitation_code1,
            ];
        }else{
            $invitation_code2 = $str;
            $add = [
                'type' => $type,
                'account' => $account,
                'head_img' => 'Public/photo_default.png',
                'add_time' => time(),
                'invitation_code2' => $invitation_code2,
            ];
        }

        $re = $this->add($add);

        // 用户端注册送优惠券
//        $CouponModel  = new \Home\Model\CouponModel();
//        $coupon5 = [
//            'user_id' => $re,
//            'money' => 5,
//            'type' => 1,
//            'add_time' => time(),
//            'end_time' => time() + 7776000
//        ];
//        $coupon10 = [
//            'user_id' => $re,
//            'money' => 10,
//            'type' => 2,
//            'add_time' => time(),
//            'end_time' => time() + 7776000
//        ];
//        for ($i=1;$i<=6;$i++) {
//            $CouponModel->add($coupon5);
//        }
//        for ($i=1;$i<=3;$i++) {
//            $CouponModel->add($coupon10);
//        }

        return $re;
    }

    /**
     * 判断账号是否存在
     * @param $type 1用户端 2司机端
     * @param $account 手机
     * @return bool
     */
    public function is_account($type, $account, $openId = '')
    {
        $where['type'] = array('eq', $type);
        $where['account'] = array('eq', $account);
        $where['is_logoff'] = array('eq', 0);
        $user_id = $this->where($where)->getField('id');

        if ($user_id) {
            if ($openId) {
               $this->where(['id' => $user_id])->save(['open_id' => $openId] );
            }
            return $user_id;
        } else {
            return false;
        }
    }
    /**
     * 判断账号是否可用
     * @param $type 1用户端 2司机端
     * @param $account 手机
     * @return bool
     */
    public function is_account_flg($type, $account)
    {
        $where['type'] = array('eq', $type);
        $where['account'] = array('eq', $account);
        $where['is_logoff'] = array('eq', 1);
        $user_id = $this->where($where)->getField('id');
        if ($user_id) {
            return $user_id;
        } else {
            return false;
        }
    }
    /**
     * 获取用户信息
     * @param $id
     * @return array|mixed
     */
    public function get_info($id)
    {
        $where['id'] = array('eq', $id);
        $data = $this->where($where)->find();
        if ($data) {
            $data['head_img'] = $data['head_img'] ? httpImg($data['head_img']) : '';
            $data['img_cards_face'] = $data['img_cards_face'] ? httpImg($data['img_cards_face']) : '';
            $data['img_cards_side'] = $data['img_cards_side'] ? httpImg($data['img_cards_side']) : '';
            $data['img_drivers'] = $data['img_drivers'] ? httpImg($data['img_drivers']) : '';
            $data['img_vehicle'] = $data['img_vehicle'] ? httpImg($data['img_vehicle']) : '';
            return $data;
        } else {
            return '';
        }
    }

    /**
     * 修改用户信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function save_info($id, $data)
    {
        $where['id'] = array('eq', $id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }


    /**
     * 获取用户列表
     * @param $where
     * @return mixed
     */
    public function get_lists($where)
    {
        $lists = $this->where($where)->order('id ASC')->limit(18)->select();
        if ($lists) {
            return $lists;
        } else {
            return false;
        }
    }

    public function get_user_by_account($where){
//        $where['account'] = array('eq', $account);
        $data = $this->where($where)->find();
        return $data;
    }

    /**
     * 获取用户信息
     * @param $id
     * @return array|mixed
     */
    public function getWhereInfo($where)
    {
        $data = $this->where($where)->find();
        if ($data) {
            $data['head_img'] = $data['head_img'] ? httpImg($data['head_img']) : '';

//            $data['img_cards_face'] = $data['img_cards_face'] ? httpImg($data['img_cards_face']) : '';
//            $data['img_cards_side'] = $data['img_cards_side'] ? httpImg($data['img_cards_side']) : '';
//            $data['img_drivers'] = $data['img_drivers'] ? httpImg($data['img_drivers']) : '';
//            $data['img_vehicle'] = $data['img_vehicle'] ? httpImg($data['img_vehicle']) : '';
//            $data['img_worker'] = $data['img_worker'] ? httpImg($data['img_worker']) : '';
//            $data['img_car_user'] = $data['img_car_user'] ? httpImg($data['img_car_user']) : '';

            $data['img_cards_face'] = strpos($data['img_cards_face'],'http') !== false?$data['img_cards_face']:httpImg($data['img_cards_face']);
            $data['img_cards_side'] = strpos($data['img_cards_side'],'http') !== false?$data['img_cards_side']:httpImg($data['img_cards_side']);
            $data['img_drivers'] = strpos($data['img_drivers'],'http') !== false?$data['img_drivers']:httpImg($data['img_drivers']);
            $data['img_vehicle'] = strpos($data['img_vehicle'],'http') !== false?$data['img_vehicle']:httpImg($data['img_vehicle']);
            $data['img_car_user'] = strpos($data['img_car_user'],'http') !== false?$data['img_car_user']:httpImg($data['img_worker']);
            $data['img_worker'] = strpos($data['img_worker'],'http') !== false?$data['img_worker']:httpImg($data['img_car_user']);

//            $data['driving_img_cards_face'] = $data['driving_img_cards_face'] ? httpImg($data['driving_img_cards_face']) : '';
//            $data['driving_img_cards_side'] = $data['driving_img_cards_side'] ? httpImg($data['driving_img_cards_side']) : '';
//            $data['driving_img_drivers'] = $data['driving_img_drivers'] ? httpImg($data['driving_img_drivers']) : '';
//            $data['driving_img_worker'] = $data['driving_img_worker'] ? httpImg($data['driving_img_worker']) : '';

            $data['driving_img_cards_face'] = strpos($data['driving_img_cards_face'],'http') !== false?$data['driving_img_cards_face']:httpImg($data['driving_img_cards_face']);
            $data['driving_img_cards_side'] = strpos($data['driving_img_cards_side'],'http') !== false?$data['driving_img_cards_side']:httpImg($data['driving_img_cards_side']);
            $data['driving_img_drivers'] = strpos($data['driving_img_drivers'],'http') !== false?$data['driving_img_drivers']:httpImg($data['driving_img_drivers']);
            $data['driving_img_worker'] = strpos($data['driving_img_worker'],'http') !== false?$data['driving_img_worker']:httpImg($data['driving_img_worker']);

            return $data;
        } else {
            return '';
        }
    }

}