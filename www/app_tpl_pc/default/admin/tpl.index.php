<?php require_once(assign_tpl_inc('inc.header.php'));?>
<div id="panelwrap">
  	<div class="header">
		<div class="title"><a href="/">用户支撑平台</a></div>
		<div class="header_right">欢迎 Admin, <a href="#" class="settings">Settings</a> <a href="#" class="logout">Logout</a> </div>
    </div>

    <hr style="border:0px;width: 100%;height: 5px;background-color: #edcd66;clear: both;float: left;" />            
    <div class="center_content">  
		<div id="right_wrap">
			<div id="right_content">             
				<h2>产品列表</h2> 
				<table id="rounded-corner">
					<thead>
						<tr>
							<th></th>
							<th>ID</th>
							<th>产品名称</th>
							<th>产品分类</th>
							<th>上传时间</th>
							<th>查看</th>
							<th>编辑</th>
							<th>删除</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="12">sed do eiusmod tempor incididunt ut.</td>
						</tr>
					</tfoot>
					<tbody>
					<?php

					?>
						<tr class="odd">
							<td><input type="checkbox" name="" /></td>
							<td>Box Software</td>
							<td>Lorem ipsum dolor sit amet consectetur</td>
							<td>45$</td>
							<td>10/04/2011</td>
							<td>web design</td>
							<td>Alex</td>
							<td><a href="#"><img src="<?php echo __IMG__;?>/edit.png" alt="" title="" border="0" /></a></td>
							<td><a href="#"><img src="<?php echo __IMG__;?>/trash.gif" alt="" title="" border="0" /></a></td>
						</tr>
						<tr class="even">
							<td><input type="checkbox" name="" /></td>
							<td>Trial Software</td>
							<td>Lorem ipsum dolor sit amet consectetur</td>
							<td>155$</td>
							<td>12/04/2011</td>
							<td>web design</td>
							<td>Carrina</td>
							<td><a href="#"><img src="<?php echo __IMG__;?>/edit.png" alt="" title="" border="0" /></a></td>
							<td><a href="#"><img src="<?php echo __IMG__;?>/trash.gif" alt="" title="" border="0" /></a></td>
						</tr>
					</tbody>
				</table>
				<div class="form_sub_buttons">
					<a href="#" class="button green">Edit selected</a>
					<a href="#" class="button red">Delete selected</a>
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