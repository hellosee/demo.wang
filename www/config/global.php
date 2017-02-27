<?php
define('ROOT_PATH_SITE',str_replace('\\','/',substr(dirname(__FILE__),0,-7)));//当前站点根目录
define("WWW_ROOT_PATH",str_replace('\\','/',dirname(dirname(__FILE__))));//根目录物理路径
define('SCRIPT_DIR', (isset($_SERVER['SCRIPT_NAME']) ? rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/\\') : ''));
define('SITE_URL', isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '');
define('__CSS__',SITE_URL.'/static/css');//分享默认图
define('__JS__',SITE_URL.'/static/js');//分享默认图
define('__IMG__',SITE_URL.'/static/images');//分享默认图
define("SITE_NAME","支撑平台");
define("UPLOAD_PATH","/home/wwwroot/t.hellosee.cc/www");

