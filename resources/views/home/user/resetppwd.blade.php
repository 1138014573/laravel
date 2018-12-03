<?php  include PATH_TPL.'/tpl.header.phtml'?>
<style type="text/css" media="screen">
    #form2 .countDown{
		 font-size: 14px;
	    display: inline-block;
	    padding: 15px 18.3px;
	    background: #ee8d02;
	    color: #fff;
	    float: right;
	    /* display: none; */
	}

	.btn {
	   
	    margin-bottom: 5px;
	}
	.btn-danger:hover {
	    color: #fff;
	    background-color: #ee8d02;
	    border-color: #ee8d02;
	}
	.btn.focus, .btn:focus, .btn:hover {
	    color: #fff;
	    text-decoration: none;
	}
	.btn-danger {
	    color: #999;
	    backgrousnd-color: #d9534f;
	    border-color: #d43f3a;
	}
	.btn {
	    display: inline-block;
	    padding: 13px 25.5px;
	    margin-bottom: 0;
	    font-size: 14px;
	    font-weight: 400;
	    line-height: 1.42857143;
	    text-align: center;
	    white-space: nowrap;
	    vertical-align: middle;
	    -ms-touch-action: manipulation;
	    touch-action: manipulation;
	    cursor: pointer;
	    -webkit-user-select: none;
	    -moz-user-select: none;
	    -ms-user-select: none;
	    user-select: none;
	    background-image: none;
	    border: 1px solid transparent;
	    border-radius: 4px;
	}
</style>
<div class="login_wrap" id = "reg_now">
	<div class="login_con register_con">
		<h2>重设密码</h2>
		<form class="register_form" method="post" id="form2">
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
			<div class="login_list lg_surepsw">
				<em></em>
				<input type="text" placeholder="短信验证码" name="captcha2" id="captcha2" style="width: 219px;">
				<button type="button" class="btn btn-danger" onclick="getVerificationCode()" style="float: right;">获取验证码</button>
				<span class="countDown">60秒重新发送</span>
			</div>

			
			<!-- error Tips -->
			<div class="errortip_box errorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="hidden"  name="phone" id="phone" value="<?php  echo $_SESSION['phone']?>">
				<input type="hidden"  name="returntype" value="2">
				<input type="submit" value="重设密码" class="loginSubmit">
			</div>
			<div class="login_forgot clearfix">
				<span class="reg_kslogin">还没有账号？现在就<a href="/user/register">免费注册</a></span>
			</div>
		</form>
	</div>
</div>
<script src="<?php  echo host()?>/js/form.js"></script>
<script>
	$(function(){
		$('#tongyi').click(function() {
			var isAgree = $(this).attr('checked');
			if(isAgree) {
				$(this).removeAttr('checked');
				$('#next_btn').css({'background':'#e1e1e1'});
			} else {
				$(this).attr("checked",'true');
				$('#next_btn').css({'background':'#fcae00'});
			}
		});
		$('#next_btn').click(function() {
			var isAgree = $('#tongyi').attr('checked');
			if(isAgree) {
				$('#reg_prev').hide();
				$('#reg_now').show();
			}
		});
	});
	// 验证码点击事件
	$('#captchaimg').click(function(){
		$(this).attr('src', '/index/captcha?t='+Math.random());
		$(this).parent().addClass('lg_borcor');
	});
	$('#captchaimg2').click(function(){
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
<script>
$('#captchaimg').click(function(){
		$(this).attr('src', '/index/captcha?t='+Math.random());
		$(this).parent().addClass('lg_borcor');
	});
	$('#captchaimg2').click(function(){
		$(this).attr('src', '/index/captcha?t='+Math.random());
		$(this).parent().addClass('lg_borcor');
	});
/*获取短信注册验证码*/
	var num = 60,countDownInteval;
	function getVerificationCode(){

		if(!(/^(13[0-9]|15[012356789]|17[01356789]|18[0-9]|14[579])[0-9]{8}$/.test($('#phone').val()))){
			$('#errorTips2').html('请输入正确的手机号码').show();
			return false;
		}

		var pLen = $('#pwd').val().length;
		if(pLen < 6 || pLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		var repLen = $('#repwd').val().length;

		if(repLen < 6 || repLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if($('#pwd').val() != $('#repwd').val()) {
			$('#errorTips2').html('密码不一致，请重新输入').show();
			return false;
		}

		if($("#captcha").val().length < 2) {
			$('#errorTips').html('请输入验证码').show();
			return false;
		}

		// console.log('sharsasdas');
		$.ajax({
             type: "GET",
             url: "/ajax/sendregmsg",
             data: {phone:$("#phone").val(), captcha:$("#captcha").val(),action:11},
             dataType: "json",
             success: function(data){
                         console.log(data);
                      }
         });

		$("#form2 .btn").hide();
		$("#form2 .countDown").show();
		countDownInteval = setInterval(function (){
			num --;
			$("#form2 .countDown").text(num+"秒重新发送"); 
			if(num < 10){
				$("#form2 .countDown").text("0"+num+"秒重新发送"); 
			}
			if(num === 0){
				$("#form2 .btn").show();
				$("#form2 .countDown").hide();
				clearInterval(countDownInteval);
				num = 60;
			}
		},1000);
		

	};
</script>
<?php  include PATH_TPL.'/tpl.footer.phtml'?>
