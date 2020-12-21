<?php
namespace Admin\Controller;
use Think\Controller;
use Home\Model\MessageModel;

/**
 * Class MessageController
 * @package Admin\Controller
 * @property MessageModel $MessageModel
 */
class MessageController extends CommonController {

    private $MessageModel;

    public function _initialize() {
        parent::_initialize();
        $this->MessageModel = new MessageModel();
    }

    /**
     * 系统消息
     */
    public function lists() {
        $this->display();
    }

    public function submit() {
        if (!empty($_POST['user_title']) && !empty($_POST['user_content'])) {
            $add = [
                'type' => '1',
                'title' => $_POST['user_title'],
                'content' => $_POST['user_content'],
                'add_time' => time()
            ];
            $this->MessageModel->add($add);

            echo 1;
        }

        if (!empty($_POST['driver_title']) && !empty($_POST['driver_content'])) {
            $add = [
                'type' => '2',
                'title' => $_POST['driver_title'],
                'content' => $_POST['driver_content'],
                'add_time' => time()
            ];
            $this->MessageModel->add($add);

            echo 1;
        }
    }

}