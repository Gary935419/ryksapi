<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\IntegralModel;
use Home\Model\UserModel;

/**
 * Class UserIntegralController
 * @package Home\Controller
 * @property IntegralModel $IntegralModel
 * @property UserModel $UserModel
 */
class UserIntegralController extends CommonController {

    private $IntegralModel;
    private $UserModel;

    public function _initialize() {
        parent::_initialize();
        $this->IntegralModel = new IntegralModel();
        $this->UserModel = new UserModel();
    }

    /**
     * 兑换列表
     */
    public function lists() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        $user = $this->UserModel->get_info($data['id']);
        $re['integral'] = $user['integral'];
        $lists = $this->IntegralModel->get_lists();
        if ($lists) {
            foreach ($lists as $k => $v) {
                $lists[$k]['is_exchange'] = $this->IntegralModel->is_exchange($v['id'], $data['id']);
            }
        }
        $re['lists'] = $lists;

        echoOk(200, '获取成功', $re);
    }

    /**
     * 兑换
     */
    public function exchange() {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['integral_id'])) {
            echoOk(301, '必填项不能为空', []);
        }
        
        $temp = $this->IntegralModel->exchange($data['id'], $data['integral_id']);
        if ($temp) {
            echoOk(200, '兑换成功');
        } else {
            echoOk(301, '兑换失败');
        }

    }
}