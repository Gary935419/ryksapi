<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\UserModel;

/**
 * Class FeedbackController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property UserModel $UserModel
 */
class FeedbackController extends CommonController {

    private $ListsModel;
    private $UserModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->UserModel = new UserModel();
    }

    /**
     * 意见反馈
     */
    public function lists() {
        // 列表配置
        $data['model'] = 'feedback';
        $data['page_size'] = 10;
        $data['order'] = 'id desc';

        // 搜索
        I('status') ? $data['where']['status'] = array('eq', I('status')) : '';

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];
        foreach ($lists as $k => $v) {
            $user = $this->UserModel->get_info($v['user_id']);
            $lists[$k]['user_name'] = $user['name'];
        }

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);

        $this->display();
    }

}