<!doctype html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>展示页面</title>
    <link href="<?php echo __CSS__?>/font.css" type=text/css rel=stylesheet />
    <script src="<?php echo __JS__?>/jquery.min.js"></script>
    <script type=text/javascript>
        $(document).ready(function(){
            // 判断img轮转，实现a跳转
            // 推荐品牌滑动翻转效果

        });

    </script>
</head>

<body>

<div class="layout_main">
    <div class="header">
        <ul class="nav">
            <?php foreach($categorys as $_k => $_v):?>
                <li><a href="<?php echo '?alias='.$_v['cid'];?>" class="<?php if($_GET['alias'] == $_v['cid']){echo 'selected';}?>"><?php echo $_v['cname'];?></a></li>
            <?php endforeach;?>
        </ul>
    </div>
    <div class="content_bottom" >
        <ul class="ui_brands">
            <?php foreach($data as $_k => $_v):?>
                <li class=brand_item>
                    <a class="brand_name" href="<?php echo "view.php?pid=".$_v['id']; ?>" target=_blank>
                        <img style="width:166px;height:138px;" src="<?php echo SITE_URL; ?>/products_unzip/<?php echo $_v['prourl']; ?>/Thumbnail.jpg" />
                        <span><?php echo $_v['name'];?></span>
                    </a>
                </li>
            <?php endforeach;?>

        </ul>
    </div>
</div>

<div style="clear:both"></div>
</body>
</html>
