@include('home.common.header')
<style type="text/css" media="screen">
	.lg_email em {
    	background: url(/img/reUser.png) #fff no-repeat;
	    margin: 14px 0 0 22px;
	    width: 28px;
	    background-size: 20px;
	}
</style>	
<div class="login_wrap">
	<div class="login_con">
		<h2>账户登录</h2>
		<form class="login_form"  method="post" onsubmit="return signinDo();" autocomplete="false">
			<div class="login_list lg_email">
				<em></em>
				<input type="text" placeholder="电子邮箱/手机号" id="email" name="email" value="<?php if(isset($emailmsg)) echo $_POST['email']?>">
			</div>
			<div class="login_list lg_psw">
				<em></em>
				<input type="password" placeholder="登录密码" name="pwd" id="pwd">
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="验证码" name="captcha" id="captcha">
				<img id="captchaimg" src="/index/captcha?t=<?php  echo rand(100000, 999999)?>">
			</div>
			<!-- error Tips -->
			<div class="errortip_box errorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="submit" value="登录" class="loginSubmit">
			</div>
			<!-- <div class="login_forgot clearfix">
				<span class="left">忘记密码？<a href="/reset">邮箱找回</a>|<a href="/user/resetp">手机找回</a></span>
				<span class="right">还没有账号？<a href="/user/register">免费注册</a></span>
			</div> -->
			<div class="login_forgot clearfix">
				<span class="left"><a href="/reset">忘记密码？</a></span>
				<span class="right">还没有账号？<a href="/user/register">免费注册</a></span>
			</div>
		</form>
	</div>
</div>
<script src="/js/form.js"></script>
<script>
	function signinDo() {

		var pLen = $('#email').val().length;
		if(pLen < 6 || pLen > 30) {
			$('#errorTips').html('请输入注册邮箱').show();
			return false;
		}

		var pwdLen = $('#pwd').val().length;
		if(pwdLen < 6 || pwdLen > 20) {
			$('#errorTips').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if ($('#captcha').val().length < 2) {
			$('#errorTips').html('请输入验证码').show();
			return false;
		}
		return true;
	}
	$('#captchaimg').click(function(){
		$(this).attr('src', '/index/captcha?t='+Math.random());
		$(this).parent().addClass('lg_borcor');
	});

	// 输入框获得焦点时改变边框颜色
	$('.login_list input').focus(function(){
		$(this).parent().addClass('lg_borcor');
	}).focusout(function(){
		$(this).parent().removeClass('lg_borcor');
	});
</script>
@include('home.common.footer')
