<?php
namespace Admin\Controller;
use Think\Controller;

class CommonController extends Controller {

    protected function _initialize() {
        header('Content-Type:text/html; charset=utf-8');
        if (!session('?admin_id') || !session('?admin_username')) {
            $this->redirect('Login/index');
        }
    }

}