<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {

    public function index() {
        header('HTTP/1.0 404 Not Found');
        echo 'Not Found';
    }
    
}