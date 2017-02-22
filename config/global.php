<?php

//调试使用，生产环境关闭
define('SQL_LOG','1');//耗时SQL记录
define('SQL_ERR_LOG','1');//错误SQL记录

define('EMAIL_FORBIDDEN','');//禁止注册的邮箱后缀
define('PAGESIZE_AUTO','10');//后台自定义列表条数
define('PAGESIZE_ADMIN','10');//后台默认分页大小
define('PAGESIZE','20');//前台默认分页大小
define('PAGESIZE_IMG_LIST',30);//前台默认图片列表分页大小
define('COOKIE_DOMAIN','.wsq.com');//SESSION共享域

// 上传设置
define('UPLOAD_EXT','ico,jpg,jpeg,gif,png,bmp,psd,swf,flv,fla,pdf,doc,docx,rtf,txt,wps,xls,xlsx,csv,ppt,pptx,mp4,mpg,wmv,mp3,wav,zip,rar');
define('UPLOAD_VERIFY','ws2015');
define('UPLOAD_MAX_SIZE','1MB');

//皮肤模板设置
define('TPL_SORT','wx,wap,pc');//没有找到模板文件情况下按用户浏览设备自适应模板顺序
define('SKIN','default');//模板皮肤目录



define('THUMB_FIELDS','num_img,quan_img,avatar,info_img,page_topimg,page_backimg');//缩略图字段
define('SHARE_IMG_DEFAULT','http://css.huiweishang.com/static/logo/hws_100.png');//分享默认图



