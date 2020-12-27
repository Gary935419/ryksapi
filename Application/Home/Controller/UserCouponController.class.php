<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\CouponModel;

/**
 * Class UserCouponController
 * @package Home\Controller
 * @property CouponModel $CouponModel
 */
class UserCouponController extends CommonController {

    private $CouponModel;

    public function _initialize() {
        parent::_initialize();
        $this->CouponModel = new CouponModel();
    }

    /**
     * 优惠券列表
     */
    public function lists() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '没有传递用户id', []);
        }

        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        $lists = $this->CouponModel->get_lists($con);
        
        if ($lists) {
            echoOk(200, '获取成功', $lists);
        }else{
            echoOk(301, '暂无优惠券', $lists);
        }
    }

}