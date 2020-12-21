<?php
namespace Admin\Controller;
use Think\Controller;
use Admin\Model\AdminUserModel;

/**
 * Class LoginController
 * @package Admin\Controller
 * @property AdminUserModel $AdminUserModel
 */
class LoginController extends Controller {

    private $AdminUserModel;

    public function _initialize() {
        $this->AdminUserModel = new AdminUserModel();
    }
    
    public function index() {
        $this->display();
    }

    public function submit() {
        $verify = new \Think\Verify();
        $temp = $verify->check($_POST['yzm']);
        if (!$temp) {
            $this->error('验证码错误');
        }

        $login_temp = $this->AdminUserModel->login($_POST['username'], $_POST['userpwd']);
        if (!$login_temp) {
            $this->error('账号密码错误');
        }
        
        session('admin_id', $login_temp['id']);
        session('admin_username', $login_temp['username']);

        $this->success('登录成功', C('admin_address').'Index/index');
    }

    public function logout() {
        session(null);
        session('[destroy]'); // 销毁session
        $this->redirect('Login/index');
    }
    
    public function verify() {
        $config =    array(
            'imageW'      =>    80,    // 宽度
            'imageH'      =>    30,    // 高度
            'fontSize'    =>    14,    // 验证码字体大小
            'length'      =>    3,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
        );
        $Verify = new \Think\Verify($config);
        $Verify->codeSet = '0123456789';
        $Verify->entry();
    }

}