<?php

class AjaxController extends Ctrl_Base{
	/**
	 * 转拼音
	 * @param string $word
	 */
	public function pinyinAction($word = ''){
		exit(Tool_Pinyin::get(urldecode($word)));
	}

	/**
	 * 判断邮箱是否存在
	 */
	public function userAction($email = ''){
		if(!Tool_Validate::email($email = strtolower(urldecode($email)))){
			$this->ajax('邮箱格式错误', 0);
		}
		if(UserModel::getRedis($email)){
			$this->ajax('邮箱已经注册，您可以直接登录', 0);
		}
		$this->ajax('', 1);
	}

	/**
     * 货币 转入转出
     * @param type in or out
     * @param mk 钱包地址
     * @coin 货币类型
	 */
	public function exchangebtcAction($mk = '', $coin='goc'){
        # 验证币种是否存在
        if(!$coInfo = User_CoinModel::getInstance()->where("name='{$coin}'")->fRow()){
            return $this->ajax('参数错误');
        }
		# 验证登录
		$this->_ajax_islogin();
        // $allow_coin = array('goc'=>0, 'btc'=>1, 'lc'=>2);
        // $coin_msg   = array('goc'=>'富途币', 'btc'=>'比特币', 'lc'=>'金全币');
        if(empty($coin)){
            return $this->ajax("请输入合法的{$coInfo['name']}地址");
        }

        $addressid = (int)trim($_POST['addressid']);

        $wd_mo = new WalletAddressModel;
        $address_info = $wd_mo->where('id = '.$addressid)->fRow();

        if (empty($address_info)) {
            $this->ajax('请选择提币地址, 没有请添加');
        }

        $mk = $address_info['wallet'];

        # 转出处理
        $mk = trim(urldecode($mk),' ');
        if(empty($mk)){
            $this->ajax("请输入您的{$coInfo['name']}地址");
        }
        if(strlen($mk) < 18){
            $this->ajax("您输入的{$coInfo['name']}地址有误");
        }
        if(empty($_POST['num'])){
            $this->ajax('转出数额不能为空');
        }

        if($_POST['num'] < $coInfo['minout'] || $_POST['num'] > $coInfo['maxout']){
            $this->ajax("转出数额不能小于".(float)$coInfo['minout']."或者大于".$coInfo['maxout']);
        } else {
            # 查询币种的number_float
            $len = Coin_PairModel::getByName($coin.'_cny');
            $tNum = Tool_Str::format((float)$_POST['num'], $len['number_float']);
        }
        if($tNum > $this->mCurUser[$coin.'_over']){
            $this->ajax("最大转出".(float)$this->mCurUser[$coin.'_over']);
        }
        /*t+1转出
        $order_mo = new Order_CoinModel();
        $ftc_buy = floatval($order_mo->ftcbuy($this->mCurUser['uid']));
        if($ftc_buy){
            $ftc_canout = bcmul($this->mCurUser['goc_over']-$ftc_buy, 1, 0);
            if($tNum > $ftc_canout){
                $this->ajax("{$ftc_buy}GOC T+1天冻结,最大转出{$ftc_canout}GOC");
            }
        }*/
		/*if(!PhoneCodeModel::verifiCode($this->mCurUser,2,trim($_POST['code']))){
            $this->ajax('手机验证码错误');
        }*/

		if(empty($_POST['pwdtrade']) || (Tool_Md5::encodePwdTrade(trim($_POST['pwdtrade']), $this->mCurUser['prand']) != $this->mCurUser['pwdtrade'])){
            $this->ajax('交易密码错误');
        }
        # 双重交易密码
        $tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']);
        if($tGA['open']){
            if(!isset($_POST['hotp']) || !Api_Google_Authenticator::verify_key($tGA['secret'], $_POST['hotp'])){
                $this->ajax('双重交易密码错误');
            }
        }
		# 保存到 DB
        $mo = 'Exchange_'.ucfirst($coin).'Model';
		$tMO = new $mo();
		if($tData = $tMO->post($mk, $tNum, $coin, $this->mCurUser)){
			// $tData['created'] = date('Y年m月d日 H:i:s');
   //          //session_id操作
   //          $smModel    = new SessionidModel();
   //          $smkey      = md5($this->mCurUser['uid'].'+'.$tData['id'].'+_ybcoin');
   //          $smData     = array(
   //              'uid'   => $this->mCurUser['uid'],
   //              'eid'   => $tData['id'],
   //              'ekey'  => $smkey,
   //              'created'   => time(),
   //              'type'  => 0
   //              );
   //          $smModel->createInfo($smData);
   //          //发送邮件
   //          $send_msg   = $this->coinoutmsg($coInfo, $tNum, 'http://'.$_SERVER['SERVER_NAME'].'/user_exchange/coinoutconfirm/coin/'.$coInfo['name'].'/k/'.$smkey);
			// Tool_Fnc::mailto($_SESSION['user']['email'], "{$coInfo['display']} - 转出{$coInfo['display']}", $send_msg);
			$this->ajax('', 1, $tData);
		}
		$this->ajax('请不要重复提交！');
	}
    private function coinoutmsg($coInfo, $tNum, $url){
        // $allow_coin = array('goc'=>0, 'btc'=>1);
        // $coin_msg   = array('goc'=>'富途币', 'btc'=>'比特币');
        $cancelurl  = $url.'/cancel/1';
        $msg    = '<p style="font-size:14px;">';
        $msg    .= "您好，{$_SESSION['user']['email']}：<br />";
        $msg    .= "&nbsp;&nbsp;本次操作您转出的数据如下：<br />";
        $msg    .= "{$coInfo['display']}数量：{$tNum} &nbsp;{$coInfo['name']}<br />";
		$msg    .= "手续费：".($tNum*$coInfo['rate_out']). " &nbsp;{$coInfo['name']}<br />";
        $msg    .= "请点击<a href='{$url}'>此处</a>确认<br />";
        $msg    .= "如果链接无法点击，请复制并打开以下网址：<br>{$url}<br /><br />";
        $msg    .= "如果想取消此笔转出操作，请点击<a href='{$cancelurl}'>此处</a>取消<br/>";
        $msg    .= "如果链接无法点击，请复制并打开以下网址：<br>{$cancelurl}<br/>";
        $msg    .= "</p>";
        return $msg;
    }

	/**
	 * 交易
	 */
	public function trustcoinAction(){
		# 验证参数
		if(!isset($_POST['type'], $_POST['price'], $_POST['number'], $_POST['pwdtrade'], $_POST['coin_from'])){
			$this->ajax('参数错误');
		}
        if(!Tool_Validate::az09($_POST['coin_from']) || !$pair=Coin_PairModel::getInstance()->getPair($_POST['coin_from'].'_cny')){
            $this->ajax('非法操作');
        }

        //验证输入数据
        $_POST['price'] = round($_POST['price'], $pair['price_float']);
        if(0 >= $_POST['price'] || stripos($_POST['price'], 'e')){
            $this->ajax('价格输入有误');
        }
        //验证输入数量
        if(0 >= $_POST['number'] || stripos($_POST['number'], 'e')){
            $this->ajax('数量输入有误');
        }
		 // 闭市
        if( $pair['rule_open'] == 1 ){
            //周末休市
            $week = date('w');
            if (in_array($week, explode(',',$pair['open_week']))) {
                $this->ajax('今天休市');
            }
            //节假日休市
            $day=date('md');
            if(false !== strpos($pair['open_date'], $day)) {
                $this->ajax('节假日休市');
            }

			$now_hi = intval(date('Hi'));
            if( $now_hi < intval($pair['open_start']) || $now_hi > intval($pair['open_end']) ){
                $this->ajax('交易时间为 '.trim(chunk_split($pair['open_start'], 2, ':'),':').' - '.trim(chunk_split($pair['open_end'], 2, ':'),':'));
            }
        }
        //价格限制
        if($pair['price_limit'] == 1){
            if (intval(date('Hi')) >= $pair['open_end']) {
                $period = date('Ymd');
            } else {
                $period = date('Ymd', strtotime('-1 day'));
            }
            $floatMo = new Coin_FloatModel();
            $dayprice = $floatMo::getInstance()->field('price_up, price_down')->where("coin_from='{$pair['coin_from']}' and day = {$period}")->fRow();
            $price_up = bcmul($dayprice['price_up'], 1, $pair['price_float']);
            $price_down = bcmul($dayprice['price_down'], 1, $pair['price_float']);
            if((float)$_POST['price']>$price_up || (float)$_POST['price']<$price_down){
                $this->ajax("挂单价格范围{$price_down} - {$price_up}");
            }
        }

		/*if ($_POST['price'] < 6.6 && $pair['coin_from'] == 'goc' ) {
			$this->ajax('挂单价格不能小于6.6元');
		}*/

		# 验证登录
		$this->_ajax_islogin();

        # 是否冻结禁止交易
        $fData = Trust_CoinModel::getTradeStatus($this->mCurUser['uid']);
        if($fData && $fData['canbuy'] == 0 && $fData['cansale'] == 0 ){
            $this->ajax('您的账户已被冻结禁止交易，请联系客服');
        }

		# 验证交易密码
        if(!Tool_Md5::pwdTradeCheck($this->mCurUser['uid'])){
            $_POST['pwdtrade'] = urldecode($_POST['pwdtrade']);
            if(empty($_POST['pwdtrade']) || Tool_Md5::encodePwdTrade($_POST['pwdtrade'], $this->mCurUser['prand']) != $this->mCurUser['pwdtrade']){
                $this->ajax('交易密码错误');
            }
            Tool_Md5::pwdTradeCheck($this->mCurUser['uid'], 'add');
        }

		# 验证输入数据
		if(0 >= ($_POST['price'] = (float)Tool_Str::format($_POST['price'], $pair['price_float'], 2))){
			$this->ajax('价格输入有误');
		}
		$_POST['number'] = (float)Tool_Str::format($_POST['number'], $pair['number_float'], 2);
		if(1E-3 > $_POST['number'] || 1E+6 < $_POST['number']){
			$this->ajax('数量输入有误');
		}

        if( $_POST['number'] > $pair['max_trade'] || $_POST['number'] < $pair['min_trade'] ){
            $this->ajax("交易数量不能小于{$pair['min_trade']}或者大于{$pair['max_trade']}");
        }

		# 买入
		if('in' == $_POST['type']){
            # 是否冻结禁止买入
            if($fData && $fData['canbuy'] == 0 ){
                $this->ajax('您的账户已被冻结禁止买入，请联系客服');
            }

            $trustmoney = $_POST['number']*$_POST['price'];
			if($this->mCurUser['cny_over'] < $trustmoney){
				$this->ajax('您的可用余额不足');
			}
		} else {
            # 是否冻结禁止卖出
            if($fData && $fData['cansale'] == 0){
                $this->ajax('您的账户已被冻结禁止卖出，请联系客服');
            }

			if($this->mCurUser[$pair['coin_from'].'_over'] < $_POST['number']){
				$this->ajax('您的可用币不足');
			}
            /* t+1限制
            $order_mo = new Order_CoinModel();
            $ftc_buy = floatval($order_mo->ftcbuy($this->mCurUser['uid']));
            $ftc_cansale = bcmul($this->mCurUser['goc_over']-$ftc_buy, 1, 3);
			if($ftc_cansale-$_POST['number'] < 0){
				$this->ajax("{$ftc_buy}GOC T+1天冻结,目前可卖{$ftc_cansale}GOC");
            }*/
		}
        $tMO = new Trust_CoinModel();
        $tMO->btc($_POST, $this->mCurUser);
        $tData = array('cny_over'=>$this->mCurUser['cny_over'], 'cny_lock'=>$this->mCurUser['cny_lock'], $pair['coin_from'].'_over'=>$this->mCurUser[$pair['coin_from'].'_over'], $pair['coin_from'].'_lock'=>$this->mCurUser[$pair['coin_from'].'_lock']);
        $this->ajax('下单成功', 1, $tData);
	}

    /**
     * fulltrade 全屏交易下单验证
     */
    public function fulltrustcoinAction(){
        // 验证登录
        $this->_ajax_islogin();

        # 是否冻结禁止交易
        $fData = Trust_CoinModel::getTradeStatus($this->mCurUser['uid']);
        if($fData && $fData['canbuy'] == 0 && $fData['cansale'] == 0 ){
            $this->ajax('您的账户已被冻结禁止交易，请联系客服');
        }

        // 验证参数
        if(!isset($_POST['type'], $_POST['price'], $_POST['number'], $_POST['coin_from'])){
            $this->ajax('参数错误');
        }
        if(!Tool_Validate::az09($_POST['coin_from']) || !$pair=Coin_PairModel::getInstance()->getPair($_POST['coin_from'].'_cny')){
            $this->ajax('非法操作');
        }
        // 验证输入数据
        if(0 >= $_POST['price'] || stripos($_POST['price'], 'e')){
            $this->ajax('价格输入有误');
        }
        // 验证输入数量
        if(0 >= $_POST['number'] || stripos($_POST['number'], 'e')){
            $this->ajax('数量输入有误');
        }
        // 价格限制
        if($pair['price_limit'] == 1){
            if (intval(date('Hi')) >= $pair['open_end']) {
                $period = date('Ymd');
            } else {
                $period = date('Ymd', strtotime('-1 day'));
            }
            $floatMo = new Coin_FloatModel();
            $dayprice = $floatMo::getInstance()->field('price_up, price_down')->where("coin_from='{$pair['coin_from']}' and day = {$period}")->fRow();
            $price_up = bcmul($dayprice['price_up'], 1, $pair['price_float']);
            $price_down = bcmul($dayprice['price_down'], 1, $pair['price_float']);
            if((float)$_POST['price']>$price_up || (float)$_POST['price']<$price_down){
                $this->ajax("挂单价格范围{$price_down} - {$price_up}");
            }
        }

		/*if ($_POST['price'] < 6.6 && $pair['coin_from'] == 'goc') {
			$this->ajax('挂单价格不能小于6.6元');
		}*/

        // 验证输入数据
        if(0 >= ($_POST['price'] = (float)Tool_Str::format($_POST['price'], 2, 2))){
            $this->ajax('价格输入有误');
        }
        $_POST['number'] = (float)Tool_Str::format($_POST['number'], 3, 2);
        if(1E-3 > $_POST['number'] || 1E+6 < $_POST['number']){
            $this->ajax('数量输入有误');
        }

        if( $_POST['number'] > $pair['max_trade'] || $_POST['number'] < $pair['min_trade'] ){
            $this->ajax("交易数量不能小于{$pair['min_trade']}或者大于{$pair['max_trade']}");
        }

        // 买入
        if('in' == $_POST['type']){
            # 是否冻结禁止买入
            if($fData && $fData['canbuy'] == 0 ){
                $this->ajax('您的账户已被冻结禁止买入，请联系客服');
            }

            $trustmoney = $_POST['number']*$_POST['price'];
            if($this->mCurUser['cny_over'] < $trustmoney){
                $this->ajax('您的可用余额不足');
            }
        } else {
            # 是否冻结禁止卖出
            if($fData && $fData['cansale'] == 0){
                $this->ajax('您的账户已被冻结禁止卖出，请联系客服');
            }

            if($this->mCurUser[$pair['coin_from'].'_over'] < $_POST['number']){
                $this->ajax('您的可用币不足');
            }
        }
        if(!Tool_Md5::pwdTradeCheck($this->mCurUser['uid'])){
            $_POST['pwdtrade'] = urldecode($_POST['pwdtrade']);
            if(empty($_POST['pwdtrade']) || Tool_Md5::encodePwdTrade($_POST['pwdtrade'], $this->mCurUser['prand']) != $this->mCurUser['pwdtrade']){
                $this->ajax('交易密码错误', 2);
            }
            Tool_Md5::pwdTradeCheck($this->mCurUser['uid'], 'add');
        }
        $this->ajax('验证成功', 1);
    }

	/**
     *
	 * 委托 撤销
     * @param type 1富途币，3比特
	 */
	public function trustcancelAction($id = 0, $type=1){
		if(!$id = abs($id)) $this->ajax('参数错误');
		if(!$type = abs($type)) $this->ajax('参数错误');
		# 验证登录
		$this->_ajax_islogin();

        Trust_CoinModel::getInstance()->cancel($id, $this->mCurUser, 1);
        $_SESSION['user'] = $this->mCurUser;
        $this->ajax('操作成功', 1, UserModel::userjson($this->mCurUser));
        break;
	}
    /**
     * 用户最新未成交挂单
     * @param id uid
     * @param type 1goc
     */
    public function mytrustAction($coin, $type=1){
		if(!$type = abs($type)) $this->ajax('参数错误');

	    $this->_ajax_islogin();

        $list   = array();
        $msg    = '操作成功';
        $list = Trust_CoinModel::getInstance()->field('id,price,number,numberover,flag,numberdeal,created')->where("uid={$this->mCurUser['uid']} and numberover>0 and status<2 and coin_from='{$coin}'")->order('id desc')->limit(10)->fList();
        $this->ajax($msg, 1, $list);
    }

    /**
     * 用户历史委托
     * @param type 1 goc
     */
    public function myhistrustAction($coin, $type=1){
        if(!$type = abs($type)) $this->ajax('参数错误');

        $this->_ajax_islogin();

        $list = array();
        $msg = '操作成功';
        $list = Trust_CoinModel::getInstance()->field('id,price,number,numberover,flag,numberdeal,status,created')->where("uid={$this->mCurUser['uid']} and coin_from='{$coin}'")->order('id desc')->limit(5)->fList();
        $this->ajax($msg, 1, $list);
    }

	public function sendmsgAction(){
		if(isset($_POST['type']) && !empty($_POST['type'])){
			$type = $_POST['type'];
		}else{
			exit('0');
		}
        if(isset($_POST['name']) && !empty($_POST['name'])){
            $name = $_POST['name'];
        }else{
            exit('0');
        }
		$num = 0;//$_POST['num'];
		if(!$type = abs($type)) $this->ajax('参数错误');
		# 验证登录
		$this->_ajax_islogin();
		$time = time();
        $start = $time - 3600;
        $count = PhoneCodeModel::getInstance()->where("uid = {$this->mCurUser['uid']} and ctime >= {$start} and ctime <= {$time} and action = {$type}")->count();
        if ($count >= 3) {
            exit('4');
        }
		if(PhoneCodeModel::verifiTime($this->mCurUser, $type)){
			$code = PhoneCodeModel::sendCode($this->mCurUser, $type, $name, $num);
			if($code && $code['Code'] == 0){
				exit('1');
			}else if($code && $code['Code'] == 22){
                exit('22');
            }else if($code && $code['Code'] == 33){
                exit('33');
            }else {
				exit('2');
			}
		}else{
			exit('0');
		}
    }

}
