<?php
/**
 * 管理中心首页
 */
class User_IndexController extends Ctrl_Base{
	# 管理员
	protected $_auth = 3;
	/**
	 * 首页
	 */
	public function indexAction(){
		$this->layout('seot', '个人中心-币交所-数字货币交易平台');

		$pairs = User_CoinModel::getInstance()->getList();
		$this->assign('pairs', $pairs);
	}
	/**
	 * 修改登录密码
	 */
	public function pwdAction(){
		$this->layout('seot', '修改登录密码-币交所-数字货币交易平台');
		# 数据处理
		if('POST' == $_SERVER['REQUEST_METHOD']){
			$this->assign('data', $_POST);
			# 操作太快了
			if($_SERVER['REQUEST_TIME'] - $this->mCurUser['updated'] < Yaf_Registry::get("config")->opt_mintime){
				return $this->assign('msg_updated', 1);
			}
			# 旧密码
			if(Tool_Md5::encodePwd($_POST['oldpwd'], $this->mCurUser['prand']) != $this->mCurUser['pwd']){
				return $this->assign('errorTips', '登录密码错误，请重新输入');
			}
			# 手机验证码
            if( !( PhoneCodeModel::verifiCode($this->mCurUser,7,trim($_POST['code'])) || PhoneCodeModel::verifiCode($this->mCurUser,8,trim($_POST['code'])) ) ){
                return $this->assign('errorTips', '手机验证码错误');
            }
			$tData = array('uid' => $this->mCurUser['uid'], 'updated' => time(), 'updateip' => USER_IP);
			# 密码
			if($_POST['pwd']){
				if($_POST['pwd'] != $_POST['repwd']){
					return $this->assign('errorTips', '新登录密码不一致，请重新输入');
				}
				$tData['pwd'] = Tool_Md5::encodePwd($_POST['pwd'], $this->mCurUser['prand']);
			}
			# 保存到 MYSQL
			$tMO = new UserModel;
			$tMO->update($tData);
			# 保存到 SESSION
			foreach($tData as $k1 => $v1) $this->mCurUser[$k1] = $v1;
			$_SESSION['user'] = $this->mCurUser;
			$tMO->saveRedis($this->mCurUser);
			# 保存成功
			$this->showMsg('保存成功', '/user/logout');
			$this->assign('errorTips', '保存成功');
		}
	}

	/**
	 * 账户信息
	 */
	public function userinfoAction(){
		$this->layout('seot', '账户信息-币交所-数字货币交易平台');

		if (UserModel::isModify($this->mCurUser['uid'])) {
            $this->assign('authFlag', 1);
        }else{
        	$this->assign('authFlag', 2);
        }

		$this->assign('data', $this->mCurUser);
	}

	/**
	 * 交易密码 重置
	 */
	public function tradepwdAction(){
		$this->layout('seot', '修改交易密码-币交所-数字货币交易平台');

		if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

		$this->seo('修改交易密码');
		# 数据处理
		if('POST' == $_SERVER['REQUEST_METHOD']){
			$this->assign('data', $_POST);
			# 操作太快了
			if($_SERVER['REQUEST_TIME'] - $this->mCurUser['updated'] < Yaf_Registry::get("config")->opt_mintime){
				return $this->assign('msg_updated', 1);
			}
			# 旧交易密码
			if(Tool_Md5::encodePwdTrade($_POST['oldpwdtrade'], $this->mCurUser['prand']) != $this->mCurUser['pwdtrade']){
				return $this->assign('errorTips', '交易密码错误，请重新输入');
			}
			# 交易密码
			if(6 > strlen($_POST['pwdtrade']) || strlen($_POST['pwdtrade']) > 20){
				return $this->assign('errorTips', '交易密码长度在6-20个字符之间');
			}
			# 判断新交易密码和登录密码是否相同
			if(Tool_Md5::encodePwd($_POST['pwdtrade'], $this->mCurUser['prand']) == $this->mCurUser['pwd']){
				return $this->assign('errorTips', '交易密码不能与登录密码相同，请重新输入');
			}
			if($_POST['pwdtrade'] <> $_POST['repwdtrade']){
				return $this->assign('errorTips', '新交易密码不一致，请重新输入');
			}
			# 手机验证码
            if( !( PhoneCodeModel::verifiCode($this->mCurUser,7,trim($_POST['code'])) || PhoneCodeModel::verifiCode($this->mCurUser,8,trim($_POST['code'])) ) ){
                return $this->assign('errorTips', '手机验证码错误');
            }
			$tData = array('uid' => $this->mCurUser['uid'], 'pwdtrade'=>Tool_Md5::encodePwdTrade($_POST['pwdtrade'], $this->mCurUser['prand']), 'updated' => time(), 'updateip' => USER_IP);
			# 保存到 MYSQL
			$tMO = new UserModel;
			if ( $tMO->update($tData) ) {
				# 保存到 SESSION
				foreach($tData as $k1 => $v1) $this->mCurUser[$k1] = $v1;
				$_SESSION['user'] = $this->mCurUser;
				# 保存成功
				$this->assign('errorTips', '保存成功');
				@$this->userlog($this->mCurUser['email'], '', 'ok', $tData['updateip']);

				$this->showMsg('保存成功', '/user/logout');
			}
			else {
				return $this->assign('errorTips', '系统错误，请重新尝试');
			}
		}
		$this->assign('data', $this->mCurUser);
	}

	/**
	 * 账户双重验证
	 * $_GET['opt']: open:开启, close:关闭, clear:清除
	 * $_GET['hotp']: 数字，双重(手机)密码
	 * $_GET['secret']: 随机KEY
	 */
	public function twofactorAction(){
		$this->layout('seot', '账户双重验证-币交所-数字货币交易平台');
		# 已有记录 #hotp, #opt
		$tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']);
		if($tGA['secret']){
			$this->assign('ga', $tGA);
			# 提交请求处理
			if(!isset($_POST['hotp'], $_POST['opt'])) return;
			# 验证密码
			if(!Api_Google_Authenticator::verify_key($tGA['secret'], $_POST['hotp'])){
				return $this->assign('errorTips', '验证密码错误');
				// Tool_Fnc::showMsg('验证密码错误', '/user_index/twofactor/');
			}
			# 关闭验证
			if(in_array($_POST['opt'], array('close', 'open'))){
				Cache_Redis::instance()->hSet('user_ga', $this->mCurUser['uid'], $tGA['secret'].','.($tOpen = $_POST['opt']=='close'?0:1));
				@setcookie('GA_'.$this->mCurUser['uid'], $tOpen, $_SERVER['REQUEST_TIME']+8640000, '/');
				return $this->assign('errorTips', '您已'.($_POST['opt']=='close'?'关闭':'开启').'双重验证密码');
				// Tool_Fnc::showMsg('您已'.($_POST['opt']=='close'?'关闭':'开启').'双重验证密码', '/user_index/twofactor/');
			}
			# 清空验证
			if($_POST['opt'] == 'clear'){
				@setcookie('GA_'.$this->mCurUser['uid'], 0, $_SERVER['REQUEST_TIME']+8640000, '/');
				Cache_Redis::instance()->hDel('user_ga', $this->mCurUser['uid']);
				return $this->assign('errorTips', '您已清除双重验证密码');
				// Tool_Fnc::showMsg('您已清除双重验证密码', '/user_index/twofactor/');
			}
			return;
		}
		# 提交 #hotp, #secret
		if(isset($_POST['hotp'], $_POST['secret']) && !isset($_POST['opt'])){
			if(!Api_Google_Authenticator::verify_key($_POST['secret'], $_POST['hotp'])){
				$this->ajax('验证密码错误');
			}
			@setcookie('GA_'.$this->mCurUser['uid'], 1, $_SERVER['REQUEST_TIME']+8640000, '/');
			Cache_Redis::instance()->hSet('user_ga', $this->mCurUser['uid'], $_POST['secret'].',1');
			if(!Member::GetLog($this->mCurUser['uid'], 8))
				Member::AddUserCredit($this->mCurUser['uid'], 8);//双重验证
			Cache_Redis::instance()->hSet('usersession', $this->mCurUser['uid'], 1);
			$this->ajax('您已成功设置双重验证密码', 1);
		}
		$this->assign('secret', Api_Google_Authenticator::generate_secret_key());
	}

	public function inviteAction(){
		$this->layout('seot', '邀请好友-币交所-数字货币交易平台');

		if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }
	}
}
