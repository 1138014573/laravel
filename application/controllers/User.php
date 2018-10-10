<?php
/**
 * 用户
 */
class UserController extends Ctrl_Base{
	# 启用 SESSION
	protected $_auth = 1;
	# 注册发送短信接口key
	protected $_sendMsgKey = 'regsendcode';

	/**
	 * 登录
     */
    public function loginAction(){
		$this->seo('用户登录-币交所-数字货币交易平台');
		# POST 请求
		if('POST' == $_SERVER['REQUEST_METHOD']){
			if(!$this->valiCaptcha()) {
                return $this->assign('errorTips', '验证码有误，请重新输入');
            }
			# 密码不能为空
			if(empty($_POST['pwd'])){
				return $this->assign('errorTips', '密码不能为空，请重新输入');
			}
			# 验证邮箱
			if(empty($_POST['email'])){
				return $this->assign('errorTips', '请填写您的登录邮箱');
			}
			if(!Tool_Validate::email($_POST['email'] = strtolower($_POST['email']))){
				return $this->assign('errorTips', '邮箱格式不正确');
			}
			# 账号存在
			if(!$tUser = UserModel::getRedis($_POST['email'], 'array')){
				return $this->assign('errorTips', '账号不存在，请重新输入');
			}
			# 验证密码
			if($tUser['pwd'] != Tool_Md5::encodePwd($_POST['pwd'], $tUser['prand'])){
				return $this->assign('errorTips', '密码错误，请重新输入');
			}
			# 成功跳转
			$user   = UserModel::getByEmail($_POST['email'], false);
		    $_SESSION['user']	= $user;

		    # 登录之后存储登录信息
		    $str = array('platform'=>'pc', 'sess_id'=>$_COOKIE['PHPSESSID'], 'uuid'=>$_COOKIE['BJSUUID'], 'flag'=>1);
		    Cache_Redis::instance()->hSet('userloginpc', $user['uid'], json_encode($str));

		    $wapArr = json_decode( Cache_Redis::instance()->hGet('userloginwap', $user['uid']), true );
		    Cache_Redis::instance('session')->del( $wapArr['sess_id'] );

			# 登陆日志
			/*if(empty($_COOKIE['BJSUUID'])){
				@setcookie('BJSUUID', $uuid = Tool_Uuid::get_uuid(), $_SERVER['REQUEST_TIME'] + 31536000, '/');
				LoginLogModel::addloginlog($user['uid'],session_id(),$uuid);
			}else{
				LoginLogModel::addloginlog($user['uid'],session_id(),$_COOKIE['BJSUUID']);
			}*/
			Cache_Redis::instance()->hSet('usersession', $user['uid'], 1);
			$this->showMsg('', '/');
		}
	}

	/**
	 * 退出
	 */
	public function logoutAction(){
		if(isset($this->mCurUser['uid']))
			// LoginLogModel::updatelogoutlog($this->mCurUser['uid']);
			Tool_Md5::pwdTradeCheck($this->mCurUser['uid'], 'del');
			session_destroy();
			$redis = Cache_Redis::instance();
			$redis->del('admin_google_auth_'.$this->mCurUser['uid']);
			$this->showMsg('', '/');
	}

	/**
	 * 注册
	 */
    public function registerAction($pid = ''){
        $this->seo('用户注册-币交所-数字货币交易平台');
        $pid = isset($_POST['pid']) && !empty($_POST['pid']) ? $_POST['pid'] : $pid;
        $this->assign('pid', $pid);

        # 数据处理
        if('POST' == $_SERVER['REQUEST_METHOD']){
            $this->assign('data', $_POST);
            $this->assign('posttype', 1);
            if(!$this->valiCaptcha()) {
                return $this->assign('errorTips', '验证码有误，请重新输入');
            }
            # 密碼
            if($_POST['pwd'] != $_POST['repwd']){
                return $this->assign('errorTips', '密码不一致，请重新输入');
            }
            # Email
            $_POST['email'] = trim($_POST['email']);
            if(!Tool_Validate::email($_POST['email'] = strtolower($_POST['email']))){
                return $this->assign('errorTips', '电子邮箱格式不正确，请重新输入');
            }
            if(UserModel::getRedis($_POST['email'])){
                return $this->assign('errorTips', '邮箱已经注册，你可以换个邮箱');
            }
			$prand = Tool_Md5::getUserRand();
			# 推荐用户监测
            if( (is_numeric($_POST['pid']) && $_POST['pid']<100800) || ($_POST['pid'] != '' && !UserModel::getInstance()->where('uid='.$pid)->fOne('uid')) ) {
                return $this->assign('errorTips', '推荐码有误，请重新输入');
            }

            # 保存到 MYSQL
            $tData = array(
                'pid' => $_POST['pid'],
                'email' => $_POST['email'],
				'prand' => $prand,
				'pwd' => Tool_Md5::encodePwd($_POST['pwd'], $prand),
                'created' => $_SERVER['REQUEST_TIME'],
                'createip' => Tool_Fnc::realip(),
                'rate'=>0.002,
                'role'=>'user',
                'updated'=>$_SERVER['REQUEST_TIME']
            );

            $tMO = new UserModel;
            if($tData['uid'] = $tMO->insert($tData)){
                UserModel::saveRedis($tData);
                Cache_Redis::instance()->hSet('usersession', $tData['uid'], 1);
                $_SESSION['user'] = $tData;
                #激活郵件
                $eMO = new EmailactivateModel;
                $pTime =$_SERVER['REQUEST_TIME'];
                $pKey = Tool_Md5::emailActivateKey($_POST['email'] , $pTime);
                $eData = array(
                    'uid' => $tData['uid'],
                    'email' => $_POST['email'],
                    'reg_time' => $pTime,
                    'senttime' => $pTime,
                    'key' => $pKey,
                );
                $eMO->insert($eData);

                # Insert Invite
	            if( isset($_POST['pid']) && $_POST['pid'] != '' && $_POST['pid'] != 0 ) {
	                $inviteMo = new InviteModel;
	                $invite_data = array(
	                    'fuid' => $pid,
	                    'tuid' => $tData['uid'],
	                    'email' => $_POST['email'],
	                    'created' => time()
	                );
	                $inviteMo->insert($invite_data);
	            }

               /* if(empty($_COOKIE['BJSUUID'])){
                    @setcookie('BJSUUID', $uuid = Tool_Uuid::get_uuid(), $_SERVER['REQUEST_TIME'] + 31536000, '/');
                    LoginLogModel::addloginlog($tData['uid'],session_id(),$uuid);
                }else{
                    LoginLogModel::addloginlog($tData['uid'],session_id(),$_COOKIE['BJSUUID']);
                }*/

                UserModel::saveEmailRedis(array('key' => $pKey , 'uid' => $tData['uid'] , 'email' => $_POST['email'] , 'name' => $_POST['email']));
                # 进入用戶中心
                $this->showMsg('', '/user_emailverify');
            }
        }
    }

	# mobile reg
	/**
	 * 注册
	 */
	public function signupAction($pid = 0){
		exit('errors');
		if (Tool_Fnc::isMobile()) {
			$this->showMsg('', '/user/reg');
		}

        //return $this->showMsg('注册功能暂未开放');
		$this->seo('注册成为网站会员');
        $pid = isset($_POST['pid']) && !empty($_POST['pid']) ? $_POST['pid'] : $pid;
		$this->assign('pid', $pid);
		# 数据处理
		if('POST' == $_SERVER['REQUEST_METHOD']){
			$this->assign('data', $_POST);
			if(!$this->valiCaptcha()) return;
			# 姓名
			if(!empty($_POST['name']) && !Tool_Validate::name($_POST['name'])){
				return $this->assign('msg_name', '姓名包含非法字符');
			}
			# 登录密码
			if(6 > strlen($_POST['pwd']) || strlen($_POST['pwd']) > 20){
				return $this->assign('msg_pwd', '密码长度在6-20个字符之间。');
			}
			# 交易密码
			if(6 > strlen($_POST['pwdtrade']) || strlen($_POST['pwdtrade']) > 20){
				return $this->assign('msg_pwdtrade', '交易密码长度在6-20个字符之间。');
			}
			# Email
			if(!Tool_Validate::email($_POST['email'] = strtolower($_POST['email']))){
				return $this->assign('msg_email', '电子邮箱格式不正确，请重新输入');
			}
			if(UserModel::getRedis($_POST['email'])){
				return $this->assign('msg_email', '邮箱已经注册，你可以换个邮箱或直接<a href="/user/login">登录</a>');
			}
			//姓名
			$_POST['name']  = trim($_POST['name']);
			if(empty($_POST['name'])){
				return $this->assign('msg_name', '真实姓名错误，请重新输入');
			}
			# 手机号码
			$_POST['mo']    = trim($_POST['mo']);
			if(empty($_POST['mo']) || !Tool_Validate::mo($_POST['mo'])){
				return $this->assign('msg_mo', '手机号码错误，请重新输入');
			}
			//验证身份证
			$_POST['cardtype'] = 1;
			$_POST['idcard']    = trim($_POST['idcard']);
			if(empty($_POST['idcard']) || ($_POST['cardtype'] == 1 && !Tool_Validate::identify($_POST['idcard']))){
			    //if(empty($_POST['idcard'])){
				return $this->assign('msg_idcard', '证件号码错误，请重新输入');
			}
			//验证邀请人id
			$_POST['pid'] = isset($_POST['pid']) && is_numeric($_POST['pid']) ? trim($_POST['pid']) : 0;
			# 保存到 MYSQL
			$_POST['idcard'] = $_POST['cardtype'] == 1?Tool_Str::filter(strtolower($_POST['idcard'])):$_POST['idcard'];
			$tData = array('pid' => $_POST['pid'], 'name' => $_POST['name'], 'email' => $_POST['email'], 'pwd' => md5($_POST['pwd']),
				'pwdtrade' => md5($_POST['pwdtrade']), 'created' => $_SERVER['REQUEST_TIME'], 'createip' => Tool_Fnc::realip(),
				'cardtype' => $_POST['cardtype'], 'idcard' => $_POST['idcard'], 'mo' => $_POST['mo'], 'credit'=>0, 'goc_over'=>0, 'goc_lock'=>0, 'cny_over'=>0, 'cny_lock'=>0, 'rate'=>0.002, 'role'=>'user', 'updated'=>$_SERVER['REQUEST_TIME']
			);

			$tMO = new UserModel;
			if($tData['uid'] = $tMO->insert($tData)){
				UserModel::saveRedis($tData);
				Cache_Redis::instance()->hSet('usersession', $tData['uid'], 1);
				$_SESSION['user'] = $tData;
                #激活邮件
                $eMO = new EmailactivateModel;
                $pTime =$_SERVER['REQUEST_TIME'];
                $pKey = Tool_Md5::emailActivateKey($_POST['email'] , $pTime);
                $eData = array(
                    'uid' => $tData['uid'],
                    'email' => $_POST['email'],
                    'reg_time' => $pTime,
                    'senttime' => $pTime,
                    'key' => $pKey,
                );
                $eMO->insert($eData);
				if(empty($_COOKIE['BJSUUID'])){
					@setcookie('BJSUUID', $uuid = Tool_Uuid::get_uuid(), $_SERVER['REQUEST_TIME'] + 31536000, '/');
					LoginLogModel::addloginlog($tData['uid'],session_id(),$uuid);
				}else{
					LoginLogModel::addloginlog($tData['uid'],session_id(),$_COOKIE['BJSUUID']);
				}

                UserModel::saveEmailRedis(array('key' => $pKey , 'uid' => $tData['uid'] , 'email' => $_POST['email'] , 'name' => $_POST['name']));
				# 进入用户中心
				$this->showMsg('', '/user_emailverify');
			}
		}
	}

	# 找回密码
	public function resetAction(){
		$this->seo('忘记密码-币交所-数字货币交易平台');
		if('POST' == $_SERVER['REQUEST_METHOD']){
			# 验证码
			if(!$this->valiCaptcha()) {
                return $this->assign('errorTips', '验证码有误，请重新输入');
            }
			# 邮箱
			if(!Tool_Validate::email($_POST['email'] = strtolower($_POST['email']))){
                return $this->assign('errorTips', '邮箱错误，请重新输入');
			}
			if(!$tUser = UserModel::getRedis($_POST['email'])){
                return $this->assign('errorTips', '邮箱不存在');
			}
			$http = $_SERVER['HTTPS'] ? 'https://' : 'http://';
			$url =  $http.$_SERVER['SERVER_NAME'].'/user/resetpwd/email/'.$_POST['email'].'/k/'.md5('goc.'.$_POST['email'].$tUser);
			Tool_Fnc::mailto($_POST['email'], '币交所 - 找回密码', '您好，<br><br>请点击链接：<br><a href="'.$url.'">重置您的密码</a><br><br>如果链接无法点击，请复制并打开以下网址：<br>'.$url);
			$this->showMsg('请您到邮箱查收邮件：'.$_POST['email'], '/');
		}
	}

	# 重设密码
	public function resetpwdAction($email, $k){
		# 验证KEY
		if(empty($k)) $this->showMsg('验证码有误');
		if(empty($email) || !Tool_Validate::email($email) || !$tUser = UserModel::getRedis($email)){
			$this->showMsg('邮箱有误');
		}
		if($k != md5('goc.'.$email.$tUser)){
			$this->showMsg('验证失败');
		}
		# POST 请求处理
		if('POST' == $_SERVER['REQUEST_METHOD']){
			# 验证码
			if(!$this->valiCaptcha()){
				return $this->assign('errorTips', '验证码错误');
			};
			# 验证输入
			if(!isset($_POST['pwd'], $_POST['repwd']) || $_POST['pwd'] != $_POST['repwd']){
				return $this->assign('errorTips', '两次输入密码不一致');
			}
			if(6 > strlen($_POST['pwd'])){
				return $this->assign('errorTips', '密码太短，不安全，请重新输入');
			}
			# 保存数据
			$tData = array('email'=>$email);
			list($tData['pwd'], $tData['uid'], $tData['role'], $tData['prand']) = explode(',', $tUser);
			$tData['pwd'] = Tool_Md5::encodePwd($_POST['pwd'], $tData['prand']);
			# 保存DB
			$tMO = new UserModel();
			if(FALSE !== $tMO->update($tData)) {
				UserModel::saveRedis($tData);
				$this->showMsg('保存成功，请返回重新登录', '/user/login/');
			}
			$this->showMsg('保存出错，请返回重新操作');
		}
	}
    #邮箱激活验证
    public function emailactivateAction(){
        $pId = trim($_GET['id']) or Tool_Fnc::showMsg('激活失败', '/user/login/');
        $pUrl = unserialize(base64_decode($pId));
        is_array($pUrl) ? '':Tool_Fnc::showMsg('激活失败', '/user/login/');

        if(empty($pUrl['uid']) || empty($pUrl['email']) || empty($pUrl['key'])){
            Tool_Fnc::showMsg('参数错误:2');
        }

        if(!Tool_Validate::email($pUrl['email'])){ Tool_Fnc::showMsg('激活失败', '/user/login/');}
        if(!Tool_Validate::int($pUrl['uid'])){ Tool_Fnc::showMsg('激活失败', '/user/login/');}
        if(!Tool_Validate::safe($pUrl['key'])){ Tool_Fnc::showMsg('激活失败', '/user/login/');}

        $tMO = new EmailactivateModel;
        $tData = $tMO->fRow('SELECT * FROM email_activate WHERE uid = ' . $pUrl['uid'] . ' and email = \''.$pUrl['email'].'\' limit 1');
        if(!count($tData)){Tool_Fnc::showMsg('激活失败:6', '/user/login/');}

        #激活时间限制
        $time = time();
        if(($time - $tData['senttime']) > 1800){
            Tool_Fnc::showMsg('激活时间不能超过30分钟,请您重新发送验证邮件进行激活！', '/user_emailverify');
        }

        if($tData['activate_time']){
		    if($this->mCurUser) unset($_SESSION['user']);
            Tool_Fnc::showMsg('您的邮箱已经激活' , '/user/login/');
        }
        if($pUrl['key'] == $tData['key']){
            if(!$tMO->exec('UPDATE email_activate set activate_time = ' . $time . ' WHERE uid = ' . $pUrl['uid'])){
                Tool_Fnc::showMsg('激活失败:7', '/user/login/');
            }else{
		        if($this->mCurUser){
					//邀请积分奖励
					if($this->mCurUser['pid'] > 0){
						//Member::FriendsAddLog($this->mCurUser['pid']);
						Cache_Redis::instance()->hSet('usersession', $this->mCurUser['pid'], 1);
					}
					session_destroy();
				}else{
					$tMO = new UserModel();
					$userdata = $tMO->getById($pUrl['uid']);
					if($userdata['pid'] > 0){
						//Member::FriendsAddLog($userdata['pid']);
						Cache_Redis::instance()->hSet('usersession', $userdata['pid'], 1);
					}
				}
                Tool_Fnc::showMsg('您的邮箱激活成功' , '/user/login/');
            }
        }else{Tool_Fnc::showMsg('由于激活链接已过期，请您重新发送验证邮件进行激活！如仍然失败，请联系官方客服处理。', '/user/login/');}
    }

   # 注册发送短信接口
   public function sendMsgAction() {
   		$err = array('Code'=>0, 'Msg'=>'');
		$realip = Tool_Fnc::realip();
		$allow 	= !isset($_GET['sign']) or empty($_GET['sign'])
				  or !isset($_GET['timestamp']) or empty($_GET['timestamp'])
				  or !isset($_GET['noncestr']) or empty($_GET['noncestr']);
        if( $allow ){
        	$err['Code']= -1;$err['Msg'] = '非法操作';exit(json_encode($err));
        }

        $sign 		= $_GET['sign'];
        $timestamp 	= $_GET['timestamp'];
        $noncestr 	= $_GET['noncestr'];
        $key 		= $this->_sendMsgKey;
        $newSign 	= md5(md5($timestamp.$noncestr) . $key);
        if( $newSign != $sign ) {
        	$err['Code']= -1;$err['Msg'] = '非法操作';exit(json_encode($err));
        }

        # 10分钟页面失效
        if( (time() - $timestamp) > 10*60 ) {
        	$err['Code']= -2;$err['Msg'] = '页面已失效，请刷新页面';exit(json_encode($err));
        }

		if( !isset($_POST['mo']) || empty($_POST['mo'])){
			$err['Code'] = -3;$err['Msg'] = '手机号为空';exit(json_encode($err));
		}

		$mo = trim($_POST['mo']);

		# 是否是手机号
		if( !Tool_Validate::mo($mo) ) {
			$err['Code'] = -3;$err['Msg'] = '手机号错误';exit(json_encode($err));
		}

		# 检测手机号是否被使用
		$user_mo = new UserModel();
		$uid 	= $user_mo->where('mo=' . $mo)->fOne('uid');
		if($uid) {
			$err['Code'] = -3;$err['Msg'] = '手机号已被使用';exit(json_encode($err));
		}

		$pc 	= new PhoneCodeModel();
		$ctime 	= time() - 300;
		# 更新数据库过期验证码状态
		$pc->exec("update {$pc->table} set status=2,utime={$_SERVER['REQUEST_TIME']}  where action=0 and status=0 and ctime<={$ctime}");
		# 查询是否已经发送，发过提示已发送
		$where 	= "mo = {$mo} and action=0 and status=0 and ctime>={$ctime}";
		$code   = $pc->where($where)->fOne('code');
		if( $code ) {
			$err['Code'] = -3;$err['Msg'] = '验证码已发送';exit(json_encode($err));
		}

		# 检测一天发送短信数量
		$stime 	= strtotime(date('Y-m-d'));
		$swhere = "mo = {$mo} and action=0 and uid=0 and ctime>={$stime}";
		$count  = $pc->where($swhere)->count();
		$count  = $count ? $count : 0;
		if($count>4) {
			$err['Code'] = -3;$err['Msg'] = '一天内注册验证码最多发5条';exit(json_encode($err));
		}

		$rand 	= rand(100000,999999);
		$message = '【币交所】您的验证码是'.$rand.'，如非本人操作，请忽略本短信。';
		# 写入数据库
		$insert = array(
			'code'=>$rand,
			'uid'=>$this->mCurUser['uid'],
			'mo'=>$mo,
			'message'=>$message,
			'action'=>0,
			'aid'=>0,
			'status'=>0,
			'ctime'=>time(),
			);

		if( $pc->insert($insert) ) {
			# 发送短信
            $returnMsg = Tool_Message::run('send', $mo, $message);
            $smsg = array();
            $smsg['Code'] = $returnMsg['code'] ? 0 : 1;

            if($smsg['Code'] == 1){
                $err['Code'] = 0;$err['Msg'] = '成功';exit(json_encode($err));
            }

            if($smsg['Code'] == -1 || $smsg['Code'] == -2){
                $err['Code'] = -2;$err['Msg'] = '账户非法';exit(json_encode($err));
            }

            if($smsg['Code'] == -3){
                $err['Code'] = -3;$err['Msg'] = '短信存量不足';exit(json_encode($err));
            }
		}

		$err['Code'] = -3;$err['Msg'] = '发送验证码失败';exit(json_encode($err));
	}

    # modify User
    public function modifyAction()
    {
        if(empty($this->mCurUser)) {
            return $this->showMsg('请先登录再执行操作', '/user/login');
        }

        $timestamp  = time();
        $noncestr   = rand(100,10000);
        $key        = $this->_sendMsgKey;
        $sign       = md5(md5($timestamp.$noncestr) . $key);
        $this->assign('timestamp', $timestamp);
        $this->assign('noncestr', $noncestr);
        $this->assign('sign', $sign);

        # judge isModify
        if (UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('', '/');
        }

        if ($this->getRequest()->isPost()) {
            $this->assign('data', $_POST);
            if(!$this->valiCaptcha()) return;
            # 姓名
            if(!empty($_POST['name']) && !Tool_Validate::name($_POST['name'])){
                return $this->assign('errorTips', '姓名包含非法字符');
            }

            # 交易密码
            if(6 > strlen($_POST['pwdtrade']) || strlen($_POST['pwdtrade']) > 20){
                return $this->assign('errorTips', '交易密码长度在6-20个字符之间');
            }
            if($_POST['pwdtrade'] != $_POST['repwdtrade']){
                return $this->assign('errorTips', '交易密码不一致，请重新输入');
            }

            //姓名
            $_POST['name']  = trim($_POST['name']);
            if(empty($_POST['name'])){
                return $this->assign('errorTips', '真实姓名错误，请重新输入');
            }

            # 手机号码
            $_POST['mo']    = trim($_POST['mo']);
            if(empty($_POST['mo']) || !Tool_Validate::mo($_POST['mo']) ){
                return $this->assign('errorTips', '手机号码错误，请重新输入');
            }

            # 手机验证码
            $_POST['vcode']    = trim($_POST['vcode']);
            if(empty($_POST['vcode'])){
                return $this->assign('errorTips', '手机验证码错误，请重新输入');
            }
            if (!PhoneCodeModel::verifiCode(array('uid' => $this->mCurUser['uid']), 0, (int)$_POST['vcode'])) {
            	return $this->assign('errorTips', '手机验证码错误，请重新输入');
            }


            # 验证身份证
            $_POST['idcard']    = trim($_POST['idcard']);
            if(empty($_POST['idcard']) || ($_POST['cardtype'] == 1 && !Tool_Validate::identify($_POST['idcard']))){
                //if(empty($_POST['idcard'])){
                return $this->assign('errorTips', '证件号码错误，请重新输入');
            }
            # 验证证件号码是否存在
            $cardflag = UserModel::getInstance()->where("idcard = '{$_POST['idcard']}' or idcard='".strtolower($_POST['idcard'])."'")->fRow();
            if( $cardflag ){
            	return $this->assign('errorTips', '此证件号码已存在，请联系客服');
            }

            # 保存到 MYSQL
            $_POST['idcard'] = $_POST['cardtype'] == 1?Tool_Str::filter($_POST['idcard']):$_POST['idcard'];
            $tData = array(
                'uid' => $this->mCurUser['uid'],
                'name' => $_POST['name'],
				'pwdtrade' => Tool_Md5::encodePwdTrade($_POST['pwdtrade'], $this->mCurUser['prand']),
                'cardtype' => $_POST['cardtype'],
                'idcard' => $_POST['idcard'],
                'mo' => $_POST['mo'],
                'updated'=>$_SERVER['REQUEST_TIME']
            );

            $tMO = new UserModel;
            if (!$tMO->update($tData)) {
                return $this->assign('errorTips', '系统异常，请稍后重试');
                // return $this->showMsg('系统异常，请稍后重试', '/');
            }

			$tRedis = Cache_Redis::instance();
			$tRedis->hSet('usersession', $this->mCurUser['uid'], 1);

            return $this->assign('errorTips', '完善资料成功');
            // return $this->showMsg('完善资料成功', '/user_index/');
        }

    }

}
