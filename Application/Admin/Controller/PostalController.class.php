<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\PostalModel;
use Home\Model\UserModel;
use Home\Model\BalanceRecordModel;

/**
 * Class PostalController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property PostalModel $PostalModel
 * @property UserModel $UserModel
 * @property BalanceRecordModel $BalanceRecordModel
 */
class PostalController extends CommonController {

    private $ListsModel;
    private $PostalModel;
    private $UserModel;
    private $BalanceRecordModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->PostalModel = new PostalModel();
        $this->UserModel = new UserModel();
        $this->BalanceRecordModel = new BalanceRecordModel();
    }
    
    /**
     * 提现审核
     */
    public function lists() {
        // 列表配置
        $data['model'] = 'postal';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('status') ? $data['where']['status'] = array('eq', I('status')) : '';

        // 列表
        $status_lists = $this->PostalModel->status('');
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['driver_id']);
            $lists[$k]['driver_name'] = $user['name'];
            $lists[$k]['driver_account'] = $user['account'];
            $lists[$k]['add_time'] = date('Y-m-d H:i:s', $v['add_time']);
            $lists[$k]['status'] = $this->PostalModel->status($v['status']);
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('status_lists', $status_lists);

        $this->display();
    }

    /**
     * 审核提交
     */
    public function check_submit() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('postal', $_POST['uid']);
        $user = $this->UserModel->get_info($info['driver_id']);

        if ($user['money'] < $info['money']) {
            die('提现金额不足');
        }

        // 扣除金额
        $this->BalanceRecordModel->balance($user['id'], '提现成功', 2, $info['money']);

        // 改变状态
        $this->PostalModel->set_info($info['id'], array('status' => '2'));
        
        echo 1;
    }

}