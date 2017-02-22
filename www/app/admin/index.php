<?php

require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间

// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'admin'));

function m__list(){
	check_login();
}


?>
