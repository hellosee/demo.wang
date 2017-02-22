<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>登录</title>
<link rel="stylesheet" type="text/css" href="<?php echo __CSS__; ?>/style.css" />
<script src="<?php echo __JS__; ?>/jquery.min.js"></script>
<script src="<?php echo __JS__; ?>/s.js?v=<?php echo time(); ?>"></script>
</head>
<body>
<form action="javascript:void(0)" name="login_form" data-submit="login.php?m=dologin" onsubmit="s.login(this);">
<div id="loginpanelwrap">
	<div class="loginheader">
    <div class="logintitle"><a href="#">用户支撑平台</a></div>
    </div>
    <div class="loginform">
        <div class="loginform_row">
        <label>用户名：</label>
        <input type="text" class="loginform_input" name="username" />
        </div>
        <div class="loginform_row">
        <label>密码：</label>
        <input type="password" class="loginform_input" name="password" />
        </div>
        <div class="loginform_row">
        <input type="submit" class="loginform_submit" value="登录" />
        </div> 
        <div class="clear"></div>
    </div>
</div>
</form>	
</body>
</html>