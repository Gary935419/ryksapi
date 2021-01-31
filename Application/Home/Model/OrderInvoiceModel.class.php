<?php
namespace Home\Model;
use Think\Model;

class OrderInvoiceModel extends Model {

    public function add_invoice( $data )
    {
        $re = $this->add( $data );
        return $re;
    }

}