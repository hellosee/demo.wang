<?php
/**
 * 检查用户是否登陆，如果没有登录，跳转到登录页面
 * @param $url 跳转路径
 * @param $ajax 是否ajax请求，默认为非ajax请求
 */
function check_login($ajax=0,$url='login.php') {
    session_open();//var_dump(session_id());var_dump($_SESSION);die();
    $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
    session_close();

    if (!is_array($session_user)) {
        if(intval(AJAX)==1){//如果是ajax请求，返回JSON串
            die('{"code":1,"msg":"<a href=\''.DOMAIN_USER.'/app/user/login.php\'>请先登录</a>","url":"'.$url.'","relogin":1}');
        }
        //否则直接跳转
        header("Location: $url");
        die();
    }
}
function check_mobile(){
    session_open();
    $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
    session_close();

    $session_user['login_mobile']=isset($session_user['login_mobile'])?intval($session_user['login_mobile']):0;
    $uid=isset($session_user['uid'])?intval($session_user['uid']):0;
    if($uid<=0) {
        H::error_show('{"code":1,"msg":"登录状态已失效，请先登录"}');
    }
    if($uid>120) {
        if (intval($session_user['login_mobile']) == 0) {
            H::error_show('{"code":1,"msg":"请先验证手机号码，<a href=\'' . DOMAIN_USER . '/app/user/info.php\' target=\'_blank\'>点此验证</a>"}');
        }
    }
}

/**
 * 检查用户是否有合法的操作权限
 * @param  $level 需要判断的权限值
 * @param  $ajax 是否ajax请求
 * @param  $return   是否返回 1=返回 true,false 0，按是否ajax状态输出信息
 */
function check_level($level,$ajax=0,$return=0) {
    $has_level=false;
    //分解登录后的用户权限代码
    //session_open();
    $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
    //session_close();

    $group_level=explode(',',isset($session_user['group_level'])?$session_user['group_level']:'');//组权限
    $user_level=explode(',',isset($session_user['login_level'])?$session_user['login_level']:'');//用户允许权限
    $user_no_level=explode(',',isset($session_user['login_no_level'])?$session_user['login_no_level']:'');//用户禁止权限
    //重组权限代码
    $level_all=array();
    foreach($group_level as $v) $level_all[$v]=$v;
    foreach($user_level as $v) $level_all[$v]=$v;
    foreach($user_no_level as $v) if(isset($level_all[$v])) unset($level_all[$v]);

    //系统管理员权限默认为100
    if(isset($session_user['group_level']) && $session_user['group_level'] == 100) {
        $has_level=true;
    }else{
        if(in_array($level,$level_all)){
            $has_level=true;
        }
    }

    //如果需要返回 true false
    if($return) return $has_level;
    //没有权限,终止信息
    if(!$has_level){
        if($ajax==1){
            die('{"code":1,"msg":"没有权限，请联系管理员"}');
        }else{
            H::error_show('{"code":1,"msg":"没有权限，请联系管理员"}');
        }
    }
}

/**
 * 动作处理函数调用，占用固定GET参数 m
 */
function call_mfunc(){

    $_GET['m'] = isset($_GET['m'])?$_GET['m']:'list';
    $_GET['m']=H::sqlxss($_GET['m']);
    if (function_exists("m__" . $_GET['m'])) {
        call_user_func("m__" . $_GET['m']);
    }else{
        if($_GET['m']!='list'){
            die(' <b>m__'.$_GET['m'].'</b> function is not exists(Code:003)');
        }
    }
}

/**
 * 调用模板,$file 为当前主程序的文件名，需要根据文件名去找对应目录的模板文件,$app_dir 应用目录名称
 * @param $file 主程序文件
 * @param $app_dir 主程序所在APP目录名
 * @param $tpl_prefix 模板前缀
 * @param $is_die 是否中止页面执行（一般模板内的模板引用会中止）
 * @param $skin_user 不读取默认皮肤，指定皮肤
 */
function assign_tpl($file, $app_dir = '',$tpl_prefix='tpl.',$is_die=1,$skin_user='') {
    $tpl_arr=explode(',',TPL_SORT);//模板查找顺序
    //判断浏览设备
    $device=H::user_dev();
    $skin=SKIN;
    if($skin_user!='') $skin=$skin_user;

    //判断需要判断的浏览设备目录
    $loop_dev=0;
    foreach($tpl_arr as $k=>$v){
        if($device==$v) {
            $loop_dev=$k;break;
        }
    }

    $app_dir=($app_dir=='')?'':$app_dir.'/';
    $error='';
    //指定设备+指定皮肤
    $tpl_file = ROOT_PATH_SITE.'/app_tpl_'.$device.'/'.$skin.'/'.$app_dir.$tpl_prefix.$file;
    if(file_exists($tpl_file)){
        $error='';
    }else{
        $error='The file does not exist : '.$tpl_file.'<br>';//echo($error);
        //指定设备+默认皮肤
        $tpl_file = ROOT_PATH_SITE.'/app_tpl_'.$device.'/default/'.$app_dir.$tpl_prefix.$file;//echo($tpl_file.'<br>');
        if(file_exists($tpl_file)){
            $error='';
        }else{
            $error='The file does not exist : '.$tpl_file.'<br>';//echo($tpl_file);
            //其他设备
            for($i=$loop_dev;$i<count($tpl_arr);$i++){
                $tpl_file = ROOT_PATH_SITE.'/app_tpl_'.$tpl_arr[$i].'/'.$skin.'/'.$app_dir.$tpl_prefix.$file;//echo($i.'.'.$tpl_file.'<br>');
                if(file_exists($tpl_file)){
                    $error='';break;
                }else{
                    $error='The file does not exist : '.$tpl_file.'<br>';//echo($error);
                    //其他设备+默认皮肤
                    $tpl_file = ROOT_PATH_SITE.'/app_tpl_'.$tpl_arr[$i].'/default/'.$app_dir.$tpl_prefix.$file;//echo($i.'.'.$tpl_file.'<br>');
                    if(file_exists($tpl_file)){
                        $error='';break;
                    }else{
                        $error='The file does not exist : '.$tpl_file.'<br>';//echo($error);
                    }
                }
            }
        }
    }

    //返回模板
    if ($error=='') {//echo($tpl_file);
        if(!defined('TPL_CACHE_TIME')) return $tpl_file;
        return $tpl_file;
    }else{
        if($is_die){
            die($file.' : The file does not exist, may be affected by the impact of directory permissions(Code:001)');
        }else{
            return '';
        }
    }
}

/**
 * 包含皮肤目录里的顶级文件，如头部文件，尾部文件 $file=inc.head.php,inc.foot.php 等
 */
function assign_tpl_inc($file,$is_die=0) {
    $tpl_file=  assign_tpl($file,"","",0);
    if($tpl_file==''){
        $error='The file does not exist : '.$file.'<br>';//echo($error);
        H :: logs('file_not_exists_', $error);
        if($is_die){
            die($file.' : The file does not exist, may be affected by the impact of directory permissions(Code:002)');
        }else{
            return '';
        }
    }else{
        return $tpl_file;
    }
}




/**
 * 是否为AJAX提交
 * @return boolean
 */
function ajax_request() {
	if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		return true;
	return false;
}

//过滤危险HTML字符，DZ论坛方式
function filter_html($html) {
    preg_match_all("/\<([^\<]+)\>/is", $html, $ms);

    $searchs[] = '<';
    $replaces[] = '&lt;';
    $searchs[] = '>';
    $replaces[] = '&gt;';
    if(count($ms)>0) {
        if($ms[1]) {
            $allowtags = 'img|a|font|div|table|tbody|caption|tr|td|th|br|p|b|strong|i|u|em|span|ol|ul|li|blockquote';
            $ms[1] = array_unique($ms[1]);
            foreach ($ms[1] as $value) {
                $searchs[] = "&lt;".$value."&gt;";

                $value = str_replace('&amp;', '_uch_tmp_str_', $value);
                $value = dhtmlspecialchars($value);
                $value = str_replace('_uch_tmp_str_', '&amp;', $value);

                $value = str_replace(array('\\','/*'), array('.','/.'), $value);
                $skipkeys = array('onabort','onactivate','onafterprint','onafterupdate','onbeforeactivate','onbeforecopy','onbeforecut','onbeforedeactivate',
                        'onbeforeeditfocus','onbeforepaste','onbeforeprint','onbeforeunload','onbeforeupdate','onblur','onbounce','oncellchange','onchange',
                        'onclick','oncontextmenu','oncontrolselect','oncopy','oncut','ondataavailable','ondatasetchanged','ondatasetcomplete','ondblclick',
                        'ondeactivate','ondrag','ondragend','ondragenter','ondragleave','ondragover','ondragstart','ondrop','onerror','onerrorupdate',
                        'onfilterchange','onfinish','onfocus','onfocusin','onfocusout','onhelp','onkeydown','onkeypress','onkeyup','onlayoutcomplete',
                        'onload','onlosecapture','onmousedown','onmouseenter','onmouseleave','onmousemove','onmouseout','onmouseover','onmouseup','onmousewheel',
                        'onmove','onmoveend','onmovestart','onpaste','onpropertychange','onreadystatechange','onreset','onresize','onresizeend','onresizestart',
                        'onrowenter','onrowexit','onrowsdelete','onrowsinserted','onscroll','onselect','onselectionchange','onselectstart','onstart','onstop',
                        'onsubmit','onunload','javascript','script','eval','behaviour','expression','style','class');
                $skipstr = implode('|', $skipkeys);
                $value = preg_replace(array("/($skipstr)/i"), '.', $value);
                if(!preg_match("/^[\/|\s]?($allowtags)(\s+|$)/is", $value)) {
                    $value = '';
                }
                $replaces[] = empty($value)?'':"<".str_replace('&quot;', '"', $value).">";
            }
        }
    }
    $html = str_replace($searchs, $replaces, $html);
    return $html;
}

function dhtmlspecialchars($string, $flags = null) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val, $flags);
		}
	} else {
		if($flags === null) {
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== false) {
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
			}
		} else {
			if(PHP_VERSION < '5.4.0') {
				$string = htmlspecialchars($string, $flags);
			} else {
				$charset = 'UTF-8';
				$string = htmlspecialchars($string, $flags, $charset);
			}
		}
	}
	return $string;
}


//session 控制函数
function session_open(){
    global $lifeTime;
    @session_start();
    if(strstr($_SERVER['HTTP_USER_AGENT'],'APP=HUIWEISHANG')){
        setcookie(session_name(), session_id(), time() + $lifeTime, "/");
    }
}

function session_close(){
    session_write_close();
}

function hash_table_id($uid) {
    if($uid <= 531000) return '';
    
    $limit = 100000; //划分基数
     //小于基数
    for($i=1;$i<100;$i++) { //最终划分 100 张表
        if($uid > $i*$limit && $uid <= ($i+1)*$limit) return '_'.$i;
    }
}
//postCurl方法
function postCurl($url, $body, $header = array(), $method = "POST")
{
	array_push($header, 'Accept:application/json');
	array_push($header, 'Content-Type:application/json');

	$ch = curl_init();//启动一个curl会话
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	//curl_setopt($ch, $method, 1);
	
	switch ($method){ 
		case "GET" : 
			curl_setopt($ch, CURLOPT_HTTPGET, true);
		break; 
		case "POST": 
			curl_setopt($ch, CURLOPT_POST,true); 
		break; 
		case "PUT" : 
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT"); 
		break; 
		case "DELETE":
			curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); 
		break; 
	}
	
	curl_setopt($ch, CURLOPT_USERAGENT, 'SSTS Browser/1.0');
	curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	
	if (isset($body{3}) > 0) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	}
	if (count($header) > 0) {
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	}

	$ret = curl_exec($ch);
	$err = curl_error($ch);

	curl_close($ch);

	if ($err) {
		return $err;
	}

	return $ret;
}
