<?php
namespace Home\Model;
use Think\Model;

class UserWorkingModel extends Model{

    /**
     * 添加上班信息
     * @param $data
     * @return mixed
     */
    public function add_working($data) {
        $id = $this->add($data);
        return $id;
    }

    /**
     * 更新上班信息
     * @param $driver_id
     * @param $data
     * @return bool
     */
    public function set_working($driver_id, $data) {
        $where['driver_id'] = array('eq', $driver_id);
        $temp = $this->where($where)->save($data);
        return $temp;
    }

    /**
     * 删除上班信息
     * @param $driver_id
     * @return mixed
     */
    public function del_working($driver_id) {
        $where['driver_id'] = array('eq', $driver_id);
        $temp = $this->where($where)->delete();
        return $temp;
    }
    
    /**
     * 获取上班状态
     * @param $driver_id
     * @return mixed|string
     */
    public function get_working_status($driver_id) {
        $where['driver_id'] = array('eq', $driver_id);
        $status = $this->where($where)->getField('status');
        if ($status) {
            return $status;
        } else {
            return '0';
        }
    }

    /**
     * 获取上班信息
     * @param $driver_id
     * @return mixed
     */
    public function get_working($driver_id) {
        $where['driver_id'] = array('eq', $driver_id);
        $data = $this->where($where)->find();
        return $data;
    }

    /**
     * 获取剩余座位个数
     * @param $driver_id
     * @return mixed
     */
    public function get_surplus_seat($driver_id) {
        $where['driver_id'] = array('eq', $driver_id);
        $surplus_seat = $this->where($where)->getField('surplus_seat');
        return $surplus_seat;
    }

    /**
     * 搜索司机
     * @param $con['taker_type_id']   上班模式 (1)城际拼车 (2)市区出行 (3)同城货运
     * @param $con['car_type_id']     车型ID
     * @param $con['longitude']       坐标-经度
     * @param $con['latitude']        坐标-纬度
     * @param $con['surplus_seat']    剩余座位个数(城际拼车)
     * @param $con['route_city_id1']  路线-出发城市ID(城际拼车)
     * @param $con['route_city_id2']  路线-目的城市ID(城际拼车)
     * @param $con['small_order_id']  小单子ID
     * @return driver_id
     */
    public function search_working_driver($con) {
        // ----- 条件 -----
//        $where = ' status_send = 0';                                       // 派送状态: (0)未派

//        $where  = ' taker_type_id = '.$con['taker_type_id'];                    // 上班模式
//        $where .= ' AND status_send = 0';                                       // 派送状态: (0)未派
//        $where .= ' AND been_order NOT LIKE "%['.$con['small_order_id'].']%"';  // 已推订单ID集

//        switch ($con['taker_type_id']) {
//            case 1: // 城际拼车
//                $where .= ' AND ( status = 1 OR status = 2 )';                  // 上班状态: (1)空闲 (2)拼车中
//                $where .= ' AND surplus_seat >= '.$con['surplus_seat'];         // 剩余座位数
//                break;
//            case 2: // 市区出行
//                $where .= ' AND status = 1';                                    // 上班状态: (1)空闲
//                break;
//        }
        $where = ' 1 = 1 ';
        // ----- 排序 -----
        $juli = "ROUND(2 * 6378.137* ASIN(SQRT(POW(SIN(PI()*(".$con['longitude']."-longitude)/360),2)+COS(PI()*".$con['latitude']."/180)* COS(latitude * PI()/180)*POW(SIN(PI()*(".$con['latitude']."-latitude)/360),2))))";
//        $juli = "ROUND(ACOS(SIN((".$con['latitude']." * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS((".$con['latitude']." * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS((".$con['longitude']."* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380,2)";
        $field = "driver_id,".$juli." AS juli";
        $order = "juli ASC";

        // ----- 搜索 -----
//        $driverData = $this->field($field)->where($where)->order($order)->find();
//        if ($driverData) {
//            return $driverData['driver_id'];
//        } else {
//            return '0';
//        }
        $driverData = $this->field($field)->where($where)->order($order)->select();
        if ($driverData) {
            foreach ($driverData as $item){
                $driverList[] =$item['driver_id'];
            }
            return $driverList;
        } else {
            return '0';
        }
    }
    /**
     * 搜索司机
     * @param $con['taker_type_id']   上班模式 (1)城际拼车 (2)市区出行 (3)同城货运
     * @param $con['car_type_id']     车型ID
     * @param $con['longitude']       坐标-经度
     * @param $con['latitude']        坐标-纬度
     * @param $con['surplus_seat']    剩余座位个数(城际拼车)
     * @param $con['route_city_id1']  路线-出发城市ID(城际拼车)
     * @param $con['route_city_id2']  路线-目的城市ID(城际拼车)
     * @param $con['small_order_id']  小单子ID
     * @return driver_id
     */
    public function search_working_driver_one($con) {
        // ----- 条件 -----
        $where = ' status_send = 0';                                       // 派送状态: (0)未派

//        $where  = ' taker_type_id = '.$con['taker_type_id'];                    // 上班模式
//        $where .= ' AND status_send = 0';                                       // 派送状态: (0)未派
//        $where .= ' AND been_order NOT LIKE "%['.$con['small_order_id'].']%"';  // 已推订单ID集

        switch ($con['taker_type_id']) {
            case 1: // 城际拼车
                $where .= ' AND ( status = 1 OR status = 2 )';                  // 上班状态: (1)空闲 (2)拼车中
                $where .= ' AND surplus_seat >= '.$con['surplus_seat'];         // 剩余座位数
                break;
            case 2: // 市区出行
                $where .= ' AND status = 1';                                    // 上班状态: (1)空闲
                break;
        }
        // ----- 排序 -----
        $juli = "ROUND(2 * 6378.137* ASIN(SQRT(POW(SIN(PI()*(".$con['longitude']."-longitude)/360),2)+COS(PI()*".$con['latitude']."/180)* COS(latitude * PI()/180)*POW(SIN(PI()*(".$con['latitude']."-latitude)/360),2))))";
//        $juli = "ROUND(ACOS(SIN((".$con['latitude']." * 3.1415) / 180 ) *SIN((latitude * 3.1415) / 180 ) +COS((".$con['latitude']." * 3.1415) / 180 ) * COS((latitude * 3.1415) / 180 ) *COS((".$con['longitude']."* 3.1415) / 180 - (longitude * 3.1415) / 180 ) ) * 6380,2)";
        $field = "driver_id,".$juli." AS juli";
        $order = "juli ASC";

        // ----- 搜索 -----
        $driverData = $this->field($field)->where($where)->order($order)->find();
        if ($driverData) {
            return $driverData['driver_id'];
        } else {
            return '0';
        }
    }
}