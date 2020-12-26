<?php

namespace Home\Model;

use Think\Model;

class UserWithdrawalModel extends Model
{

    /**
     * 提现记录插入
     * @return bool
     */
    public function withdrawal_insert($insert)
    {
        $re = $this->add($insert);
        return $re;
    }
}