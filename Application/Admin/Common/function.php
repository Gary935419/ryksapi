<?php
/**
 * 补全路径-接口
 * @param $img
 * @return string
 */
function httpImg($img) {
    $img1 = C('web_address').$img;
    return $img1;
}
