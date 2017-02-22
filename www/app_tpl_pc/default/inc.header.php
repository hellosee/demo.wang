<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户支撑平台</title>
<link rel="stylesheet" type="text/css" href="<?php echo __CSS__;?>/style.css" />
<!-- jQuery file -->
<script src="<?php echo __JS__;?>/jquery.min.js"></script>
<script src="<?php echo __JS__;?>/jquery.tabify.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
var $ = jQuery.noConflict();
$(function() {
$('#tabsmenu').tabify();
$(".toggle_container").hide(); 
$(".trigger").click(function(){
	$(this).toggleClass("active").next().slideToggle("slow");
	return false;
});
});
</script>
</head>
<body>