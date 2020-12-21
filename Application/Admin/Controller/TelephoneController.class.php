<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\TelephoneModel;

/**
 * Class TelephoneController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property TelephoneModel $TelephoneModel
 */
class TelephoneController extends CommonController {

    private $ListsModel;
    private $TelephoneModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->TelephoneModel = new TelephoneModel();
    }

    /**
     * 班次时间列表
     */
    public function lists() {
        // 列表配置
        $data['model'] = 'telephone';
        $data['page_size'] = 10;
        $data['order'] = 'id asc';
        $data['where']['type'] = array('eq', 1);

        // 搜索

        // 列表
        $re = $this->ListsModel->get_lists($data);
        $lists = $re['lists'];

        $this->assign('lists', $lists);
        $this->assign('count', $re['count']);
        $this->assign('page', $re['show']);

        $this->display();
    }

    /**
     * 添加班次
     */
    public function add() {
        $this->display();
    }

    /**
     * 添加班次 - 提交
     */
    public function add_submit() {
        if (empty($_POST['name']) || empty($_POST['content'])) {
            die('没有参数');
        }

        $add = [
            'type' => 1,
            'name' => $_POST['name'],
            'content' => $_POST['content']
        ];
        $this->TelephoneModel->add($add);

        echo 1;
    }

    /**
     * 删除
     */
    public function del() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }

        $this->TelephoneModel->delete($_POST['uid']);
        echo 1;
    }

}