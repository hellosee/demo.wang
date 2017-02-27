<?php

require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间

// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'admin'));

function m__list(){
    
}
function m__dologin(){
	global $dbm;
	$_POST = H::sqlxss($_POST);
	$username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
	if(!$username){die ('{"code":"1","msg":"请输入用户名","data":""}');}
	if(!$password){die ('{"code":"1","msg":"请输入密码","data":""}');}
	
	//通过用户名查询账号
	$user = $dbm->where('username',$username)->getOne('tb_member',"id,username,password,salt");
	if($user && is_array($user)){
		if($password === $user['password']){
			session_open();
			$_SESSION['uid'] = $user['id'];
			$_SESSION['user'] = $user;
			session_close();
			unset($user);
			die ('{"code":"0","msg":"登录成功","data":""}');
		} else {
			unset($user);
			die ('{"code":"1","msg":"用户密码不正确","data":""}');
		}
	
	} else{
		die ('{"code":"1","msg":"用户不存在","data":""}');
	}
	
	
	
}

function m__logout(){
	session_open();
	$_SESSION['uid'] = null;
	$_SESSION['user'] = null;
	session_close();
	header('Location: login.php');
}



?>
