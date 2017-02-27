<?php
/**
 * 展示产品
 */
require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间
$data = array();
// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'admin'));

function m__list(){
    check_login();
    $pid = isset($_GET['pid']) ? H::sqlxss($_GET['pid']) : 0;
    if(!$pid){
        die('pid not found!');
    }
    global $dbm,$data;
    $data = $dbm->where('id',$pid)->getOne('tb_product','*');
    if(!$data){
        die('没有找到产品信息');
    }
}
?>
