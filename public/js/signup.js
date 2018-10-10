"use struct";

var vali = {'email': 0, 'pwd' : 0, 'pwdtrade' : 0, 'name' : 0, 'idcard' : 0, 'mo' : 0, 'captcha' : 0};

function validateMsg(id, msg, status){
	if (status != 1) {
		$.tips({content:msg,stayTime:1000,type:"warn"});
	}
}

function valiForm(){

	var result = returnJson('/ajax/user/email/' + encodeURIComponent($('#email').val()), '', 'GET');

	if (result.status == 0) {
		validateMsg('email', result['msg'], result['status']);
		return false;
	}
	vali.email = result['status'];
	
	var pLen = $('#pwd').val().length;
	if(pLen < 6 || pLen > 20) {
		validateMsg('pwd', '登录密码长度在6-20个字符之间', 0);
		return false;
	}
	vali.pwd = 1;

	var pLen = $('#pwdtrade').val().length;
	if(pLen < 6 || pLen > 20) {
		validateMsg('pwdtrade', '交易密码长度在6-20个字符之间', 0);
		return false;
	}
	vali.pwdtrade = 1;

	if ($('#pwd').val() == $('#pwdtrade').val()) {
		validateMsg('common', '登录密码不能与交易密码相同', 0);
		return false;
	}

	if($('#name').val().length < 2) {
		validateMsg('name', '请输入正确的姓名', 0);
		return false;
	}
	vali.name = 1;

	if($('#idcard').val().length != 18) {
		validateMsg('idcard', '您输入的身份证号不正确', 0);
		return false;
	}
	vali.idcard = 1;
	
	if($('#mo').val().length != 11) {
		validateMsg('mo', '您输入的手机号格式不正确', 0);
		return false;
	}
	vali.mo = 1;

	if($('#captcha').val().length < 2) {
		validateMsg('captcha', '请输入验证码', 0);
		return false;
	}
	vali.captcha = 1;

	console.log(vali);
	for(var i in vali){
		if(!vali[i]) return false;
	}

	return true;

}

function returnJson(url, data, type, async) {
        //默认参数
        var data    = data ? data : {}; 
        var type    = type ? type : 'GET'; 
        var async   = async ? async : false;

        var returnJson;
        $.ajax({
            type : type,
            url : url,
            data : data,
            dataType : 'json',
            async : async,
            success : function(data) {
                returnJson = data;
            },
            error : function(xhr, type){
                console.log('Ajax error!')
            }
        });

        return returnJson;
    }

