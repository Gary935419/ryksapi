<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\CouponModel;
use Home\Model\TopupModel;
use Home\Model\MerchantsApplyModel;
/**
 * Class UserCouponController
 * @package Home\Controller
 * @property CouponModel $CouponModel
 * @property TopupModel $TopupModel
 * @property MerchantsApplyModel $MerchantsApplyModel
 */
class UserCouponController extends CommonController {

    private $CouponModel;
    private $TopupModel;
    private $MerchantsApplyModel;
    public function _initialize() {
        parent::_initialize();
        $this->CouponModel = new CouponModel();
        $this->TopupModel = new TopupModel();
        $this->MerchantsApplyModel = new MerchantsApplyModel();
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

    /**
     * 充值列表
     */
    public function topuplists() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '没有传递用户id', []);
        }

        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        $lists = $this->TopupModel->get_lists($con);

        if ($lists) {
            echoOk(200, '获取成功', $lists);
        }else{
            echoOk(301, '暂无订单', $lists);
        }
    }
    /**
     * 商户申请列表
     */
    public function merchantslists() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '没有传递用户id', []);
        }

        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        $lists = $this->MerchantsApplyModel->get_lists($con);

        if ($lists) {
            echoOk(200, '获取成功', $lists);
        }else{
            echoOk(301, '暂无订单', $lists);
        }
    }
}