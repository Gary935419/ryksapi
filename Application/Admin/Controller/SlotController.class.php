<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\ListsModel;
use Home\Model\TimeSlotModel;

/**
 * Class SlotController
 * @package Admin\Controller
 * @property ListsModel $ListsModel
 * @property TimeSlotModel $TimeSlotModel
 */
class SlotController extends CommonController {

    private $ListsModel;
    private $TimeSlotModel;

    public function _initialize() {
        parent::_initialize();
        $this->ListsModel = new ListsModel();
        $this->TimeSlotModel = new TimeSlotModel();
    }

    /**
     * 班次时间列表
     */
    public function lists() {
        // 列表配置
        $data['model'] = 'time_slot';
        $data['page_size'] = 10;
        $data['order'] = 'id asc';

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
        $times = $this->TimeSlotModel->get_times();
        $times_1 = $this->TimeSlotModel->get_times_1();
        
        $this->assign('times', $times);
        $this->assign('times_1', $times_1);
        
        $this->display();
    }

    /**
     * 添加班次 - 提交
     */
    public function add_submit() {
        $times = $_POST['times'] ? $_POST['times'] : '00';
        $times_1 = $_POST['times_1'] ? $_POST['times_1'] : '00';
        $this->TimeSlotModel->add(array('start_time' => $times.':'.$times_1));
        
        echo 1;
    }

    /**
     * 删除
     */
    public function del() {
        if (empty($_POST['uid'])) {
            die('没有参数');
        }
        
        $this->TimeSlotModel->delete($_POST['uid']);
        echo 1;
    }

}