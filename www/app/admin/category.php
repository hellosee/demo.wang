<?php

require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间
$data = array();
// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'admin'));

function m__list(){
    check_login();
}
function m__addcategory(){
    check_login();
    global $dbm;
    $_POST = H::sqlxss($_POST);
    $fields['cname'] = isset($_POST['cname'])? $_POST['cname'] :'';//分类名称

    if($fields['cname'] == ""){
        die('{"code":1,"msg":"请输入分类"}');
    }

    $fields['parentid'] = 0;
    $fields['createtime'] = time();
    $rs = $dbm->insert("tb_category", $fields);
    if(!$rs['error']){
        die('{"code":0,"msg":"提交成功"}');
    }
    die('{"code":1,"msg":"提交失败"}');
}
?>
