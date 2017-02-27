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
	check_login();
	
	global $dbm,$categorys;
	//查询分类
	$lists = $dbm->orderBy("createtime")->get ("tb_category", null, "id,cname");

	if(!empty($lists)){
		foreach($lists as $_k => $_v){
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
	$fields['archive'] = isset($_POST['archive'])? $_POST['archive'] :0;//产品是否解压
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
	$rs = $dbm->insert("tb_product", $fields);
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
	$upload->savePath = UPLOAD_PATH.'/products';
	$upload->uploadReplace = true;
	$data = array();
	if(!$upload->upload()){
		$data['code'] = 1;
		$data['data'] = $upload->getErrorMsg();
	} else {
		$file = $upload->getUploadFileInfo();
		$unzip = true;
		$unzip = unzip($file[0]['savepath'],$file[0]['savename']);
		if($unzip){
			$file[0]['archive'] = 1;
		}else {
			$file[0]['archive'] = 0;
		}
		$data['code'] = 0;
		$data['data'] = $file[0];
	}
	die(json_encode($data));
}

function unzip($fileurl,$filename){
	//上传成功后解压
	$file_path = $fileurl.$filename;
	$save_path = ROOT_PATH_SITE.DS.'products_unzip'.DS.basename($filename,'.zip').DS;
	$archive = new PclZip($file_path);
	if( @$archive->extract(PCLZIP_OPT_PATH,$save_path)){
		return true;
	} else {
		//file_put_contents('error', $archive->errorInfo());
		return false;
	}
}
?>
