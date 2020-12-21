<?php
namespace Admin\Model;
use Think\Model;

class ListsModel extends Model {

    public function __construct() {}

    /**
     * 列表
     * @param $data['model'] 模型
     * @param $data['where'] 条件
     * @param $data['page_size'] 每页条数
     * @param $data['order'] 排序
     * @return mixed
     */
    public function get_lists($data) {
        $model = M($data['model']);

        $re['count'] = $model->where($data['where'])->count();
        
        $page = new \Think\Page($re['count'], $data['page_size']);
        
        $re['show'] = $page->show();
        $re['lists'] = $model->where($data['where'])->order($data['order'])->limit($page->firstRow.','.$page->listRows)->select();
        
        return $re;
    }

    /**
     * 获取详情
     * @param $model
     * @param $id
     * @return mixed
     */
    public function get_info($model, $id) {
        $model = M($model);
        
        $where['id'] = array('eq', $id);
        $info = $model->where($where)->find();
        
        return $info;
    }

}