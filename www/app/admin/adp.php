<?php
/**
 * 增加产品
 */
require_once(dirname(__FILE__) . "/../../config/init.php");
require_once(dirname(__file__) . '/../../class/UploadFile.class.php');
require_once(dirname(__file__) . '/../../class/unzip.class.php');
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

function m__addpro(){
	check_login();
	global $dbm;
	$_POST = H::sqlxss($_POST);
	$fields['cid'] = isset($_POST['cid'])? $_POST['cid'] :0;//分类ID
	$fields['name'] = isset($_POST['name'])? $_POST['name'] :'';//产品名称
	$fields['prourl'] = isset($_POST['profile'])? $_POST['profile'] :0;//产品文件名
	if($fields['cid'] == ""){
		die('{"code":1,"msg":"请选择分类"}');
	}
	if($fields['name'] == ""){
		die('{"code":1,"msg":"请填写产品名称"}');
	}
	if($fields['prourl'] == ""){
		die('{"code":1,"msg":"请上传产品压缩包"}');
	}

	//对文件名
	$fields['prourl'] = basename($fields['prourl'],".zip");
	$fields['createtime'] = time();
	$rs = $dbm->single_insert(DB_DBNAME.".tb_product", $fields);
	if(!$rs['error']){
		die('{"code":0,"msg":"提交成功"}');
	}
	die('{"code":1,"msg":"提交失败"}');

}

function m__profile(){
	check_login();
	$upload = new UploadFile();
	$upload->maxSize = 100 * pow(2,20);
	$upload->allowExts = array('zip');
	$upload->savePath = 'F:\products';
	$data = array();
	if(!$upload->upload()){
		$data['code'] = 1;
		$data['data'] = $upload->getErrorMsg();
	} else {
		//上传成功后解压

		$data['code'] = 0;
		$data['data'] = $upload->getUploadFileInfo();
	}
	die(json_encode($data));
}


?>
