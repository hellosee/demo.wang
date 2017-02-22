<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */
class Page {
    private $dbm = null; //数据库操作对象
    /**
     * 初始化对象
     * @param $dbm object 数据库操作对象
     */
    public function __construct($dbm) {
        $this->dbm = $dbm;
    }

    /**
     * 获取微主页列表
     */
    public function get_page_list($params){
        global $U,$V,$T,$p;

        $params['table_name'] = DB_DBNAME.".ws_page";
        $params['fields'] = isset($params['fields'])?trim($params['fields']):'*';
        $params['user_fields'] = isset($params['user_fields'])?trim($params['user_fields']):'*';
        $params['count']=isset($params['count']) ? intval($params['count']):0;
        $params['info_list']=isset($params['info_list']) ? intval($params['info_list']):0;
        $params['where']=isset($params['where']) ? trim($params['where']):'';
        $params['pagesize'] = isset($params['pagesize']) ? intval($params['pagesize']):PAGESIZE;
        $params['p'] = isset($params['p']) ? $params['p']:$p;
        $params['order'] = isset($params['order']) ? $params['order']:'order by create_time desc';
        $params['suffix']= ' '.$params['order'].' '.$this->dbm->get_limit_sql($params['pagesize'],$params['p']);
        $result = $this->dbm->single_query($params);

        foreach($result['list'] as $k=>$v){
            if(isset($v['uid'])) {
                $result['list'][$k]['userinfo'] = $U->get($v['uid'],$params['user_fields']);
                if(isset($result['list'][$k]['userinfo']['login_pass'])) unset($result['list'][$k]['userinfo']['login_pass']);
                if(isset($result['list'][$k]['userinfo']['login_salt'])) unset($result['list'][$k]['userinfo']['login_salt']);
            }
            if(isset($result['list'][$k]['page_trade'])) {
                $tmp_tree=$T->trees($result['list'][$k]['page_trade']);
                if(isset($tmp_tree['txt'])) {
                    $result['list'][$k]['page_trade'] = $tmp_tree['txt'];
                } else {
                    $result['list'][$k]['page_trade'] = '';
                }
            }
            if($params['info_list']) {
                $sql = "select * from ".DB_DBNAME.".ws_page_info where uid=".$result['list'][$k]['uid'];
                $rss = $this->dbm->query($sql);
                foreach($rss['list'] as $ks=>$vs) {
                    $res = $this->dbm->scalar("select cate_name from ".DB_DBNAME. ".ws_page_cate where cate_id=".$vs['cate_id']." and uid=".$vs['uid']);
                    $rss['list'][$ks]['cate_name'] = $res ? $res['cate_name'] : '';
                }
                $result['list'][$k]['info_list'] = $rss['list'];
            }
            $result['list'][$k]['surl'] = '/app/page/list.php?p='.$params['p'];
            //缩略图URL
            $thumb_fields=explode(',',THUMB_FIELDS);
            foreach($v as $k1=>$v1){
                if(in_array($k1,$thumb_fields)){
                    if($v1==''){
                        $result['list'][$k]['thumb_'.$k1]='';
                    }else{
                        $result['list'][$k]['thumb_'.$k1]=H::preview_url($v1);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取微主页信息
     * @param $uid 用户UID
     * @param $fields 字段
     */
    public function get_page($uid,$fields="*"){
        $sql = "select ".$fields." from ".DB_DBNAME.".ws_page where uid='$uid' limit 1";
        $rs = $this->dbm->query($sql);
        //微主页不存在
        if(count($rs['list'])==0) return false;

        $page=$rs['list'][0];

        //获取主页相册图片
        $sql="select b.*,a.attach_id from ".DB_DBNAME.".ws_page_attach as a use index(uid) left join ".DB_DBNAME.".ws_file as b on a.file_md5=b.file_md5 where a.uid='$uid' order by b.file_order limit 10";
        $rs=$this->dbm->query($sql);
        $page['attachs']=$rs['list'];
        //缩略图URL
        $thumb_fields=explode(',',THUMB_FIELDS);
        foreach($page['attachs'] as $k=>$v){
            foreach($v as $k1=>$v1){
                if(in_array($k1,$thumb_fields)){
                    if($v1==''){
                        $page['attachs'][$k]['thumb_'.$k1]='';
                    }else{
                        $page['attachs'][$k]['thumb_'.$k1]=H::preview_url($v1);
                    }
                }
            }
        }
        return $page;
    }

    /**
     * 获取微主页文章列表
     * @param
     */
    public function get_info_list($params){
        global $p;
		$user_dev = H::user_dev();
        $params['table_name'] = DB_DBNAME.".ws_page_info";
        $params['fields'] = isset($params['fields'])?trim($params['fields']):'*';
        $params['count']=isset($params['count']) ? intval($params['count']):0;
        $params['where']=isset($params['where']) ? trim($params['where']):'';
        $params['pagesize'] = isset($params['pagesize']) ? intval($params['pagesize']):PAGESIZE;
        $params['p'] = isset($params['p']) ? $params['p']:1;
        $params['order'] = isset($params['order']) ? $params['order']:'order by create_time desc';
        $params['suffix']= ' '.$params['order'].' '.$this->dbm->get_limit_sql($params['pagesize'],$params['p']);
        if($params['where']!='') { $params['where'] .= " and is_del=0"; } else { $params['where'] .= " is_del=0"; }
        $result = $this->dbm->single_query($params);
        //缩略图URL
        $thumb_fields=explode(',',THUMB_FIELDS);
        foreach($result['list'] as $k=>$v){
            foreach($v as $k1=>$v1){
                if(in_array($k1,$thumb_fields)){
                    if($v1==''){
                        $result['list'][$k]['thumb_'.$k1]='';
                    }else{
                    	if($user_dev=='pc') {
                        	$result['list'][$k]['thumb_'.$k1]=H::preview_url($v1);
						} else {
							$result['list'][$k]['thumb_'.$k1]=H::preview_url($v1,'preview','100_');
						}
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 获取微主页文章内容
     * @param
     */
    public function get_info($info_id,$uid=0){
        $info_id = intval($info_id);
        $uid = intval($uid);//var_dump($info_id);

        if($info_id == 0) return false;

        $sql = "select * from ".DB_DBNAME.".ws_page_info where info_id='$info_id' limit 1";
        //指定了查询某个用户的文章
        if($uid>0) {
            $sql = "select * from ".DB_DBNAME.".ws_page_info where info_id='$info_id' and uid='$uid' limit 1";
        }
        $rs = $this->dbm->query($sql);//print_r($rs);
        if(count($rs['list'])==0) return false;

        $page_info = $rs['list'][0];
        //处理缩略图
        $thumb_fields=explode(',',THUMB_FIELDS);
        foreach($page_info as $k1=>$v1){
            if(in_array($k1,$thumb_fields)){
                if($v1==''){
                    $page_info['thumb_'.$k1]='';
                }else{
                    $page_info['thumb_'.$k1]=H::preview_url($v1);
                }
            }
        }
        return $page_info;
    }

    /**
     * 根据分类ID获取分类
     * @param
     */
    function get_cate_by_cid($cate_id=0){
        $cate=array('cate_id'=>0,'cate_name'=>'','uid'=>0);
        if(intval($cate_id)==0) return $cate;
        $rs = $this->dbm->query("select * from ".DB_DBNAME.".ws_page_cate where cate_id='$cate_id' limit 1");
        if(count($rs['list'])==0) $cate=$rs['list'][0];
        return $cate;
    }

    /**
     * 根据用户ID获取分类数组
     * @param
     */
    function get_cate_by_uid($uid=0){
        if($uid == 0) {
            session_open();
            $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
            session_close();
        }
        //未传递，则取当前登录用户
        $rs = $this->dbm->query("select * from ".DB_DBNAME.".ws_page_cate where uid='$uid' or uid=0");
        $tmp=array();
        foreach($rs['list'] as $v){
            $v['surl']=DOMAIN_WWW.'/app/user/list.php?id='.$uid.'&cid='.$v['cate_id'];
            $tmp[$v['cate_id']]=$v;
        }
        return $tmp;
    }
    /**
     * 根据用户ID获取分类导航
     * @param
     */
    function get_nav_by_uid($uid=0){
        session_open();
        $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
        session_close();
        //未传递，则取当前登录用户
        $rs = $this->dbm->query("select * from ".DB_DBNAME.".ws_page_nav where page_uid='$uid' or page_uid=0 order by nav_order desc");
        return $rs;
    }
    /**
     * 获取粉丝列表
     * @param
     */
    function get_page_fans($params) {
        global $p;
        $params['table_name'] = DB_DBNAME.".ws_page_follow";
        $params['fields'] = isset($params['fields'])?trim($params['fields']):'*';
        $params['count']=isset($params['count']) ? intval($params['count']):0;
        $params['where']=isset($params['where']) ? trim($params['where']):'';
        $params['pagesize'] = isset($params['pagesize']) ? intval($params['pagesize']):PAGESIZE;
        $params['p'] = isset($params['p']) ? $params['p']:$p;
        $params['order'] = isset($params['order']) ? $params['order']:'order by create_time desc';
        $params['suffix']= ' '.$params['order'].' '.$this->dbm->get_limit_sql($params['pagesize'],$params['p']);
        return $this->dbm->single_query($params);
    }
    /**
     * 获取导航
     * @param $uid 用户id
     */
    function get_page_nav($uid) {
        $sql = "select nav_name,nav_url from ".DB_DBNAME.".ws_page_nav where page_uid='{$uid}' order by nav_order asc";
        $rs = $this->dbm->query($sql);
        $tmp = array();
        if(count($rs['list'])) {
            foreach ($rs['list'] as $k=>$v ) {
                preg_match("~cid=([\d]+)~",$v['nav_url'],$arr);
                $tmp2 = (substr($v['nav_url'],0,strlen(DOMAIN_WWW)) == DOMAIN_WWW); //判断是否是当前域下链接
                if(isset($arr[1])) {
                    $tmp[$arr[1]] = array('nav_name'=>$v['nav_name'],'nav_url'=>$v['nav_url'],'target'=>'_blank');
                    if($tmp2) $tmp[$arr[1]]['target'] = '_self'; //是当前域下链接无需跳出
                }else{
                    if($tmp2) {
                        $tmp[] = array('nav_name'=>$v['nav_name'],'nav_url'=>$v['nav_url'],'target'=>'_self');
                    }else{
                        $tmp[] = array('nav_name'=>$v['nav_name'],'nav_url'=>$v['nav_url'],'target'=>'_blank');
                    }
                }
            }
            return $tmp;
        }
        $rs = $this->get_cate_by_uid($uid);
        foreach ($rs as $k=>$v ) {
            $tmp[$v['cate_id']] = array('nav_name'=>$v['cate_name'],'nav_url'=>$v['surl'],'target'=>'_self');
        }
        return $tmp;
    }
    /**
     * 主页关注
     * @$fields
     */
    public function update_page_follow($fields,$ac='add') {
        $fields['uid'] = isset($fields['uid']) ? intval($fields['uid']) : 0;
        $fields['page_uid'] = isset($fields['page_uid']) ? intval($fields['page_uid']) : 0;
        $fields['create_time'] = time();
        //判断是否已经关注
        $tmp = $this->check_page_follow($fields['uid'],$fields['page_uid']);
        if($ac=='add') {
            $str = '关注';
            if($tmp) return '您已关注过TA'; //已经关注过TA 不必执行插入
            //插入关注记录
            $rs = $this->dbm->single_insert(DB_DBNAME.'.ws_page_follow',$fields);
            $rs['rows'] = $rs['error'] ? '' : 1;  //由于该表没有主键 所以不返回autoid
        } else {
            $str = '取消关注';
            if(!$tmp) '您还没有关注TA'; //没有关注过TA 不必执行删除
            //删除关注记录
            $rs = $this->dbm->single_del(DB_DBNAME.'.ws_page_follow',"uid='{$fields['uid']}' and page_uid='{$fields['page_uid']}' limit 1");
        }
        if(!$rs['rows']) return $str.'失败'; //没有影响到数据 不需要维护关注数
        //维护主页关注数
        $sql = " update ".DB_DBNAME.".ws_page set page_follows=page_follows".($ac=='add'?"+":"-")."1 where uid ='{$fields['page_uid']}' limit 1";
        $rs = $this->dbm->query_update($sql);
        if($rs['rows']) return $str.'成功';
        return $str.'失败';
    }
    /**
     * 是否关注主页
     * @$fields
     */
     public function check_page_follow($uid,$page_uid){
        $rs = $this->dbm->query("select page_uid from ".DB_DBNAME.".ws_page_follow where uid='{$uid}' and page_uid='{$page_uid}' limit 1");
        if(count($rs['list']) == 1) {
            return true;
        } else {
            return false;
        }
     }
    /**
     * 统计微主页文章数
     * @param $uid=用户ID
     */
    function count_page_info($uid=0) {
        session_open();
        $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
        session_close();
        //未传递，则取当前登录用户
        $sql = "select count(*) as t from ".DB_DBNAME.".ws_page_info where uid='$uid' and is_del=0";
        $rs = $this->dbm->scalar($sql);
        return $rs['t'];
    }

    /**
     * 统计微主页分类数
     * @param $uid=用户ID
     */
    function count_page_cate($uid=0) {
        session_open();
        $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
        session_close();
        //未传递，则取当前登录用户
        $sql = "select count(*) as t from ".DB_DBNAME.".ws_page_cate where uid='$uid'";
        $rs = $this->dbm->scalar($sql);
        return $rs['t'];
    }

    /**
     * 统计微主页导航
     * @param $uid=用户ID
     */
    function count_page_nav($uid=0) {
        session_open();
        $uid=isset($_SESSION['user']['uid']) ? intval($_SESSION['user']['uid']) : 0;
        session_close();
        //未传递，则取当前登录用户
        $sql = "select count(*) as t from ".DB_DBNAME.".ws_page_nav where page_uid='$uid'";
        $rs = $this->dbm->scalar($sql);
        return $rs['t'];
    }
    /**
     * 取自定义导航
     */
    public function get_page_nav_auto($uid,$limit=4){
        $result = $this->dbm->query("select * from ".DB_DBNAME.".ws_page_nav where page_uid=$uid order by nav_order asc limit $limit");
        return $result['list'];
    }
    /*更新产品总数*/
    public function update_product_total($uid,$num) {
        $has_product_total = $this->dbm->find(DB_DBNAME.'.ws_page','product_total',"uid='{$uid}'");
        $fields = array();
        $fields['product_total'] = $has_product_total + $num;
        if($fields['product_total'] < 0) $fields['product_total'] = 0;
        if($has_product_total == $fields['product_total']) return;
        $this->dbm->single_update(DB_DBNAME.'.ws_page',$fields,"uid='{$uid}' limit 1");
    }

}