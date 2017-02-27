<?php require_once(assign_tpl_inc('inc.header.php'));?>
<div id="panelwrap">
	<?php require_once(assign_tpl_inc('inc.sub.header.php'));?>
    <hr style="border:0px;width: 100%;height: 5px;background-color: #edcd66;clear: both;float: left;" />
	<form action="javascript:void(0)" name="pro_form" data-submit="adp.php?m=addpro" onsubmit="pro_submit(this)">
    <div class="center_content">  
		<div id="right_wrap">
			<div id="right_content">             
				<h2>添加产品</h2> 
				<div class="form">
					<div class="form_row">
						<label>产品名称:</label>
						<input type="text" class="form_input" name="pro_name" />
					</div>
					<div class="form_row">
						<label>产品栏目:</label>
						<select class="form_select" name="pro_category">
							<?php
								foreach($categorys as $v){
							?>
								<option value="<?php echo $v['cid']; ?>"><?php echo $v['cname']; ?></option>
							<?php 
								}
							?>
						</select>
					</div>
					<div class="form_row">
						<label>产品压缩包(ZIP):</label>
						<!--<div class="form_input uploader" style="width:100px;height:100px;">
						</div>
						-->
						<div class="message">
							<input type="button" value="选择产品" size="30" class="liulan" id="profile">
							<input type="text" id="profileurl" name="profileurl" class="input" value="" disabled="disabled" style="display: inline;" />
						</div>
					</div>

					<input type="text" id="archive" name="archive" value="1" style="display:none" />
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
	<div class="footer"></div>
</div>
<script src="<?php echo __JS__;?>/jquery.ajaxupload.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo __JS__;?>/layer/layer.js" type="text/javascript" charset="utf-8"></script>
<script>
	var index = null;
	function pro_submit(obj){
		var proObj = document.pro_form;
		if(!proObj){ return ; }
		var pro_name = proObj.pro_name.value;
		var pro_category = proObj.pro_category.value;
		var profileurl = proObj.profileurl.value;
		var archive = proObj.archive.value;
		if(pro_name == ''){ alert('请输入产品名称');proObj.pro_name.focus(); return; }
		if(pro_category == ''){ alert('请选择产品分类'); proObj.pro_category.focus(); return; }
		if(profileurl == ''){ alert('请上传产品压缩包（只支持zip格式）'); proObj.profileurl.focus(); return; }
		$.ajax({
			type:"post",
			url:$(obj).attr('data-submit'),
			async:true,
			dataType:'json',
			data:{name:pro_name,cid:pro_category,profile:profileurl,archive:archive},
			beforeSend:function(xhr){index = layer.msg('信息提交中....');},
			success:function(data){
				layer.confirm(data.msg, {
					btn: ['确定'] //按钮
				}, function(){
					window.location.href=window.location.href;
				});

			},
			complete:function(XHR, TS){layer.close(index);},
			error: function(xhr, type, errorThrown) {layer.close(index);layer.msg('信息提交出错');}
		});

	}
	jQuery(function ($) {

		$('#profile').ajaxUploadPrompt({
			url: '?m=profile',
			onprogress: function (e) {
				if (e.lengthComputable) {
					var percentComplete = e.loaded / e.total;
					var per = percentComplete.toFixed(2) * 100;
					console.log(percentComplete);
					index = layer.msg('产品上传中....' + per + "%");
				}
			},
			success: function (data, status, xhr) {
				var d = JSON.parse(data);
				var dd = d.data;
				if(d.code){
					layer.alert(d.data,{icon: 2});
				} else {
					$("#profileurl").val(dd.savename);
					$("#profileurl").css("display",'inline');
					$("#archive").val(dd.archive);
				}
				layer.close(index);
				console.log(data);
			}
		});
	});
</script>
</body>
</html>