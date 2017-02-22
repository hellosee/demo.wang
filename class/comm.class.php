<?php
/*
 * MCMS Copyright (c) 2012-2013 ZhangYiYeTai Inc.
 *
 *  http://www.mcms.cc
 *
 * The program developed by loyjers core architecture, individual all rights reserved,
 * if you have any questions please contact loyjers@126.com
 */
class Comm {
    private $dbm = null; //数据库操作对象

    /**
     * 初始化对象
     * @param $dbm object 数据库操作对象
     */
    public function __construct($dbm)
    {
        $this->dbm = $dbm;
    }

    /**
     * 获取关键词
     * @param $order 排序字段
     * @param $qtype 搜索类型 0=不限制 1=微商大全 2=圈子 3=帖子 4=微商团队
     * @param $num 关键词个数
     */
    public function get_word($order='qorder',$qtype=0,$num=5) {
        $sql = "select qword,qnum from ".DB_DBNAME.".ws_qword ".($qtype>0 ? "where qtype={$qtype}" : '')." order by {$order} asc limit 0,{$num}";
        $rs = $this->dbm->query($sql);
        return $rs['list'];
    }

    /**
     * 统计私信数量
     * @param $where 查询条件
     */
    public function count_message($where='') {
        $sql = "select count(*) as t from ".DB_DBNAME_USER.".ws_message ".($where == '' ? '' : "where {$where}");
        $rs = $this->dbm->query($sql);
        return empty($rs['list'][0]['t']) ? 0 : $rs['list'][0]['t'];
    }

    /**
     * 判断是否阅读了私信内容
     * @param $uid 用户ID
     * @param $message_id 私信ID
     */
    public function is_sysread($uid,$message_id) {
        global $dbm;
        $sql = "select read_time from ".DB_DBNAME_USER.".ws_message_sysread where uid='{$uid}' and message_id='{$message_id}' limit 1";
        $rs = $this->dbm->query($sql);
        if(count($rs['list']) !=1 ) return 0;
        return $rs['list'][0]['read_time'];

    }
    /*
     *@$comment_type 获取某个项目 $comment_type=评论类型（1=微商大全，2=微主页文章）
     *@$source_id 评论对象ID（微商大全ID，微主页文章ID）
     */
    public function get_comment_list($comment_type,$source_id,$p) {
        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        $params['table_name']=DB_DBNAME.'.ws_comment';
        $params['fields']='*';
        $params['where']="comment_type='$comment_type' and source_id='$source_id' and parent_id=0 and uid>0";
        $params['count']=1;
        $params['pagesize']=5;
        $params['suffix']='order by comment_id desc '.$this->dbm->get_limit_sql($params['pagesize'],$p);

        $rs=$this->dbm->single_query($params);//print_r($rs);
        $user = new User($this->dbm);
        $html = '';
        $html.='<ul class="comment_list">';
        foreach($rs['list'] as $v){
            $sql="select * from ".DB_DBNAME.".ws_comment where comment_type=$comment_type and parent_id='{$v['comment_id']}' order by comment_id asc limit 50";
            $son=$this->dbm->query($sql);//print_r($son);
            $user_info=$user->get($v['uid'],'nick_name,avatar,true_name,gender');
            $html .= '  <li>';
            $html .= '  <a href="'.$this->surl('page',$v['uid']).'" class="comm_face l" target="_blank"><img src="'.$user_info['thumb_avatar'].'" onerror="img_err(this,\'user\');" /></a>';
            $html .= '    <div class="comm_info l">';
            $html .= '      <p class="comm_hd"><strong><a href="'.$this->surl('page',$v['uid']).'" target="_blank">'.$user_info['nick_name'].'</a></strong>';
            $html .= '<em>'.date("Y-m-d H:i",$v['create_time']).'</em>';
            if(count($son['list'])==0 && $uid==$v['uid']) {
                $html .= '<a style="margin-left:5px;" href="javascript:void(0);" class="hehe_hide on_replay huifu_show" onclick="del_comment('.$comment_type.','.$source_id.','.$v['comment_id'].');">删除</a>';
            }
            $html .= '<a href="javascript:void(0);" class="on_replay huifu_show" onclick="is_show(this);">回复</a></p>';
            $html .= '    <span class="comm_desc">'.$v['content'].'</span>';
            $html .= '      <p class="line-t-10"></p>';

            $html .= '<div class="replay_txt hfshow_box" style="display:none;">';
            $html .= '    <textarea placeholder="回复 '.$user_info['nick_name'].'：" id="content"></textarea>';
            $html .= '    <p class="line-t-20"></p>';
            $html .= '    <a href="javascript:void(0);" class="replay_btn" onclick="save_comment('.$v['comment_id'].','.$source_id.',this,'.$comment_type.','.$v['uid'].')">回复评论</a>';
            $html .= '    <p class="line-t-20"></p>';
            $html .= '</div>';

            //子评论
            //print_r($son['list']);
            if(count($son['list'])>0) {
            $html .= '<div class="pr">';
            $html .= '    <ul class="comment_list comment_children">';
            }
            foreach($son['list'] as $v1){
                $user_comment=$user->get($v1['uid'],'nick_name,avatar,true_name,gender');
                $user_reply=$user->get($v1['uid_reply'],'nick_name,avatar,true_name,gender');
                $html .= '<li>';
                $html .= '  <a href="'.$this->surl('page',$v1['uid']).'" class="comm_face l" target="_blank"><img src="'.$user_comment['thumb_avatar'].'" onerror="img_err(this,\'user\');" /></a>';
                $html .= '  <div class="comm_info l">';
                $html .= '      <p class="comm_hd"><strong><a href="'.$this->surl('page',$v1['uid']).'" target="_blank">'.$user_comment['nick_name'].'</a><span>回复了</span><a href="'.$this->surl('page',$v1['uid_reply']).'" target="_blank">'.$user_reply['nick_name'].'</a></strong><em>'.date("Y-m-d H:i",$v1['create_time']).'</em>';
                if($uid==$v1['uid']) {
                    $html .= '<a style="margin-left:5px;" href="javascript:void(0);" class="hehe_hide on_replay huifu_show" onclick="del_comment('.$comment_type.','.$source_id.','.$v1['comment_id'].');">删除</a>';
                }
                $html .='<a href="javascript:void(0);" class="on_replay" onclick="is_show(this);">回复</a></p>';
                $html .= '       <span class="comm_desc">'.$v1['content'].'</span>';

                $html .= '       <p class="line-t-10"></p>';
                $html .= '       <div class="replay_txt hfshow_box" style="display:none;">';
                $html .= '          <textarea placeholder="回复 '.$user_comment['nick_name'].'：" id="content"></textarea>';
                $html .= '          <p class="line-t-20"></p>';
                $html .= '          <a href="javascript:void(0);" class="replay_btn" onclick="save_comment('.$v['comment_id'].','.$source_id.',this,'.$comment_type.','.$v1['uid'].')">回复评论</a>';
                $html .= '          <p class="line-t-20"></p>';
                $html .= '       </div>';

                $html .= '  </div>';
                $html .= '</li>';
            }
            if(count($son['list'])>0) {
            $html .= '    </ul>';
            $html .= '</div>';
            }
            $html .= '<p class="line-t-10"></p>';
            $html .= '    </div>';
            $html .= '    </li>';
        }
        $html.='</ul>';

        $html .=' <p class="line-t-20"></p>';
        if(count($rs['pagebar']['pagearr'])>0) {
            $pagehtml ='<div class="pagebar clearfix" id="comment_pagebar">';
            foreach($rs['pagebar']['pagearr'] as $k=>$v) {
               if($v['url']=='') {
                   $pagehtml .='<span class="now_class">'.$v['txt'].'</span>';
               } else {
                   $pagehtml .='<a onclick="get_comment('.$comment_type.','.$source_id.','.$v['txt'].');" href="javascript:void(0)">'.$v['txt'].'</a>';
               }
           }
            $pagehtml .='<span class="ptpage"> '.$_GET['p'].'/'.ceil($rs['total']/$params['pagesize']).' 页</span>';

            $pagehtml.='</div><p class="line-t-20"></p>';
            $html.=$pagehtml;
        }
        $html.='<div class="replay_txt">';
        $html.='    <textarea placeholder="大家踊跃发言喔！！"></textarea>';
        $html.='    <p class="line-t-20"></p>';
        $html.='    <a href="javascript:void(0);" class="replay_btn" onclick="save_comment(0,'.$source_id.',this,'.$comment_type.',0);">发布评论</a>';
        $html.='    <p class="line-t-20"></p>';
        $html.='</div>';
        return $html;
    }

    /**
     * 统计浏览量
     * @param $type 类型 array(number,quan,post,page)
     * @param $id 要统计的ID
     */
    public function count_view($type,$id){
        $count_type=array('num','quan','post','page','page_info');
        if(in_array($type,$count_type)){
            switch($type){
                case 'num':
                    $sql="update ".DB_DBNAME.".ws_number set num_view=num_view+1 where num_id='$id'";
                    break;
                case 'quan':
                    $sql="update ".DB_DBNAME.".ws_quan set quan_view=quan_view+1 where uid='$id'";
                    break;
                case 'post':
                    $sql="update ".DB_DBNAME.".ws_quan_post set post_view=post_view+1 where post_id='$id'";
                    break;
                case 'page':
                    $sql="update ".DB_DBNAME.".ws_page set page_view=page_view+1 where uid='$id'";
                    break;
                case 'page_info':
                    $sql="update ".DB_DBNAME.".ws_page_info set info_view=info_view+1 where info_id='$id'";
                    break;
                default:
                    break;
            }
            $this->dbm->query_update($sql);
            /*$cookie_name=$type.$id;
            if(!isset($_COOKIE[$cookie_name])) {
                if ($sql != '') $this->dbm->query_update($sql);
                H::set_cookie($cookie_name, '1', time()+300);
            }*/
        }
    }

    /**
     * 返回URL，仅支持单ID参数调用
     * @param $type 类型 array(qun,quan,quan_fans,post,page,info,page_fans)
     * @param $id 如圈子ID，群号ID，话题ID，主页ID，主页文章ID等
     */
    public function surl($type,$id){
        $url="javascript:void(0);";
        switch($type) {
            case 'qun'://微商群
                $url=DOMAIN_WWW."/app/qun/view.php?id=".$id;
                break;
            case 'quan'://圈子
                $url=DOMAIN_WWW."/app/quan/view.php?id=".$id;
                break;
            case 'quan_fans'://圈子粉丝
                $url=DOMAIN_WWW."/app/quan/fans.php?id=".$id;
                break;
            case 'post'://圈子话题
                $url=DOMAIN_WWW."/app/quan/quan.post.php?id=".$id;
                break;
            case 'page'://微商主页
                $url=DOMAIN_WWW."/app/user/index.php?id=".$id;
                break;
            case 'info'://微商主页文章
                $url=DOMAIN_WWW."/app/user/view.php?id=".$id;
                break;
            case 'page_fans'://微商主页文章
                $url=DOMAIN_WWW."/app/user/fans.php?id=".$id;
                break;
            default:
                break;
        }
        return $url;
    }

    /**
     * 输出会员中心的URL
     * @param $type 类型 array(number,quan,post,page)
     */
    public function surl_user($type){
        $url="javascript:void(0);";
        switch($type) {
            case 'add_qun'://添加群
                $url=DOMAIN_USER.'/app/user/qun.php?type=new';
                break;
            case 'add_product'://添加产品
                $url=DOMAIN_USER."/app/user/page.info.php?type=new&cate_id=2";
                break;
            case 'add_attachs'://添加展示相册
                $url=DOMAIN_USER."/app/user/page.php";
                break;
            default:
                break;
        }
        return $url;
    }

    /**
     * 取数据列表通用方法
     * @param $params  参数数组
     * @param $params->type  可获取数据列表类型
     * @param $params->where SQL查询条件
     * @param $params->ids   ID字符串如 1,2,3,4
     * @param $params->len   读取条数
     * @param $params->order 排序
     * @param $params->isjoin 是否连表（文章是否跟圈子表关联以便区分行业圈和产品圈文章）
     */
    public function get_list($params){
        $result=array('error'=>'未知错误','list'=>array());
		$user_dev = H::user_dev();
        //可以获取的数据类型
        $params['type']=isset($params['type'])?$params['type']:'';
        $type_array=array('num','quan','post','page','info');
        //不在允许范围内，返回空数组
        if(!in_array($params['type'],$type_array)) {
            $result['error']='不在查询允许范围';
            return $result;
        }

        //查询条件
        $params['where']=isset($params['where'])?$params['where']:'';
        $params['ids']=isset($params['ids'])?$params['ids']:'';
        //如果2个查询条件都没有，则返回空数组
        if($params['where']=='' && $params['ids']=='') {
            $params['where']='1=1';
        }

        //读取条数
        $params['len']=isset($params['len'])?intval($params['len']):5;
        //排序
        $params['order']=isset($params['order'])?$params['order']:'';
        $params['isjoin']=isset($params['isjoin'])?intval($params['isjoin']):0;

        switch($params['type']){
            case 'num'://群号列表
                $sql="select num_id,uid,num_title,num_qrcode,num_img from ".DB_DBNAME.".ws_number where is_del=0";
                if($params['ids']==''){
                    $sql.=" and ".$params['where'];
                }else{
                    $sql.=" and num_id in({$params['ids']})";
                }
                if($params['order']=='') { $sql .= " order by num_id desc"; }else{ $sql .= " {$params['order']}"; }
                break;
            case 'quan'://圈子列表
                $sql="select uid,quan_name,quan_img,quan_desc,quan_vote,quan_credit,quan_follows,quan_view,create_time from ".DB_DBNAME.".ws_quan where is_del=0";
                if($params['ids']==''){
                    $sql.=" and ".$params['where'];
                }else{
                    $sql.=" and uid in({$params['ids']})";
                }
                if($params['order']=='') { $sql .= " order by create_time desc"; }else{ $sql .= " {$params['order']}"; }
                break;
            case 'post'://话题列表
                $sql="select post_id,post_title,post_content,uid,quan_uid,post_view,post_reply,create_time from ".DB_DBNAME.".ws_quan_post where parent_id=0 and is_del=0";
                if($params['ids']==''){
                    $sql.=" and ".$params['where'];
                }else{
                    $sql.=" and post_id in({$params['ids']})";
                }
                if($params['order']=='') { $sql .= " order by create_time desc"; }else{ $sql .= " {$params['order']}"; }
                break;
            case 'page'://主页列表
                $sql="select uid,page_title,page_desc,page_view,page_follows,create_time from ".DB_DBNAME.".ws_page where is_del=0";
                if($params['ids']==''){
                    $sql.=" and ".$params['where'];
                }else{
                    $sql.=" and uid in({$params['ids']})";
                }
                if($params['order']=='') { $sql .= " order by create_time desc"; }else{ $sql .= " {$params['order']}"; }
                break;
            case 'info'://文章列表
                if($params['ids']==''){
                    if($params['isjoin']==1) {
                        $sql = "select a.info_id,a.uid,a.info_title,a.info_img,a.product_words,a.info_body,a.info_view,a.create_time from " . DB_DBNAME . ".ws_page_info as a left join " . DB_DBNAME . ".ws_quan as b on a.uid=b.uid where a.is_del=0";
                        $sql .= " and " . $params['where'];
                    }else{
                        $sql="select info_id,uid,info_title,info_img,product_words,info_body,info_view,create_time from ".DB_DBNAME.".ws_page_info where is_del=0";
                        $sql .= " and " . $params['where'];
                    }
                }else{
                    $sql="select info_id,uid,info_title,info_img,product_words,info_body,info_view,create_time from ".DB_DBNAME.".ws_page_info where is_del=0";
                    $sql.=" and info_id in({$params['ids']})";
                }
                if($params['order']=='') { $sql .= " order by create_time desc"; }else{ $sql .= " {$params['order']}"; }
                break;
            default:
                break;
        }
        $sql.=" limit {$params['len']}";
        $result=$this->dbm->query($sql);
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
						} else{
							$result['list'][$k]['thumb_'.$k1]=H::preview_url($v1,'preview','100_');
						}
                    }
                }
            }
        }
        //print_r($result);
        return $result;
    }

    /**
     * 搜索框侧边输出关键词
     */
    public function show_top_hot() {
        $list[1]=$this->get_word('qorder',1);
        $list[2]=$this->get_word('qorder',2);
        $list[3]=$this->get_word('qorder',3);
        $list[4]=$this->get_word('qorder',4);
        foreach($list as $k=>$v){
            $style='style="display:none"';
            if($k==$_GET['search_type']) $style='';
            echo('<div class="qlist_div" id="qlist'.$k.'" '.$style.'>');
            foreach($v as $v1){
                $surl="javascript:void(0);";
                if($k==1) $surl=DOMAIN_WWW."/app/qun/list.php?kw=".urlencode($v1['qword']);
                if($k==2) $surl=DOMAIN_WWW."/app/quan/list.php?kw=".urlencode($v1['qword']);
                if($k==3) $surl=DOMAIN_WWW."/app/quan/list.post.php?kw=".urlencode($v1['qword']);
                if($k==4) $surl=DOMAIN_WWW."/app/home/list.php?kw=".urlencode($v1['qword']);
                echo('<a href="'.$surl.'">'.$v1['qword'].'</a>');
            }
            echo('</div>');
        }

    }

    /**
     * 单一推荐位内容通用方法
     * @param $params  参数数组
     * @param $params->rec_id 推荐ID
     * @param $params->type  可获取数据列表类型
     * @param $params->where SQL查询条件
     * @param $params->ids   ID字符串如 1,2,3,4
     * @param $params->len   读取条数
     * @param $params->order 排序
     * @param $params->isjoin 是否连表（文章是否跟圈子表关联以便区分行业圈和产品圈文章）
     */
    function get_recommend($params) {

        //查询条件
        $params['rec_id']=isset($params['rec_id'])?intval($params['rec_id']):'0';
         //读取条数
        $params['len']=isset($params['len'])?intval($params['len']):10;
         //排序
        $params['order']=isset($params['order'])?$params['order']:'';
        $params['isjoin']=isset($params['isjoin'])?intval($params['isjoin']):0;
        if($params['rec_id']> 0) {
            $sql = "select * from ".DB_DBNAME.".ws_recommend where rec_id='{$params['rec_id']}' limit 1";
            $result = $this->dbm->query($sql);
            if(count($result['list'])==1) {
                $recommed = $result['list'][0];
                $params['type'] = $recommed['rec_type'];
                $params['ids'] = $recommed['id_list'];
                if($params['type']=='num') {
                    $params['order'] = "order by find_in_set(num_id,'".$params['ids']."')";
                } else if($params['type']=='quan') {
                    $params['order'] = "order by find_in_set(uid,'".$params['ids']."')";
                } else if($params['type']=='post') {
                    $params['order'] = "order by find_in_set(post_id,'".$params['ids']."')";
                } else if($params['type']=='page') {
                    $params['order'] = "order by find_in_set(uid,'".$params['ids']."')";
                } else if($params['type']=='info') {
                    $params['order'] = "order by find_in_set(info_id,'".$params['ids']."')";
                }

                $a=$this->get_list($params);
                $a['type'] = $params['type'];
                return $a;
            }
        }
        return array();
    }

    /**
     * 混合推荐位数据通用获取方法
     */
    function get_stuff($stuff_code,$count=10){
        global $dbm,$U;
        $sql="select * from ".DB_DBNAME.".ws_good_stuff where stuff_code='$stuff_code' order by create_time desc limit $count";
        $rs=$dbm->query($sql);
        $ret=array();
        foreach($rs['list'] as $v){
            $v['surl']=$this->surl($v['source_type'],$v['source_id']);
           // array_push($ret,$v);
			//查询发布用户

		switch($v['source_type']) {
            case 'num'://微商群
                $result = $dbm->scalar("select uid from ".DB_DBNAME.".ws_number where num_id='{$v['source_id']}' limit 1");
                $res = $U->get($result['uid']);
				$v['uid'] = $result['uid'];
				$v['nick_name'] = $res['nick_name'];
				array_push($ret,$v);
                break;
            case 'quan'://圈子
                $res = $U->get($v['source_id']);
			    $v['uid'] = $v['source_id'];
                $v['nick_name'] = $res['nick_name'];
				array_push($ret,$v);
                break;
            case 'info'://文章
                $result = $dbm->scalar("select uid from ".DB_DBNAME.".ws_page_info where info_id='{$v['source_id']}' limit 1");
                $v['uid'] = $result['uid'];
				$res = $U->get($result['uid']);
				$v['nick_name'] = $res['nick_name'];
				array_push($ret,$v);
                break;
            case 'post'://圈子话题
                $result = $dbm->scalar("select uid from ".DB_DBNAME.".ws_quan_post where post_id='{$v['source_id']}' limit 1");
                $v['uid'] = isset($result['uid'])?intval($result['uid']):0;
                $res = $U->get($v['uid']);
                $v['nick_name'] = $res['nick_name'];
				array_push($ret,$v);
                break;
			case 'page'://主页
			    $res = $U->get($v['source_id']);
				$v['uid'] = $v['source_id'];
				$v['nick_name'] = $res['nick_name'];
				array_push($ret,$v);
                break;
		  	}
        }
        return $ret;
    }
     /**
     * 没有搜索结果
     */
    public function no_search_result(){
        echo '<div class="search_tips">抱歉，没有找到“<span>'.$_GET['kw'].'</span>”的搜索结果 <a href="?kw=&search_type='.$_GET['search_type'].'">回到列表页</a></div>';
    }
    /**
     * 搜索框代码
     */
    public function show_search(){
        global $V,$global_vars;
        ?>
        <div class="sear_sel head_sel l" id="search">
            <div class="sel_box" onclick="select_single(event,this);return false;">
                <a href="javascript:void(0);" class="txt_box" id="txt_box">
                    <?php
                    $search_txt=$V->get_field_str('search_type',$_GET['search_type']);
                    ?>
                    <div class="sel_inp" id="sel_inp"><?php echo($search_txt);?></div>
                    <input type="hidden" name="search_type" id="search_type" value="<?php echo($_GET['search_type']);?>" class="sel_subject_val">
                </a>
                <div class="sel_list" id="sel_list" style="display:none;">
                    <?php foreach($global_vars['search_type'] as $v){
                        echo('<a href="javascript:void(0);" value="'.$v['value'].'" class="" onclick="show_top_hot(this);">'.$v['txt'].'</a>');
                    }?>
                </div>
            </div>
        </div>
        <input type="text" id="kw" name="kw" value="<?php echo($_GET['kw']);?>" class="search_txt" onfocus="document.getElementById('lab_txt').style.display = 'none'" onblur="this.value == '' ?  document.getElementById('lab_txt').style.display='block' : document.getElementById('lab_txt').style.display = 'none'" onkeyup="if(event.keyCode==13) do_search();"/>
        <label for="kw" id="lab_txt"><?php if($_GET['kw']==''){?>请输入要搜索的关键词<?php } ?></label>
        <a href="javascript:void(0);" onclick="do_search();" class="search_btn"></a>
    <?php
    }
    /**
     * 右侧登录注册
     */
    public function show_top_right(){
        global $dbm,$quan_open,$msg_noread;

        session_open();
        $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();

        if($uid>0) {
            //判断是否开通了圈子
            $sql="select count(*) as t from ".DB_DBNAME.".ws_quan where uid='$uid'";
            $quan_open=$dbm->scalar($sql);
            //查询未读消息
            $sql="select count(*) as t from ".DB_DBNAME_USER.".ws_message where uid_to='$uid' and read_time=0";
            $msg_noread=$dbm->scalar($sql);

//          echo '<span id="login_nav"><a href="'.DOMAIN_USER.'/app/user/index.php" class="login_btn login_end l"><i>'.($session_user['thumb_avatar']?'<img onerror="$(this).remove();" src="'.$session_user['thumb_avatar'].'"/>':'').'</i>'.$session_user['nick_name'].'</a><dl class="child_cz pa">
//                          <dd><a class="ct_ico1" href="'.$this->surl('page',$uid).'" target="_blank">我的团队主页</a><a href="'.DOMAIN_USER.'/app/user/page.php" class="el_link">设置</a></dd>
//                          <dd><a class="ct_ico8" href="'.DOMAIN_USER.'/app/user/page.info.php?type=my&cate_id=2">产品管理</a><a class="el_link" href="'.DOMAIN_USER.'/app/user/page.info.php?type=new&cate_id=2">发布</a></dd>
//                          <dd><a class="ct_ico9" href="'.DOMAIN_USER.'/app/user/page.info.php?type=my&cate_id=1">视频管理</a><a class="el_link" href="'.DOMAIN_USER.'/app/user/page.info.php?type=new&cate_id=1">发布</a></dd>
//                          <dd><a class="ct_ico4" href="'.DOMAIN_USER.'/app/user/page.info.php?type=my">文章管理</a><a class="el_link" href="'.DOMAIN_USER.'/app/user/page.info.php?type=new">发布</a></dd>
//                          <dd><a class="ct_ico5" href="'.DOMAIN_USER.'/app/user/message.php?type=inbox">我的私信(<font color="red"><b>'.$msg_noread['t'].'</b></font>)</a></dd>
//                          <dd><a class="ct_ico10" href="'.DOMAIN_USER.'/app/user/qun.php?type=my">我的微信群</a><a href="'.DOMAIN_USER.'/app/user/qun.php?type=new" class="el_link">发布</a></dd>
//                          ';
            echo '<span id="login_nav"><a href="'.DOMAIN_USER.'/app/user/index.php" class="login_btn login_end l"><i>'.($session_user['thumb_avatar']?'<img onerror="$(this).remove();" src="'.$session_user['thumb_avatar'].'"/>':'').'</i>'.$session_user['nick_name'].'</a><dl class="child_cz pa">
                            
                            <dd><a class="ct_ico5" href="'.DOMAIN_USER.'/app/user/message.php?type=inbox">我的私信(<font color="red"><b>'.$msg_noread['t'].'</b></font>)</a></dd>
                            <dd><a class="ct_ico10" href="'.DOMAIN_USER.'/app/user/qun.php?type=my">我的微信群</a><a href="'.DOMAIN_USER.'/app/user/qun.php?type=new" class="el_link">发布</a></dd>
                            ';
//          if($quan_open['t']>0) {
//              echo('<dd><a class="ct_ico3" href="' . DOMAIN_WWW . '/app/quan/view.php?id=' . $uid . '">管理自家圈子</a><a href="' . DOMAIN_USER . '/app/user/quan.php?type=my" class="el_link">设置</a></dd>');
//          }else{
//              echo('<dd><a class="ct_ico3" href="' . DOMAIN_USER . '/app/user/quan.php?type=my">申请开通社区圈子</a></dd>');
//          }
//          echo('          <dd><a class="ct_ico6" href="'.DOMAIN_USER.'/app/user/info.php">账户设置</a><a class="el_link" href="'.DOMAIN_USER.'/app/user/password.php">修改密码</a></dd>
//                          <dd>');
//          if(isset($_COOKIE['is_quit_sugges'])) {
//              echo('<a class="ct_ico7" href="'.DOMAIN_USER.'/app/api/common.ver1.0.php?m=logout" id="quit_s">安全退出</a>');
//          } else {
                echo('<a class="ct_ico7" href="javascript:void(0);" id="quit_s" onclick="show_quit();">安全退出</a>');
//          }
            echo('</dd>
                        </dl></span>');
            //echo '<a href="'.DOMAIN_USER.'/app/api/common.ver1.0.php?m=logout" class="register_btn l">安全退出</a>';
        }else{
            echo '<a href="'.DOMAIN_USER.'/app/user/register.php" class="register_btn l">免费注册</a>';
            echo '<span id="login_w"><a href="'.DOMAIN_USER.'/app/user/login.php" class="login_btn l"><i></i>请登录</a><div class="login_dialog ue_animation pa" id="login_div">
                    <iframe src="'.DOMAIN_USER.'/app/user/login.php?tpl=dom" width="210" height="220" border="0" frameborder="0"></iframe>
                </div></span>';
        }
    }

    /**
    * PC 退出弹窗
    */
    public function show_quit(){
        ?>
            <div class="fq_wrap"></div>
            <div class="ft_quit">
                <div class="fq_tit">
                    <h3>真的不能再爱了吗？ <a href="javascript:void(0);" class="close_quit r">×</a></h3>
                </div>
                <p>Dear小伙伴，退出前能告诉我们原因吗？</p>
                <p class="line-t-6"></p>
                <ul class="fq_form" id="fq_form">
                    <li>
                        <span><input type="checkbox" id="fq_cbx1" class="chk_list" value="没有我需要的内容" /><label for="fq_cbx1">没有我需要的内容</label></span>
                        <span><input type="checkbox" id="fq_cbx2" class="chk_list" value="对弹层提醒无法再爱" /><label for="fq_cbx2">对弹层提醒无法再爱</label></span>
                    </li>
                    <li>
                        <span><input type="checkbox" id="fq_cbx3" class="chk_list" value="功能体验屋里吐槽" /><label for="fq_cbx3">功能体验屋里吐槽</label></span>
                        <span><input type="checkbox" id="fq_cbx4" class="chk_list" value="我就钟爱非登录首页" /><label for="fq_cbx4">我就钟爱非登录首页</label></span>
                    </li>
                    <li class="fq_txt">
                        <p>更多槽点吐这里哦：</p>
                        <textarea id="content"></textarea>
                    </li>
                </ul>
                <div class="fq_footer">
                    <?php
                        if(isset($_COOKIE['is_quit_sugges'])) {
                            echo('<a href="'.DOMAIN_USER.'/app/api/common.ver1.0.php?m=logout" class="fq_btn ff_q">残忍退出</a>');
                        } else {
                            echo('<a href="javascript:void(0);" onclick="user_quit();" class="fq_btn ff_q">残忍退出</a>');
                        }
                    ?>
                    <a href="javascript:void(0);" class="fq_btn no_ff_q">施舍机会</a>
                </div>
            </div>
        <?php
    }
    /**
     * 手机版 会员中心首页
     */
    public function show_sidebar_wap() {
        global $Q;
        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        if($uid<=0) return '';
        ?>
            <div class="sidewrap_x" id="sidewrap_x"></div>
            <section id="sidebar_x" class="sidebar_x">
                <div class="cnt_nav">
                    <a href="javascript:void(0);" onclick="show_send_message('','');" class="inbox"><i></i>发私信</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/message.php?type=inbox" class="sibox"><i></i>收件箱</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/message.php?type=outbox" class="outbox"><i></i>发件箱</a>
                </div>
                <!--
                <div class="cnt_nav">
                    <a href="<?php //echo DOMAIN_USER;?>/app/user/qun.php?type=new" class="art_manageer"><i></i>发布微商群</a>
                    <a href="<?php //echo DOMAIN_USER;?>/app/user/qun.php?type=my" class="my_msg"><i></i>我发布的</a>
                    <a href="<?php //echo DOMAIN_USER;?>/app/user/qun.php?type=fav" class="my_coll"><i></i>我收藏的</a>
                </div>
                
                <div class="cnt_nav">
                    <a href="<?php echo DOMAIN_USER;?>/app/user/quan.post.php" class="my_topic"><i></i>我发布的话题</a>
                    <?php
                        if($Q->get_quan($uid,'uid')) {
                            echo '<a href="'.DOMAIN_WWW.'/app/quan/view.php?id='.$uid.'" class="my_circle"><i></i>查看自家圈子</a>';
                            echo '<a href="'.DOMAIN_USER.'/app/user/quan.php?type=my" class="my_quan_set"><i></i>圈子设置</a>';
                        }else{
                            echo '<a href="'.DOMAIN_USER.'/app/user/quan.php?type=my" class="my_set"><i></i>申请开通圈子</a>';
                        }
                    ?>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/quan.php?type=quan_follow" class="my_att_circle"><i></i>我关注的圈子</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/quan.php?type=page_follow" class="my_att_der"><i></i>我关注的微商</a>
                </div>
                <div class="cnt_nav">
                    <a href="<?php echo DOMAIN_USER;?>/app/user/page.php" class="my_set"><i></i>主页设置</a>
                </div>
                <div class="cnt_nav">
                    <a href="<?php echo DOMAIN_USER;?>/app/user/product.php?type=new" class="rel_product"><i></i>发布产品</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/product.php" class="my_msg"><i></i>产品管理</a>
                </div>
                <div class="cnt_nav">
                    <a href="<?php echo DOMAIN_USER;?>/app/user/info.php" class="edit_data"><i></i>基本设置</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/password.php" class="edit_pwd"><i></i>修改密码</a>
                </div>-->
                <div class="cnt_nav">
                    <a href="<?php echo DOMAIN_USER;?>/app/api/common.ver1.0.php?m=logout" class="exit"><i></i>安全退出</a>
                </div>
            </section>
        <?php
    }
    /**
     * 手机版 顶部大栏目导航
     */
    public function show_nav_wap() {
        session_open();
        $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        ?>
        <header>
            <span class="hd_left l">
                <?php if(isset($session_user['avatar'])) {?>
                    <a class="log_ico"  href="javascript:void(0);"> <!-- href="<?php echo DOMAIN_USER;?>/app/user/index.php"-->
                        <img src="<?php echo $session_user['avatar'];?>" onerror="$(this).remove();"/>
                    </a>
					<a href="<?php echo($this->surl('page',$session_user['uid']));?>">我的主页</a>

                <?php }else{?>
                    <a class="log_ico" href="<?php echo DOMAIN_USER;?>/app/user/login.php"></a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/login.php">请登录</a>
                    <a href="<?php echo DOMAIN_USER;?>/app/user/register.php">免费注册</a>
                <?php } ?>
            </span>
            <a class="home r" href="<?php echo DOMAIN_WWW;?>"></a>
        </header>
        <?php
    }

    /**
     * 手机版 底部
     */
    public function show_bot_wap() {
        global $dbm;
        ?>
        <footer>
            <a href="<?php echo DOMAIN_WWW.'/app/quan/picked.quan.php'?>" class="i_qun"><i></i><span>产品</span></a>
            <a href="<?php echo DOMAIN_WWW.'/app/quan/list.php'?>" class="i_quan"><i></i><span>社区</span></a>
            <a href="<?php echo DOMAIN_WWW.'/app/home/list.php'?>" class="i_shang"><i></i><span>微商</span></a>
            <a href="<?php echo DOMAIN_WWW.'/app/search/'?>" class="i_search"><i></i><span>搜索</span></a>
        </footer>
        <div style="display:none;"><script src="http://s11.cnzz.com/stat.php?id=1254701924&web_id=1254701924" language="JavaScript"></script></div>
        <?php
        unset($dbm);
    }
    /**
     * 顶部大栏目导航
     */
    public function show_nav(){
        $arr = array(
            array('tag'=>'qun','name'=>'微信群','url'=>DOMAIN_WWW.'/app/qun/','target'=>'_self'),
            array('tag'=>'quan','name'=>'微商社区','url'=>DOMAIN_WWW.'/app/quan/','target'=>'_self'),
            array('tag'=>'home','name'=>'微商团队','url'=>DOMAIN_WWW.'/app/home/','target'=>'_self'),
            array('tag'=>'fx','name'=>'加粉神器','url'=>DOMAIN_TOOL.'/','target'=>'_blank'),
        );
        foreach ($arr as $k=>$v ) {
            $style = '';
            if(strpos($_SERVER['PHP_SELF'],$v['tag'])) $style = 'current';
            echo '<li class="nav1"><a class="'.$style.'" href="'.$v['url'].'" target="'.$v['target'].'">'.$v['name'].'</a></li>';
        }
    }
    /**
     * 顶部导航
     */
    public function show_top_nav() {
        session_open();
        $session_user=isset($_SESSION['user'])?$_SESSION['user']:'';
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        ?>
            <link rel="stylesheet" type="text/css" href="<?php echo DOMAIN_CSS;?>/public/top.nav.css">
            <div class="header_top">
                <div class="WB_global_nav">
                    <div class="gn_header">
                        <div class="logo_im"><a href="<?php echo DOMAIN_WWW;?>"><img src="<?php echo(DOMAIN_CSS);?>/www/images/logo1.png"></a></div>
                        <div class="search inp_hear">
                            <?php $this->show_search();?>
                        </div>
                        <div class="gn_right r">
                            <div class="gn_nav">
                                <ul>
                                    <?php $this->show_nav();?>
                                </ul>
                            </div>
                            <div class="r_dl r">
                                <?php
                                    if($uid>0) {
                                ?>
                                <a href="<?php echo DOMAIN_USER;?>" class="deng">
                                    <i>
                                       <img onerror="$(this).remove();" src="<?php echo $session_user['thumb_avatar'];?>"/>
                                    </i>
                                </a>
                                <a href="<?php echo DOMAIN_USER;?>/app/api/common.ver1.0.php?m=logout" class="zu" id="quit_s">
                                    安全退出
                                </a>
                                <?php } else{ ?>
                                <a href="<?php echo DOMAIN_USER;?>" class="deng">
                                    <i></i>
                                    登录
                                </a>
                                <a href="<?php echo DOMAIN_USER;?>/app/user/register.php" class="zu">
                                    注册
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
    /**
     * 底部
     */
    public function show_bot(){
        global $P,$time_start,$dbm;
    ?>
        <p class="line-t-30"></p>
        <div class="footer_wrap" style="height:320px;">
            <div class="footer">
                <p class="line-t-30"></p>
                <p><img src="<?php echo(DOMAIN_CSS);?>/www/images/ft_i.png" /></p>
                <p class="line-t-30"></p>
                <p class="line-t-20"></p>
                <p>
                <?php
                    $nav = $P->get_page_nav_auto(76);
                    foreach($nav as $k=>$v) {
                        if($k>0) echo('|');
                        echo('<a href="'.$v['nav_url'].'" target="_blank">'.$v['nav_name'].'</a>');
                    }
                    echo('|<a href="'.$this->surl('quan','38').'" target="_blank">问题反馈</a>');
                    echo('|<a href="http://m.kuaidi100.com" target="_blank">快递查询</a>');
                    echo('|&nbsp;&nbsp;&nbsp;&nbsp;服务热线 4000-96-56-76&nbsp;&nbsp;&nbsp;&nbsp;市场合作QQ：509411334 周小姐');
                ?>
               </p>
                <p class="line-t-20"></p>
                <span>© 2012-2015 HuiWeiShang.COM 版权所有 赣ICP备10004530号 <font style="font-size:11px;">Processed in <?php echo(number_format((H::getmicrotime()-$time_start)/1000,2));?> second(s)</font></span>
                <p class="line-t-20"></p>
                <a href="http://webscan.360.cn/index/checkwebsite/url/www.huiweishang.com"><img border="0" src="http://img.webscan.360.cn/status/pai/hash/2d575b3e43490f22a172f59d25c137ae"/></a>
            </div>
            <a href="javascript:void(0)" target="_self" title="返回顶部" id="toTop"></a>
            <div style="display:none;"><script src="http://s11.cnzz.com/stat.php?id=1254701924&web_id=1254701924" language="JavaScript"></script></div>
        </div>
    <?php
    unset($dbm);
    }

    public function show_msg_div() {
    ?>
        <!-- pc私信-->
        <div id="show_send_message_div" style="display:none;">
            <p class="line-t-20"></p>
            <ul class="lr_form release_form">
                <li>
                    <?php if(USER_DEV == 'pc') echo '<span class="tit_span"><i>*</i>收件人：</span>';?><input type="text" class="lr_ipt" id="uid_to" value="<?php echo isset($_GET['uid_to']) ? urldecode($_GET['uid_to']) : '';?>" placeholder="收件人"/>

                </li>
                <!--<li>
                    <?php if(USER_DEV == 'pc') echo '<span class="tit_span"><i>*</i>标题：</span>';?><input type="text" class="lr_ipt" id="title" value="<?php echo isset($_GET['title']) ? urldecode($_GET['title']) : '';?>" placeholder="标题"/>
                </li>-->
                <li class="txt_li" style="height:100px;">
                    <?php if(USER_DEV == 'pc') echo '<span class="tit_span"><i>*</i>内容：</span>';?>
                    <textarea id="message" class="lr_ipt" style="max-width:400px;height:60px" placeholder="内容"></textarea>
                </li>
                <li>
                    <?php if(USER_DEV == 'pc') echo '<span class="tit_span">&nbsp;</span>';?>
                    <div class="quanzi_btn l fl_none">
                        <p class="line-t-6"></p>
                        <a href="javascript:void(0);" class="replay_btn" id="saveinfo" onclick="send_message()">发送</a>
                    </div>
                </li>
            </ul>
        </div>
    <?php
    }
    //分享代码
    public function share_code(){
    ?>
        <div id="site_share_div" class="bdsharebuttonbox"><a href="#" class="bds_more" data-cmd="more"></a><a title="分享到QQ好友" href="#" class="bds_sqq" data-cmd="sqq"></a><a title="分享到微信" href="#" class="bds_weixin" data-cmd="weixin"></a><a title="分享到QQ空间" href="#" class="bds_qzone" data-cmd="qzone"></a><a title="分享到新浪微博" href="#" class="bds_tsina" data-cmd="tsina"></a><a title="分享到人人网" href="#" class="bds_renren" data-cmd="renren"></a><a title="分享到豆瓣网" href="#" class="bds_douban" data-cmd="douban"></a></div>
        <script>with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion='+~(-new Date()/36e5)];</script>
    <?php
    }

    //二维码输出
    public function qrcode_url($type,$id){
        if(in_array($type,array('num','page','quan','post','info'))) {
            $dir='/qrcode/'.$type.'/'.($id%10000).'/';
            $file=$id.'.png';
            echo('<img class="qrcode" src="'.DOMAIN_UPLOAD.'/'.$dir.$file.'" onerror="show_img_delay(this,\''.DOMAIN_UPLOAD.'/upload/qrcode.php?type='.$type.'&id='.$id.'\',1);"/>');

        }
    }

   /**
     * 获取评论列表
     *
     * @$params
     */
    public function get_comment($source_id,$parent_id=0) {
        global $U;
        $pagesize = 5;
        $sql = "select * from " . DB_DBNAME . ".ws_comment where  parent_id='$parent_id' and source_id='$source_id' order by create_time asc";
        $rs = $this->dbm -> query($sql);
        $i=0;
        foreach($rs['list'] as $k=>$v) {
            $user = $U->get($v['uid']);
            $rs['list'][$k]['uname'] = $user['login_name'];
            $rs['list'][$k]['avatar'] = $user['avatar'];
            $sql = "select * from " . DB_DBNAME . ".ws_comment where  parent_id='".$v['comment_id']."' and source_id='$source_id' order by create_time asc";
            $ret = $this->dbm->query($sql);
            foreach($ret['list'] as $k2=>$v2) {
                $user = $U->get($v2['uid']);
                $ret['list'][$k2]['uname']=$user['login_name'];
                $ret['list'][$k2]['avatar']=$user['avatar'];
            }
            $rs['list'][$i]['son'] = $ret['list'];
            $i++;
        }
        return $rs;
    }
    //跨域方法===============================
    //发私信
    public function m__save_message(){
        global $dbm,$U,$result;
        check_login();check_mobile();

        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();

        $_POST = H::filter_txt($_POST);
        $_GET = H::filter_txt($_GET);

        $_POST['uid_to'] = isset($_POST['uid_to']) ? trim($_POST['uid_to']) : '';
        $_POST = H::sqlxss($_POST);
        $_GET = H::sqlxss($_GET);

        if($_POST['uid_to'] == '') H::error_show('{"code":"1","msg":"请填写收件人","id":"uid_to"}');
        //if($_POST['title'] == '') H::error_show('{"code":"1","msg":"请填写标题","id":"title"}');
        if($_POST['message'] == '') H::error_show('{"code":"1","msg":"请填写内容","id":"message"}');

        $_POST['uid_to'] = explode(',',str_replace('，',',',$_POST['uid_to']));

        $user = array();
        $tmp = array();
        foreach ($_POST['uid_to'] as $k=>$v ) {
            $v = trim($v);
            if(empty($v)) continue;
            $sql = "select uid from ".DB_DBNAME_USER.".ws_user where binary nick_name='{$v}' limit 1";
            $uinfo = $dbm->query($sql);
            if(count($uinfo['list']) != 1) {
                $tmp[] = $v;
            }else{
                $user[$k] = $uinfo['list'][0];
                if($user[$k]['uid'] == $uid) {
                    H::error_show('{"code":"1","msg":"不能给自己发送私信","id":"uid_to"}');
                }else{
                    $user[$k] = $user[$k]['uid'];
                }
            }
        }
        if(count($tmp)) H::error_show('{"code":"1","msg":"收件人 “'.implode('”,“',$tmp).'” 不存在","id":"uid_to"}');
        $user = array_unique($user);
        $fields = array(
            'uid_from'=>$uid,
            //'title'=>$_POST['title'],
            'message'=>$_POST['message'],
            'post_time'=>time(),
            'msg_type'=>0,
        );
        foreach ($user as $k=>$v ) {
            $fields['uid_to'] = $v;
            $dbm->single_insert(DB_DBNAME_USER.".ws_message",$fields);
        }
        H::error_show('{"code":"0","msg":"私信发送成功"}');
    }
    //获取代码树子集
    public function m__get_code_son(){
        global $T,$V,$dbm;
        $_POST = H::sqlxss($_POST);
        $_GET = H::sqlxss($_GET);
        $_POST['code_id'] = isset($_POST['code_id']) ? intval($_POST['code_id']) : -1;
        $_POST['is_get'] = isset($_POST['is_get']) ? intval($_POST['is_get']) : 0; //是否获取下一级
        $_POST['width'] = isset($_POST['width']) ? intval($_POST['width']) : 254;
        $_POST['div_id'] = isset($_POST['div_id']) ? trim($_POST['div_id']) : '';
        $_POST['def'] = isset($_POST['def']) ? trim($_POST['def']) : '请选择';
        $_POST['next_id'] = isset($_POST['next_id']) ? trim($_POST['next_id']) : 'abc100';

        $_POST['div_id'] = explode('__',$_POST['div_id']);
        // 注意联动级别每一级的容器id命名规律是 名称+双下划线+数字 如 get_code_son__1 get_code_son__2 get_code_son__3
        $_POST['div_id'] = $_POST['div_id'][0].'__'.($_POST['div_id'][1]+1);

        $def_html = '<div style="width:'.$_POST['width'].'px;z-index: 1;" class="sel_box">';
        $def_html .= '    <a id="txt_box" class="txt_box" href="javascript:void(0);">';
        $def_html .= '        <div id="sel_inp" class="sel_inp">';
        $def_html .= '            '.$_POST['def'].'';
        $def_html .= '        </div>';
        $def_html .= '        <input type="hidden" class="sel_subject_val" value="0" id="'.$_POST['next_id'].'" name="'.$_POST['next_id'].'"/>';
        $def_html .= '    </a>';
        $def_html .= '</div>';

        if(count($T->trees($_POST['code_id']))<=1) die ('{"code":"0","msg":"拉取数据成功","html":'.H::json_encode_ch($def_html).'}');
        $tmp_tree=$T->trees($_POST['code_id']);
        if(count($tmp_tree['son']) == 0) die ('{"code":"0","msg":"拉取数据成功","html":'.H::json_encode_ch($def_html).'}');

        $on = '';
        if($_POST['is_get']) $on = 'get_code_son(this,\''.$_POST['div_id'].'\','.$_POST['is_get'].')';

        $V->set_fields($_POST['next_id'],$T->get_code_son($_POST['code_id']));
        $V->set_field($_POST['next_id'],array('value' =>0, 'txt' =>$_POST['def'], 'txt_color' => ''));
        $str = $V->input_str(array('node'=>$_POST['next_id'],'type'=>'select_single','default'=>0,'style'=>'style="width:'.$_POST['width'].'px;z-index: 1;"','on'=>$on));
        unset($dbm);
        die ('{"code":"0","msg":"拉取数据成功","html":'.H::json_encode_ch($str).'}');
    }
    //关注微商
    public function m__save_page_follow(){
        global $dbm,$P,$U;
        check_login();

        $page_uid = isset($_POST['page_uid'])?intval($_POST['page_uid']):0; //主页id
        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        $ac = isset($_POST['ac'])?trim($_POST['ac']):''; //动作

        if($page_uid <= 0) H::error_show('{"code":"1","msg":"主页ID不正确"}');
        if($page_uid == $uid) H::error_show('{"code":1,"msg":"请不要关注自己的主页"}');

        $fields = array('uid'=>$uid,'page_uid'=>$page_uid);

        if($ac=='add') {
            $msg = $P->update_page_follow($fields,'add');
            //关注微商加积分
            $U->update_point(array('uid'=>$uid,'code_id'=>11445));
        } else {
            $msg = $P->update_page_follow($fields,'cancel');
            //取消关注微商扣积分
            $U->update_point(array('uid'=>$uid,'code_id'=>11446));
        }
        $U->update_count($uid,array('page'));
        H::error_show('{"code":"0","msg":"'.$msg.'"}');
    }
    //关注圈子
    public function m__save_quan_follow(){
        global $dbm,$Q,$U;
        check_login();
        $quan_uid = isset($_GET['uid'])?intval($_GET['uid']):0;
        $ac = isset($_GET['ac'])?trim($_GET['ac']):'';
        if($quan_uid <=0) H::error_show('{"code":"1","msg":"圈子ID出错"}');
        session_open();
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();
        //不能关注自己创建的圈子
        if($quan_uid==$uid) H::error_show('{"code":1,"msg":"请不要关注自己创建的圈子"}');
        $fields = array('uid'=>$uid,'quan_uid'=>$quan_uid);
        if($ac=='add') {
            $Q->update_quan_follow($fields,'add');
            $msg = '关注成功';
            //关注微商圈加积分
            $U->update_point(array('uid'=>$uid,'code_id'=>11445));
        } else {
            $Q->update_quan_follow($fields,'cancel');
            $msg = '成功取消关注';
            //取消关注微商圈扣积分
            $U->update_point(array('uid'=>$uid,'code_id'=>11446));
        }
        $U->update_quan_count($quan_uid,array('quan'));
        H::error_show('{"code":"0","msg":"'.$msg.'"}');
    }

    //用户安全退出
    function m__user_quit() {
        global $dbm;
        $_POST = H::sqlxss($_POST);
        $fields = array();
        $content = isset($_POST['content'])?trim($_POST['content']):'';
        $sugges = isset($_POST['sugges'])?$_POST['sugges']:'';
        if($content!='') array_push($fields, $content);
        if($sugges!='') $fields = array_merge($fields, explode(',',$sugges));
        //die(print_r($fields));
        if(count($fields)>0) {
            foreach($fields as $v) {
                if(strlen($v)<1) continue;
                $result = $dbm->query("select content from ".DB_DBNAME.".ws_sugges where content='$v' limit 1");
                if(count($result['list'])==1) {
                    $sql = "update ".DB_DBNAME.".ws_sugges set ctotal=ctotal+1 where content='$v'";
                    $dbm->query_update($sql);
                } else {
                    $dbm->single_insert(DB_DBNAME.".ws_sugges",array("content"=>$v,'create_time'=>time(),'ctotal'=>1));
                }
            }
        }
        H::error_show('{"code":"0","msg":"意见录入成功"}');
    }

    /**
     * 获取广告位
     * @param $area_id 广告位id
     * @param $is_return 是否直接返回 图片数组 默认 0=不返回
     */
    public function get_ad($area_id,$is_return=0) {
        $rs = $this->dbm->query("select * from ".DB_DBNAME.".ws_ad where area_status=1 and area_id=".intval($area_id)." limit 1");
        if(count($rs['list']) ==0) return '广告位ID有误';
        //代码广告
        if($rs['list'][0]['area_type'] == 'code') {echo htmlspecialchars_decode($rs['list'][0]['area_html']); return;}
        $rs['img_list'] = $this->dbm->query("select * from ".DB_DBNAME.".ws_adv_img where img_status=0 and area_id=".intval($area_id)." order by img_order asc");
        //直接返回
        if($is_return) return $rs['img_list']['list'];
        //单张图片直接输出
        if(count($rs['img_list']['list']) == 1) {
            echo('<li>');
            if($rs['img_list']['list'][0]['go_url']) {
                echo '<a href="'.$rs['img_list']['list'][0]['go_url'].'"><img src="'.$rs['img_list']['list'][0]['img_url'].'" onerror="img_err(this)"/></a>';
            }else{
                echo '<img src="'.$rs['img_list']['list'][0]['img_url'].'" onerror="img_err(this)"/>';
            }
            echo('</li>');
            return;
        }
        //遍历输出
        foreach($rs['img_list']['list'] as $k=>$v) {
            echo '<li><a href="'.$v['go_url'].'"><img src="'.$v['img_url'].'" onerror="img_err(this)" alt="'.$v['img_title'].'"/></a></li>';
        }
    }
    //注册
    function m__register() {
        global $dbm,$U;
        //H::error_show('{"code":1,"msg":"服务器维护，暂停注册1","id":"login_mobile"}');
        $fields = array();
        foreach($_POST as $k=>$v){
            if($k != 'login_pass' && $k != 'login_pass_repeat') $_POST[$k] = H::sqlxss($v);
        }
        
        $fields['login_name'] = isset($_POST['login_name']) ? strtolower(trim($_POST['login_name'])) : '';
        $fields['login_pass'] = isset($_POST['login_pass']) ? $_POST['login_pass']:'';
        $_POST['login_pass_repeat'] = isset($_POST['login_pass_repeat']) ? $_POST['login_pass_repeat'] : '';
        $hwsid = isset($_COOKIE['hwsid'])?intval($_COOKIE['hwsid']):0;
        $fields['login_mobile'] = isset($_POST['login_mobile']) ? $_POST['login_mobile'] : '';
        $fields['wx'] = isset($_POST['wx'])?H::filter_txt($_POST['wx']):'';
        $_POST['receive'] = isset($_POST['receive']) ? intval($_POST['receive']) : 0;
        $_POST['mcode'] = isset($_POST['mcode']) ? intval($_POST['mcode']) : 0;//手机验证码
        $_POST['from_type'] = isset($_POST['from_type'])?trim($_POST['from_type']):'';
        $fields['nick_name'] = isset($_POST['nick_name'])&&trim($_POST['nick_name'])!=''?trim($_POST['nick_name']):$fields['login_name'];
        
        $verify = verify::verify_uname($fields['login_name']);
        //die($verify);
        if($verify != '') H::error_show('{"code":1,"msg":"'.$verify.'","id":"login_name"}');
        if($fields['login_name']=='') H::error_show('{"code":1,"msg":"请填写帐号","id":"login_name"}');

        $user = $U->get($fields['login_name'],'uid',2);
        if($user) H::error_show('{"code":1,"msg":"帐号已经存在","id":"login_name"}');

        $verify_pass = verify::verify_upass($fields['login_pass']);
        if($verify_pass != '') H::error_show('{"code":1,"msg":"'.$verify_pass.'","id":"login_pass"}');
        /*
        if($fields['login_pass'] != $_POST['login_pass_repeat']) {
            H::error_show('{"code":1,"msg":"确认密码与上面不一致","id":"login_pass_repeat"}');
        }
        */

        $fields['province'] = isset($_POST['province'])?intval($_POST['province']):0;
        $fields['district'] = isset($_POST['district'])?intval($_POST['district']):0;
        $_POST['trade'] = isset($_POST['trade'])?intval($_POST['trade']):0;

        if($_POST['from_type']=='from_tools') {
            //if($_POST['trade']==0) H::error_show('{"code":1,"msg":"请选择行业","id":"province"}');
            //if($fields['province']==0) H::error_show('{"code":1,"msg":"请选择省份","id":"province"}');
            //if($fields['district']==0) H::error_show('{"code":1,"msg":"请选择市区","id":"district"}');
            $fields['reg_type']=3;
        }
        //if($fields['wx']=='') H::error_show('{"code":1,"msg":"请填写微信号","id":"wx"}');

        $verify = verify::verify_mobile($fields['login_mobile']);
        if($verify != '') H::error_show('{"code":1,"msg":"请填写正确的手机号码","id":"login_mobile"}');

        //验证手机号码唯一性
        $sql="select uid from ".DB_DBNAME_USER.".ws_user force index(login_mobile) where login_mobile='{$fields['login_mobile']}' limit 2";
        $rs=$dbm->query($sql);
        if(count($rs['list'])>0) H::error_show('{"code":1,"msg":"手机号码已经被使用，请更换手机号码","id":"login_mobile"}');
        
        if($_POST['mcode']<=0) H::error_show('{"code":1,"msg":"短信验证码错误","id":"login_name"}');
        
        
        
        //查询填写的验证码是否正确
        global $global_global;
        $myredis=new myredis($global_global);
        $vcode_true=$myredis->is_true_code('verify_',$fields['login_mobile'],$_POST['mcode']);
        unset($myredis);
        if(!$vcode_true) die('{"code":1,"msg":"短信验证码错误","id":"mcode"}');
    

        if($_POST['receive'] != 1) H::error_show('{"code":1,"msg":"请选择同意协议","id":"receive"}');

        $fields['login_group'] = 2; //默认注册到 注册用户组
        $user_autoid = $U->add($fields);

        if($user_autoid) {
            
            $page_fields = array(
                'uid'=>$user_autoid,
                'page_title'=>$fields['nick_name'],
                'create_time'=>time(),
                'page_province'=>$fields['province'],
                'page_district'=>$fields['district'],
                'page_trade'=>$_POST['trade'],
                'page_theme'=>'default',
            );
            $dbm->single_insert(DB_DBNAME.".ws_page",$page_fields);

            //插入环信数据
            $huanxin_upass = rand(100000,999999).$user_autoid;
            $reg_status = 0;
            $dbm->single_insert(
                DB_DBNAME_USER.".ws_user_huanxin",
                array('reg_status'=>$reg_status,'uid'=>$user_autoid,'huanxin_upass'=>$huanxin_upass)
            );

            $group = get_user_group_level($fields['login_group']);
            session_open();
            $_SESSION['user']['group_name']=implode(',',$group['group_name']);//组名称
            $_SESSION['user']['group_level']=implode(',',$group['group_level']);//组权限
            $_SESSION['user']['login_level']='';//附加权限
            $_SESSION['user']['login_no_level']='';//禁止权限
            $_SESSION['user']['avatar'] = '';
            $_SESSION['user']['thumb_avatar'] = '';
            $_SESSION['user']['login_mobile'] = $fields['login_mobile'];
            $_SESSION['user']['uid'] = $user_autoid;
            $_SESSION['user']['login_name'] = $fields['login_name'];
            $_SESSION['user']['nick_name'] = empty($fields['nick_name']) ? $fields['login_name'] : $fields['nick_name'];
            $_SESSION['user']['login_from']='front';
            session_close();
            //注册赠送积分
            $U->update_point(array('uid'=>$user_autoid,'code_id'=>11438));
            /*注册写入推荐人*/
            if($hwsid>0) {
                $sql="select uid from ".DB_DBNAME_USER.".ws_user where uid='$hwsid' limit 1";
                $ret = $dbm->query($sql);
                if(count($ret['list'])==1) {
                    $rec_user = array('uid'=>$user_autoid,'uid_recommend'=>$hwsid,'create_time'=>time());
                    $dbm->single_insert(DB_DBNAME_USER.".ws_recommend_reg", $rec_user);
                    //推荐人赠送积分
                    $U->update_point(array('uid'=>$hwsid,'code_id'=>11494));

                    //被推荐用户注册赠送100名额
                    $sql = " update ".DB_DBNAME_TOOL.".ws_fans_stat set can_use=can_use+".RECOMMEND_REG.",rec_total=rec_total+1 where uid='$hwsid'";
                    $dbm->query_update($sql);
                }

            }
            //新用注册赠送200名额
            $dbm->single_insert(DB_DBNAME_TOOL.".ws_fans_stat",
                array('uid'=>$user_autoid,'can_use'=>TRADE_INIT,'rec_total'=>0,'add_total'=>0));
            H::error_show('{"code":0,"msg":"注册成功！","id":""}');
        }

        H::error_show('{"code":1,"msg":"注册失败，请联系管理员!","id":""}');
    }

    function m__check_mobile(){
        global $dbm;
        $_POST = H::sqlxss($_POST);
        $sql="select uid from ".DB_DBNAME_USER.".ws_user where login_mobile='{$_POST['login_mobile']}' limit 2";
        $rs=$dbm->query($sql);
        if(count($rs['list'])>0) {
            H::error_show('{"code":1,"msg":"手机号码已经被使用，请更换手机号码","id":"login_mobile"}');
        } else {
            H::error_show('{"code":0,"msg":"手机号码正确","id":"login_mobile"}');
        }
    }
    /*输入的图形验证码是否正确*/
    function m__check_code(){
        global $dbm;
        //H::error_show('{"code":1,"msg":"数据故障，无法发送验证码","id":"mcode"}');
        //H::error_show('{"code":1,"msg":"服务器维护，暂停注册2","id":"login_mobile"}');
        $fields=array();
        $_POST = H::sqlxss($_POST);
        $_POST['vcode'] = isset($_POST['vcode'])?trim($_POST['vcode']):'';

        session_open();
        $session_verify_reg=isset($_SESSION['verify']['reg'])?$_SESSION['verify']['reg']:'';
        $uid=isset($_SESSION['user']['uid'])?intval($_SESSION['user']['uid']):0;
        session_close();

        if($session_verify_reg!=md5(strtoupper($_POST['vcode']))) {
            H::error_show('{"code":1,"msg":"请输入正确的验证码"}');
        }
        if(isset($_POST['login_mobile'])) {
            $verify=verify::verify_mobile($_POST['login_mobile']);
            if($verify!='') die('{"code":1,"msg":"'.$verify.'","id":"login_mobile"}');
        }
		// 登录用户不验证，注册用户要验证唯一性
		if ($uid>0){
            //验证手机号码唯一性
            $sql="select uid from ".DB_DBNAME_USER.".ws_user where login_mobile='{$_POST['login_mobile']}' and (uid>$uid or uid<$uid) limit 1";
            $rs=$dbm->query($sql);
		}else{
            $sql="select uid from ".DB_DBNAME_USER.".ws_user where login_mobile='{$_POST['login_mobile']}' limit 1";
            $rs=$dbm->query($sql);
		}
        if(count($rs['list'])>0) H::error_show('{"code":1,"msg":"手机号码已经被使用，请更换手机号码","id":"login_mobile"}');

        unset($dbm);

        $fields['create_user']=isset($_POST['login_mobile'])?intval($_POST['login_mobile']):0;
        $fields['code'] = rand(1000,9999);
        //$fields['update_time'] =time();
        //print_r($fields);die();
        if($fields['create_user']>0){
            global $global_global;$myredis=new myredis($global_global);
            $myredis->add_sms_pool('pool_sms1','verify_',$fields['create_user'],$fields['code']);
            unset($myredis);
        }
        //把生成的短信验证码和手机号插入数据库中
        //$rs=$dbm->single_insert(DB_DBNAME_USER.".ws_verify",$fields,1);//print_r($rs);

      
        //H::error_show('{"code":1,"msg":"数据故障，无法发送验证码","id":"mcode"}');
        H::error_show('{"code":0,"msg":"短信验证码已发送，注意查看手机","id":"vcode"}');


    }
    //登录
    function m__login() {
        global $dbm,$U;

        session_open();
        if(isset($_SESSION['user']['uid'])) H::error_show('{"code":"1","msg":"您已经登录了"}');

        //处理数据
        $_POST['login_pass'] = isset($_POST['login_pass']) ? trim($_POST['login_pass']) : '';
        $_POST['login_name'] = isset($_POST['login_name']) ? H::sqlxss(strtolower(trim($_POST['login_name']))) : '';
        $_POST['remember_uname'] = isset($_POST['remember_uname']) ? trim($_POST['remember_uname']) : '';
        $_POST['http_referer'] = isset($_POST['http_referer']) ? H::sqlxss(trim($_POST['http_referer'])) : '';

        $tmp = array(
            DOMAIN_USER.'/app/user/login.php',
            DOMAIN_USER.'/app/user/register.php',
            '',
        );
        if(in_array($_POST['http_referer'],$tmp)) $_POST['http_referer'] = DOMAIN_USER.'/app/user/index.php';


        //检查帐号是否输入
        if($_POST['login_name'] == '') H::error_show('{"code":"1","msg":"请输入帐号","id":"login_name"}');
        //检查密码是否输入
        if($_POST['login_pass'] == '') H::error_show('{"code":"1","msg":"请输入密码","id":"login_pass"}');

        //查询帐号是否存在
        $user = $U->get($_POST['login_name'],"*");
        if(!$user) H::error_show('{"code":"1","msg":"帐号密码不匹配","id":"login_name"}');
        if(in_array($user['login_group'],array(1,3,4))) H::error_show('{"code":"1","msg":"系统帐号禁止登录","id":"login_name"}');
        if($user['login_status']!=0) H::error_show('{"code":"1","msg":"账号被禁用","id":"login_name"}');
        if($user['login_pass'] != H::password_encrypt_salt($_POST['login_pass'], $user['login_salt'])) {
            H::error_show('{"code":"1","msg":"帐号密码不匹配","id":"login_pass"}');
        }

        //记录登录信息
        $fields = array(
            'login_ip'=>H::getip(),
            'login_num'=>($user['login_num']+1),
            'login_time'=>time(),
        );
        $dbm->single_update(DB_DBNAME_USER.".ws_user",$fields,"uid='{$user['uid']}'");

        if($_POST['remember_uname']) {
            H::set_cookie('login_name',$user['login_name'],time()+86400*10);
        }else{
            H::set_cookie('login_name',$user['login_name'],time()-3600);
        }

        //用户组和权限拼接
        $user['group'] = get_user_group_level($user['login_group']);

        //记录SESSION
        session_regenerate_id();//重置sessionid
        $_SESSION['user']['group_name']=implode(',',$user['group']['group_name']);//组名称
        $_SESSION['user']['group_level']=implode(',',$user['group']['group_level']);//组权限
        $_SESSION['user']['login_level']=$user['login_level'];//附加权限
        $_SESSION['user']['login_no_level']=$user['login_no_level'];//禁止权限
        $_SESSION['user']['login_mobile'] = $user['login_mobile'];
        $_SESSION['user']['uid'] = $user['uid'];
        $_SESSION['user']['login_name'] = $user['login_name'];
        $_SESSION['user']['nick_name'] = $user['nick_name']==''?$user['login_name']:$user['nick_name'];
        $_SESSION['user']['avatar'] = $user['avatar'];
        $_SESSION['user']['thumb_avatar'] = $user['thumb_avatar'];
        $_SESSION['user']['login_from']='front';
        session_close();

        H::error_show('{"code":"0","msg":"登录成功","http_referer":"'.$_POST['http_referer'].'"}');
    }
}