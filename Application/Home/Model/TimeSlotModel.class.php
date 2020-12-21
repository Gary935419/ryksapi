<?php
namespace Home\Model;
use Think\Model;

class TimeSlotModel extends Model {

    /**
     * 获取时间段列表
     * @return mixed
     */
    public function get_lists() {
        $lists = $this->order('start_time ASC')->select();
        return $lists;
    }

    /**
     * 获取时间
     * @return array
     */
    public function get_times() {
        for ($i=0;$i<25;$i++) {
            $times[] = sprintf("%02d", $i);
        }
        return $times;
    }

    /**
     * 获取时间
     * @return array
     */
    public function get_times_1() {
        for ($i=0;$i<61;$i++) {
            $times[] = sprintf("%02d", $i);
        }
        return $times;
    }

}