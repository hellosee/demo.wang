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
	global $dbm,$data;
	//查询分类
	$params = array(
		'table_name' => DB_DBNAME.'.tb_product',
		'fields'     => 'id,cid,name,createtime',
		'suffix'     => 'order by createtime desc ',
		'count'      => 0,
	);
	$lists = $dbm->single_query($params);
	if(!empty($lists['list'])){
		foreach($lists['list'] as $_k => $_v){
			$data[$_k]['cname'] = $dbm->find(DB_DBNAME.'.tb_category', 'cname', "id='{$_v['cid']}'");
			$data[$_k]['pname'] = $_v['name'];
			$data[$_k]['createtime'] = $_v['createtime'];
		}
	}
}


?>
