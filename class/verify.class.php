<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */

class verify {
    public function __construct() {
    }
    public function __destory() {
    }
    // 验证QQ号码
    public static function verify_qq($qq) {
        if(preg_match('/^[1-9][0-9]{4,9}$/', $qq)) return '';
        return 'QQ号码不正确';
    }
     // 验证微信号码
    public static function verify_wx($wx) {
        if(strlen($wx) > 4) return '';
        return '微信号码不正确';
    }
    // 判断长度，长度按UTF8计算，字母为1个字节，汉字为3个字节
    public static function verify_length($str,$min=0,$max=100){
        //preg_match_all("/./u", $str, $arr);
        //$count = count($arr[0]);
        $count = strlen($str);

        if($count<$min) return "不能少于 $min 个英文字符或 ".intval($min/3)." 个汉字";
        if($count>$max) return "不能多于 $max 个英文字符或 ".intval($max/3)." 个汉字";
        return '';
    }
    // 判断用户名
    public static function verify_uname($str) {
        $str=strtolower($str);
        $no_prefix=array('temp','sys','manage','admin');
        foreach($no_prefix as $v){
            if(substr($str,0,strlen($v))==$v) return '不能以敏感字符 '.implode(' , ',$no_prefix).' 开头';
        }
        if(!preg_match('~^[a-z][a-z0-9_]{5,19}$~', $str)) return '6～20个字符，字母开头，字母、数字组成'; /*return '用户名长度6～20个字符，以字母a～z（不区分大小写）开头，且只能由字母、数字0～9和下划线组成';*/
        return '';
    }
    // 判断密码
    public static function verify_upass($str) {
        if (strlen($str) >= 6 && strlen($str) <= 20) {
            return '';
        } else {
            return '密码长度为6-20个字符';
        }
        if(strstr($str,"'")) return '密码不能有单引号';
    }
    // 判断电子邮箱
    public static function verify_email($str) {

        if( strlen($str) > 6 && preg_match("~^[\w\-\.]+@[\w\-]+(\.\w+)+$~", $str)){
            $email_forbidden=explode('|',EMAIL_FORBIDDEN);
            foreach($email_forbidden as $v){
                if(!empty($v) && strpos($str,$v)>0) return '为确保收到系统邮件，'.$v.' 邮箱不允许使用';
            }
            /*if(strpos($str,'126.com')>0 ||
                strpos($str,'163.com')>0 ||
                strpos($str,'yeah.net')>0 ||
                strpos($str,'qq.com')>0 ||
                strpos($str,'sohu.com')>0 ||
                strpos($str,'sogou.com')>0 ||
                strpos($str,'sina.cn')>0 ||
                strpos($str,'sina.com')>0){
                return '';
            }else{
                return '为确保收到系统邮件，只允许使用 QQ，网易，新浪，搜狐邮箱';
            }*/
        }else{
            return '电子邮箱格式不正确';
        }
    }
    // 判断URL
    public static function verify_url($str) {
        if( preg_match("~^http://[A-Za-z0-9]+\.[A-Za-z0-9]+[/=\?%\-&_\~`@[\]\':+!]*([^<>\"])*$~", $str)){
            return '';
        }else{
            return '网址格式不正确，必须以 http:// 开头';
        }
    }
    // 判断手机号码
    public static function verify_mobile($str) {
        if( preg_match("~^((\(\d{3}\))|(\d{3}\-))?(13\d{9}|15\d{9}|17\d{9}|18\d{9}|14\d{9})$~", $str)){
            return '';
        }else{
            return '手机号码格式不正确';
        }
    }
    // 判断固定电话号码
    public static function verify_phone($str) {
        //if( preg_match("~^((\+?[0-9]{2,4}\-[0-9]{3,4}\-)|([0-9]{3,4}\-))?([0-9]{7,8})(\-[0-9]+)?$~", $str)){
        if( preg_match("~^(0\d{2,3}\-\d{7,8})|(\+\d{2,3}\-0\d{2,3}\-\d{7,8})|^([0-9]{7,8})$~", $str)){
            return '';
        }else{
            return '固定电话号码格式不正确，正确格式如：010-68885678';
        }
    }
    // 判断性别和身份证号码是否相符，1=男，0=女
    public static function verify_idcard_gender($idcard,$gender){
        if (substr($idcard, 16, 1) % 2 == $gender){
            return '';
        }else{
            return '填写的性别和身份证号码上不符';
        }
    }
    // 判断生日和身份证号码是否相符，生日格式：XXXX-XX-XX
    public static function verify_idcard_birthday($idcard, $birtyday) {
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        if ($sBirthday == $birtyday) {
            return '';
        }else{
            return '出生日期和身份证号码上不符';
        }
    }
    // 判断身份证号码，正确则返回18位后的身份证号码（兼容15位）
    public static function verify_idcard($idcard) {
        $City = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北",43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum = 0;
        $idCardLength = strlen($idcard);
        // 长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $idcard) and !preg_match('/^\d{15}$/i', $idcard)) {
            return false;
        }
        // 地区验证
        if (!array_key_exists(intval(substr($idcard, 0, 2)), $City)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($idCardLength == 15) {
            $sBirthday = '19' . substr($idcard, 6, 2) . '-' . substr($idcard, 8, 2) . '-' . substr($idcard, 10, 2);
            try{
                $d = new DateTime($sBirthday);
            }catch(Exception $e){return false;}
            $dd = $d -> format('Y-m-d');
            if ($sBirthday != $dd) {
                return false;
            }
            $idcard = substr($idcard, 0, 6) . "19" . substr($idcard, 6, 9); //15to18
            $Bit18 = verify :: get_verify_bit($idcard); //算出第18位校验码
            $idcard = $idcard . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($idcard, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }
        // 18位身份证处理
        $sBirthday = substr($idcard, 6, 4) . '-' . substr($idcard, 10, 2) . '-' . substr($idcard, 12, 2);
        $d = new DateTime($sBirthday);
        $dd = $d -> format('Y-m-d');
        if ($sBirthday != $dd) {
            return false;
        }
        // 身份证编码规范验证
        $idcard_base = substr($idcard, 0, 17);
        if (strtoupper(substr($idcard, 17, 1)) != verify :: get_verify_bit($idcard_base)) {
            return false;
        }
        return $idcard;
    }
    // 计算身份证校验码，根据国家标准GB 11643-1999
    private static function get_verify_bit($idcard_base) {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        // 加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        // 校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }
    //判断验证码是否和SESSION一致
    public static function verify_vcode($vcode,$type='login'){
        if(VERIFY_CODE=='1'){
            session_open();
            $type=isset($_SESSION[$type])?$_SESSION[$type]:'';
            session_close();
            if($type!=md5(strtoupper($vcode))) {
                return '验证码填写错误，请重新输入';
            }
        }
        return '';
    }
}
