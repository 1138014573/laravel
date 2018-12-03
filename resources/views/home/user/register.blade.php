@include('home.common.header')
<style type="text/css" media="screen">
	.registerTypes{
		margin-bottom: 10px;
		overflow: auto;
	}
	.registerTypes .emailType,.registerTypes .mobileType{
		float: left;
		width: 50%;
		padding: 10px 0;
		text-align: center;
		font-size: 16px;
		color: #999;
		cursor:pointer;
	}
	.selectType{
		border-bottom: 3px solid #ee8d02;
		color: #ee8d02 !important;
	}
	.lg_email.rePhone em {
		background: url(/img/rePhone.png) #fff no-repeat;
		margin: 14px 0 0 23px;
		width: 27px;
		background-size: 15px;
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
	#form2 .countDown{
		font-size: 14px;
		display: inline-block;
		padding: 15px 18.3px;
		background: #ee8d02;
		color: #fff;
		float: right;
		/* display: none; */
	}
	.lg_veri #captchaimg2 {
		float: left;
		width: 123px;
		max-width: 123px;
		cursor: pointer;
	}
	.register_con {
		padding: 20px 35px 20px;
	}
	.registerTypes {
		margin-bottom: 20px;
	}
</style>
<!-- 风险提示 -->
<div class="reg_addbftips" <?php if(isset($posttype) && $posttype == 1){?>style="display: none;"<?php }?>>
	<div class="main" id="reg_prev">
		<div class="main_box">
			<div class="warning1">
				<h2>风险警示</h2>
				<p>
					根椐人民银行等有关部委的相关规定，比特币等数字货币系特殊的虚拟商品，作为互联网上的商品买卖行为，普通民众在自担风险的前提下拥有参与的自由。数字货币行业目前存在很多不确定，不可控的风险因素（如预挖、暴涨暴跌、庄家操控、团队解散、技术缺陷等），导致交易具有极高的风险。币交所仅为数字货币等虚拟商品的爱好者提供一个自由的网上交换平台，对在币交所平台交换的数字货币等虚拟商品的来源、价值，网站运营方不承担任何审查、担保、赔偿的法律责任。</p>
				<h3>请您务必注意以下几点：</h3>
				<p>1.警惕虚假宣传，不要听信任何币值会永远上涨的宣传，数字货币作为一种虚拟商品，具有极高的风险，很可能出现价值归零的情况。		<br>
					2.对于推广和运营方的市场承诺，需要谨慎判别，目前并没有相关法律能保证其兑现承诺，币交所不会对任何数字货币进行背书和承诺。<br>
					3.坚决拒绝多层次传销组织，在我国参与该类组织是违法行为，造成的一切后果自负，平台将配合相关执法部门的要求进行调查、取证。<br>
					4.根据《中华人民共和国反洗钱法》等有关法律规定，严格禁止利用平台进行洗钱等违法犯罪活动，平台将配合相关执法部门的要求进行调查、取证。<br>
					5.数字货币和数字积分等虚拟商品所对应的实物财产和持有者享有的权利存在因发行方等义务相关方破产，关闭或违法犯罪等其他经营风险导致无法兑现的风险。<br>
					6.在币交所注册参与交换的用户，应保证注册身份信息的真实、准确，保证拟交换的数字货币等虚拟商品的来源合法。因信息不真实造成的任何问题，平台概不负责。<br>
					7.因国家法律，法规及政策性文件的制定和修改，导致数字货币等虚拟商品的交易被暂停或者禁止的，由此导致的全部损失由用户自行承担。<br>
					8.请控制风险，不要投入超过您风险承受能力的资金，不要购买您所不了解的数字货币，数字积分等虚拟商品。</p>
			</div>
			<div class="bjs_user2 clearfix">
				<div class="bjs_text left" style="margin: 0 5px 0 0;"><input type="checkbox" name='agree' id="tongyi"/></div>
				<div class="bjs_hint left"><label for="tongyi">我已经认真阅读以上风险提示，并已同意币交所 <a href="/service.html?type=1" id="agreement" class="tycolor">服务条款</a>，同意在自担风险，自担损失的情况下参与交</label></div>
				<div class="bjs_hint left"><span id="agree"></span></div>
			</div>
			<div class="bjs_user1 clearfix">
				<div class="bjs_label left"><label>&nbsp;</label></div>
				<div class="bjs_text left"><input type="submit" value="继续注册" id="next_btn" class="bjs_next" style="background-color:#e1e1e1; " /></div>
				<div class="bjs_hint1 left"><span id="agree"> <a href="/" class="tycolor">取消</a></span></div>
			</div>
		</div>
	</div>
</div>
<!-- 注册页面内容 -->
<div class="login_wrap display_c" id="reg_now" <?php if(isset($posttype) && $posttype == 1){?>style="display: block;"<?php }?>>
	<div class="login_con register_con">
		<div class="registerTypes">
			<div class="emailType selectType" onclick="selectType('email')">
				邮箱注册
			</div>
			<div class="mobileType" onclick="selectType('mobile')">
				手机注册
			</div>
		</div>
		<form class="register_form"  id="form" method="post" onsubmit="return signupDo();" style="margin: 10px 0 0 0;">
			<div class="reg_agreetk clearfix">
				<span class="reg_agrespn left" style="color: #ff0000; font-size: 16px; line-height: 22px;">  QQ邮箱会被拦截导致无法收到邮件，请使用QQ以外邮箱注册，如：126、163等其他邮箱运营商</span>
			</div>
			<div class="login_list lg_email">
				<em></em>
				<input type="text" placeholder="电子邮箱"  value="<?php  if(isset($data['email'])) echo $data['email'] ?>" id="email" name="email">
			</div>
			<div class="login_list lg_psw">
				<em></em>
				<input type="password" placeholder="登录密码" id="pwd" name="pwd">
			</div>
			<div class="login_list lg_surepsw">
				<em></em>
				<input type="password" placeholder="确认密码" value="" id="repwd" name="repwd">
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="验证码" name="captchas" id="captcha">
				<img id="captchaimg" src="/index/captcha?t=<?php  echo rand(100000, 999999)?>">
			</div>
			<!-- <div class="login_list lg_yqcode">
				<em></em>
				<input type="text" placeholder="邀请码" value="<?php // if($pid) echo $pid; ?>" id="pid" name="pid">
			</div> -->

			<div class="reg_agreetk clearfix">
				<div class="reg_check left active">
					<input type="checkbox" checked>
				</div>
				<span class="reg_agrespn left">已阅读并同意 <a href="/service.html?type=1">币交所网络服务条款</a></span>
			</div>
			<!-- error Tips -->
			<div class="errortip_box errorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="hidden" name="regtype" value="email">
				<input type="submit" value="注册" class="loginSubmit">
			</div>
			<div class="login_forgot clearfix">
				<span class="reg_kslogin">我已经注册，现在<a href="/user/login">登录</a></span>
			</div>
		</form>

		<form class="register_form"  id="form2" method="post"  onsubmit="" style="margin: 10px 0 0 0; display: none;">
			<div class="login_list lg_email rePhone">
				<em></em>
				<input type="text" placeholder="手机号码"  value="" id="phone" name="phone">
			</div>
			<div class="login_list lg_psw">
				<em></em>
				<input type="password" placeholder="登录密码" id="pwd2" name="pwd2">
			</div>
			<div class="login_list lg_surepsw">
				<em></em>
				<input type="password" placeholder="确认密码" value="" id="repwd2" name="repwd2">
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="验证码" name="captcha" id="captcha2">
				<img id="captchaimg2" src="">
			</div>
			<div class="login_list lg_veri">
				<em></em>
				<input type="text" placeholder="短信验证码" name="captcha2" id="captchas" style="width: 219px;">
				<button type="button" class="btn btn-danger" onclick="getVerificationCode()" style="float: right;">获取验证码</button>
				<span class="countDown">60秒重新发送</span>
			</div>
			<div class="reg_agreetk clearfix">
				<div class="reg_check left active">
					<input type="checkbox" checked>
				</div>
				<span class="reg_agrespn left">已阅读并同意 <a href="/service.html?type=1">币交所网络服务条款</a></span>
			</div>
			<div class="errortip_box errorTips" id="errorTips2" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>
			<div class="login_list lg_nobor">
				<input type="hidden" name="regtype" value="phone">
				<input type="submit" value="注册" class="loginSubmit">
				<input type="hidden" value=1 id="pstype" >
			</div>
			<div class="login_forgot clearfix">
				<span class="reg_kslogin">我已经注册，现在<a href="/user/login">登录</a></span>
			</div>
		</form>

	</div>
</div>

<!--QQ邮箱注册提醒 -->
<div class="modal fade in" id="qqMsgModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="text-align: center; display: block; padding-right: 17px; display: none;" >
	<div class="modal-dialog" style="width:640px;text-align:center;">
		<div id="autoCenter" class="modal-content" style="padding: 30px 30px 10px; height: 264px; margin-top: 310px;">
			<div class="modal-header" style="border:none;text-align:left;">
				<h3 class="modal-title" id="myModalLabel" style="font-size:18px;">
					币交所提醒您:
				</h3>
			</div>
			<div class="modal-body" style="font-size:18px;width: 550px;margin-top:-10px;">
				<div class="paragraph paragraph_news" style="font-size:18px;text-indent:2em;line-height: 30px;text-align:left;">
					QQ邮箱会被拦截导致无法收到邮件，请使用QQ以外邮箱注册，如：126、163等其他邮箱运营商。
				</div>
			</div>
			<div class="modal-footer" style="border:none;">
				<button type="button" style="cursor:pointer;font-family:Microsoft YaHei !important;font-size:18px;background: #ff0000;width:200px;border:0px;color:white; padding: 13px 15px; margin-right: 50px;" class="btn btn-warning" data-dismiss="modal" id="yes_sure" onclick="closeModal()">强制使用QQ邮箱注册
				</button>
				<button type="button" style="cursor:pointer;font-family:Microsoft YaHei !important;font-size:18px;background: #faad09;width:200px;border:0px;color:white; padding: 13px 15px;" class="btn btn-warning" data-dismiss="modal" id="yes_sure" onclick="switchEmail()">切换其他邮箱注册
				</button>
			</div>
		</div>
	</div>
</div>
<!--QQ邮箱注册提醒 -->

<script src="<?php  //echo host()?>/js/form.js"></script>
<script>
	$(function(){
		//验证邮箱是否注册
		$("#email").blur(function(){
			var email=$("#email").val();
			var filter  = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if (!filter.test(email))
			{$('#errorTips').html('邮箱格式不对').show();
				return false;
			}
			else{

				$.ajax({
					url:'/modifyemail/checkemail',
					type:'POST', //GET
					data:{
						'email':email
					},
					dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
					success:function(data,textStatus,jqXHR){
						if(data['status']==1){
							$('#errorTips').html('邮箱已经注册，您可以直接登录').show();
							console.log('邮箱已经注册');
						}else{
							$('#errorTips').hide();
							console.log('可以注册');
						}
					},
					error:function(xhr,textStatus){
						console.log(xhr);
					}

				})
			}

		});
		$("#phone").blur(function(){
			var phone=$("#phone").val();
			var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
			if (!myreg.test(phone))
			{$('#errorTips2').html('请输入正确的手机号码').show();
				$('#pstype').val(1);	//1表示禁止发送短信验证码
				return false;
			}
			$.ajax({
				url:'/modifyemail/checkphone',
				type:'POST', //GET
				data:{
					'phone':phone
				},
				dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
				success:function(data,textStatus,jqXHR){
					console.log(data);
					if(data['status']==1){
						$('#errorTips2').html('此手机号已被使用').show();
						$('#pstype').val(1);
						console.log('手机已经注册');
					}else{
						$('#pstype').val(0);	//0表示可以发送验证码
						$('#errorTips2').hide();
						console.log('手机可以注册');
					}
				},
				error:function(xhr,textStatus){
					console.log(xhr);
				}

			})
		});
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

	// 点击注册前的同意条款
	$('.reg_check').click(function(){
		var oChecked = $('.reg_check input').is(':checked');
		if(oChecked == false){
			$(this).removeClass('active');
		}else{
			$(this).addClass('active');
		}
	})

</script>
<script>
	/*qq邮箱注册提示*/
	$("#email").blur(function(){
		var qqEmailReg = /\w[-\w.+]*@((qq|QQ)+\.)+[A-Za-z]{2,14}/;
		if(qqEmailReg.test($("#email").val())){
			$("#qqMsgModal").show();
		}
	});

	function switchEmail(){
		$('#email').val('');
		$('#pwd').val('');
		$('#repwd').val('');
		$('#captcha').val('');
		$("#qqMsgModal").hide();
	}

	function closeModal(){
		$("#qqMsgModal").hide();
	}
	/*qq邮箱注册提示*/

	/*邮箱注册和手机注册切换*/
	function selectType(type){
		if(type == "email"){
			$(".registerTypes .emailType").addClass("selectType");
			$(".registerTypes .mobileType").removeClass("selectType");
			$("#form").show();
			$("#form2").hide();
		}else{
			$('#captchaimg2').click();
			$(".registerTypes .emailType").removeClass("selectType");
			$(".registerTypes .mobileType").addClass("selectType");
			$("#form2").show();
			$("#form").hide();
		}
	}
	/*邮箱注册和手机注册切换*/

	/*获取短信注册验证码*/
	var num = 60,countDownInteval;
	function getVerificationCode(){
		if($('#pstype').val()==1){
			$('#errorTips2').html('此手机号已被使用，获取验证码失败').show();
			return false;
		}
		if(!(/^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[0-9])[0-9]{8}$/.test($('#phone').val()))){
			$('#errorTips2').html('请输入正确的手机号码').show();
			return false;
		}

		var pLen = $('#pwd2').val().length;
		if(pLen < 6 || pLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		var repLen = $('#repwd2').val().length;
		if(repLen < 6 || repLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if($('#pwd2').val() != $('#repwd2').val()) {
			$('#errorTips2').html('密码不一致，请重新输入').show();
			return false;
		}

		if($("#captcha2").val().length < 2) {
			$('#errorTips2').html('请输入验证码').show();
			return false;
		}
		// console.log('sharsasdas');
		$.ajax({
			type: "GET",
			url: "/ajax/sendregmsg",
			data: {phone:$("#phone").val(),captcha:$("#captcha2").val()},
			dataType: "json",
			success: function(data){
				if(data=='1')
				{
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
					$('#errorTips2').hide();
				}
				else if(typeof data == 'object' && data.status===0)
				{
					$('#errorTips2').html(data.msg).show();
				}
				
			}
		});
		

	};
	/*获取短信注册验证码*/

	/*手机注册提交表单*/
	function signupDoPhone(){
		if(!(/^(13[0-9]|15[0-9]|17[0-9]|18[0-9]|14[0-9])[0-9]{8}$/.test($('#phone').val()))){
			$('#errorTips2').html('请输入正确的手机号码').show();
			return false;
		}
		var pLen = $('#pwd2').val().length;
		if(pLen < 6 || pLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		var repLen = $('#repwd2').val().length;
		if(repLen < 6 || repLen > 20) {
			$('#errorTips2').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if($('#pwd2').val() != $('#repwd2').val()) {
			$('#errorTips2').html('密码不一致，请重新输入').show();
			return false;
		}

		if($("#captcha2").val().length < 2) {
			$('#errorTips2').html('请输入验证码').show();
			return false;
		}
		phone = $('#phone').val();
		pwd2 = $('#pwd2').val();
		repwd2 = $('#repwd2').val();
		captcha = $('#captcha2').val();
		captcha2 = $('#captchas').val();
		var html = $.ajax({
			type: "get",
			url: '/ajax/user/reg',
			data: {phone:phone,pwd2:pwd2,repwd2:repwd2,captcha:captcha,captcha2:captcha2},
			async: false,
		}).responseText;
		var content = JSON.parse(html); console.log(content);

		// if (content.status == 0) {
		// 	$('#errorTips').html(content.msg).show();
		// 	return false;
		// }



		return true;
	}
	/*手机注册提交表单*/

	function signupDo() {
		var html = $.ajax({type: "get", url: '/ajax/user/email/' + encodeURIComponent($('#email').val()), async: false,
		}).responseText;
		var content = JSON.parse(html);

		if (content.status == 0) {
			$('#errorTips').html(content.msg).show();
			return false;
		}

		var pLen = $('#pwd').val().length;
		if(pLen < 6 || pLen > 20) {
			$('#errorTips').html('密码长度在6-20个字符之间').show();
			return false;
		}

		var repLen = $('#repwd').val().length;
		if(repLen < 6 || repLen > 20) {
			$('#errorTips').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if($('#pwd').val() != $('#repwd').val()) {
			$('#errorTips').html('密码不一致，请重新输入').show();
			return false;
		}

		if($(this).val().length < 2) {
			$('#errorTips').html('请输入验证码').show();
			return false;
		}

		return true;

	}
</script>
@include('home.common.footer')
