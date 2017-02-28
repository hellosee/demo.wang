<?php

require_once(dirname(__FILE__) . "/../../config/init.php");
$time_start = H :: getmicrotime(); //开始时间
$categorys = array();
$data = array();
// 动作处理
call_mfunc();

// 模板处理
require_once(assign_tpl(basename(__FILE__), 'index'));

function m__list(){
    global $dbm,$p,$data,$categorys;
    $_GET['alias'] = isset($_GET['alias']) ? $_GET['alias'] :0;//判断是否存在栏目分类ID，如果不存在的话，默认栏目
    //获取所有栏目
    $lists = $dbm->orderBy('createtime')->get('tb_category',null,'id,cname');
    if(!empty($lists)){
        foreach($lists as $_k => $_v){
            $categorys[$_k]['cid'] = $_v['id'];
            $categorys[$_k]['cname'] = $_v['cname'];
        }
        $_GET['alias'] = $_GET['alias'] ? $_GET['alias'] : $categorys[0]['cid'];
        $data = $dbm->orderBy('createtime')->where('cid',$_GET['alias'])->get('tb_product',null,'id,cid,name,prourl');
    }
}

function m__add(){
}


?>
