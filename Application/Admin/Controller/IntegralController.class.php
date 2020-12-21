<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\IntegralModel;
use Home\Model\UserModel;

/**
 * Class IntegralController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property IntegralModel $IntegralModel
 * @property UserModel $UserModel
 */
class IntegralController extends CommonController {

    private $ListsModel;
    private $IntegralModel;
    private $UserModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->IntegralModel = new IntegralModel();
        $this->UserModel = new UserModel();
    }

    /**
     * 商品列表
     */
    public function lists() {
        $status_arr = [
            '0' => '下架',
            '1' => '正常'
        ];
        
        // 列表配置
        $data['model'] = 'integral';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('status') ? $data['where']['status'] = array('eq', I('status')) : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $lists[$k]['status_font'] = $status_arr[$v['status']];
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        
        $this->display();
    }

    /**
     * 添加兑换商品
     */
    public function add_integral() {
        $this->display();
    }

    /**
     * 添加兑换商品 - 提交
     */
    public function add_integral_submit() {
        if (empty($_POST['number']) || empty($_POST['name']) || empty($_POST['content'])) {
            die('没有参数');
        }

        $add = [
            'number' => $_POST['number'],
            'name' => $_POST['name'],
            'content' => $_POST['content']
        ];
        $this->IntegralModel->add_integral($add);
        
        echo 1;
    }

    /**
     * 编辑兑换商品
     */
    public function edit_integral() {
        if (empty($_GET['id'])) {
            die('没有参数');
        }

        $info = $this->ListsModel->get_info('integral', $_GET['id']);
        $this->assign('data', $info);

        $this->display();
    }

    /**
     * 添加兑换商品 - 提交
     */
    public function edit_integral_submit() {
        if (empty($_POST['id']) || empty($_POST['number']) || empty($_POST['name']) || empty($_POST['content'])) {
            die('没有参数');
        }

        $add = [
            'number' => $_POST['number'],
            'name' => $_POST['name'],
            'content' => $_POST['content']
        ];
        $this->IntegralModel->set_integral($_POST['id'], $add);

        echo 1;
    }

    /**
     * 积分兑换
     */
    public function exchange() {
        // 列表配置
        $data['model'] = 'integral_exchange';
        $data['page_size'] = 5;
        $data['order'] = 'id desc';

        // 搜索

        // 列表
        $status_lists = $this->IntegralModel->exchange_status('');
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['user_id']);
            $integral = $this->ListsModel->get_info('integral', $v['integral_id']);
            $lists[$k]['integral_name'] = $integral['name'];
            $lists[$k]['user_name'] = $user['name'];
            $lists[$k]['user_account'] = $user['account'];
            $lists[$k]['add_time'] = date('Y-m-d', $v['add_time']);
            $lists[$k]['status'] = $this->IntegralModel->exchange_status($v['status']);
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);
        $this->assign('status_lists', $status_lists);

        $this->display();
    }

    /**
     * 发货
     */
    public function check_submit() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }
        
        $this->IntegralModel->fahuo($_POST['uid']);
        
        echo 1;
    }

    /**
     * 状态
     */
    public function status() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }

        $integral = $this->IntegralModel->where('id = '.$_POST['uid'])->find();
        if ($integral['status'] == 1) {
            $this->IntegralModel->where('id = '.$_POST['uid'])->save(array('status' => '0'));
        } elseif ($integral['status'] == 0) {
            $this->IntegralModel->where('id = '.$_POST['uid'])->save(array('status' => '1'));
        }

        echo 1;
    }

}