<?php
namespace Home\Model;
use Think\Model;

class TakerTypeModel extends Model {

    public function __construct() {}

    /**
     * 获取接单模式
     * @return array
     */
    public function get_lists() {
        $arr = [
            [
                'id' => '1',
                'name' => '城际拼车'
            ],
            [
                'id' => '2',
                'name' => '市区出行'
            ],
            [
                'id' => '3',
                'name' => '同城货运'
            ],
        ];
        return $arr;
    }
    
}