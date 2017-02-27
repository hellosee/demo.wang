<?php require_once(assign_tpl_inc('inc.header.php'));?>
<div id="panelwrap">
    <?php require_once(assign_tpl_inc('inc.sub.header.php'));?>

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
						</tr>
					</thead>

					<tbody>
					<?php if(!empty($data)): ?>
						<?php foreach($data as $key => $var):?>
							<tr class="odd">
								<td><!--<input type="checkbox" name="" />--></td>
								<td><?php echo $var['id'];?></td>
								<td><?php echo $var['pname'];?></td>
								<td><?php echo $var['cname'];?></td>
								<td><?php echo $var['createtime'];?></td>
								<td>
									<?php if($var['archive']):?>
									<a target="_blank" href="<?php echo 'view.php?pid='.$var['id']; ?>"><img src="<?php echo __IMG__;?>/view.png" alt="" title="" border="0" /></a>
									<?php else:?>
										<font color="red">未解压</font>
								<?php endif; ?>
								</td>
							</tr>
						<?php endforeach;?>
						<?php endif;?>
					</tbody>
					<tfoot>
					<tr>
						<td colspan="12"></td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div><!-- end of right content-->
		<?php require_once(assign_tpl_inc('inc.menu.php'));?>      
		<div class="clear"></div>
    </div> <!--end of center_content-->
	<div class="footer">
	</div>
</div>
</body>
</html>