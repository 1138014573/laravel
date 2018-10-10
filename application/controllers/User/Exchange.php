<?php
/**
 * 后台转入转出
 */
class User_ExchangeController extends Ctrl_Base{
	# 普通用户
	protected $_auth = 3;

	/**
	 * 币种 转入
	 */
	public function coininAction($name = 'goc'){
		# 判断表中是否存在
		if (!Tool_Validate::az09($name) || !$coInfo = User_CoinModel::getByName($name)) {
			exit('参数非法');
		}

		if( $this->mCurUser['uid'] == 100817 ){
            return $this->showMsg('禁止操作');
        }

		# 判断是否可以转入
		if($coInfo['in_status'] != 0){
			return $this->showMsg('暂时无法转入'.$coInfo['display'], '/user_index');
		}

		$this->layout('seot', '转入'.$coInfo['display'].'-币交所-数字货币交易平台');

        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

        # 币种信息
        $this->assign('coInfo', $coInfo);

		# 钱包地址
		$addressMo = new AddressModel;
		$wallet = $addressMo->getAddr($this->mCurUser['uid'], $name);

		if (!$wallet) {
			$this->showMsg('wallet create address error.', '/user_index');
		}

		$this->assign('moneykey', $wallet);
		# 转入列表
		$mo = 'Exchange_'.ucfirst($name).'Model';
		$this->_list(new $mo, 'OB=id DESC&opt_type=in&uid='.$this->mCurUser['uid']);
	}
	/**
	 * 比特币 转入
	 */
	/*public function btcinAction(){
		# 钱包地址
		#$this->assign('moneykey', Api_Rpc_Client::getAddrByCache('uid_'.$this->mCurUser['uid'], 2, 'btc'));
        $this->assign('moneykey', '比特币转入已暂停');
		# 转入列表
		$this->_list(new Exchange_BtcModel(), 'OB=id DESC&opt_type=in&uid='.$this->mCurUser['uid']);
	}*/

	/**
	 * 比特币 转出
	 */
	public function btcoutAction($page='default'){
        if($this->mCurUser['btc_lock'] < 0 || $this->mCurUser['rmb_lock'] < 0){
            error_log('比特币转出非法操作:'.$this->mCurUser['uid'].' - '.date('Y-m-d H:i')."\n", 3, './btcout.log');
            return $this->showMsg('非法操作，请联系管理员', '/');
        }
        $this->assign('page', $page);
        $tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']);
		$this->assign('ga', $tGA);
		$this->_list(new Exchange_BtcModel(), 'OB=id DESC&opt_type=out&uid='.$this->mCurUser['uid']);
	}
	/**
	 * 币种 转出
	 */
	public function coinoutAction($name, $page='default'){
		if( isset(User_AdminModel::$email[$this->mCurUser['uid']]) ) {
			return $this->showMsg('此账户不能提现', '/user_index');
		}

		# 是否被冻结禁止数字货币提现
		$cancoinout = User_CoinModel::getCoinOutStatus($this->mCurUser['uid']);
		if($cancoinout == 0){
			return $this->showMsg('您的账户已被冻结禁止数字货币提现，请联系客服', '/user_index');
		}

		if( $this->mCurUser['uid'] == 100817 ){
            return $this->showMsg('禁止操作');
        }
		if(!$coInfo = User_CoinModel::getInstance()->where("name='{$name}'")->fRow()){
			return $this->showMsg('参数错误', '/user_index');
		}
		# 判断是否可以转入
		if($coInfo['out_status'] != 0){
			return $this->showMsg('暂时无法转出'.$coInfo['display'], '/user_index');
		}
		$this->assign('coInfo', $coInfo);

		$this->layout('seot', '转出'.$coInfo['display'].'-币交所-数字货币交易平台');

        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

        $wd_mo = new WalletAddressModel;
        $address_list = $wd_mo->where("coin = '{$name}' and uid = ".$this->mCurUser['uid'])->order('id desc')->fList();
        $this->assign('addresslist' , $address_list);

        if($this->mCurUser[$name.'_lock'] < 0 || $this->mCurUser['cny_lock'] < 0){
            error_log($coInfo['display'].'转出非法操作:'.$this->mCurUser['uid'].' - '.date('Y-m-d H:i')."\n", 3, './gocout.log');
            return $this->showMsg('非法操作，请联系管理员', '/');
        }
        $this->assign('page', $page);
		$this->assign('ga',$tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']));
		$mo = 'Exchange_'.ucfirst($name).'Model';
		$this->_list(new $mo, 'OB=id DESC&opt_type=out&uid='.$this->mCurUser['uid']);
	}

	/**
	 * 人民币 充值(转入)
	 */
	public function rmbinAction(){
		$this->layout('seot', '人民币充值-币交所-数字货币交易平台');
        if(empty($this->mCurUser)){
            return $this->showMsg('请先登录','/user/login');
        }
        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }
        if(isset($_POST['number']) && $amount = floatval($_POST['number'])){
            if ( $amount < Exchange_CnyModel::LOWEST_IN_MONEY ){
                $this->showMsg('充值最小金额为'.Exchange_CnyModel::LOWEST_IN_MONEY.'元，请重新输入', '/user_exchange/rmbin2');
            }
			if(isset($_POST['name']) && !empty($_POST['name'])){
				$name = htmlspecialchars(trim($_POST['name']));
			}else{
				$this->showMsg('开户姓名不能为空', '/user_exchange/rmbin2');
			}
			if(isset($_POST['bankfrom']) && !empty($_POST['bankfrom'])){
				$bankfrom = htmlspecialchars(trim($_POST['bankfrom']));
			}else{
				$this->showMsg('银行名称不能为空', '/user_exchange/rmbin2');
			}
			if(isset($_POST['account']) && !empty($_POST['account'])){
            	if( preg_match("/^(\d{16}|\d{18}|\d{19})$/", $_POST['account']) ){
            		$account = htmlspecialchars(trim($_POST['account']));
            	}else{
            		$this->showMsg('银行账号错误', '/user_exchange/rmbin2');
            	}
			}else{
				$this->showMsg('银行账号错误', '/user_exchange/rmbin2');
			}
            $bank_arr   = array(
                // 'zs'    => array('深圳布罗克城网络科技有限公司', '7559 3109 4110 301', '招商银行 深圳分行罗湖支行')
                'zs'    => array('深圳布罗克城网络科技有限公司', '4100 3200 0400 3307 1', '中国农业银行深圳科技园支行')
            );
			$bank_array   = array(
                // 'zs'    => array('深圳布罗克城网络科技有限公司', '7559 3109 4110 301', '招商银行 深圳分行罗湖支行')
                'zs'    => array('深圳布罗克城网络科技有限公司', '4100 3200 0400 3307 1', '中国农业银行深圳科技园支行')
            );

            $tRmbMO = new Exchange_CnyModel();
            $orderData = array('uid'=>$this->mCurUser['uid'], 'email'=>$this->mCurUser['email'],
			'accounttype'=>'银行卡', 'bank'=>$bank_array['zs'][2], 'money'=>$amount, 'opt_type'=>'in',
			'status'=>'等待', 'created'=>time(), 'name' => $name, 'account' => $account, 'bankfrom' => $bankfrom,'createip'=>Tool_Fnc::realip());
            if(!$id = $tRmbMO->insert($orderData)){
                return $this->showMsg('生成订单发生错误，请重试', '/user_exchange/rmbin2');
            }
            $this->assign('b', $bank_arr['zs']);
            $this->assign('orderid', $id);
            $this->assign('userid', $this->mCurUser['uid']);
            $this->assign('amount', $amount);
			$this->assign('name', $name);
			$this->assign('bankfrom', $bankfrom);
			$this->assign('account', $account);
        } else if(isset($_POST['orderid']) && !empty($_POST['orderid']) && $orderid = intval($_POST['orderid'])){
			return $this->showMsg('', '/user_exchange/rmbin2');
		}else{
            $_SESSION['rmbinrand']  = rand(10, 99);
            $this->assign('rand', $_SESSION['rmbinrand']);
			return $this->showMsg('', '/user_exchange/rmbin2');
        }
		# 充值记录
		$this->_list(new Exchange_CnyModel(), 'OB=id DESC&opt_type=in&uid='.$this->mCurUser['uid']);
	}


	public function rmbin2Action(){
		$this->layout('seot', '人民币充值-币交所-数字货币交易平台');

		//return $this->showMsg('请选择人工充值','/user_exchange/rmbin');
		if(empty($this->mCurUser)){
            return $this->showMsg('请先登录','/user/login');
        }

        if( $this->mCurUser['uid'] == 100817 ){
            return $this->showMsg('禁止操作');
        }

		if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

		#return $this->showMsg('暂停充值');

		$this->assign('id', 0);
		$this->assign('name', $this->mCurUser['name']);
		$tRmbMO = new Exchange_CnyModel();
		$this->_list($tRmbMO, 'OB=id DESC&opt_type=in&uid='.$this->mCurUser['uid']);


        # decimal
        $uid = $this->mCurUser['uid'];
        $effective_numbers = Exchange_CnyModel::getInstance()->where("uid = {$uid}")->count();
        $effective_decimal = 0.01 * $effective_numbers;

		$rand = floatval(substr($uid, -3, 1).'.'.substr($uid, -2, 2)) + $effective_decimal;
        $rand = substr(sprintf("%.2f", $rand), -4, 4);

        $this->assign('rand', $rand);

		if($this->getRequest()->getMethod() == 'POST'){
            return $this->showMsg('请使用银行转账方式进行充值','/user_exchange/rmbin2');
			if(isset($_POST["Amount"]) && !empty($_POST["Amount"]) && is_numeric($_POST["Amount"])){
				if($_POST["Amount"] >= Exchange_CnyModel::LOWEST_IN_MONEY || $this->mCurUser['uid'] == 22081){
					$Amount = $_POST["Amount"];
				}else{
					return $this->showMsg('输入金额不能小于'.Exchange_CnyModel::LOWEST_IN_MONEY.'元');
				}
			}else{
				return $this->showMsg('输入金额有问题');
			}
			if(isset($_POST["name"]) && !empty($_POST["name"])){
				$name = $_POST["name"];
			}else{
				return $this->showMsg('汇款开户名不能为空');
			}
            $orderData = array('uid'=>$this->mCurUser['uid'], 'email'=>$this->mCurUser['email'], 'accounttype'=>'银行卡', 'bank'=>'未知', 'accounttype'=>'双乾', 'money'=>$Amount, 'opt_type'=>'in', 'status'=>'等待', 'name'=>$name,'created'=>time(), 'createip'=>Tool_Fnc::realip());
            if(!$id = $tRmbMO->insert($orderData)){
                return $this->showMsg('生成订单发生错误，请重试');
            }

			$MerNo 			= "180932";
			$MD5key      	= "~KO)mUYZ";
			$BillNo 		= $id;
			$products 		= "YBH充值";
			$MerRemark 		= $id;
			$MD5info 		= $this->getSignature($MerNo, $BillNo, $name,$Amount,$MerRemark,$MD5key);

			$this->assign('id', $id);
			$this->assign('MerNo', $MerNo);
			$this->assign('BillNo', $BillNo);
			$this->assign('Amount', $Amount);
			$this->assign('name', $name);
			$this->assign('MD5info', $MD5info);
			$this->assign('MerRemark', $MerRemark);
			$this->assign('products', $products);
		}
	}
	private function getSignature($MerNo, $BillNo, $name,$Amount,$MerRemark,$MD5key){
		 $params = array(
		 	"MerNo" =>  $MerNo,
		 	"BillNo" => $BillNo,
		 	"name" => $name,
		 	"Amount" => $Amount,
		 	"MerRemark" => $MerRemark,
		 );
		 $sign_str = "";
		 ksort($params);
		 foreach ($params as $key => $val) {
            $sign_str .= sprintf("%s=%s&", $key, $val);
         }
  		return strtoupper(md5($sign_str. strtoupper(md5($MD5key))));
	}

	private function bankname($code){
		switch($code){
			case 'ICBC':
				$bankname = "工商银行";
				break;
			case 'ABC':
				$bankname = "农业银行";
				break;
			case 'BOCSH':
				$bankname = "中国银行";
				break;
			case 'CCB':
				$bankname = "建设银行";
				break;
			case 'CMB':
				$bankname = "招商银行";
				break;
			case 'BOCOM':
				$bankname = "交通银行";
				break;
			case 'PSBC':
				$bankname = "邮政储蓄银行";
				break;
			case 'PAB':
				$bankname = "平安银行";
				break;
			case 'BOS':
				$bankname = "上海银行";
				break;
			case 'BOCOM':
				$bankname = "交通银行";
				break;
			case 'SPDB':
				$bankname = "浦发银行";
				break;
			case 'GDB':
				$bankname = "广发银行";
				break;
			case 'CNCB':
				$bankname = "中信银行";
				break;
			case 'CMBC':
				$bankname = "民生银行";
				break;
			case 'CEB':
				$bankname = "光大银行";
				break;
			case 'CIB':
				$bankname = "兴业银行";
				break;
			default:
				$bankname = "未知银行";
		}
		return $bankname;
	}



	private function editrmbin($array){
		$msg = array();
		if(isset($array['orderid']) && !empty($array['orderid'])){
			$orderid = htmlspecialchars(trim($array['orderid']));
		}else{
			$msg['err'] = 0;
			$msg['msg'] = "汇款单号ID不能为空";
			exit(json_encode($msg));
		}
		if(isset($array['name']) && !empty($array['name'])){
			$name = htmlspecialchars(trim($array['name']));
		}else{
			$msg['err'] = 0;
			$msg['msg'] = "名字不能为空";
			exit(json_encode($msg));
		}
		if(isset($array['order']) && !empty($array['order'])){
			$order = htmlspecialchars(trim($array['order']));
		}else{
			$order = 0;
		}
		if(isset($array['account']) && !empty($array['account'])){
			$account = htmlspecialchars(trim($array['account']));
		}else{
			$account = 0;
		}
		if(isset($array['bankfrom']) && !empty($array['bankfrom'])){
			$bankfrom = htmlspecialchars(trim($array['bankfrom']));
		}else{
			$msg['err'] = 0;
			$msg['msg'] = "银行名字不能为空";
			exit(json_encode($msg));
		}
		$tRmbMO = new Exchange_CnyModel();
		$value = array('id' => $orderid, 'name' => $name, 'order' => $order, 'account' => $account, 'bankfrom' => $bankfrom);
		if($tRmbMO->update($value)){
			$msg['err'] = 1;
			$msg['msg'] = "提交成功，我们收到汇款后，客服会尽快人工确认到帐";
			exit(json_encode($msg));
		}else{
			$msg['err'] = 0;
			$msg['msg'] = "失败";
			exit(json_encode($msg));
		}
	}
	public function bankinfoAction(){
        return $this->showMsg('123');
    }

	/**
	 * 人民币 确认充值信息
	 */
	public function confirmAction(){
		# 验证合法性
		if ( empty($_POST['money']) || !is_numeric($_POST['money']) )
			$this->showMsg('请填写正确的充值金额');
		if ( $_POST['money'] < 10 )
			$this->showMsg('最低充值金额为10元');
		$this->assign('money', $_POST['money']);
		$this->assign('realmoney', round($_POST['money']*0.99, 2));
	}
	/**
	 * 人民币 充值成功信息
	 */
	public function paysuccessAction(){
		# 验证合法性
		if ( empty($_GET['money']) || !is_numeric($_GET['money']) )
			$this->showMsg('发生未知错误', '/');
		$this->assign('money', round($_GET['money']*0.99, 2));
	}
	/**
	 * 人民币 转出
	 */
	public function rmboutAction(){
		$this->layout('seot', '人民币提现-币交所-数字货币交易平台');

		if( isset(User_AdminModel::$email[$this->mCurUser['uid']]) ) {
			return $this->showMsg('此账户不能提现', '/user_index');
		}

		# 是否被冻结禁止人民币提现
		$canrmbout = Exchange_CnyModel::getRmbOutStatus($this->mCurUser['uid']);
		if($canrmbout == 0){
			return $this->showMsg('您的账户已被冻结禁止人民币提现，请联系客服', '/user_index');
		}

		if( $this->mCurUser['uid'] == 100817 ){
            return $this->showMsg('禁止操作');
        }

        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善用户资料', '/user/modify');
        }
        $tMO = new UserBankCardsModel;
        $tData = $tMO->query($tSql = 'SELECT * FROM user_bank_cards WHERE uid = ' . $this->mCurUser['uid'] . ' and status=0 ORDER BY created DESC');
        $this->assign('banklist' , $tData);
		$this->assign('fee', Exchange_CnyModel::RMBOUT_FEE);

		$this->assign('ga', $tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']));
		# 提现记录
		$this->_list(new Exchange_CnyModel(), 'OB=id DESC&opt_type=out&uid='.$this->mCurUser['uid']);
		# 数据处理
		if('POST' == $_SERVER['REQUEST_METHOD']){
			$this->assign('data', $_POST);
			# 账号类型
			in_array($_POST['accounttype'], array('支付宝', '财付通', '银行卡')) || exit;

            $bankInfo = $tMO->where('id = '.$_POST['bank'])->fRow();

            if (empty($bankInfo)) {
                return $this->assign('errorTips', '请选择提现银行卡，没有请添加');
            }

			# 交易密码
			if(Tool_Md5::encodePwdTrade($_POST['pwdtrade'], $this->mCurUser['prand']) != $this->mCurUser['pwdtrade']){
			    return $this->assign('errorTips', '交易密码不一致，请重新输入');
			}
			# 双重交易密码
			if($tGA['open']){
				if(!isset($_POST['hotp']) || !Api_Google_Authenticator::verify_key($tGA['secret'], $_POST['hotp']) ){
					return $this->assign('errorTips', '双重交易密码错误');
				}
            }
			# 提现金额
            if(!preg_match("/^[0-9]+(\.[0-9]{1,2})?$/" , $_POST['money'])){
				return $this->assign('errorTips', '提现金额为两位小数');
            }
			if($_POST['money'] > 50000){
				return $this->assign('errorTips', '单笔最大提现金额为50000元');
			}
			if($_POST['money']  < 500 || ($_POST['money'] = Tool_Str::format($_POST['money'], 2, 2)) < 500){
				return $this->assign('errorTips', '最少提现金额为500.00元人民币');
			}
            if($_POST['money'] > $this->mCurUser['cny_over']){
				return $this->assign('errorTips', '余额不足，当前可用余额为'.$this->mCurUser['cny_over'].'元');
			}

			# 当日提现总金额
			$day_sum = Exchange_CnyModel::getInstance()->field('sum(money) sum')->where("opt_type = 'out' and uid = {$this->mCurUser['uid']} and created > ".strtotime(date('Y-m-d', time())))->fRow();
			if( $day_sum['sum'] >= 500000 ){
				return $this->assign('errorTips', '今日提现已到500000元上限');
			}else if( $day_sum['sum'] + $_POST['money'] > 500000 ){
				return $this->assign('errorTips', '今日还可提现'.(500000-$day_sum['sum']).'元');
			}

			# 手机验证码
            if( !( PhoneCodeModel::verifiCode($this->mCurUser,1,trim($_POST['code'])) || PhoneCodeModel::verifiCode($this->mCurUser,5,trim($_POST['code'])) ) ){
                return $this->assign('errorTips', '手机验证码错误');
            }

			# 保存到提现记录表
			$tRmbModel = new Exchange_CnyModel();
			if(!$tRmbModel->out($this->mCurUser, $bankInfo)){
                return $this->assign('errorTips', '系统错误，请联系管理员[错误编号:rmb_out_02]');
			}
			# 保存SESSION
			$_SESSION['user'] = $this->mCurUser;
			# 跳转
			$this->showMsg('申请提现成功，工作人员会尽快处理您的提现请求。');
		}
	}
	/**
	 * 委托富途币卖出(买入)
	 */
	public function trustybcAction($type='in'){
		$this->assign('type', $type=='in'? array('in', '买', '买入'): array('out', '卖', '卖出'));
	}
    /**
     * 货币转出确认
     */
	public function coinoutconfirmAction($coin, $k, $cancel=0){
        $return_url = '/user_exchange/coinout/name/'.$coin.'/page/%s';
        # 验证币种信息
        if(!$coInfo = User_CoinModel::getInstance()->where("name='{$coin}'")->fRow()){
			return $this->showMsg('',sprintf($return_url, 'fail'));
		}

        $k  = trim($k);
        if(empty($k) || strlen($k) != 32){
            return $this->showMsg('',sprintf($return_url, 'fail1'));
        }
        //查询session_id
        $smMo       = new SessionidModel();
        $session    = $smMo->getInfoByKey($k);
        if(empty($session) || md5($session['uid'].'+'.$session['eid'].'+_ybcoin') != $k || $session['uid'] != $this->mCurUser['uid']){
            return $this->showMsg('',sprintf($return_url, 'fail2'));
        }
		# 查询记录
		$coinMo = 'Exchange_'.ucfirst($coin).'Model';
		$ExchangeMO = new $coinMo();
		# 验证
		if(!$tExCoin = $ExchangeMO->lock()->fRow($session['eid'])){
            return $this->showMsg('',sprintf($return_url, 'fail3'));
		}
		if($tExCoin['opt_type'] != 'out' || $tExCoin['status'] != '等待'){
            return $this->showMsg('',sprintf($return_url, 'fail4'));
		}
		if( $tExCoin['admin'] == 6 ){
			return $this->showMsg('',sprintf($return_url, 'already'));
		}
        if($cancel){
        	# 事务开始
			$ExchangeMO->begin();
            $tUData = array($coin.'_over'=>$tExCoin['number'],$coin.'_lock'=>-$tExCoin['number']);
            $tOutData = array('id' => $session['eid'], 'status' => '已取消');
            $tUser = array('uid' => $tExCoin['uid']);
            $tUserMO = new UserModel();
            if(TRUE !== $tUserMO->safeUpdate($tUser, $tUData)){
                $ExchangeMO->back();
                return $this->showMsg('',sprintf($return_url, 'fail5'));
            }
            if(!$ExchangeMO->save($tOutData)){
                $ExchangeMO->back();
                return $this->showMsg('',sprintf($return_url, 'fail6'));
            }
            $ExchangeMO->commit();

            Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
            $smMo->commonUpdate(array('id'=>$session['id'],'updated'=>time(),'status'=>1));
            return $this->showMsg('',sprintf($return_url, 'timeout'));
        }
        //todo 限额
        if($tExCoin['number'] > $coInfo['maxout'] || $tExCoin['number'] < $coInfo['minout']){
            return $this->showMsg('',sprintf($return_url, 'fail7'));
        }

        # 更改admin为6
        $exData = array('id'=>$tExCoin['id'], 'admin' => 6);
        if(!$ExchangeMO->save($exData)){
            return $this->showMsg('',sprintf($return_url, 'fail8'));
        }
        return $this->showMsg('',sprintf($return_url, 'success'));

        /*//钱包限额
        if($tExCoin['number'] > Api_Rpc_Client::getBalance($coInfo['name'])){
            return $this->showMsg('',sprintf($return_url, 'sys'));
	   	}
		# 数据
		$tUData = array($coin.'_lock'=>-$tExCoin['number']);
		$tOutData = array('id' => $session['eid'], 'status' => '成功');
		# 操作用户
		$tUser = array('uid' => $tExCoin['uid']);
		$tUserMO = new UserModel();
		# 更新用户
		if(!$tUserMO->safeUpdate($tUser, $tUData)){
			$ExchangeMO->back();
            return $this->showMsg('',sprintf($return_url, 'fail8'));
		}

        //手续费转入uid1
        $exMo = 'Exchange_'.ucfirst($coin).'Model';
        $eMo = new $exMo();

        $tData = array(
        	'uid'=>2,
        	'wallet'=>'提现手续费',
        	'number'=>Tool_Str::format($tExCoin['number']*$coInfo['rate_out'], 5),
        	'opt_type'=>'in',
        	'status'=>'成功',
        	'created'=>time(),
        	'updated'=>time()
        );

        if(!$eMo->insert($tData)){
            return $this->showMsg('',sprintf($return_url, 'fail9'));
        }
        $feeData    = array($coin.'_over'=>$tDatas['number']);
        $feeUser    = array('uid'=>2);
        if($tExCoin['uid'] != 2 && !$tUserMO->safeUpdate($feeUser, $feeData, true)){
			$ExchangeMO->back();
            return $this->showMsg('',sprintf($return_url, 'fail10'));
        }
		$newtExCoin = $tExCoin['number']*(1-$coInfo['rate_out']);
		# 更新转出请求
        $exp_log = $ExchangeMO->save($tOutData);
		if(!$exp_log){
			$ExchangeMO->back();
            return $this->showMsg('',sprintf($return_url, 'fail12'));
		}
		# 进行自动转出
        if(!$tOutData['txid'] = Api_Rpc_Client::sendToUserAddress($tExCoin['wallet'], $newtExCoin, $coin)){
            $ExchangeMO->back();
            return $this->showMsg('',sprintf($return_url, 'fail11'));
        }
		$ExchangeMO->commit();
		Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
        //session_id 更新
        $smMo->commonUpdate(array('id'=>$session['id'],'updated'=>time(),'status'=>1));
        return $this->showMsg('',sprintf($return_url, 'success'));*/
	}
    /**
     * 常用银行卡
     */
    public function bankcardsAction(){
        $tMO = new UserBankCardsModel;
        $tData = $tMO->query($tSql = 'SELECT * FROM user_bank_cards WHERE uid = ' . $this->mCurUser['uid'] . ' ORDER BY created DESC');

        $this->assign('pData' , $tData);
        $this->display('bankcards');
        exit;
    }
    /**
     * 删除常用银行卡
     */
    public function delcardAction(){
        $bid = empty($_GET['id'])?'':$_GET['id'];
        if(!Tool_Validate::int($bid)){
            $this->showMsg('删除失败', '/user_exchange/bankbind');
        }
        $user_bank_mo = new UserBankCardsModel;
        $where = "id={$bid} and uid=".$this->mCurUser['uid'];
        $data = array('status'=>1);
        if(!$user_bank_mo->where($where)->update($data)){
            $this->showMsg('删除失败', '/user_exchange/bankbind');
        }

        $this->showMsg('已删除', '/user_exchange/bankbind');
    }

    /**
     * 发送短信前验证短信验证码
     */
    public function verifyCheckAction(){
    	if(!isset($_POST['captcha'], $_SESSION['captcha']) || (strtolower($_SESSION['captcha']) != strtolower($_POST['captcha']))){
    		exit('fail');
		}
		// unset($_SESSION['captcha']);
		exit('success');
    }

    /**
     * bind bank cards
     */
    public function bankbindAction(){
    	$this->layout('seot', '绑定银行卡-币交所-数字货币交易平台');

        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

        $uMo = new UserModel;
        $name = $uMo->where('uid = '.$this->mCurUser['uid'])->fOne('name');

        $tMO = new UserBankCardsModel;
        $tData = $tMO->query($tSql = 'SELECT * FROM user_bank_cards WHERE uid = ' . $this->mCurUser['uid'] . ' and status=0 ORDER BY created DESC');

        $this->assign('banklist' , $tData);
        $this->assign('name', $name);

        # 数据处理
        if('POST' == $_SERVER['REQUEST_METHOD']){
            $this->assign('data', $_POST);
            # 开户行
            if(empty($_POST['bank']) || !Tool_Validate::safe($_POST['bank'])) {
                return $this->assign('errorTips', '请选择您的提现银行');
            }

            if(empty($_POST['province']) || empty($_POST['city']) ||  empty($_POST['district'])){
                return $this->assign('errorTips', '请正确选择您的银行卡所在地');
            }
            if(empty($_POST['subbranch'])) {
                return $this->assign('errorTips', '请填写您的开户支行信息');
            }

            # 提现账号
            if(empty($_POST['account']) || !Tool_Validate::safe($_POST['account'])){
                return $this->assign('errorTips', '提现账户格式错误');
            }
            if( !preg_match("/^(\d{16}|\d{17}|\d{19})$/", $_POST['account']) ){
        		return $this->assign('errorTips', '银行卡号不正确');
        	}

            # 手机验证码
            if( !( PhoneCodeModel::verifiCode($this->mCurUser,1,trim($_POST['code'])) || PhoneCodeModel::verifiCode($this->mCurUser,5,trim($_POST['code'])) ) ){
                return $this->assign('errorTips', '手机验证码错误');
            }

            if($this->mCurUser['uid'] < 6 && empty($_POST['bak'])){
                return $this->assign('errorTips', '官方账号需填写操作人，用途');
            }

            # 保存到bank cards
            $data = array(
                'uid'       => $this->mCurUser['uid'],
                'bank'      => $_POST['bank'],
                'name'      => $this->mCurUser['name'],
                'account'   => $_POST['account'],
                'created'   => time(),
                'email'     => $this->mCurUser['email'],
				'province'  => $_POST['province'],
				'city'		=> $_POST['city'],
				'district'	=> $_POST['district'],
				'subbranch'	=> $_POST['subbranch']
            );

            if (!$tMO->insert($data)) {
                return $this->assign('errorTips', '系统错误，请联系管理员');
            }

            # 跳轉
            return $this->assign('errorTips', '银行卡添加成功');
        }

    }

    /**
     * 绑定提币地址
     */
    public function addressbindAction($coin){
    	if(!$coInfo = User_CoinModel::getInstance()->where("name='{$coin}'")->fRow()){
			return $this->showMsg('参数错误', '/user_index');
		}
		$this->assign('coInfo', $coInfo);

		$this->layout('seot', '添加'.$coInfo['display'].'地址-币交所-数字货币交易平台');

        if (!UserModel::isModify($this->mCurUser['uid'])) {
            return $this->showMsg('请先完善资料', '/user/modify');
        }

        $wd_mo = new WalletAddressModel;
        $address_list = $wd_mo->where("coin = '{$coin}' and uid = ".$this->mCurUser['uid'])->order('id desc')->fList();

        $this->assign('datas' , $address_list);

        # Data
        if('POST' == $_SERVER['REQUEST_METHOD']){
            $name       = trim($_POST['name']);
            $address    = trim($_POST['address']);
            $code       = trim($_POST['code']);
            $coin = trim($_POST['coin']);

            if (empty($name)) {
                return $this->assign('errorTips', '地址标签不能为空');
            }

            if (empty($address) || strlen($address) < 18) {
                return $this->assign('errorTips', '请输入合法的转出币地址');
            }

            if( !( PhoneCodeModel::verifiCode($this->mCurUser,2,trim($_POST['code'])) || PhoneCodeModel::verifiCode($this->mCurUser,6,trim($_POST['code'])) ) ){
                return $this->assign('errorTips', '手机验证码错误');
            }

            $data = array(
                'uid'       => $this->mCurUser['uid'],
                'email'     => $this->mCurUser['email'],
                'name'      => $name,
                'wallet'   => $address,
                'coin'      => $coin,
                'created'   => time(),
                'createip'  => USER_IP
            );

            if (!$wd_mo->insert($data)) {
                return $this->assign('errorTips', '系统错误，请稍后重试');
            }

            return $this->assign('errorTips', '地址添加成功');
        }

    }

    public function moneyrandAction() {
    	if(empty($this->mCurUser)){
            return $this->showMsg('请先登录','/user/login');
        }

        if( !isset($_POST['money']) ) {
        	 exit( json_encode(array('code'=>1, 'msg'=>'非法请求')) );
        }

        $money= (int)$_POST['money'];
        $rand = Exchange_CnyModel::randMoney($money);
        $code = 0;
        $msg  = $rand;

        exit( json_encode(array('code'=>$code, 'msg'=>$msg)) );
    }
}
