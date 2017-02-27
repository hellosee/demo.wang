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
		'fields'     => 'id,cid,name,createtime,archive',
		'suffix'     => 'order by createtime desc ',
		'count'      => 0,
	);
	$lists = $dbm->orderBy("createtime")->get ("tb_product", null, "id,cid,name,createtime,archive");

	if(!empty($lists)){
		foreach($lists as $_k => $_v){
			$data[$_k]['id'] = $_v['id'];
			$categoryTmp = $dbm->where ("id", $_v['cid'])->getOne('tb_category', 'cname');
			$data[$_k]['cname'] = $categoryTmp['cname'];
			$data[$_k]['pname'] = $_v['name'];
			$data[$_k]['archive'] = $_v['archive'];
			$data[$_k]['createtime'] = date("Y-m-d H:i:s",$_v['createtime']);
		}
	}
}

?>
