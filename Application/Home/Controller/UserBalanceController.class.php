<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\UserModel;
use Home\Model\BalanceRecordModel;
use Home\Model\PostalModel;

/**
 * Class UserBalanceController
 * @package Home\Controller
 * @property UserModel $UserModel
 * @property BalanceRecordModel $BalanceRecordModel
 * @property PostalModel $PostalModel
 */
class UserBalanceController extends CommonController {

    private $UserModel;
    private $BalanceRecordModel;
    private $PostalModel;

    public function _initialize() {
        parent::_initialize();
        $this->UserModel = new UserModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
        $this->PostalModel = new PostalModel();
    }
    /**
     * 余额明细
     */
    public function detailed() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空');
        }

        $user = $this->UserModel->get_info($data['id']);

        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        $re['balance'] = $user['money'];
        $re['lists'] = $this->BalanceRecordModel->get_lists($con);
        
        echoOk(200, '获取成功', $re);
    }

    /**
     * 提现
     */
    public function postal() {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['bank_account']) || empty($data['name']) || empty($data['card_number']) ||
            empty($data['money'])) {
            echoOk(301, '必填项不能为空');
        }

        $user = $this->UserModel->get_info($data['id']);
        if ($user['money'] < $data['money']) {
            echoOk(301, '总余额不足');
        }

        $add = [
            'driver_id' => $data['id'],
            'bank_account' => $data['bank_account'],
            'name' => $data['name'],
            'card_number' => $data['card_number'],
            'money' => $data['money']
        ];
        $temp = $this->PostalModel->add_postal($add);

        if ($temp) {
            echoOk(200, '提现申请成功,请等待管理员审核');
        } else {
            echoOk(301, '提现申请失败');
        }
    }

}