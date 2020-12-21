<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\MessageModel;

/**
 * Class MessageController
 * @package Home\Controller
 * @property MessageModel $MessageModel
 */
class MessageController extends CommonController {

    private $MessageModel;

    public function _initialize() {
        parent::_initialize();
        $this->MessageModel = new MessageModel();
    }

    /**
     * 未读消息
     */
    public function unread_num() {
        $data = self::$_DATA;

        if (empty($data['type']) || empty($data['id'])) {
            echoOk(301, '必填项不能为空');
        }
        
        $temp = $this->MessageModel->is_unread($data['type'], $data['id']);
        
        echoOk(200, '获取成功', $temp);
    }

    /**
     * 消息列表
     */
    public function lists() {
        $data = self::$_DATA;

        if (empty($data['id'])) {
            echoOk(301, '必填项不能为空');
        }
        
        $con = [
            'id' => $data['id'],
            'page' => $data['page'],
            'limit' => $data['limit']
        ];
        $lists = $this->MessageModel->get_lists($con);

        echoOk(200, '获取成功', $lists);
    }

    /**
     * 消息详情
     */
    public function details() {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['user_id'])) {
            echoOk(301, '必填项不能为空');
        }

        $this->MessageModel->read_message($data['id'], $data['user_id']);
        $temp = $this->MessageModel->get_info($data['id']);

        echoOk(200, '获取成功', $temp);
    }

    /**
     * 删除消息
     */
    public function del() {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['user_id'])) {
            echoOk(301, '必填项不能为空');
        }
        
        $this->MessageModel->del_message($data['id'], $data['user_id']);

        echoOk(200, '操作成功');
    }

}