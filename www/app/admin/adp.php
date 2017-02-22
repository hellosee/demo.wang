<?php
/**
 * 增加产品
 */
require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间
$categorys = array();
// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'admin'));

function m__list(){
	//check_login();
	
	global $dbm,$categorys;
	//查询分类
	$params = array(
		'table_name' => DB_DBNAME.'.tb_category',
		'fields'     => 'id,cname',
		'suffix'     => 'order by createtime desc ',
		'count'      => 0,
	);
	$lists = $dbm->single_query($params);
	if(!empty($lists['list'])){
		foreach($lists['list'] as $_k => $_v){
			$categorys[$_k]['cid'] = $_v['id'];
			$categorys[$_k]['cname'] = $_v['cname'];
		}
	} else{
		
	}
}


?>
