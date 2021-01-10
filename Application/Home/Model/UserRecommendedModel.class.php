<?php
namespace Home\Model;
use Think\Model;

class UserRecommendedModel extends Model {

    /**
     * 推荐记录 金额记录
     * @return bool
     */
    public function recommended_insert($insert)
    {
        $re = $this->add($insert);
        return $re;
    }

}