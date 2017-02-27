var s = {
	"login":function(obj){
		var loginObj = document.login_form;
		if(!loginObj){ return ; }
		var username = loginObj.username.value;
		var password = loginObj.password.value;
		if(username == ''){ alert('请输入用户名');loginObj.username.focus(); return; }
		if(password == ''){ alert('请输入密码'); loginObj.password.focus(); return; }
		$.ajax({
            type:"post",
            url:$(obj).attr('data-submit'),
            async:true,
            dataType:'json',
            data:{username:username,password:password},
            beforeSend:function(xhr){},
            success:function(data){
                console.log(data);
                if(data.code == 0){
					window.location.href = 'index.php';
				} else {
					alert(data.msg);
				}
            },
            complete:function(XHR, TS){},
            error: function(xhr, type, errorThrown) {}
        });
		return false;
	},
	"addproduct":function(){

	}
};