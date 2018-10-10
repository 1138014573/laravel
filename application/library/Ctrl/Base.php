<?php
/**
 * 基础类
 */
abstract class Ctrl_Base extends Yaf_Controller_Abstract{
	/**
	 * 开启 SESSION : 1
	 * 必须登录 : 2
	 * session登陆：3
	 * 必须管理员 : 4
	 */
	protected $_auth = 0;

	/**
	 * 当前登录用户
	 * @var array
	 */
	public $mCurUser = array();

	/**
	 * 构造函数
	 */
	public function init(){
		# 判断设备
		# $isMobile = Tool_Fnc::isMobile() ? true : false;
		# $this->layout('isMobile', $isMobile);

		# 移动端，从邮箱确认币种转出时，登录之后直接跳转至转出页面
		if( strpos($_SERVER['REQUEST_URI'], '/user_exchange/coinoutconfirm/coin') !== false ){
			@setcookie('request_uri', $_SERVER['REQUEST_URI'], $_SERVER['REQUEST_TIME'] + 120, '/');
		}

		if(empty($_COOKIE['BJSUUID'])){
			@setcookie('BJSUUID', $uuid = Tool_Uuid::get_uuid(), $_SERVER['REQUEST_TIME'] + 31536000, '/');
		}

		// 查询所有的币种
		/*$allCoins = Coin_PairModel::getInstance()->getList();
		foreach ($allCoins as $k => $v) {
			$allCoins[$k]['info'] = json_decode(Coin_PairModel::getInstance()->getCoInfo($v['name']), true);
		}
		$this->layout('allCoins', $allCoins);*/
		// 查询所有币种当前价格
		/*$coinPrices = json_decode(Coin_PairModel::getInstance()->getCoinPrice(), true);
		$this->layout('coinPrices', $coinPrices);*/

		(1 & $this->_auth) && $this->_session();
		(1 < $this->_auth) && $this->_role();
		#language choice,set view folder
		$this->setViewPath(PATH_TPL);
	}

	/**
	 * 以某EMAIL身份登录
	 * @param bool $ip
	 * @param string $email
	 */
	private function _login_by_email($ip=false, $email=''){
		if(!$ip || !$email) return;
		if($ip == USER_IP){
			$_SESSION['user'] = array('email'=>$email);
			$_GET['yafphp_session'] = 1;
		}
	}

	/**
	 * 需要登录
	 */
	protected function _session(){
		$this->domain = Tool_Url::getDomain();
		# 用户唯一标识
		if(empty($_COOKIE['USER_UNI'])){
			@setcookie('USER_UNI', $_COOKIE['USER_UNI'] = md5(uniqid()), $_SERVER['REQUEST_TIME'] + 86400, '/', $this->domain);
		}
		# 如果没有PHPSESSID，则程序给生成一个
		@$tSessId = md5($_SERVER['HTTP_USER_AGENT'].'BJS'.USER_IP.'.com'.$_COOKIE['USER_UNI']);
		if(empty($_COOKIE['PHPSESSID']) || $_COOKIE['PHPSESSID'] != $tSessId){
			@setcookie('PHPSESSID', $_COOKIE['PHPSESSID'] = $tSessId, $_SERVER['REQUEST_TIME'] + 86400, '/', $this->domain);
		}
		//session_start();
		new Tool_Session();

		# 当前登录用户
		if(!empty($_SESSION['user'])){
			# 正常用户处理
			$this->mCurUser = $_SESSION['user'];
			$tRedis = Cache_Redis::instance();

			if(isset($_GET['yafphp_session']) || $tRedis->hGet('usersession', $this->mCurUser['uid'])){
				$this->mCurUser = $_SESSION['user'] = UserModel::getByEmail($this->mCurUser['email']);
				$tRedis->hSet('usersession', $this->mCurUser['uid'], 0);
			}
            if(false ==strpos($_SERVER['REQUEST_URI'] ,'user/logout') && false ==strpos($_SERVER['REQUEST_URI'] ,'user/emailactivate')){
                $uid = empty($_SESSION['user']['uid'])?'':$_SESSION['user']['uid'];
                $this->emailActivate($uid);
            }
        }
		$this->layout('user', $this->mCurUser);
	}

	/**
	 * ajax 验证登录
	 */
	protected function _ajax_islogin(){
		$this->_session();
		empty($this->mCurUser) && $this->ajax('请先登录再进行此操作！');
	}

	/**
	 * 角色验证
	 * @param string $msg 提示消息
	 */
	protected function _role($msg = ''){
		if(empty($this->mCurUser) || (4 & $this->_auth && (('admin' != $this->mCurUser['role']) && ('read' != $this->mCurUser['role'])))){
			$this->showMsg($msg, '/user/login');
		}
	}

	/**
	 * 注册变量到模板
	 * @param str|array $pKey
	 * @param mixed $pVal
	 */
	protected function assign($pKey, $pVal = ''){
		if(is_array($pKey)){
			$this->_view->assign($pKey);
			return $pKey;
		}
		$this->_view->assign($pKey, $pVal);
		return $pVal;
	}

	/**
	 * 注册变量到布局
	 * @param str $k
	 * @param mixed $v
	 */
	protected function layout($k, $v){
		static $layout;
		$layout || $layout = Yaf_Registry::get('layout');
		@$layout->$k = $v;
		$this->assign($k, $v);
	}

	/**
	 * SEO设置
	 *
	 * @param str $pTitle
	 * @param str $pKW
	 * @param str $pDes
	 */
	protected function seo($pTitle = '', $pKW = '', $pDes = '', $pBodyCss = ''){
		$this->assign(array('seot' => $pTitle, 'seok' => $pKW, 'seod' => $pDes, 'bodycss' => $pBodyCss));
	}

	/**
	 * 提示信息
	 */
	protected function showMsg($pMsg, $pUrl = false){
		Tool_Fnc::showMsg($pMsg, $pUrl);
	}

	/**
	 * 退出消息
	 */
	protected function exitMsg($pMsg){
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />', $pMsg;
		exit;
	}

	/**
	 * AJAX返回
	 */
	protected function ajax($pMsg = '', $pStatus = 0, $pData = '', $pType = 'json'){
		Tool_Fnc::ajaxMsg($pMsg, $pStatus, $pData, $pType);
	}

	/**
	 * Flash Ajax
	 *
	 * @param str $pMsg
	 */
	protected function fjax($pMsg){
		header('Content-type: text/xml');
		echo '<?xml version="1.0" encoding="utf-8"?>', "\n";
		echo "<wvke>\n<content><![CDATA[\n";
		echo $pMsg;
		exit("\n	]]></content>\n</wvke>");
	}

	/**
	 * 显示、保存、添加
	 * @param str|obj $pTable 表名或表对象
	 * @param array $pData 数据
	 * @param string $pFieldFilter 过滤字段
	 * @return false:dberror, true:GET, int:db->pk
	 */
	protected function _save($pTable, $pData, $pFieldFilter = ''){
		# 实例化
		if(is_string($pTable)){
			$pTable = ucfirst($pTable) . 'Model';
			$pTable = new $pTable();
		}
		$_GET[$pTable->pk] = isset($_REQUEST[$pTable->pk])? intval($_REQUEST[$pTable->pk]): 0;
		# 处理POST提交
		if('POST' == $_SERVER['REQUEST_METHOD']){
			# 过滤掉非法字段
			if($pFieldFilter){
				$pFieldFilter = explode(',', $pFieldFilter);
				foreach($pData as $tField){
					if(!in_array($tField, $pFieldFilter)) unset($pData[$tField]);
				}
			}
			# 更新时间
			isset($pTable->field['updated']) && $pData['updated'] = $_SERVER['REQUEST_TIME'];
			# 修改记录
			if($_GET[$pTable->pk]){
				$pData[$pTable->pk] = $_GET[$pTable->pk];
				$pTable->update($pData) && $tId = $_GET[$pTable->pk];
			}
			else{
				# 新增记录
				isset($pTable->field['created']) && $pData['created'] = $_SERVER['REQUEST_TIME'];
				if(isset($pData[$pTable->pk])){
					unset($pData[$pTable->pk]);
				}
				$tId = $pTable->insert($pData);
			}
			return empty($tId)? false: $tId;
		}
		if($_GET[$pTable->pk]){
			$this->_view->assign('data', $pTable->fRow($_GET[$pTable->pk]));
		}
		$this->assign('fields', $pFieldFilter? explode(',', $pFieldFilter): array_keys($pTable->field));
		return 0;
	}

	/**
	 * 将表名转为表对象
	 * @param $pTable str|obj 表名或表对象
	 */
	protected function _table2obj(&$pTable){
		if(is_string($pTable)){
			if(strpos($pTable, '_')){
				$pTable = str_replace(' ', '_', ucwords(str_replace('_', ' ', $pTable))) . 'Model';
			}
			else{
				$pTable = ucwords($pTable) . 'Model';
			}
			$pTable = new $pTable();
		}
	}

	/**
	 * 获得模型的列表
	 * @param String $pTable 数据表名
	 * @param String $pConn 条件 L=查询条数 &OB=排序 &cid=分类ID &字段=值
	 * @return array
	 */
	protected function _list($pTable, $pConn = '', $pPage = ''){
		# 实例化模型
		$this->_table2obj($pTable);
		# 自动搜索
		if(!empty($_GET['field']) && !empty($_GET['kw'])){
			empty($pConn) || $pConn .= '&';
			# like 搜索
			if(false === strpos($_GET['field'], '*')){
				$pConn .= $_GET['field'] . '=' . $_GET['kw'];
			}
			else{
				$pConn .= str_replace('*', '', $_GET['field']) . '=LIKE *' . $_GET['kw'] . '*';
			}
		}
		# 查询条数
		parse_str($pConn, $tConn);
		if(isset($tConn['L'])){
			$tLimit = $tConn['L'];
			unset($tConn['L']);
		}
		else{
			$tLimit = 10;
		}
		# 排序
		if(isset($tConn['OB'])){
			$tOB = $tConn['OB'];
			unset($tConn['OB']);
		}
		else{
			$tOB = '';
		}
		# Where 条件
		if(!empty($pTable->options['where'])){
			$tWhere = $pTable->options['where'];
		} else {
			$tWhere = array();
			foreach($tConn as $k1 => $v1){
				if(0 === strpos($k1, 'SQL')){
					# SQL: SQL=abc
					$tWhere[] = "$v1";
				}
				elseif(0 === strpos($v1, 'IN')){
					# IN: field=IN(1,2,3) 将转换为 field IN(1,2,3)
					$tWhere[] = "$k1 $v1";
				}
				elseif(0 === strpos($v1, 'LIKE')){
					# LIKE：field=LIKE abc* 将转换为 field LIKE 'abc%'
					$v1 = str_replace('*', '%', substr($v1, 5));
					$tWhere[] = "$k1 LIKE '$v1'";
				}
				else
					$tWhere[] = "$k1='$v1'";
			}
			if($tWhere = join(' AND ', $tWhere)){
				$pTable->where($tWhere);
			}
		}
		# 不带分页
		if(false === $pPage){
			return $this->_view->assign('datas', $pTable->limit($tLimit)->order($tOB)->fList());
		}
		# 需要分页
		$tField = isset($pTable->options['field'])? $pTable->options['field']: '*';
		if(!$tCnt = $pTable->count()){
			return $this->_view->assign(array('datas' => array(), 'pageinfo' => ''));
		}
		$tPage = new Tool_Page($tCnt, $tLimit);
		$tWhere && $pTable->where($tWhere);
		$this->_view->assign('datas', $pTable->field($tField)->limit($tPage->limit())->order($tOB)->fList());
		$this->_view->assign('pageinfo', $tPage->show($pPage));
	}

	/**
	 * 删除记录
	 * @param Orm_Base $pTable 表对象
	 * @param string $id 主键
	 */
	protected function _del($pTable, $id){
		if($id){
			# 实例化模型
			if(is_string($pTable)){
				$pTable = ucfirst($pTable) . 'Model';
				$pTable = new $pTable();
			}
			$pTable->del($id) && $this->showMsg('删除成功');
		}
		$this->showMsg('删除失败');
	}

	/**
	 * 记录用户登录日志
	 * @param $email
	 * @param $status
	 * @param string $ip
	 */
	protected function userlog($email, $bak='', $status, $ip=''){
		$ip || $ip = USER_IP;
		if(!$handle = @fopen(APPLICATION_PATH . "/conf/user$status.log", 'a')){
			return false;
		}
		@fwrite($handle, "[$status] ".date("Y-m-d H:i:s")."	$email	$bak ip:$ip\n");
		@fclose($handle);
	}

	# 验证码
	protected function valiCaptcha(){
		if(!isset($_POST['captcha'], $_SESSION['captcha']) || (strtolower($_SESSION['captcha']) != strtolower($_POST['captcha']))){
			$this->assign('captchamsg', '验证码错误');
			return false;
		}
		unset($_SESSION['captcha']);
		return true;
	}
    #邮件激活
    protected function emailActivate($uid){
        $tMO = new EmailactivateModel;
        $pData = $tMO->fRow('SELECT activate_time FROM email_activate WHERE uid = ' . $uid . ' LIMIT 1');
		if(!isset($pData['activate_time']) || empty($pData['activate_time'])){
			if(true == strpos(REDIRECT_URL , 'user_emailverify')){ return ;}
			Tool_Fnc::showMsg('', '/user_emailverify');
		}
    }

}
function level($user, $level){
	$array = array();
	for($i=0; $i<count($level); $i++){
		if($i < count($level)-2 && $user['credit']<2000000000){
			if($level[$i]['credit'] <= $user['credit'] && $level[$i+1]['credit'] > $user['credit'] ){
				$array[]=$level[$i];
				$array[]=$level[$i+1];
				return $array;
			}
		}elseif($i == count($level)-2 && $user['credit']<2000000000){
			$array[0]=$level[$i];
			$array[1]='top';
			return $array;
		}elseif($i == count($level)-1 && $user['credit']>=2000000000){
			$array[0]=$level[$i];
			$array[1]='top';
			return $array;
		}
	}

}

function leveluser($uid){
	$level = new UserLevelModel();
	$leveldata = $level->fList();$tUserMO = new UserModel();
	$aa = level($tUserMO->getById($uid),$leveldata);
	return $aa[0];
}
