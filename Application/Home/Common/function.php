<?php
/**
 * 获取请求数据
 * @return array|mixed
 */
function getRequest() {
    $data = [];
    if (!empty($GLOBALS['HTTP_RAW_POST_DATA'])) {
        $data = array_merge($data, json_decode($GLOBALS['HTTP_RAW_POST_DATA'], true));
    }
    if (!empty($_POST)) {
        $data = array_merge($data, $_POST);
    }
    if (!empty($_GET)) {
        $data = array_merge($data, $_GET);
    }
    return $data;
}

/**
 * 发送数据
 * @param $status
 * @param string $msg
 * @param string $return
 */
function echoOk($status, $msg = '', $return = '') {
    header("Content-Type:application/json");
    $arr = array(
        'status' => $status,
        'msg' => $msg,
        'result' => $return
    );
    echo json_encode($arr);
    die;
}

/**
 * 补全路径-接口
 * @param $img
 * @return string
 */
function httpImg($img) {
    $img1 = C('web_address').$img;
    return $img1;
}

/**
 * 上传图片
 * @param string $savePath 图片相对上传地址
 * @return array 上传成功后的地址
 * @throws Exception 上传出错抛出
 */
function uploadImg($savePath){
    $config = [
        'maxSize' => 1048576*15, //15M
        'rootPath' => C('upload_path').'/',
        'savePath' => trim($savePath),
        'saveName' => array('uniqid',''),
        'subName'  => array('date', 'Ymd'),
        'exts' => array('jpg','gif','png','jpeg'),
    ];
    if(!file_exists($config['rootPath'].$savePath)){
        mkdir($config['rootPath'].$savePath, 0777, true);
    }
	
    $uploadModel = new \Think\Upload($config);
	$uploadModel->saveName = array('uniqid', array('', true));
    $info = $uploadModel->upload($_FILES);
    if(!$info){
        throw new \Exception($uploadModel->getError());
    }else{
        foreach($info as $index => $file){
            $info[$index]['path'] = $uploadModel->rootPath.$file['savepath'].$file['savename'];
        }
    }
    return $info;
}