<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */

date_default_timezone_set('Asia/Shanghai'); //默认时区
error_reporting(-1); //报告所有错误，0为忽略所有错误
ini_set('display_errors', '1'); //开启错误提示
ini_set('magic_quotes_runtime', '0'); //魔法反斜杠转义关闭
ini_set('default_charset', 'utf-8'); //默认编码


define("ROOT_PATH",str_replace('\\','/',dirname(__FILE__)));//根目录物理路径
define("DS",DIRECTORY_SEPARATOR);
require_once(ROOT_PATH . "/../class/helper.class.php"); //通用方法类
require_once(ROOT_PATH . "/../config/global.php"); //基本配置

// 兼容 DOCUMENT_ROOT 变量
$_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));

if (!isset($_SERVER['QUERY_STRING']) || empty($_SERVER['QUERY_STRING'])) $_SERVER['QUERY_STRING']='';
if (!isset($_SERVER['REQUEST_URI']) || empty($_SERVER['REQUEST_URI'])) $_SERVER['REQUEST_URI']='';
if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) $_SERVER['HTTP_HOST']='';

require_once(ROOT_PATH . "/../config/conn.php"); //数据库连接

require_once(ROOT_PATH . "/../class/database.class.php"); //数据库操作类
require_once(ROOT_PATH . "/../class/function.php"); //前后台公用方法
require_once(ROOT_PATH . "/../class/comm.class.php");

define('AJAX',ajax_request()?1:0);
define('USER_DEV',H::user_dev()); //判断请求设备,手机AJAX瀑布流数据请求

$p = $_GET['p'] = isset($_GET['p'])?intval($_GET['p']):1; //分页页码
if ($p<=0) $p = 1;
$_GET['tpl'] = isset($_GET['tpl']) ? trim($_GET['tpl']) : ''; //模板参数


//初始化数据库
$dbm = database::init();

/*
$V = new Vars($global_vars);//变量类
$U = new User($dbm);//用户类
$T = new Tree($dbm);//树类
$Q = new Quan($dbm);//圈子类
$P = new Page($dbm);//微主页类
$N = new Number($dbm);//微信大全类
$C = new Comm($dbm);//公共操作方法

*/
//JS防止缓存或者上传需要标识
$timestamp=time();
