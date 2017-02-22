<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */
class User {
    private $dbm = null; //数据库操作对象
    /**
     * 初始化对象
     * @param $dbm object 数据库操作对象
     */
    public function __construct($dbm) {
        $this->dbm = $dbm;
    }

    /**
     * 添加单个用户
     * @return void  成功返回 自增ID 失败返回具体信息
     */
    public function add($fields) {
        $fields['login_salt'] = H::security_code();
        $fields['login_pass'] = H::password_encrypt_salt($fields['login_pass'], $fields['login_salt']);
        $fields['create_ip'] = H::getip();
        $fields['create_time'] = time();
        $fields['mobile_flag'] = 1;
        $fields['nick_name'] = !empty($fields['nick_name']) ? $fields['nick_name'] : $fields['login_name'];

        $a = $this->dbm->single_insert(DB_DBNAME_USER.".ws_user",$fields);
        if($a['error'] || $a['autoid'] <= 0) {
            die('{"code":"1","msg":"插入用户失败"}');
        }
        return $a['autoid'];
    }

    /** 随机用户名生成
     * $login_name 传入的用户名前缀，后面加随机字符
     */
    public function rand_login_name($login_name){
        global $dbm;
        $login_name_new=$login_name.time().rand(1000,9999);

        //查询重名
        $sql="select login_name from ".DB_DBNAME_USER.".ws_user where login_name='$login_name_new' limit 2";
        $rs=$dbm->query($sql);
        if(count($rs['list'])>0){
            $login_name_new=$this->rand_login_name($login_name);
        }

        return $login_name_new;
    }


    /**
     * 获取用户信息
     * @param $uid 用户ID或者用户名
     * @param $fields 需要查询的字段 默认 *
     * @param $type 查询方式，1=用户ID，2=用户名，3=手机，4=邮箱
     */
    public function get($uid,$fields='*',$type=1) {
        $user_dev = H::user_dev();
        $uid=trim($uid);
        //自动判断，由于用户ID基本无法达到100亿（11位数字）级别，所以，认为纯数字类型的和手机号码判断不冲突
        if(verify::verify_uname($uid)=='') $type=2;
        if(verify::verify_mobile($uid)=='') $type=3;
        if(verify::verify_email($uid)=='') $type=4;

        if($type==1){
            if(intval($uid)<=0) return false;
            $sql = "select $fields from ".DB_DBNAME_USER.".ws_user where uid='".intval($uid)."' limit 2";
        }
        if($type==2){
            $sql = "select $fields from ".DB_DBNAME_USER.".ws_user force index(login_name) where login_name='$uid' limit 2";
        }
        if($type==3){
            $sql = "select $fields from ".DB_DBNAME_USER.".ws_user force index(login_mobile) where login_mobile='$uid' limit 2";
        }
        if($type==4){
            $sql = "select $fields from ".DB_DBNAME_USER.".ws_user force index(login_email) where login_email='$uid' limit 2";
        }
        if($sql=='') return false;
        if(in_array(trim($fields),array('uid','login_name','login_mobile','login_email'))){
            //**************缓存开始
            global $global_global;
            $cache_params = array(
                'key'=>md5($sql),
                'time'=>300,
                'cache_type'=>CACHE_TYPE,
                'server'=>$global_global['mem_server'],
                'path'=>'cache/'.md5($sql)
            );
            $cache_val = H :: cache($cache_params);
            if ($cache_val == 'timeout' || (isset($cache_val['list']) && is_array($cache_val['list']) && count($cache_val['list'])==0)) {
                $cache_params['val'] = $this->dbm->query($sql);
                $cache_val = H :: cache($cache_params);
            }
            $rs = $cache_val;
            //**************缓存结束
        }else{
            $rs = $this -> dbm -> query($sql);//唯一用户则返回全部用户数据
        }
	    //print_r($rs);die();
        if (count($rs['list'])==1) {
            $user = $rs['list'][0];
            //处理缩略图
            $thumb_fields=explode(',',THUMB_FIELDS);
            foreach($user as $k1=>$v1){
                if(in_array($k1,$thumb_fields)){
                    if($v1==''){
                        $user['thumb_'.$k1]='';
                    }else{
                        if($user_dev=='pc') {
                        	   $user['thumb_'.$k1]=H::preview_url($v1);
						} else{
							$user['thumb_'.$k1]=H::preview_url($v1,'preview','100_');
						}
                    }
                }
            }
            return $user;
        }else{
            return false;
        }
    }

    /**
     * 删除用户
     * @param $uid 用户ID
     */
    public function del($uid) {
        $this->dbm->query_update("delete from ".DB_DBNAME_USER.".ws_user where uid='$uid'");
    }

    /**
     * 获取用户剩余积分
     * @param $uid 用户ID
     */
    public function get_user_point($uid=0) {
        session_open();
        $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
        session_close();
        if($uid>0){
            $rs = $this->dbm->scalar("select sum(point) as t from ".DB_DBNAME_USER.".ws_point where uid='$uid'");
            return intval($rs['t']);
        }else{
            return 0;
        }
    }
    /**
     * 更新用户总积分数
     * @params $uid 用户uid
     * @params $point 积分增减数 0=统计积分表后获得
     */
    public function update_user_point($uid,$point=0) {
        if($point == 0) {
            $point = $this->get_user_point($uid); //获取总积分
        }else{
            $point += $this->dbm->find(DB_DBNAME_USER.'.ws_user','point',"uid='{$uid}'"); //已有总积分+增减数
        }
        $this->dbm->query_update("update ".DB_DBNAME_USER.".ws_user set point='".$point."' where uid='{$uid}' limit 1");
    }

    /**
     * 用户组信息查询
     * @param $group_id 组ID
     */
    public function get_group($group_id){
        if($group_id == 0) return '';
        $rs = $this->dbm->query("select * from ".DB_DBNAME_USER.".ws_group where group_id =$group_id limit 1");
        return isset($rs['list'][0]) ? $rs['list'][0] : '';
    }

    /**
     * 积分增减操作
     * @param $params -> uid 用户ID，可不传，不传递uid则代表当前用户
     * @param $params -> point 分值，可不传
     * @param $params -> code_id 积分类型ID，必传
     */
    public function update_point($params){
        global $T;
        //积分方式不存在
        $tmp_tree=$T->trees($params['code_id']);
        if(count($T->trees($params['code_id']))<=1) return false;
        //判断分是由系统给定，还是由参数传递，判断val==0
        if($tmp_tree['val']==0) { //手工充值
            $fields['point'] = isset($params['point']) ? $params['point'] : 0;
        }else{
            $fields['point']=$tmp_tree['val'];
        }
        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        $fields['uid'] = isset($params['uid']) ? intval($params['uid']) : $uid;

        $fields['point_type'] = isset($params['code_id']) ? $params['code_id'] : 0;

        $fields['create_time'] = time();

        $ret = $this->dbm->single_insert(DB_DBNAME_USER.".ws_point",$fields);

        if($ret['error']=='' && $ret['autoid']>0) {
            $this->update_user_point($fields['uid'],$fields['point'],$fields['point']); //修改总积分
            return true;
        }
        return false;
    }

    /**
     * 用户统计数据更新
     * @param $uid 用户ID
     * @param $type array number = 微商群 ，quan = 微商圈 , page = 微主页
     */
    public function update_count($uid, $type=array('number','quan','page')) {
        foreach($type as $v) {
            $func = 'update_'.$v.'_count';
            if(!method_exists($this, $func)) continue;
            $this->$func($uid);
        }

    }
    /**
    *微主页统计数更新
    *更新关注数
    */
    public function updatre_page_count($uid){
        $sql="select count(*) as t from ".DB_DBNAME.".ws_page_follow where page_uid='$uid'";
        $rs=$this->dbm->scalar($sql);
        $sql="update ".DB_DBNAME.".ws_page set page_follows='{$rs['t']}' where uid='$uid'";
        $this->dbm->query_update($sql);

    }
   /**
    *微商圈统计数更新
    *更新投票数、评分数、主题数、总话题数、关注数
    */
    public function update_quan_count($uid){
        $vote_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_quan_vote where quan_uid='$uid'");
        $credit_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_quan_credit where quan_uid='$uid'");
        $post_res = $this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_quan_post where  quan_uid='$uid' and parent_id=0");
        $post_all_res = $this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_quan_post where  quan_uid='$uid'");
        $follow_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_quan_follow where quan_uid='$uid'");
        $parmas = array('quan_post'=>$post_res['t'],
                        'quan_post_all'=>$post_all_res['t'],
                        'quan_vote'=>$vote_rs['t'],
                        'quan_credit'=>$credit_rs['t'],
                        'quan_follows'=>$follow_rs['t']
                    );
        $this->dbm->single_update(DB_DBNAME.".ws_quan", $parmas, "uid='$uid'");
    }
    /**
    *微商群统计数更新
    *更新收藏数、点赞数和评论数
    */
    public function update_number_count($uid){
        $fav_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_number_fav where uid='$uid'");
        $good_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_number_good where uid='$uid'");
        $comment_rs=$this->dbm->scalar("select count(*) as t from ".DB_DBNAME.".ws_comment where comment_type=1 and uid='$uid'");
        $quan_params = array('num_good'=>$good_rs['t'],'num_fav'=>$fav_rs['t'],'num_comment'=>$comment_rs['t']);
        $this->dbm->single_update(DB_DBNAME.".ws_number",$quan_params," uid='$uid'");
    }
    /**
     * 判断用户资料是否完善 注意传递具体的待验证字段 完善=true 不完善=false
     */
    public function is_perfect_user_info($uid){

        $is_perfect = true;
        $rs = $this->get($uid,'nick_name,true_name,province,district,login_mobile,login_email,qq,wx');
        if(strlen($rs['nick_name']) == 0) $is_perfect = false;
        if(strlen($rs['true_name']) == 0) $is_perfect = false;
        if(!$rs['province']) $is_perfect = false;
        if(!$rs['district']) $is_perfect = false;
        if(strlen($rs['login_mobile']) < 5) $is_perfect = false;
        if(strlen($rs['login_email']) < 4) $is_perfect = false;
        if(strlen($rs['qq']) < 4) $is_perfect = false;
        if(strlen($rs['wx']) < 4) $is_perfect = false;
        return $is_perfect;
    }
    /** 获取用户被他人导入次数
     */
    public function get_fans_down($uid) {
        $down_num = $this->dbm->find(DB_DBNAME_TOOL.".ws_fans_down_num",'down_num',"uid='{$uid}'");
        return $down_num === false ? 0 : $down_num;
    }
    /** 获取用户被他人导入次数
     * @param $uid 用户uid
     * @param $num 增减数量 正数 增加 负数减少
     */
    public function update_fans_down($uid,$num) {
        $down_num = $this->dbm->find(DB_DBNAME_TOOL.".ws_fans_down_num",'down_num',"uid='{$uid}'");

        if($down_num === false) {
            $this->dbm->single_insert(DB_DBNAME_TOOL.".ws_fans_down_num",array('uid'=>$uid,'down_num'=>$num));
        }else{
            $down_num += $num;
            if($down_num < 0 ) $down_num = 0;
            $this->dbm->single_update(DB_DBNAME_TOOL.".ws_fans_down_num",array('down_num'=>$down_num),"uid='{$uid}'");
        }
    }
    /**
     * 返回微信加粉配额权限
     */
    public function get_app_trade_info($uid){
        $data=array('trade_total'=>0,'trade_used'=>0,);
        //推荐总数
        $sql="select count(*) as t from ws_user.ws_recommend_reg where uid_recommend='$uid'";
        $rs=$this->dbm->scalar($sql);
        $data['trade_total']=$rs['t']*RECOMMEND_REG+TRADE_INIT;
        //资料完善名额
        $tmp = $this->is_perfect_user_info($uid);
        if($tmp) $data['trade_total'] += PERFECT_USER_INFO;
        //每日签到名额
        //$data['trade_total'] += $this->dbm->sum(DB_DBNAME_USER.'.ws_user_sign','sign_prize',"uid='{$uid}'");
        //下载总数
        $table = DB_DBNAME_TOOL.'.ws_fans_down'.hash_table_id($uid);
        $sql="select count(*) as t from {$table} where uid_down='$uid'";
        $rs=$this->dbm->scalar($sql);
        $data['trade_used']=$rs['t'];
        return $data;
     }
     /* * 是否签到 签过返回具体的签到时间，未签返回 false
      * $params $uid 用户uid
      * $params $sign_time 格式化时间或者是时间戳
      * */
    public function is_sign($uid,$sign_time) {
        if(!is_numeric($sign_time)) $sign_time = strtotime($sign_time);
        $sign_time = strtotime(date('Y-m-d',$sign_time)); //2015-05-06 00:00:00 的时间戳
        $tmp = $this->dbm->find(DB_DBNAME_USER.".ws_user_sign","create_time","uid='{$uid}' and sign_time='{$sign_time}'");
        return $tmp;
    }
    /* * 每日签到 成功返回 具体的签到时间与奖励值  失败返回 false
      * $params $uid 用户uid
      * $params $sign_time 格式化时间或者是时间戳
      * */
    public function sign($uid,$sign_time) {
        if(!is_numeric($sign_time)) $sign_time = strtotime($sign_time);
        $sign_time = strtotime(date('Y-m-d',$sign_time)); //2015-05-06 00:00:00 的时间戳
        $fields = array();
        $fields['sign_time'] = $sign_time;
        $fields['uid'] = $uid;
        $fields['create_time'] = time();
        $tmp = explode(',', SIGN_PRIZE);
        $fields['sign_prize'] = rand($tmp[0], $tmp[1]); //随机奖励名额
        $rs = $this->dbm->single_insert(DB_DBNAME_USER.".ws_user_sign",$fields);
        if($rs['error']) return false;
        return array('create_time'=>$fields['create_time'],'sign_prize'=>$fields['sign_prize']); //返回具体的签到时间
    }
    /* * 获取签到排名
      * $params $uid 用户uid
      * $params $sign_time 格式化时间或者是时间戳
      * $params $create_time 具体签到时间
      * */
    public function sign_order($uid,$sign_time,$create_time) {
        if(!is_numeric($sign_time)) $sign_time = strtotime($sign_time);
        $sign_time = strtotime(date('Y-m-d',$sign_time)); //2015-05-06 00:00:00 的时间戳
        $tmp = $this->dbm->counts(DB_DBNAME_USER.".ws_user_sign",1,"sign_time='{$sign_time}' and create_time<$create_time");
        return $tmp+1;
    }
     /* 区分团队等级*/
    public function get_team_rank() {
        session_open();
        $uid = isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        $team_rank = array();
        $result = $this->dbm->query("select uid from ".DB_DBNAME_USER.".ws_recommend_reg where uid_recommend='$uid'");
        if(count($result['list'])>0) {
            foreach($result['list'] as $k1=>$v1) {
                $team_rank[$uid]= array('team_name'=>'一级推荐','team_level'=>1);
                $result = $this->dbm->query("select uid from ".DB_DBNAME_USER.".ws_recommend_reg where uid_recommend='{$v1['uid']}'");
                if(count($result['list'])>0) {
                    foreach($result['list'] as $k2=>$v2) {
                        $team_rank[$v1['uid']] = array('team_name'=>'二级推荐','team_level'=>2);
                        $result = $this->dbm->query("select uid from ".DB_DBNAME_USER.".ws_recommend_reg where uid_recommend='{$v2['uid']}'");
                        if(count($result['list'])>0) {
                            foreach($result['list'] as $k3=>$v3) {
                                $team_rank[$v2['uid']] = array('team_name'=>'三级推荐','team_level'=>3);
                            }
                        }
                    }
                }
            }

        }
        return $team_rank;
    }

    /*获取当前用户加V状态*/
    public function get_user_vip_status(){
        session_open();
        $uid = isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        $result = $this->dbm->scalar("select * from ".DB_DBNAME_USER.".ws_user_vip where uid='$uid'");
        if($result) {
            return $result['vip_status'];
        }
         return false;
    }
    //判断是否是加V认证用户
    public function is_v($uid){
        $sql="select vip_status from ".DB_DBNAME_USER.".ws_user_vip where vip_status=1 and uid='$uid'";
        $result = $this->dbm->query($sql);

        //print_r($result);exit;
        if(isset($result['list'][0]['vip_status']) && $result['list'][0]['vip_status']==1){
            return true;
        }else{
            return false;
        }
    }
}

?>