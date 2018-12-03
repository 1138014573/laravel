<?php  include PATH_TPL.'/tpl.header.phtml'?>
<div class="login_wrap" id = "reg_now">
	<div class="login_con register_con">
		<h2>重设密码</h2>
		<form class="register_form" method="post">
			<div class="login_list lg_psw">
				<em></em>
				<input type="password" placeholder="新密码" id="pwd" name="pwd" value="<?php if(isset($_POST['pwd']))echo $_POST['pwd']?>">
				<?php  if(isset($pwdmsg)) echo '<b class="false">', $pwdmsg, '</b>';?>
			</div>
			<div class="login_list lg_surepsw">
				<em></em>
				<input type="password" placeholder="确认密码" value="" id="repwd" name="repwd" value="<?php if(isset($_POST['repwd']))echo $_POST['repwd']?>">
				<?php  if(isset($repwdmsg)) echo '<b class="false">', $repwdmsg, '</b>';?>
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="验证码" name="captcha" id="captcha">
				<img id="captchaimg" src="/index/captcha?t=<?php  echo rand(100000, 999999)?>"  onclick="$('#captchaimg').attr('src', '/index/captcha?t='+Math.random())">
			</div>
			<!-- error Tips -->
			<div class="errortip_box errorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="submit" value="重设密码" class="loginSubmit">
			</div>
			<div class="login_forgot clearfix">
				<span class="reg_kslogin">还没有账号？现在就<a href="/user/register">免费注册</a></span>
			</div>
		</form>
	</div>
</div>
<?php  include PATH_TPL.'/tpl.footer.phtml'?>
