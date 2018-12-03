<?php  include PATH_TPL.'/tpl.header.phtml';?>
<style media="screen">
	input[type="file"] {
		display: inline-block;
		height: 80px;
		border: 1px solid #ccc;
	}
	input[type="file"]:hover {
		cursor: pointer;
	}
	.auth_pic {
		width: 100%;
	}
	.auth_pic .img_container{
		width: 100%;
		
		margin: 70px auto 0 auto;
	}
	.auth_pic .img_container img{
		width: 30%;
	}
	.auth_pic::after {
		content: '';
		display: table;
		height: 0;
		width: 0;
		clear: both;
	}
	.auth_pic>div {
		position: relative;
		margin: 10px 0 10px 130px;
		width: 230px;
		text-align: center;
	}
	.auth_pic p {
		line-height: 26px;
		font-size: 16px;
		font-weight: 500;
		text-align: center;
	}
	.reject_reason textarea{
		width: 100%;
		height: 100px;
	}
  .modifyLeft .login .show_text {
    text-align: left;
    padding-left: 16px;
  }
</style>
<div class="content clear">
	<div class="center">
		<div class="wrap">
			<div class="box">
				<div class="TitleBox">
					<h3 class="PlateTitle">待审核</h3>
				</div>
				<div class="loginBox">
					<form id="form" method="post" onsubmit="return modifyDo();" class="modifyLeft" enctype="multipart/form-data">
						<!-- 真实姓名 -->
						<div class="login">
							<div class="clear">
								<span><b class="importantB">*</b>真实姓名：</span>
                        <span class="show_text"><?php echo  $data[0]['name']?></span>
								<!-- <input type="text" value="<?php  if(isset($data['name'])) echo $data['name'] ?>" id="name" name="name" class="loginValue" placeholder="请输入真实姓名"> -->
							</div>
						</div>
						<!-- 证件类型 -->
						<div class="login">
							<div class="clear">
								<span><b class="importantB">*</b>证件类型：</span>
                                <span class="show_text"><?php if($data[0]['cardtype']==1){
										echo "身份证";
									}else if($v['cardtype']==2){
										echo "驾驶证";
									}else if($v['cardtype']==3){echo "护照";}else if(
										$v['cardtype']==4){
										echo "军官证";
									}else if($v['cardtype']==5){
										echo "港澳通行证";
									}else if($v['cardtype']==6){
										echo "香港身份证";
									}else if($v['cardtype']==7){
										echo "澳门身份证";
									}

									?></span>
								<!-- <select class="loginValue" id="cardtype" name="cardtype" style="width:232px;height:36px;line-height:36px;">
									<option value="1" selected>身份证</option>
									<option value="2">驾驶证</option>
									<option value="3">护照</option>
									<option value="4">军官证</option>
									<option value="5">港澳通行证</option>
									<option value="6">香港身份证</option>
									<option value="7">澳门身份证</option>
								</select> -->
								<!-- <span style="text-align: left">下拉有更多选项</span> -->
							</div>
						</div>
						<!-- 身份證号 -->
						<div class="login">
							<div class="clear">
								<span><b class="importantB">*</b>证件号：</span>
                                <span class="show_text"><?php echo $data[0]['idcard'];?></span>
								<!-- <input type="text" value="<?php  if(isset($data['idcard'])) echo $data['idcard'] ?>" id="idcard" name="idcard" class="loginValue" placeholder="请输入有效的证件号"> -->
							</div>
						</div>
            <!-- 实名状态 -->
            <div class="login">
              <div class="clear">
                <span><b class="importantB">*</b>实名状态：</span>
                <span class="show_text"><?php if($data[0]['status']==0){
						echo '待审核';

					}?></span>
                <!-- <input type="text" value="<?php  if(isset($data['mo'])) echo $data['mo'] ?>" id="mo" name="mo" class="loginValue" placeholder="必须填写自己真实的手机号码"> -->
              </div>
            </div>
            <!-- 手机号码 显示 block 隐藏 none -->
            <div class="login" style="display:none">
              <div class="clear">
                <span><b class="importantB">*</b>手机号码：</span>
                <span class="show_text">185666666</span>
                <!-- <input type="text" value="<?php  if(isset($data['mo'])) echo $data['mo'] ?>" id="mo" name="mo" class="loginValue" placeholder="必须填写自己真实的手机号码"> -->
              </div>
            </div>
						<!-- 选择证件照片 -->

						<div class="auth_pic">
							<div class="img_container">
								<img src="<?php echo preg_replace('#(../public/|./)(.+)\.(.+?)$#', '/$2_thumb.jpg',$data[0]['frontFace'])?>">
								<img src="<?php echo preg_replace('#(../public/|./)(.+)\.(.+?)$#', '/$2_thumb.jpg',$data[0]['backFace'])?>">
								<img src="<?php echo preg_replace('#(../public/|./)(.+)\.(.+?)$#', '/$2_thumb.jpg',$data[0]['handkeep'])?>">
							</div>

						</div>

						<!-- error Tips -->
						<div class="errorTips modifyErrorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>

						<div class="loginBtn clear">
							<!--<input type="submit" value="重新认证" class="regBtn modifyBtn">-->
							<a class="regBtn modifyBtn" href="/user/shiming">重新认证</a>
							<input type="hidden" value=1 id="pstype" >
						</div>
						<div style="clear: both; height: 30px;"></div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?php  echo host()?>/js/form.js"></script>
<script>
	$("#mo").blur(function(){
		var phone=$("#mo").val();
		var myreg = /^(((13[0-9]{1})|(14[0-9]{1})|(17[0-9]{1})|(15[0-3]{1})|(15[5-9]{1})|(18[0-9]{1}))+\d{8})$/;
		if (!myreg.test(phone))
		{$('#errorTips').html('请输入正确的手机号码').show();
			$('#pstype').val(1);	//1表示禁止发送短信验证码
			return false;
		}
//	$.ajax({
//	    url:'/modifyemail/checkphone',
//	    type:'POST', //GET
//	    data:{
//	        'phone':phone
//	    },
//	    dataType:'json',    //返回的数据格式：json/xml/html/script/jsonp/text
//	    success:function(data,textStatus,jqXHR){
//	    		console.log(data);
//	       if(data['status']==1){
//	       	$('#errorTips').html('该手机已经使用，不能绑定').show();
//	       	$('#pstype').val(1);
//	       //	console.log('手机已经注册');
//	       }else{
//	       	$('#pstype').val(0);	//0表示可以发送验证码
//	       	$('#errorTips').hide();
//	       //	console.log('手机可以注册');
//	       }
//	    },
//	    error:function(xhr,textStatus){
//	        console.log(xhr);
//	    }
//	})
	});
	var authFile = {
		frontFace: false,
		backFace: false,
		handkeep: false
	};
	// 校验
	$('input[data-auth="pic"]').change(function(e) {
		var id = $(this).attr('id');
		var fileType = this.files[0].type;
		var size = this.files[0].size;
		if (size > 2097152) {
			return alert('所选图片大小不能超过2M。');
		}
		if (fileType.match(/(jpg|jpeg|png)/ig) === null) {
			return alert('证件图片支持jpg/jpeg/png格式,暂时还不支持其他格式。');
		}
		authFile[id] = true;
	});

	function modifyDo() {

		if($('#name').val().length < 2) {
			$('#errorTips').html('请输入真实姓名（请用中文）').show();
			return false;
		}

		if($('#cardtype').val() == 1 ){
			if(!($('#idcard').val().length == 8 || $('#idcard').val().length == 18)) {
				$('#errorTips').html('您输入的身份证号不正确').show();
				return false;
			}
		} else {
			if($('#idcard').val().length == '') {
				$('#errorTips').html('您输入的证件号不能为空').show();
				return false;
			}
		}

		// if(!($('#mo').val().length == 8 || $('#mo').val().length == 11)) {
		// 	$('#errorTips').html('您输入的手机号格式不正确').show();
		// 	return false;
		// }

		// if($('#vcode').val().length < 2) {
		// 	$('#errorTips').html('请输入手机验证码').show();
		// 	return false;
		// }


		var pLen = $('#pwdtrade').val().length;
		if(pLen < 6 || pLen > 20) {
			$('#errorTips').html('密码长度在6-20个字符之间').show();
			return false;
		}

		var repwdLen = $('#repwdtrade').val().length;
		if(repwdLen < 6 || repwdLen > 20) {
			$('#errorTips').html('密码长度在6-20个字符之间').show();
			return false;
		}

		if($('#repwdtrade').val() != $('#pwdtrade').val()) {
			$('#errorTips').html('密码不一致，请重新输入').show();
			return false;
		}

		// if($('#captcha').val().length < 2) {
		// 	$('#errorTips').html('请输入图形验证码').show();
		// 	return false;
		// }
		// 证件图片判断
		if (!authFile.frontFace ) {
			$('#errorTips').html('请选择需要上传的证件正面图片').show();
			return false
		}
		if (!authFile.backFace) {
			$('#errorTips').html('请选择需要上传的证件被面图片').show();
			return false
		}
		if (!authFile.handkeep) {
			$('#errorTips').html('请选择需要上传的证件手持拍照图片').show();
			return false
		}

		return true;
	}

	var interValObj;
	var curCount = 1*60;
	$('#btnSendCode').bind('click', regCode);

	function regCode()
	{
		var mo = $('#mo').val();
		var reg = /^0?1[3|4|5|7|8][0-9]\d{8}$/;

		if (!reg.test(mo))
		{
			// return validateMsg('mo', '您输入的手机号格式不正确', 0);
			$('#errorTips').html('您输入的手机号格式不正确1').show();
			return false;
		}
//	if ($('#pstype').val() == 1)
//	{
//		$('#errorTips').html('该手机已经使用，不能绑定').show();
//		return false;
//	}
		// 发送短信之前验证图形验证码
		if (!$('#captcha').val())
		{
			$('#errorTips').html('请填写图形验证码').show();
			return false;
		}

		$.post('/user_exchange/verifycheck', {'captcha': $('#captcha').val()}, function (data)
		{
			if (data == 'fail')
			{
				$('#errorTips').html('图形验证码错误').show();
				return false;
			}
			else
			{
				$('#errorTips').hide();

				$('#btnSendCode').unbind('click');
				// 取消绑定事件
				var sign = '<?php echo $sign?>';
				var timestamp = <?php echo  $timestamp?>;
				var noncestr = '<?php echo $noncestr?>';
				// var url  = '/user/sendmsg?timestamp=' + timestamp + '&noncestr=' + noncestr + '&sign=' + sign;
				var url = "/ajax/sendregmsg";
				$.ajax({
					type    : "GET",
					url     : url,
					data    : {phone: $("#mo").val(), captcha: $('#captcha').val()},
					dataType: "json",
					success : function (data)
					{

						if (1 == data)
						{
							$("#btnSendCode").val("重新发送(" + curCount + ")");
							interValObj = window.setInterval(setRemainTime, 1000); //启动计时器，1秒执行一次
						}
						else
						{
							$('#errorTips').html('您操作太频繁了,请稍后再试').show();
							$('#btnSendCode').bind('click', regCode);
						}
						$('#captchaimg').click();//刷新验证码
//						window.clearInterval(interValObj);//停止计时器

					},
					error   : function ()
					{
						$('#btnSendCode').bind('click', regCode);
						window.clearInterval(interValObj);//停止计时器
						alert("已发送短信次数太多，请稍后再试");
					}
				});
			}
		})
	}
	function setRemainTime() {
		if (curCount <= 0) {
			window.clearInterval(interValObj);//停止计时器
			$("#btnSendCode").removeAttr("disabled");//启用按钮
			$("#btnSendCode").val("重新发送验证码");
			$('#btnSendCode').bind('click', regCode);
			curCount = 1*60;
		} else {
			curCount--;
			$("#btnSendCode").val("重新发送("+curCount+")");
		}
	}
</script>
<?php  include PATH_TPL.'/tpl.footer.phtml'?>
<script>
	var reloadPage = '<?php  echo  isset($errorTips) ? $errorTips : '' ?>';
	if (reloadPage == '完善资料成功') {
		setTimeout(function() {
			top.location = '/user_index';
		}, 2000);
	}
</script>
