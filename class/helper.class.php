<?php
/**
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */

class H {
/**
 * 可逆加密解密的公钥，不能出现重复字符，内有A-Z,a-z,0-9,/,=,+,_,-
 */
public static $lockstream = 'st=lDEFABCkVWXYZabc89LMmGH012345uvdefIJK6NOPyzghijQRSTUwx7nopqr';

//==========================文本处理方法==========================

/**
 * 中文字符截取
 *
 * @param  $str 要截取字符串
 * @param  $start 开始位置
 * @param  $length 长度
 */
public static function utf8_substr($str, $start, $length) {
    if (function_exists('mb_substr')) {
        return mb_substr($str, $start, $length, 'UTF-8');
    }
    preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $str, $arr);
    return implode("", array_slice($arr[0], $start, $length));
}
/**
 *  解析 文件路径 返回其组成部分
 *
 * @param  $str 要解析的路径 例如：/a/b/test.php
 * @param  $type 返回类型 all=返回全部 oname=返回文件名 ext=返回文件后缀 path=返回不包含文件名的文件路径
 */
public static function parse_path($str,$type='all') {
    $start = strripos($str,'/');
    if($start !== false) $start += 1;
    $end = strripos($str,'.');
    if($end <= 0) return false;
    $oname = substr($str,$start,$end - $start);
    $ext = substr($str,$end+1);
    $path = substr($str,0,$start);
    if($type == 'oname') return $oname;
    if($type == 'ext') return $ext;
    if($type == 'path') return $path;
    return array('oname'=>$oname,'ext'=>$ext,'path'=>$path);
}
/**
 * 根据.号分段提取字符中数字，并转换为数字，支持4段格式 2.1.1.104
 *
 * @param  $str 字符串
 */
public static function get_str_num($str) {
    $a = explode('.', $str);
    $i = 10000;
    $r = 0;
    foreach($a as $b) {
        if (is_numeric($b)) {
            $r += $i * $b;
        } else {
            if (strpos($b, '-') > 0) {
                $b = substr($b, 0, strpos($b, '-'));
            }
            preg_match_all('~(\d+)~', $b, $c);
            $e = '';
            foreach($c[1] as $d) {
                $e .= $d;
            }
            if ($e == '') $e = 0;
            $r += $i * $e;
        }
        $i = $i / 10;
        if ($i == 1) break;
    }
    return $r;
}
/**
 * 字符串防SQL注入编码，对GET,POST,COOKIE的数据进行预处理
 *
 * @param  $input 要处理字符串或者数组
 * @param  $urlencode 是否要URL编码
 */
public static function escape($input, $urldecode = 0) {
    if(is_array($input)){
        foreach($input as $k=>$v){
            $input[$k]=H::escape($v,$urldecode);
        }
    }else{
        $input=trim($input);
        if ($urldecode == 1) {
            $input=str_replace(array('+'),array('{addplus}'),$input);
            $input = urldecode($input);
            $input=str_replace(array('{addplus}'),array('+'),$input);
        }
        // PHP版本大于5.4.0，直接转义字符
        if (strnatcasecmp(PHP_VERSION, '5.4.0') >= 0) {
            $input = addslashes($input);
        } else {
            // 魔法转义没开启，自动加反斜杠
            if (!get_magic_quotes_gpc()) {
                $input = addslashes($input);
            }
        }
    }
    //防止最后一个反斜杠引起SQL错误如 'abc\'
    if(substr($input,-1,1)=='\\') $input=$input."'";//$input=substr($input,0,strlen($input)-1);
    return $input;
}
/**
 * 过滤HTML为纯TXT，并且可截取长度
 * @param $input 字符串或者数组
 * @param $len 截取长度，0为不截取
 * @param $filter 数组不过滤的元素名数组
 * 
 */
public static function filter_txt($input,$len=0,$filter=array()){
    if(is_array($input)){
        foreach($input as $k=>$v){
            if(!in_array($k,$filter)) {
                $input[$k] = H::filter_txt($v, $len,$filter);
            }
        }
    }else{
        $input=H::escape($input,1);
        $input=H::sqlxss_decode($input);//解码成HTML
        $input=strip_tags($input);
        $input=str_replace(array('　',' ','"'),'',$input);
        if($len>0) $input=H::utf8_substr($input,0,$len);
        $input=trim($input);
    }
    return $input;
}
/**
 * 过滤HTML为纯TXT，并且可截取长度
 * @param $input 字符串
 * @param $len 截取长度，0为不截取
 */
public static function filter_desc($str,$len=30) {
    $str = stripcslashes(H::sqlxss_decode($str));
    $str = strip_tags($str);
    $str = str_replace(array(' ','　',chr(10),chr(13),',','&nbsp;','&amp;','&ldquo;','&rdquo;'),array('','','','','，','','','“','”'),$str);
    $str = H::utf8_substr($str,0,$len);
    return H::sqlxss($str);
}
/**
 * 处理XSS，$input=$_COOKIE,$_GET,$_POST
 */
public static function sqlxss($input){
    if(is_array($input)){
        foreach($input as $k=>$v){
            $k=H::sqlxss($k);
            $input[$k]=H::sqlxss($v);
        }
    }else{
        $input=H::escape($input,1);
        $input=htmlspecialchars($input,ENT_QUOTES);
    }
    return $input;
}
public static function sqlxss_decode($input){
    if(is_array($input)){
        foreach($input as $k=>$v){
            $input[$k]=H::sqlxss_decode($v);
        }
    }else{
        //$input=H::escape($input,1);
        $input=htmlspecialchars_decode($input,ENT_QUOTES);
    }
    return $input;
}
/**
 * 字符串去反斜杠处理，模板编辑源码时候需要使用
 *
 * @param  $str 反斜杠
 */
public static function escape_stripslashes($str) {
    $str=trim($str);
    // PHP版本大于5.4.0，直接转义字符
    if (strnatcasecmp(PHP_VERSION, '5.4.0') < 0) {
        // 魔法转义没开启，自动加反斜杠
        if (get_magic_quotes_gpc()) {
            $str = stripslashes($str);
        }
    }
    return $str;
}
/**
 * 补位函数
 * @param $str:原字符串
 * @param $len：新字符串长度
 * @param $msg：填补字符
 * @param $type：类型，0为后补，1为前补
 */
public static function numfix($str,$len,$msg,$type='1') {
  $length = $len - strlen($str);
  if($length<1)return $str;
  if ($type == 1) {
    $str = str_repeat($msg,$length).$str;
  } else {
    $str .= str_repeat($msg,$length);
  }
  return $str;
}
/**
 * 过滤HTML内容，只保留br,p,a,img标签
 * @param $con 要过滤的内容
 */
public static function filter_html($con){
    $con=  htmlspecialchars_decode($con);
    $con=  strip_tags($con, '<br><p><a><img>');
    return $con;
}
/**
 * app 输出预处理函数
 */
public static function filter_html_app($con) {
    $tags = '<br><p><a><img>'; //保留标签
    $con = strip_tags($con,$tags); //去除不被保留的标签
    //处理P标签
    $con=preg_replace('~<p[^>]*>~','<p>',$con);
    //处理BR标签
    $con=preg_replace('~<br[^>]*>~','<br>',$con);
    //处理IMG标签
    $con=preg_replace('~<img([^>]*)( src=[^>]* )([^>]*)>~','<img${2}>',$con);
    //处理A标签属性
    $con=preg_replace('~<a([^>]*)( href=[^>]* )([^>]*)>~','<a${2}>',$con);
    $con=preg_replace('~<a([^>]*/app/quan/view.php\?id=)(\d+)([^>]*)>~','<a ourl="quan-info.html" oid="${2}" class="quan_index" title="圈子">',$con);
    $con=preg_replace('~<a([^>]*/app/quan/quan.post.php\?id=)(\d+)([^>]*)>~','<a ourl="quan-post.html" oid="${2}" class="quan_post" title="话题">',$con);
    $con=preg_replace('~<a([^>]*/app/user/index.php\?id=)(\d+)([^>]*)>~','<a ourl="page-index.html" oid="${2}" class="user_index" title="微商团队">',$con);
    $con=preg_replace('~<a([^>]*/app/user/view.php\?id=)(\d+)([^>]*)>~','<a ourl="page-info.html" oid="${2}" class="user_view" title="微商文章">',$con);

    return $con;
}
/**
 * app 输出预处理函数
 */
public static function filter_html_wap($con) {
    $tags = '<br><p><a><img>'; //保留标签
    $con = strip_tags($con,$tags); //去除不被保留的标签
    //处理P标签
    $con=preg_replace('~<p[^>]*>~','<p>',$con);
    //处理BR标签
    $con=preg_replace('~<br[^>]*>~','<br>',$con);
    //处理IMG标签
    $con=preg_replace('~<img([^>]*)( src=[^>]* )([^>]*)>~','<img${2}>',$con);
    //处理A标签属性
    $con=preg_replace('~<a([^>]*)( href=[^>]* )([^>]*)>~','<a${2}>',$con);
    
    return $con;
}
/**
 * 返回图片缩略图地址
 * @param url 缩略图、预览文件地址地址
 * @param type 返回地址类型，默认为 preview
 * @param $prefix 文件名前缀
 */
public static function preview_url($url,$type='preview',$prefix='') {
	if($url == '') return 'noimg';
	if(strstr($url,'http://css')) return $url;
    switch($type){
        case 'same_dir':
            $pos = strrpos($url, '/');
            $url = substr($url, 0, $pos + 1) . 'thumb_' . substr($url, $pos + 1, strlen($url) - $pos);
        default:
            $host=parse_url($url, PHP_URL_HOST);

            if($host==''){

            }else{
                $url=substr($url,7);//去掉HTTP头
                $url_arr=explode('/',$url);//分割数组
                
                	array_splice($url_arr,2,0,$type);//合并插入$type=preview目录
				
                //加入文件名前缀
                if($prefix!='') {
                    $file = $url_arr[count($url_arr) - 1];
                }
                $url='http://'.implode('/',$url_arr);//die($url);
            }
    }
    //die($url);
    return $url;
}
/**
 * 替换URL中GET参数
 * @url 缩略图地址
 * @params 要替换的参数列表 array('id'=>123,'type'=>1)
 */
public static function url_params($url,$params=array(),$params_filter=array('p')) {
    $a=parse_url($url);
    $a['query']=isset($a['query'])?$a['query']:'';
    $b=explode('&',$a['query']);//得到参数
    $c=array();
    foreach($b as $k=>$v){//拆解重组参数
        $tmp=explode('=',$v);
        if(count($tmp)>1){
            $c[$tmp[0]]=$tmp[1];
        }else{
            if($tmp[0]!='') $c[$tmp[0]]='';
        }
    }

    foreach($params as $k=>$v){//替换参数
        $c[$k]=$v;
    }
    //回拼字符串
    $d=array();
    foreach($c as $k=>$v){
        if(in_array($k,$params_filter)) continue;
        array_push($d,$k.'='.$v);
    }
    $query=implode('&',$d);
    $url=(isset($a['path'])?$a['path']:'').($query==''?'':'?'.$query);
    return $url;
}
//==========================数组处理方法==========================

/**
 * JSON_ENCODE中文不编码，显示纯中文
 */
public static function json_encode_ch($a = false) {
    if (is_null($a)) return 'null';
    if ($a === false) return 'false';
    if ($a === true) return 'true';
    if (is_scalar($a)) {
        if (is_float($a)) {
            // Always use "." for floats.
            return floatval(str_replace(",", ".", strval($a)));
        }

        if (is_string($a)) {
            static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '\\b', '\\f', '\"'));
            return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
        } else {
            return $a;
        }
    }

    $isList = true;
    for ($i = 0, reset($a); $i < count($a); $i++, next($a)) {
        if (key($a) !== $i) {
            $isList = false;
            break;
        }
    }

    $result = array();
    if ($isList) {
        foreach ($a as $v) $result[] = H::json_encode_ch($v);
        return '[' . join(',', $result) . ']';
    } else {
        foreach ($a as $k => $v) $result[] = H::json_encode_ch($k).':'.H::json_encode_ch($v);
        return '{' . join(',', $result) . '}';
    }
}
/**
 * 二维数组排序
 *
 * @param  $arr 数组
 * @param  $keys 排序字段
 * @param  $type 升序降序
 */
public static function array_sort($arr, $keys, $type = 'asc') {
    $keysvalue = $new_array = array();
    foreach ($arr as $k => $v) {
        $keysvalue[$k] = $v[$keys];
    }
    if ($type == 'asc') {
        asort($keysvalue);
    } else {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k => $v) {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}
/**
 * 将二维数组的某个字符串维度转换为数字
 *
 * @param  $arr 要转换的数组
 * @param  $col_name 要转换的列名
 */
public static function ver_sort($arr, $col_name) {
    for($i = 0;$i < count($arr);$i++) {
        $arr[$i]['new_order_set'] = H :: get_str_num($arr[$i][$col_name]);
    }
    // 排序
    $arr_tmp = H :: array_sort($arr, 'new_order_set', 'desc');

    $ret = array();
    foreach($arr_tmp as $k => $v) {
        unset($v['new_order_set']);
        array_push($ret, $v);
    }
    return $ret;
}
/**
 * 数组变成字符串
 * @param $array 数组
 * @level 深度
 */
public static function array_eval($array, $level = 0) {
    $space = '';
    $str_t = '\t';
    $str_t = '';
    $str_n = '\n';
    $str_n = '';
    for($i = 0; $i <= $level; $i++) {
        $space .= $str_t;
    }
    $evaluate = "array" . $str_n . "$space(" . $str_n;
    $comma = $space;
    foreach($array as $key => $val) {
        $key = is_string($key) ? '\'' . addcslashes($key, '\'\\') . '\'' : $key;
        //$val = !is_array($val) && (!preg_match("/^\-?\d+$/", $val) || strlen($val) > 12) ? '\'' . addcslashes($val, '\'\\') . '\'' : $val;
        if (is_array($val)) {
            $evaluate .= "$comma$key=>" . H::array_eval($val, $level + 1);
        } else {
            $val='\'' . addcslashes($val, '\'\\') . '\'';
            $evaluate .= "$comma$key=>$val";
        }
        $comma = "," . $str_n . "$space";
    }
    $evaluate .= $str_n . "$space)";
    return $evaluate;
}


//==========================HTTP处理方法==========================

/**
 * 三次重试，获取指定url的内容
 *
 * @param  $url URL地址或者本地文件物理地址
 * @param  $charset 文件编码
 */
public static function get_contents($url, $charset = 'UTF-8') {
    $retry = 3;
    $content = '';
    while (empty($content) && $retry > 0) {
        $content = @file_get_contents($url);
        $retry--;
    }
    if (strtoupper($charset) != 'UTF-8') $content = iconv($charset . "//IGNORE", "UTF-8", $content); //die($contents);
    return $content;
}
/**
 * curl POST
 *
 * @param   string  url
 * @param   array   数据
 * @param   int     请求超时时间
 * @param   bool    HTTPS时是否进行严格认证
 * @return  string
 */
public static function curl_post($url, $data = array(), $timeout = 30, $CA = false){
    if($url=='') return '';
    $cacert = getcwd() . '/cacert.pem'; //CA根证书
    $SSL = substr($url, 0, 8) == "https://" ? true : false;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout-2);
    if ($SSL && $CA) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   // 只信任CA颁布的证书
        curl_setopt($ch, CURLOPT_CAINFO, $cacert); // CA根证书（用来验证的网站证书是否是CA颁布）
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名，并且是否与提供的主机名匹配
    } else if ($SSL && !$CA) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1); // 检查证书中是否设置域名
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:')); //避免data数据过长问题
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    //curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); //data with URLEncode

    $ret = curl_exec($ch);
    $error=curl_error($ch);  //查看报错信息
    if(!empty($error)) print_r($error);
    curl_close($ch);
    return $ret;
}

/**
 * 写入cookie
 *
 * @param  $var 键
 * @param  $value 值
 * @param  $time 过期时间 单位秒
 */
public static function set_cookie($var,$value='',$time=0,$path='/',$domain=''){
    $_COOKIE[$var] = $value;
    if(is_array($value)){
        foreach($value as $k=>$v){
            if(is_array($v)){
                foreach($v as $a=>$b){
                    setcookie($var.'['.$k.']['.$a.']',$b,$time,$path,$domain);
                }
            }else{
                setcookie($var.'['.$k.']',$v,$time,$path,$domain);
            }
        }
    }else{
        setcookie($var,$value,$time,$path,$domain);
    }
}

/**
 * 文件存储随机目录，避免一个目录文件过多
 *
 * @param  $type 目录类型 0=默认
 */
public static function rnd_save_path($type=''){
    $ret='';
    $str='0123456789abcdefghijklmnopqrstuvwxyz';
    switch($type){
        case 1:
            $ret=date('Y').'/'.date('m').'/'.date('d').'/';
            break;
        case 2:
            $ret=date('Ym').'/'.date('d').'/';
            break;
        default:
            $ret=date('Ym').'/'.date('d').'/';
//          $rnd_dir1=substr($str,rand(0,35),1).substr($str,rand(0,35),1);
//          $rnd_dir2=substr($str,rand(0,35),1).substr($str,rand(0,35),1);
//          $rnd_dir3=substr($str,rand(0,35),1).substr($str,rand(0,35),1);
//          $ret=$rnd_dir1.'/'.$rnd_dir2.'/'.$rnd_dir3.'/';
    }
    return $ret;
}

//==========================缓存处理==========================

/**
 * Memcached 缓存
 *
 * @param  $params->key 缓存名称
 * @param  $params->val 缓存内容
 * @param  $params->time 缓存时间，默认3600秒=1小时
 * @param  $params->path 缓存目录，默认为/cache
 * @param  $params->server->host:port 缓存服务器，默认为/127.0.0.1:11211
 */
public static function cache($params){
    global $global_global;
    $params['server']=isset($params['server'])?$params['server']:$global_global['mem_server'];
    $file_cache=0;

    //测试memcache连接方式
    if (class_exists('Memcache')) {
        $mem = new Memcache;
        $tmps=explode(':',$params['server'][0]);
        if(@!$mem->connect($tmps[0], $tmps[1])) $file_cache=1;
    }
    if(isset($params['cache_type'])){
        //指定缓存方式
        if($params['cache_type']=='file_cache') return H::file_cache($params);
        if($params['cache_type']=='memcache') return H::memcache($params);
        if($params['cache_type']=='memcached') return H::memcached($params);
        if($params['cache_type']=='database_cache') return H::database_cache($params);
        if($params['cache_type']=='redis') return H::redis_cache($params);
    }else{
        //如果memcached服务器连接不上，则用文件缓存
        if($file_cache==1){
            return H::file_cache($params);
        }else{
            return H::memcached($params);
        }
    }
}
public static function redis_cache($params=array()) {
    global $global_global;
    $cache_name=isset($params['key'])?$params['key']:'tmp';
    $cache_time=isset($params['time'])?$params['time']:3600;
    $cache_path=isset($params['path'])?$params['path']:'cache';
    $cache_server=isset($params['server'])?$params['server']:$global_global['mem_server'];

    
    $redis = new Redis();//print_r($cache_server);
    
    foreach($cache_server as $server){
        $tmps=explode(':',$server);
        $redis->connect($tmps[0],$tmps[1]);
        break;//只取第一组服务器
    }

    // 如果没传入内容，则读取缓存
    if (!isset($params['val'])) {
        
        
        $content=$redis->get($cache_name);
        //echo('read:');var_dump($content);
        
    } else {
        
        $content=serialize($params['val']);
        //echo('write:');var_dump($content);
        $redis->setex($cache_name,$cache_time,$content);
    }
    unset($redis);
    //echo('over:');var_dump($content);
    if($content){
        return unserialize($content);
    }else{
        return 'timeout';
    }
}
/**
 * Memcached 缓存
 *
 * @param  $params->key 缓存名称
 * @param  $params->val 缓存内容
 * @param  $params->time 缓存时间，默认3600秒=1小时
 * @param  $params->server->host:port 缓存服务器，默认为/127.0.0.1:11211
 */
public static function memcached($params=array()) {
    global $global_global;
    $cache_name=isset($params['key'])?$params['key']:'tmp';
    $cache_time=isset($params['time'])?$params['time']:3600;
    $cache_path=isset($params['path'])?$params['path']:'cache';
    $cache_server=isset($params['server'])?$params['server']:$global_global['mem_server'];

    if (!class_exists('Memcached')) {
        return H::memcache($params);
    }
    $mem = new Memcached;
    foreach($cache_server as $server){
        $tmps=explode(':',$server);
        $mem->addServer($tmps[0], $tmps[1]);
    }

    // 如果没传入内容，则读取缓存
    if (!isset($params['val'])) {
        $content=$mem->get($cache_name);
    } else {
        // echo('写缓存');
        $mem->set($cache_name,$params['val'],intval($cache_time));
        $content=$params['val'];
    }
    unset($mem);
    if($content){
        return $content;
    }else{
        return 'timeout';
    }
    return $content;
}
/**
 * Memcache 缓存
 *
 * @param  $params->key 缓存名称
 * @param  $params->val 缓存内容
 * @param  $params->time 缓存时间，默认3600秒=1小时
 * @param  $params->server->host:port 缓存服务器，默认为/127.0.0.1:11211
 */
public static function memcache($params=array()) {
    global $global_global;
    $cache_name=isset($params['key'])?$params['key']:'tmp';
    $cache_time=isset($params['time'])?$params['time']:3600;
    $cache_path=isset($params['path'])?$params['path']:'cache';
    $cache_server=isset($params['server'])?$params['server']:$global_global['mem_server'];

    if (!class_exists('Memcache')) {
        return H::file_cache($params);
    }
    $mem = new Memcache;
    foreach($cache_server as $server){
        $tmps=explode(':',$server);
        $mem->addServer($tmps[0], $tmps[1]);
    }

    // 如果没传入内容，则读取缓存

    if (!isset($params['val'])) {
        $content=$mem->get($cache_name);
    } else {
        // echo('写缓存');
        $mem->set($cache_name,$params['val'],0,intval($cache_time));
        $content=$params['val'];
    }
    unset($mem);
    if($content){
        return $content;
    }else{
        return 'timeout';
    }
    return $content;
}
/**
 * 一般文件缓存
 *
 * @param  $params->key 缓存名称
 * @param  $params->val 缓存内容
 * @param  $params->time 缓存时间，默认3600秒=1小时
 * @param  $params->path 缓存目录，默认为/cache
 */
public static function file_cache($params=array()) {
    $cache_name=isset($params['key'])?$params['key']:'tmp';
    $cache_time=isset($params['time'])?$params['time']:3600;
    $cache_path=isset($params['path'])?$params['path']:'cache';

    // 创建缓存目录，以网站根目录为起始位置
    $cache_path = dirname(__FILE__).'/../config/' . $cache_path;
    $file_path = $cache_path . '/' . $cache_name .'.php';
    H :: mkdirs($cache_path);
    // 如果没传入内容，则读取缓存
    if (!isset($params['val'])) {
        // echo('读缓存');
        $time_expire=false;
        if($cache_time>0 && file_exists($file_path) && time() - filemtime($file_path) < $cache_time) $time_expire=true;
        if($cache_time==0) $time_expire=true;
        if (file_exists($file_path) && $time_expire) {
            $file_contents = file_get_contents($file_path);
            $file_contents=require_once($file_contents);
            H::logs('cache_read',json_encode($params));
            if(is_array($file_contents)) extract($file_contents);
            return $file_contents;
        } else {
            return 'timeout';
        }
        // 否则是强制写缓存
    } else {
        //H::logs('cache_write',json_encode($params));
        $content=$params['val'];
        $fp = @fopen($file_path, 'w');
        $content = "<?php \nreturn ".var_export($content,true).';';
        @fwrite($fp, $content);
        @fclose($fp);
        return $params['val'];
    }
}
/**
 * 一般文件缓存
 *
 * @param  $params->key 缓存名称
 * @param  $params->val 缓存内容
 * @param  $params->time 缓存时间，默认3600秒=1小时
 * @param  $params->path 缓存目录，默认为/cache
 */
public static function database_cache($params=array()) {
    $cache_name=isset($params['key'])?$params['key']:'tmp';
    $cache_time=isset($params['time'])?$params['time']:3600;
    $cache_path=isset($params['path'])?$params['path']:'cache';
    
    global $dbm;
    
    // 如果没传入内容，则读取缓存
    if (!isset($params['val'])) {
        // echo('读缓存');
        $sql="select * from cache_table where cache_name='$cache_name' limit 1";
        $rs=$dbm->query($sql);
        if(count($rs['list'])==1){
            if(time() - $rs['list'][0]['cache_time'] < $cache_time){
                //print_r(unserialize($rs['list'][0]['cache_val']));
                return unserialize($rs['list'][0]['cache_val']);
            }else{
                return 'timeout';
            }
        }else{
            return 'timeout';
        }
        // 否则是强制写缓存
    } else {
        // echo('写缓存');
        $fields=array();
        $fields['cache_name']=$cache_name;
        $fields['cache_val']=serialize($params['val']);
        $fields['cache_time']=time();
        $rs=$dbm->single_insert('cache_table', $fields,1);print_r($rs);
        return $params['val'];
    }
    
}

//==========================IP操作==========================

/**
 * 获取客户端IP地址
 */
public static function getip() {
    $onlineip = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $onlineip = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $onlineip = getenv('REMOTE_ADDR');
    } elseif (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $onlineip = $_SERVER['REMOTE_ADDR'];
    }
    if(!@ereg("^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$",$onlineip)) {
        return "";
    }else{
        return addslashes(htmlspecialchars($onlineip));
    }
}
/**
 * 转换IP为真实地址
 *
 * @param  $ip IP地址
 */
public static function convertip($ip) {
    global $default_charset;
    $return = '';
    if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
        $iparray = explode('.', $ip);
        if ($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
            $return = '- LAN';
        } elseif ($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
            $return = '- Invalid IP Address';
        } else {
            $tinyipfile = './../data/ipdata/tinyipdata.dat';
            $fullipfile = './../data/ipdata/wry.dat';
            if (@file_exists($tinyipfile)) { // die($tinyipfile);
                $return = $this -> convertip_tiny($ip, $tinyipfile);
            } elseif (@file_exists($fullipfile)) { // die($fullipfile);
                $return = $this -> convertip_full($ip, $fullipfile);
            } else {
                $return = '';
            }
        }
    }
    return iconv($default_charset . "//IGNORE", "UTF-8", $return);
}
/**
 * 转换IP库1,私有方法
 */
private static function convertip_tiny($ip, $ipdatafile) {
    static $fp = null, $offset = array(), $index = null;
    $ipdot = explode('.', $ip);
    $ip = pack('N', ip2long($ip));

    $ipdot[0] = (int)$ipdot[0];
    $ipdot[1] = (int)$ipdot[1];

    if ($fp === null && $fp = @fopen($ipdatafile, 'rb')) {
        $offset = @unpack('Nlen', @fread($fp, 4));
        $index = @fread($fp, $offset['len'] - 4);
    } elseif ($fp == false) {
        return '- Invalid IP data file';
    }

    $length = $offset['len'] - 1028;
    $start = @unpack('Vlen', $index[$ipdot[0] * 4] . $index[$ipdot[0] * 4 + 1] . $index[$ipdot[0] * 4 + 2] . $index[$ipdot[0] * 4 + 3]);

    for ($start = $start['len'] * 8 + 1024; $start < $length; $start += 8) {
        if ($index{$start} . $index{$start + 1} . $index{$start + 2} . $index{$start + 3} >= $ip) {
            $index_offset = @unpack('Vlen', $index{$start + 4} . $index{$start + 5} . $index{$start + 6} . "\x0");
            $index_length = @unpack('Clen', $index{$start + 7});
            break;
        }
    }

    @fseek($fp, $offset['len'] + $index_offset['len'] - 1024);
    if ($index_length['len']) {
        return '- ' . @fread($fp, $index_length['len']);
    } else {
        return '- Unknown';
    }
}
/**
 * 隐藏IP地址最后一位
 *
 * @param  $ip IP地址
 */
public static function ip_hide($ip) {
    $t = strrpos($ip, ".");
    $sr = substr($ip, 0, $t);
    return $sr . ".*";
}
/**
 * 转换IP库2，私有方法
 */
private static function convertip_full($ip, $ipdatafile) {
    if (!$fd = @fopen($ipdatafile, 'rb')) {
        return '- Invalid IP data file';
    }
    $ip = explode('.', $ip);
    $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];

    if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4))) return;
    @$ipbegin = implode('', unpack('L', $DataBegin));
    if ($ipbegin < 0) $ipbegin += pow(2, 32);
    @$ipend = implode('', unpack('L', $DataEnd));
    if ($ipend < 0) $ipend += pow(2, 32);
    $ipAllNum = ($ipend - $ipbegin) / 7 + 1;

    $BeginNum = $ip2num = $ip1num = 0;
    $ipAddr1 = $ipAddr2 = '';
    $EndNum = $ipAllNum;

    while ($ip1num > $ipNum || $ip2num < $ipNum) {
        $Middle = intval(($EndNum + $BeginNum) / 2);

        fseek($fd, $ipbegin + 7 * $Middle);
        $ipData1 = fread($fd, 4);
        if (strlen($ipData1) < 4) {
            fclose($fd);
            return '- System Error';
        }
        $ip1num = implode('', unpack('L', $ipData1));
        if ($ip1num < 0) $ip1num += pow(2, 32);

        if ($ip1num > $ipNum) {
            $EndNum = $Middle;
            continue;
        }

        $DataSeek = fread($fd, 3);
        if (strlen($DataSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
        fseek($fd, $DataSeek);
        $ipData2 = fread($fd, 4);
        if (strlen($ipData2) < 4) {
            fclose($fd);
            return '- System Error';
        }
        $ip2num = implode('', unpack('L', $ipData2));
        if ($ip2num < 0) $ip2num += pow(2, 32);

        if ($ip2num < $ipNum) {
            if ($Middle == $BeginNum) {
                fclose($fd);
                return '- Unknown';
            }
            $BeginNum = $Middle;
        }
    }

    $ipFlag = fread($fd, 1);
    if ($ipFlag == chr(1)) {
        $ipSeek = fread($fd, 3);
        if (strlen($ipSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
        fseek($fd, $ipSeek);
        $ipFlag = fread($fd, 1);
    }

    if ($ipFlag == chr(2)) {
        $AddrSeek = fread($fd, 3);
        if (strlen($AddrSeek) < 3) {
            fclose($fd);
            return '- System Error';
        }
        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        } while (($char = fread($fd, 1)) != chr(0))
        $ipAddr2 .= $char;

        $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
        fseek($fd, $AddrSeek);

        while (($char = fread($fd, 1)) != chr(0))
        $ipAddr1 .= $char;
    } else {
        fseek($fd, -1, SEEK_CUR);
        while (($char = fread($fd, 1)) != chr(0))
        $ipAddr1 .= $char;

        $ipFlag = fread($fd, 1);
        if ($ipFlag == chr(2)) {
            $AddrSeek2 = fread($fd, 3);
            if (strlen($AddrSeek2) < 3) {
                fclose($fd);
                return '- System Error';
            }
            $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
            fseek($fd, $AddrSeek2);
        } else {
            fseek($fd, -1, SEEK_CUR);
        } while (($char = fread($fd, 1)) != chr(0))
        $ipAddr2 .= $char;
    }
    fclose($fd);

    if (preg_match('/http/i', $ipAddr2)) {
        $ipAddr2 = '';
    }
    $ipaddr = "$ipAddr1 $ipAddr2";
    $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
    $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
    $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
    if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
        $ipaddr = '- Unknown';
    }
    return '- ' . $ipaddr;
}


//==========================时间处理==========================

/**
 * 返回时间，单位是毫秒 ms
 */
public static function getmicrotime() {
    list($usec, $sec) = explode(" ", microtime());
    $tim = ((float)$usec + (float)$sec) * 1000;
    return $tim;
}
/**
 * 根据秒数返回格式 x天x时x分x秒
 * @param $time 秒数
 */
public static function get_time_str($time){
        $d = 86400; //天
        $h = 3600; //时
        $s = 60; //分
        if($time<=60){
                return $time.'秒';
        }elseif($s<$time && $time<$h){
                $a = intval($time/$s); //分
                $b = $time - $a*$s; //秒
                return $a.'分'.$b.'秒';
        }elseif($h<=$time && $time<$d){
                $a = intval($time/$h); //时
                $b = $time - $a*$h;
                if($b<60){
                        return $a.'时'.$b.'秒';
                }else{
                        $c = intval($b/$s); //分
                        $d = $b - $c*$s; //秒
                        return $a.'时'.$c.'分'.$d.'秒';
                }
        }elseif($d<=$time){
                $a = intval($time/$d); //天
                $b = $time - $a*$d;
                if($b<$s){
                        return $a.'天'.$b.'秒';
                }elseif($b<$h){
                        $c = intval($b/$s); //分
                        $d = $b - $c*$s;
                        return $a.'天'.$c.'分'.$d.'秒';
                }else{
                        $c = intval($b/$h); //时
                        $d = $b - $c*$h; //剩余秒数
                        if($d<60){
                                return $a.'天'.$c.'时'.$d.'秒';
                        }else{
                                $e = intval($d/$s); //分
                                $f = $d - $e*$s;
                                return $a.'天'.$c.'时'.$e.'分'.$f.'秒';
                        }
                }
        }
}
/**
 * 根据时间戳返回距现在的秒，分钟，小时
 *
 * @param  $stamp 时间戳
 */
public static function datef($stamp,$str='Y-m-d H:i:s') {
    $time_add = time() - $stamp;
    if ($time_add < 60) return $time_add . ' 秒前';
    if ($time_add >= 60 and $time_add < 60 * 60) return intval($time_add / 60) . ' 分钟前';
    if ($time_add >= 60 * 60 and $time_add < 60 * 60 * 12) return intval($time_add / (60 * 60)) . ' 小时前';
    return date($str, $stamp);
}

//==========================加密解密==========================
/**
 * 密码加密方式1
 *
 * @param  $string 要加密字符串
 */
public static function password_encrypt($string) {
    $string = md5(md5(md5($string)));
    return $string;
}
/**
 * 密码加密方式2
 *
 * @param  $str 要加密字符串
 */
public static function password_encrypt_net($str) {
    return H :: md5_net(H :: md5_net(H :: md5_net($str)));
}
/**
 * .NET方式MD5加密用户密码
 *
 * @param  $str 要加密字符串
 */
private static function md5_net($str) {
    $md5hex = md5($str);
    $len = strlen($md5hex) / 2;
    $md5raw = "";
    for($i = 0;$i < $len;$i++) {
        $md5raw = $md5raw . chr(hexdec(substr($md5hex, $i * 2, 2)));
    }
    $keyMd5 = base64_encode($md5raw);
    return $keyMd5;
}
/**
 * 可逆加密
 *
 * @param  $txtStream 要加密的字符串
 * @param  $password 加密私钥=解密私钥
 */
public static function encrypt($txtStream, $password) {
    // 随机找一个数字，并从密锁串中找到一个密锁值
    $lockstream=defined('LOCK_STREAM')?LOCK_STREAM:(self :: $lockstream);
    $lockLen = strlen($lockstream);
    $lockCount = rand(0, $lockLen-1);
    $randomLock = $lockstream[$lockCount];
    // 结合随机密锁值生成MD5后的密码
    $password = md5($password . $randomLock);
    // 开始对字符串加密
    $txtStream = base64_encode($txtStream);
    $tmpStream = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txtStream); $i++) {
        $k = $k == strlen($password) ? 0 : $k;
        $j = (strpos($lockstream, $txtStream[$i]) + $lockCount + ord($password[$k])) % ($lockLen);
        $tmpStream .= $lockstream[$j];
        $k++;
    }
    return $tmpStream . $randomLock;
}
/**
 * 可逆解密
 *
 * @param  $txtStream 要解密的字符串
 * @param  $password 解密私钥=加密私钥
 */
public static function decrypt($txtStream, $password) {
    $lockstream=defined('LOCK_STREAM')?LOCK_STREAM:(self :: $lockstream);
    $lockLen = strlen($lockstream);
    // 获得字符串长度
    $txtLen = strlen($txtStream);
    // 截取随机密锁值
    $randomLock = $txtStream[$txtLen - 1];
    // 获得随机密码值的位置
    $lockCount = strpos($lockstream, $randomLock);
    // 结合随机密锁值生成MD5后的密码
    $password = md5($password . $randomLock);
    // 开始对字符串解密
    $txtStream = substr($txtStream, 0, $txtLen-1);
    $tmpStream = '';
    $i = 0;
    $j = 0;
    $k = 0;
    for ($i = 0; $i < strlen($txtStream); $i++) {
        $k = $k == strlen($password) ? 0 : $k;
        $j = strpos($lockstream, $txtStream[$i]) - $lockCount - ord($password[$k]);
        while ($j < 0) {
            $j = $j + ($lockLen);
        }
        $tmpStream .= $lockstream[$j];
        $k++;
    }
    return base64_decode($tmpStream);
}
/**
 * 密码加密方式3，配合security_code方法使用，先生成安全码，再根据安全码生成密码
 * @param string $password 密码
 * @param string $salt 安全码
 */
public static function password_encrypt_salt($password,$salt){
    return md5(md5($password).$salt);
}
/**
 * 生成登录安全码
 */
public static function security_code($length = 8,$type = '') {
    $source = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_+-=';
    if($type=='number') $source='0123456789';
    if($type=='numstr') $source='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $len = strlen($source);
    $return = '';
    for ($i = 0; $i < $length; $i++) {
        $index = rand() % $len;
        $return .= substr($source, $index, 1);
    }
    return $return;
}

//==========================其他杂项方法==========================
/**
 * 强制GZIP压缩文件
 */
public static function ob_gzip($content) {
    if (!headers_sent() && // 如果页面头部信息还没有输出
            extension_loaded("zlib") && // 而且zlib扩展已经加载到PHP中
            strstr($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") // 而且浏览器说它可以接受GZIP的页面
            ) {
        $content = gzencode($content, 9); //用zlib提供的gzencode()函数执行级别为9的压缩，这个参数值范围是0-9，0表示无压缩，9表示最大压缩，当然压缩程度越高越费CPU。
        // 然后用header()函数给浏览器发送一些头部信息，告诉浏览器这个页面已经用GZIP压缩过了！
        header("Content-Encoding: gzip");
        header("Vary: Accept-Encoding");
        header("Content-Length: " . strlen($content));
    }
    return $content; //返回压缩的内容，或者说把压缩好的饼干送回工作台。
}
/**
 * 根据UserAgent检查用户浏览设备
 * @return pc 默认为PC，wap 手机  wx 微信
 */
public static function user_dev() {
    $dev='pc';
    $regex_match = "/(nokia|iphone|android|motorola|^mot\-|softbank|foma|docomo|kddi|up\.browser|up\.link|";
    $regex_match .= "htc|dopod|blazer|netfront|helio|hosin|huawei|novarra|CoolPad|webos|techfaith|palmsource|meizu|miui|ucweb";
    $regex_match .= "blackberry|alcatel|amoi|ktouch|nexian|samsung|^sam\-|s[cg]h|^lge|ericsson|philips|sagem|wellcom|bunjalloo|maui|";
    $regex_match .= "symbian|smartphone|midp|wap|phone|windows ce|iemobile|^spice|^bird|^zte\-|longcos|pantech|gionee|^sie\-|portalmmm|";
    $regex_match .= "jig\s browser|hiptop|^ucweb|^benq|haier|^lct|opera\s*mobi|opera\*mini|320x320|240x320|176x220";
    $regex_match .= ")/i";

    if (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']) || (isset($_SERVER['HTTP_USER_AGENT']) && preg_match($regex_match, strtolower($_SERVER['HTTP_USER_AGENT'])))) {
        $dev='wap';
    }
    if(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger')){
        $dev='wx';
    }
    //判断是否COOKIE设置了当前浏览设备
    //setcookie('mcms_device','wap',time()-3600,'/');
    if(isset($_COOKIE['mcms_device']) && $_COOKIE['mcms_device']!='') return $_COOKIE['mcms_device'];//echo($_COOKIE['mcms_device']);
    return $dev;
}
/**
 * 获取字体文件的名称
 * @param string array $FilePath  字体文件名（含路径）
 * @return array $names 读取到字体文件名称 失败则返回 false
 * 此函数在读取字体文件的名称时需要用到
 */
public static function get_font_name($FilePath) {
    $fp=fopen($FilePath,'r');
    if($fp){
        //从二进制字符串对数据进行解包
        $meta=unpack('n6',fread($fp,12));
        //检查是否是一个true type字体文件以及版本号是否为1.0
        if($meta[1] != 1 || $meta[2] != 0){
            return false;
        }
        $found = false;
        for($i=0; $i<$meta[3]; $i++){
            //TT_TABLE_DIRECTORY
            $tablemeta=unpack('N4',$data=fread($fp, 16));
            if(substr($data,0,4)=='name'){
                $found = TRUE;
                break;
            }
        }
        if($found){
            fseek($fp, $tablemeta[3]);
            //TT_NAME_TABLE_HEADER
            $tablecount=unpack('n3',fread($fp,6));
            $found = FALSE;
            for($i=0; $i<$tablecount[2]; $i++){
                //TT_NAME_RECORD
                $table=unpack('n6',fread($fp,12));
                if($table[4] == 1){
                    $npos=ftell($fp);
                    fseek($fp,$n=$tablemeta[3] + $tablecount[3] + $table[6], SEEK_SET);
                    @$fontname=trim($x=fread($fp,$table[5]));
                    //print_r($fontname);
                    if(strlen($fontname) > 0){
                    if($table[3]==1033){
                        $code = 'utf-16le';
                    }elseif($table[3]==2052){
                        $code = 'utf-16be';
                    }elseif($table[3]==1042){
                        $code = 'utf-16';
                    }else{
                        $code = 'utf-8';
                    }
                    if($code != 'utf-8'){
                        @$fontname = iconv($code,"UTF-8//IGNORE",$fontname);
                    }
                        $names[]=array(
                                'platform'=>$table[1], //平台（操作系统）
                                'language'=>$table[3], //字体名称的语言
                                'encoding'=>$table[2], //字体名称的编码
                                'name'=>$fontname //字体名称
                        );
                    }
                    fseek($fp,$npos,SEEK_SET);
                }
            }
        }
        fclose($fp);
    }
    if(isset($names)){
        return $names;
    }else{
        return false;
    }
}
/**
 * 记录文本日志，如果根目录有 logs 目录才会记录
 *
 * @param  $logs_type 日志类型，日志文件名称
 * @param  $logs_txt 日志内容
 */
public static function logs($logs_type, $logs_txt) {
    // 创建缓存目录
    if(!is_dir(dirname(__FILE__) . '/../logs/')) return;
    try {
        $fp = fopen(dirname(__FILE__) . '/../logs/' . $logs_type . '_' . date('Y_m_d') . '.txt', 'a');
        fwrite($fp, date('Y-m-d H:i:s') . ' ' . H :: getip() . ' ' . $logs_txt . ' ' . chr(10));
        fclose($fp);
    }
    catch(Exception $e) {
        echo($e -> getMessage());
    }
}
//转码
public static function detect_encoding($string,$encoding = 'gbk'){
    $is_utf8 =  preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]| [\xC2-\xDF][\x80-\xBF]|  \xE0[\xA0-\xBF][\x80-\xBF] | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}    |  \xED[\x80-\x9F][\x80-\xBF] |  \xF0[\x90-\xBF][\x80-\xBF]{2}  | [\xF1-\xF3][\x80-\xBF]{3}  |  \xF4[\x80-\x8F][\x80-\xBF]{2} )*$%xs', $string);
    if($is_utf8 && $encoding == 'utf8'){
        return $string;
    }elseif($is_utf8){
        return mb_convert_encoding($string, $encoding, "UTF-8");
    }else{
        return mb_convert_encoding($string, $encoding, 'gbk,gb2312,big5');
    }
}
/**
 * 重定向出错页面
 * @param $data 出错信息JSON对象 {"code":1,"msg":"没有权限","tpl":"/app_tpl_pc/default/error/tpl.show.php"}
 * return 无返回值
 */
public static function error_show($data){
    global $global_global,$C,$V,$U,$T,$Q,$N,$P,$dbm;
    $data_str=$data;
    if(AJAX==1) die($data);
    $data=json_decode($data,1);
    
    if(isset($data['ajax']) && $data['ajax']==1) {
        if(isset($dbm)) unset($dbm);
        die($data_str);
    }
    
    require_once(ROOT_PATH_SITE . '/app/error/show.php');
    if(isset($dbm)) unset($dbm);
    die();
}
/**
 * 根据百度搜索结果自动提取关键词
 * @param $title 要提取关键词的标题
 * @param $filter_words1 精准过滤词数组 array('过滤词1','过滤词2')
 * @param $filetr_words2 模糊过滤词数组 array('过滤词1','过滤词2')
 */
public static function get_tags_baidu($title,$filter_words1=array(),$filter_words2=array()) {
    if (strlen($title) <= 4) die('{"code":1,"msg":"","data":["' . $title . '"]}');
    $ret = H :: get_contents('http://www.baidu.com/s?wd=' . urlencode($title));
    preg_match_all('~<em>(.*?)</em>~', $ret, $keys);
    //过滤字符
    for($i=0;$i<count($keys[1]);$i++){
        $keys[1][$i] = preg_replace('~"|\'|“|”|【|】|\(|\)|（|）|:|：|\-|—~','',$keys[1][$i]);
    }
    //去重并过滤
    $nkeys = array();
    foreach($keys[1] as $a) {
        //去重
        $is_key = 0;
        for($b = 0;$b < count($nkeys);$b++) {
            if ($a == $nkeys[$b]['k']) {
                $is_key = 1;
                $nkeys[$b]['t'] = 1 + $nkeys[$b]['t'];
                break;
            }
        }
        //过滤
        $is_k1=0;
        foreach($filter_words1 as $b){
            if($b==$a) {$is_k1=1;break;}
        }
        $is_k2=0;
        foreach($filter_words2 as $b){
            if(strstr($a,$b)) {$is_k2=1;break;}
        }
        if ($is_key == 0 && $is_k1==0 && $is_k2==0) array_push($nkeys, array('k' => $a, 't' => 1,'l'=>strlen($a)));
    }
    //过滤字符长度
    $tags = array();
    for($i = 0;$i < count($nkeys);$i++) {
        if (strlen($nkeys[$i]['k']) >= 9 && strlen($nkeys[$i]['k']) <= 18) array_push($tags, $nkeys[$i]);
    }
    //排序
    $tags = H :: array_sort($tags, 'l');//print_r($info_tags);
    $ntags=array();
    //重做数组
    foreach($tags as $a){
        array_push($ntags,$a);
    }

    return $ntags;
}

//==========================百度地图和GPS地理位置==========================

/**
 * 根据地址获取百度坐标
 * @param $addr 物理地址
 */
public static function get_geo($addr){
    $ret=H::get_contents('http://api.map.baidu.com/geocoder/v2/?address='.$addr.'&output=json&ak=9f2feaa6d4a8a3eaae63d3b6d212fd13&callback=');
    $json= json_decode($ret,1);
    $arr=array('status'=>'1');
    if($json['status']=='0'){
        $arr['status']=$json['status'];
        if(isset($json['result']['location'])){
            $arr['longitude']=$json['result']['location']['lng'];
            $arr['latitude']=$json['result']['location']['lat'];
        }else{
            $arr['status']='2';
        }
    }
    return $arr;
}

/**
 * 根据百度坐标返回真实地址
 * @param $lat 纬度
 * @param $lng 经度
 */
public static function get_address($lat,$lng){
    $ret=H::get_contents("http://api.map.baidu.com/geocoder/v2/?location=$lat,$lng&output=json&ak=9f2feaa6d4a8a3eaae63d3b6d212fd13&callback=");
    $json= json_decode($ret,1);
    $addr='';
    if($json['status']=='0'){
        $addr=$json['result']['formatted_address'];
    }
    return $addr;
}
/**
 * 显示多点百度坐标
 */
public static function show_more_point_map_baidu($div_id,$arr = array(array('addr'=>'','default'=>'','content'=>'')),$map_size = 17) {
	if($arr[0]['addr'] == '' && $arr[0]['default'] == '' && $arr[0][''] == 'content') return;
	$map_size = abs(intval($map_size));
	if($map_size==0) $map_size = 17;
	if($arr[0]['default']!='') {
		$point=$arr[0]['default'];
	}else{
		$addr = get_geo($arr[0]['addr']);
		$point = $addr['longitude'].','.$addr['latitude'];
	}
	$script='';
	$script.='<script type="text/javascript" src="http://api.map.baidu.com/api?v=2.0&ak=9f2feaa6d4a8a3eaae63d3b6d212fd13"></script>';
	$script.='<script type="text/javascript">
	var map = new BMap.Map("'.$div_id.'");            // 创建Map实例
	var point = new BMap.Point('.$point.');
	//map.addControl(new BMap.NavigationControl());  //添加默认缩放平移控件
	//map.addControl(new BMap.MapTypeControl({mapTypes: [BMAP_NORMAL_MAP,BMAP_HYBRID_MAP]}));     //2D图，卫星图
	//map.addControl(new BMap.MapTypeControl({anchor: BMAP_ANCHOR_TOP_LEFT}));    //左上角，默认地图控件
	map.centerAndZoom(point, '.$map_size.');
	map.enableScrollWheelZoom();    //启用滚轮放大缩小，默认禁用
	map.enableContinuousZoom();    //启用地图惯性拖拽，默认禁用
	var marker = new BMap.Marker(point);  // 创建标注
	map.addOverlay(marker);              // 将标注添加到地图中
	var label = new BMap.Label("<div style=\"padding:5px;line-height:180%;\">'.$arr[0]['content'].'</div>",{offset:new BMap.Size(20,-10)});
	marker.setLabel(label);';
	array_shift($arr);
	if(count($arr) >0 ) foreach($arr as $k=>$v) {
		if(empty($v['addr']) && empty($v['default'])) continue;
		if($v['default'] != '') {
			$point=$v['default'];
		}else{
			$addr = get_geo($v['addr']);
			$point = $addr['longitude'].','.$addr['latitude'];
		}
		$script.='var point__'.$k.' = new BMap.Point('.$point.');
		var marker__'.$k.' = new BMap.Marker(point__'.$k.');  // 创建标注
		map.addOverlay(marker__'.$k.');              // 将标注添加到地图中
		var label__'.$k.' = new BMap.Label("<div style=\"padding:5px;line-height:180%;\">'.$v['content'].'</div>",{offset:new BMap.Size(20,-10)});
		marker__'.$k.'.setLabel(label__'.$k.');';
	}
	$script .= '</script>';
	echo($script);
}
/**
 * 计算百度坐标之间距离
 * @param $lon2 起点经度
 * @param $lat2 起点纬度
 * @param $lon1 终点经度
 * @param $lat1 终点纬度
 * return 返回距离，单位：米
 */
public static function get_baidu_gps_dis($lon1, $lat1, $lon2, $lat2){
        $def_pi180= 0.01745329252; // PI/180.0
        $def_r =6370693.5; // radius of earth
        // 角度转换为弧度
        $ew1 = $lon1 * $def_pi180;
        $ns1 = $lat1 * $def_pi180;
        $ew2 = $lon2 * $def_pi180;
        $ns2 = $lat2 * $def_pi180;
        // 求大圆劣弧与球心所夹的角(弧度)
        $distance = sin($ns1) * sin($ns2) + cos($ns1) * cos($ns2) * cos($ew1 - $ew2);
        // 调整到[-1..1]范围内，避免溢出
        if ($distance > 1.0)
            $distance = 1.0;
        else if ($distance < -1.0)
            $distance = -1.0;
        // 求大圆劣弧长度
        $distance = $def_r * acos($distance);
        return $distance;
}
/**
 * 根据坐标和具体，返回附近的坐标经纬度的最大值和最小值
 * @param lat 纬度
 * @lon 经度
 * @raidus 单位米
 * return minLat,minLng,maxLat,maxLng
 */
public static function get_baidu_around($lon,$lat,$raidus){
    $PI = 3.14159265;

    $latitude = $lat;
    $longitude = $lon;

    $degree = (24901*1609)/360.0;
    $raidusMile = $raidus;

    $dpmLat = 1/$degree;
    $radiusLat = $dpmLat*$raidusMile;
    $minLat = $latitude - $radiusLat;
    $maxLat = $latitude + $radiusLat;

    $mpdLng = $degree*cos($latitude * ($PI/180));
    $dpmLng = 1 / $mpdLng;
    $radiusLng = $dpmLng*$raidusMile;
    $minLng = $longitude - $radiusLng;
    $maxLng = $longitude + $radiusLng;
    return array('ymin'=>$minLat,'ymax'=>$maxLat,'xmin'=>$minLng,'xmax'=>$maxLng);
}
/**
 * GPS坐标转换百度坐标
 * @param $x 经度
 * @param $y 纬度
 */
public static function gps_to_baidu($x,$y){
    //GPS坐标
    if($x>0 && $y>0){
        $xy=H::get_contents("http://api.map.baidu.com/ag/coord/convert?from=0&to=4&x=$x&y=$y");
        $xy=  json_decode($xy,1);
        unset($xy['error']);
        $xy['x']=  base64_decode($xy['x']);
        $xy['y']=  base64_decode($xy['y']);

    }else{
        $xy['x']=$x;
        $xy['y']=$y;
    }
    return $xy;
}

//==========================目录文件操作==========================

/**
 * 如果不存在，则根据传入目录自动创建多级目录
 *
 * @param  $dir 目录
 */
public static function mkdirs($dir,$mode=0777,$recursive=true) {
    if (!is_dir($dir)) {
        if (!H :: mkdirs(dirname($dir),$mode,$recursive)) {
            return false;
        }
        if (!mkdir($dir, $mode,$recursive)) {
            return false;
        }else{
            chmod($dir,$mode); //mkdir()函数指定的目录权限只能小于等于系统umask设定的默认权限。
        }
    }
    return true;
}
/**
 * @param $src 文件所在原目录
 * @param $dst 需要拷贝到的目标目录
 */
public static function file_copy($src,$dst) {  // 原目录，复制到的目录

    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                H::file_copy($src . '/' . $file,$dst . '/' . $file,$file);
            }
            else {
                @copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
/**
 * 根据传入的目录名计算目录下的文件大小
 *
 * @param  $dirname 要统计的目录
 */
public static function dirsize($dirname) {
    $dirsize = 0;
    if ($dir_handle = opendir($dirname)) {
        while ($filename = readdir($dir_handle)) {
            $subFile = $dirname . DIRECTORY_SEPARATOR . $filename;
            if ($filename == '.' || $filename == '..') {
                continue;
            } else if (is_dir($subFile)) {
                $dirsize += H::dirsize($subFile);
            } else if (is_file($subFile)) {
                $dirsize += filesize($subFile);
            }
        }
        closedir($dir_handle);
    }
    return $dirsize;
}
/**
 * 获取某个文件夹下面的所有文件
 *
 * @param  $dir 某个文件夹所在的路径
 * @return array
 */
public static function get_files($dir) {
    $files = array();
    if (!file_exists($dir)) return $files;
    $key = 0;
    if (!file_exists($dir)) return $files;
    if ($handle = opendir($dir)) {
        while (($file = readdir($handle)) !== false) {
            if ($file != ".." && $file != ".") {
                if (is_dir($dir . "/" . $file)) {
                    // if($file=="css" ) continue;
                    //$files[$file] = H::get_files($dir . "/" . $file);
                } else {
                    $files[$key]['name'] = $file;
                    $files[$key]['size'] = filesize($dir . "/" . $file);
                    $files[$key]['update_time'] = filemtime($dir . "/" . $file);
					$key++;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}

/**
 * 获取某个文件夹下面的所有文件
 *
 * @param  $dir 某个文件夹所在的路径
 * @return array
 */
public static function get_dirs($dir) {
    $dirArray=array();
    if (false != ($handle = opendir ( $dir ))) {
        $i=0;
        while ( false !== ($file = readdir ( $handle )) ) {
            if ($file != "." && $file != ".." && is_dir($dir.'/'.$file)) {
                $dirArray[$i]=$file;
                $i++;
            }
        }
        //关闭句柄
        closedir ( $handle );
    }
    return $dirArray;
}

/**
 * 删除目录及目录下的所以文件 清除缓存时可用到
 *
 * @param  $file 要删除的文件（含路径）
 * @return boolean 成功返回true,失败返回false;
 */
public static function del_dir($file) {
    if (!file_exists($file) && !is_dir($file)) return true; // 文件或目录不存在不需清除
    if (is_dir($file) && !is_link($file)) {
        foreach(glob($file . '/*') as $sf) {
            if (!H::del_dir($sf)) {
                return false;
            }
        }
        // 删除目录
        return @rmdir($file);
    } else {
        // 删除文件
        return @unlink($file);
    }
}

/**
 * 转换字节单位
 *
 * @param  $num 转换数字
 */
public static function num_bitunit($num) {
    $bitunit = array(' B', ' KB', ' MB', ' GB');
    for($key = 0;$key < count($bitunit);$key++) {
        if ($num >= pow(2, 10 * $key)-1) { // 1023B 会显示为 1KB
            $num_bitunit_str = (ceil($num / pow(2, 10 * $key) * 100) / 100) . " $bitunit[$key]";
        }
    }
    return $num_bitunit_str;
}


}

?>