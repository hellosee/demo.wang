<?php require_once(assign_tpl_inc('inc.header.php'));?>
<div id="panelwrap">
  	<div class="header">
		<div class="title"><a href="#">用户支撑平台</a></div>
		<div class="header_right">欢迎 Admin, <a href="#" class="settings">Settings</a> <a href="#" class="logout">Logout</a> </div>
    </div>

    <hr style="border:0px;width: 100%;height: 5px;background-color: #edcd66;clear: both;float: left;" />            
    <div class="center_content">  
		<div id="right_wrap">
			<div id="right_content">             
				<h2>添加产品</h2> 
				<div class="form">
					<div class="form_row">
						<label>产品名称:</label>
						<input type="text" class="form_input" name="" />
					</div>
					<div class="form_row">
						<label>产品栏目:</label>
						<select class="form_select" name="">
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
						<label>产品压缩包:</label>
						<div class="form_input weui-uploader__input-box" style="width:100px;height:100px;">
						</div>
					</div>

					
					<div class="form_row">
						<input type="submit" class="form_submit" value="Submit" />
					</div> 
					<div class="clear"></div>
				</div>
			</div>
		</div><!-- end of right content-->
		<?php require_once(assign_tpl_inc('inc.menu.php'));?>      
		<div class="clear"></div>
    </div> <!--end of center_content-->
	<div class="footer">
		Panelo - Free Admin Collect from 
		<a href="http://h2design.taobao.com/" title="氢设计" target="_blank">氢设计</a>
	</div>
</div>
</body>
</html>