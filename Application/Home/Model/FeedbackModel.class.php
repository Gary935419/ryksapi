<?php
namespace Home\Model;
use Think\Model;

class FeedbackModel extends Model {

    /**
     * 添加数据
     * @param $data
     * @return mixed
     */
    public function add_info($data) {
        $id = $this->add($data);
        return $id;
    }
}