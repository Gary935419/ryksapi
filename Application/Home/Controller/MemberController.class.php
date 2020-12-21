<?php
namespace Home\Controller;
use Think\Controller;
use Home\Model\SetConfigModel;
use Home\Model\FeedbackModel;

/**
 * Class MemberController
 * @package Home\Controller
 * @property SetConfigModel $SetConfigModel
 * @property FeedbackModel $FeedbackModel
 */
class MemberController extends CommonController {

    private $SetConfigModel;
    private $FeedbackModel;

    public function _initialize() {
        parent::_initialize();
        $this->SetConfigModel = new SetConfigModel();
        $this->FeedbackModel = new FeedbackModel();
    }
    
    /**
     * 联系我们
     */
    public function contact_us() {
        $data = self::$_DATA;

        if (empty($data['type'])) {
            echoOk(301, '必填项不能为空');
        }

        $set_config = '';
        if ($data['type'] == 1) { // 用户端
            $set_config = $this->SetConfigModel->get_content('user_contact_us');
        } elseif ($data['type'] == 2) { // 司机端
            $set_config = $this->SetConfigModel->get_content('driver_contact_us');
        }

        echoOk(200, '获取成功', $set_config);
    }

    /**
     * 意见反馈
     */
    public function feedback() {
        $data = self::$_DATA;

        if (empty($data['id']) || empty($data['content'])) {
            echoOk(301, '必填项不能为空');
        }

        $add_data = [
            'user_id' => $data['id'],
            'content' => $data['content']
        ];
        $re = $this->FeedbackModel->add_info($add_data);

        if ($re) {
            echoOk(200, '提交成功');
        } else {
            echoOk(301, '提交失败');
        }
    }

    /**
     * 关于我们
     */
    public function about_us() {
        $data = self::$_DATA;

        if (empty($data['type'])) {
            echoOk(301, '必填项不能为空');
        }

        $set_config = '';
        if ($data['type'] == 1) { // 用户端
            $set_config = $this->SetConfigModel->get_content('user_about_us');
        } elseif ($data['type'] == 2) { // 司机端
            $set_config = $this->SetConfigModel->get_content('driver_about_us');
        }

        echoOk(200, '获取成功', $set_config);
    }

    /**
     * 积分规则
     */
    public function integral_rule() {
        $set_config = $this->SetConfigModel->get_content('integral_rule');
        echoOk(200, '获取成功', $set_config);
    }

}