<?php require_once(assign_tpl_inc('inc.header.php'));?>
<div id="panelwrap">
    <?php require_once(assign_tpl_inc('inc.sub.header.php'));?>
    <hr style="border:0px;width: 100%;height: 5px;background-color: #edcd66;clear: both;float: left;" />
    <form action="javascript:void(0)" name="category_form" data-submit="category.php?m=addcategory" onsubmit="category_submit(this)">
        <div class="center_content">
            <div id="right_wrap">
                <div id="right_content">
                    <h2>添加栏目</h2>
                    <div class="form">
                        <div class="form_row">
                            <label>栏目名称:</label>
                            <input type="text" class="form_input" name="category_name" />
                        </div>
                        <div class="form_row">
                            <input type="submit" class="form_submit" value="提交" />
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
            </div><!-- end of right content-->
    </form>
    <?php require_once(assign_tpl_inc('inc.menu.php'));?>
    <div class="clear"></div>
</div> <!--end of center_content-->
<div class="footer">
</div>
</div>
<script src="<?php echo __JS__;?>/layer/layer.js" type="text/javascript" charset="utf-8"></script>
<script>
    var index = null;
    function category_submit(obj){
        var categoryObj = document.category_form;
        if(!categoryObj){ return ; }
        var category_name = categoryObj.category_name.value;
        if(category_name == ''){ alert('请输入栏目名称');categoryObj.category_name.focus(); return; }
        $.ajax({
            type:"post",
            url:$(obj).attr('data-submit'),
            async:true,
            dataType:'json',
            data:{cname:category_name},
            beforeSend:function(xhr){/*index = layer.msg('信息提交中....');*/},
            success:function(data){
                alert(data.msg);
            },
            complete:function(XHR, TS){/*layer.close(index);*/},
            error: function(xhr, type, errorThrown) {/*layer.close(index);layer.msg('信息提交出错');*/}
        });

    }

</script>
</body>
</html>