<?php  include PATH_TPL.'/tpl.header.phtml'?>
<div class="login_wrap">
	<div class="login_con">
		<h2>找回密码</h2>
		<form class="login_form"  method="post"  onsubmit="return signinDo();" autocomplete="false">
			<div class="login_list lg_email">
				<em></em>
				<input type="text" placeholder="手机号码"  id="email" name="phone" value="<?php if(isset($emailmsg))echo $_POST['email']?>">
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="验证码" name="captcha" id="captcha">
				<img id="captchaimg" src="/index/captcha?t=<?php  echo rand(100000, 999999)?>">
			</div>
			<!-- error Tips -->
			<div class="errortip_box errorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="submit" value="找回密码" class="loginSubmit">
			</div>
			<div class="login_forgot clearfix">
				<input type="hidden"  name="returntype" value="2">
				<span class="right"><a href="/user/register">免费注册</a></span>
			</div>
		</form>
	</div>
</div>
<?php  include PATH_TPL.'/tpl.footer.phtml'?>
<script>
function resetDo() {

	var pLen = $('#email').val().length;
	if(pLen != 11) {
		$('#errorTips').html('请输入注册手机').show();
		return false;
	}

	if ($('#captcha').val().length < 2) {
		$('#errorTips').html('请输入验证码').show();
		return false;
	}

	return true;

}
	// 验证码点击事件
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
