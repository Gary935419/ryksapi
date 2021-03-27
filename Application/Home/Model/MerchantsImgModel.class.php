<?php

namespace Home\Model;

use Think\Cache\Driver\Db;
use Think\Model;

class MerchantsImgModel extends Model
{

    /**
     * 添加大单
     * @param $data
     * @return mixed
     */
    public function add_order( $data )
    {
        $re = $this->add( $data );
        return $re;
    }

}