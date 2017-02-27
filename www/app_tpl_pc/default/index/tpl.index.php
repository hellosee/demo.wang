<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>展示页面</title>
    <link href="<?php echo __CSS__?>/font.css" type=text/css rel=stylesheet />
    <script src="<?php echo __CSS__?>/jquery.min.js"></script>
    <script type=text/javascript>
        $(document).ready(function(){
            // 判断img轮转，实现a跳转
            // 推荐品牌滑动翻转效果
            brandpicturn();
        });
        function brandpicturn(){
            $(".brand_detail").hide();
            $(".brand_item").hover(
                function(){
                    $(this).children(".brand_name").hide();
                    $(this).children(".brand_detail").show();
                }
                , function(){
                    $(this).children(".brand_detail").hide();
                    $(this).children(".brand_name").show();
                }
            );
        }
    </script>
</head>

<body>

<div class="layout_main">
    <div class="header">
        <ul class="nav">
            <li>aaaa</li>
            <li>bbbb</li>
            <li>cccc</li>
            <li>ddddd</li>
        </ul>
    </div>
    <div class="content_bottom" >
        <ul class="ui_brands">
            <li class=brand_item><a class="brand_name" href="#" target=_blank><img src="images/pic_jy_name.gif" /><span>九阳
		  joyoung</span></a> <a class="brand_detail"  href="#" target=_blank><img src="images/pic_jy_product.gif" /><span>九阳 joyoung</span></a> </li>
            <li class=brand_item><a class="brand_name" href="#" target=_blank><img src="images/pic_sx_name.gif" /><span>三星
		  samsung</span></a> <a class="brand_detail" href="#" target=_blank><img src="images/pic_sx_product.gif" /><span>三星
		  samsung</span></a> </li>
            <li class=brand_item><a class="brand_name" href="#" target=_blank><img src="images/pic_oly_name.gif" /><span>欧莱雅
		  loreal</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_oly_product.gif" /><span>欧莱雅
		  loreal</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_jn_name.gif" /><span>佳能 cannon
		  </span></a><a class=brand_detail href="#" target=_blank><img src="images/pic_jn_product.gif" /><span>佳能
		  cannon </span></a></li></ul>
        <ul class=ui_brands>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_bl_name.gif" /><span>百丽
		  belle</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_bl_product.gif" /><span>百丽
		  belle</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_shlsq_name_1.jpg" /><span>施华洛世奇
		  swarovski</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_shlsq_product.gif" /><span>施华洛世奇
		  swarovski</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/hp_logo_3.jpg" /><span>惠普 hp
		  </span></a><a class=brand_detail href="#" target=_blank><img src="images/hp-elitebook_3.jpg" /><span>惠普 hp
		  </span></a></li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_dmz_name.gif" /><span>多美滋 dumex
		  </span></a><a class=brand_detail href="#" target=_blank><img src="images/pic_dmz_product.gif" /><span>多美滋
		  dumex </span></a></li></ul>
        <ul class=ui_brands>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_qpl_name.gif" /><span>七匹狼
		  septwolves</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_qpl_product.gif" /><span>七匹狼
		  septwolves</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_lklk_name.gif" /><span>乐扣乐扣
		  lock&amp;lock</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_lklk_product.gif" /><span>乐扣乐扣
		  lock&amp;lock</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_dsn_name.gif" /><span>迪士尼
		  disney</span></a> <a class=brand_detail href="#" target=_blank><img src="images/pic_dsn_product.gif" /><span>迪士尼
		  disney</span></a> </li>
            <li class=brand_item><a class=brand_name href="#" target=_blank><img src="images/pic_elh_name.gif" /><span>e路航 eroda
		  </span></a><a class=brand_detail href="#" target=_blank><img src="images/pic_elh_product.gif" /><span>e路航
		  eroda </span></a></li>
        </ul>
    </div>
</div>

<div style="clear:both"></div>
</body>
</html>
