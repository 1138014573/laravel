<?php  include PATH_TPL.'/tpl.header.phtml';?>
<style media="screen">
    input[type="file"] {
        display: inline-block;
        height: 30px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
    }
    input[type="file"]:hover {
        cursor: pointer;
    }
    .auth_pic {
        width: 100%;
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
    .auth_pic img {
      display: none;
      width: 300px;
    }
</style>
<div class="content clear">
    <div class="center">
        <div class="wrap">
            <div class="box">
                <div class="TitleBox">
                    <h3 class="PlateTitle">完善资料</h3>
                </div>
                <div class="loginBox">
                    <form id="form" method="post" onsubmit="return modifyDo();" class="modifyLeft" enctype="multipart/form-data">
                        <!-- 真实姓名 -->
                        <div class="login">
                            <div class="clear">
                                <span><b class="importantB">*</b>真实姓名：</span>
                                <input type="text" value="<?php  if(isset($data['name'])) echo $data['name'] ?>" id="name" name="name" class="loginValue" placeholder="请输入真实姓名">
                            </div>
                        </div>
                        <!-- 证件类型 -->
                        <div class="login">
                            <div class="clear">
                                <span><b class="importantB">*</b>证件类型：</span>
                                <select class="loginValue" id="cardtype" name="cardtype" style="width:232px;height:36px;line-height:36px;">
                                    <option value="1" selected>身份证</option>
                                    <option value="2">驾驶证</option>
                                    <option value="3">护照</option>
                                    <option value="4">军官证</option>
                                    <option value="5">港澳通行证</option>
                                    <option value="6">香港身份证</option>
                                    <option value="7">澳门身份证</option>
                                </select>
                                <span style="text-align: left">下拉有更多选项</span>
                            </div>
                        </div>
                        <!-- 身份證号 -->
                        <div class="login">
                            <div class="clear">
                                <span><b class="importantB">*</b>证件号：</span>
                                <input type="text" value="<?php  if(isset($data['idcard'])) echo $data['idcard'] ?>" id="idcard" name="idcard" class="loginValue" placeholder="请输入有效的证件号">
                            </div>
                        </div>

                        <!-- 选择证件照片 -->
                        <div class="login">
                            <div class="clear">
                                <span><b class="importantB">*</b>实名状态：待审核</span>
                            </div>
                        </div>
                        <div class="auth_pic">
                            <div class="">
                                <p>正面</p>
                                <input type="file" name="frontFace" id="frontFace" data-auth="pic">
                                <img src="" alt="">
                            </div>
                            <div class="">
                                <p>背面</p>
                                <input type="file" name="backFace" id="backFace" data-auth="pic">
                                <img src="" alt="">
                            </div>
                            <div class="">
                                <p>手持证件照</p>
                                <input type="file" name="handkeep" id="handkeep" data-auth="pic">
                                <img src="" alt="">
                            </div>
                        </div>
                        <!-- error Tips -->
                        <div class="errorTips modifyErrorTips" id="errorTips" style="display: <?php  echo isset($errorTips) ? 'block' : 'none'; ?>;"><?php  echo isset($errorTips) ? $errorTips : ''; ?></div>

                        <div class="loginBtn clear">
                            <input type="submit" value="重新认证" class="regBtn modifyBtn">
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

    });
    
    // 检测
  	+function () {
  		if (typeof(FileReader) === 'undefined') {
  			alert("抱歉，你的浏览器不支持 FileReader,请使用谷歌(chrome)或火狐(firefox)浏览器操作！");
  		}
  	}();
  	// img check result
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
  		var img = $(this).siblings('img');
  		img.hide();
  		//
  		if (size > 2097152) {
  			return alert('所选图片大小不能超过2M。');
  		}
  		if (fileType.match(/(jpg|jpeg|png)/ig) === null) {
  			return alert('证件图片支持jpg/jpeg/png格式,暂时还不支持其他格式。');
  		}
  		var reader = new FileReader();
      reader.readAsDataURL(this.files[0]);
  		reader.onload = function(e) {
  			img.attr('src', this.result).show();
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
    if (reloadPage == '重新认证成功') {
        setTimeout(function() {
            top.location = '/user_index';
        }, 2000);
    }
</script>
