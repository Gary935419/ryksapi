<?php
namespace Home\Model;
use Think\Model;

class SetConfigModel extends Model {

    /**
     * 获取配置信息
     * @param $name
     * @return mixed
     */
    public function get_content($name) {
        $where['name'] = array('eq', $name);
        $content = $this->where($where)->getField('content');
        return $content;
    }

    /**
     * 设置
     * @param $name
     * @param $content
     */
    public function set_content($name, $content) {
        $where['name'] = array('eq', $name);
        $this->where($where)->save(array('content' => $content));
    }
    
}