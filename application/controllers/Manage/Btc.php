<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_BtcController extends Ctrl_Admin {
    
   	//protected $disableAction = array('usergaclose', 'usertpchange', 'rmbout', 'rmbpay',
	//				 'btcout', 'trustcancel', 'betcancel');
	//protected $disableMethodPost = array('rmbcsv', 'rmbin');
	# YBC转入
	public function btcAction($type='out', $status='等待'){
		$this->assign('type', $type);
		$this->assign('status', $status = urldecode($status));
		$this->_list('exchange_btc', 'OB=id DESC&opt_type='.$type.'&status='.$status);
	}
	# BTC：状态改为成功
	public function btcoutAction($id, $cancel = 0, $autosend = 0){
		if ( $autosend ) {
			# 验证双重验证
			if( !isset($_POST['pass']) || !Api_Google_Authenticator::verify_key("P2HUDRQV3NU2LV4I", $_POST['pass']) ){
				$this->showMsg('code error');
			}
		}
		# 查询记录
		$ExchangeMO = new Exchange_BtcModel();
		# 事务开始
		$ExchangeMO->begin();
		# 验证
		if(!$tExCoin = $ExchangeMO->lock()->fRow($id)){
			$this->showMsg('没有找到记录');
		}
		if($tExCoin['opt_type'] != 'out' || $tExCoin['status'] != '等待'){
			$this->showMsg('您已经操作过了');
		}
		# 数据
		$tUData = array('btc_lock'=>-$tExCoin['number']);
		$tOutData = array('id' => $id, 'status' => '成功');
		if($cancel){
			$tUData['btc_over'] = $tExCoin['number'];
			$tOutData['status'] = '已取消';
		} else {
			//手续费转入uid 1
			/* $tDatas = array('number'=>Tool_Str::format($tExCoin['number']*0.01, 5),'price'=>1,'buy_uid'=>$tExCoin['uid'],'sale_uid'=>1,'opt'=>2,'created'=>time());
			$oMO    = new Order_BtcModel();
			if(!$oMO->insert($tDatas)){
				return $this->showMsg('手续费扣取失败[1]，请联系技术');
			}
			$feeData    = array('btc_over'=>$tDatas['number']);
			$feeUser    = array('uid'=>1);
			if(!$tUserMO->safeUpdate($feeUser, $feeData)){
				return $this->showMsg('手续费扣取失败[2]，请联系技术');
			} */
		}
		# 操作用户
		$tUser = array('uid' => $tExCoin['uid']);
		$tUserMO = new UserModel();
		# 更新用户
		if(!$tUserMO->safeUpdate($tUser, $tUData)){
			$ExchangeMO->back();
			$this->showMsg($tUserMO->error[2]);
		}
		# 进行自动转出
		if(!$cancel && $autosend){
			try{
				if(!$tOutData['txid'] = Api_Rpc_Client::sendToUserAddress($tExCoin['btckey'], Tool_Str::format($tExCoin['number'], 5), 'btc')){
					$ExchangeMO->back();
					$this->showMsg($tOutData['txid']);
				}
			}catch (Exception $e) {
				$ExchangeMO->back();
				$this->showMsg($e->getMessage());
			}
		}
		# 更新转出请求
		if(!$ExchangeMO->save($tOutData)){
			$ExchangeMO->back();
			$this->showMsg($ExchangeMO->error[2]);
		}
		$ExchangeMO->commit();
		Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
		$this->showMsg(($cancel? '撤消': '转出') . ' 操作已成功');
	}

	# 比特委托
	public function trustAction($status=false){
		$tConn = 'OB=id DESC';
		# 新
		if(isset($_GET['isnew'])) {
			$tConn .= "&isnew=Y";
			$this->assign('cur', -1);
		}
		# 按状态查询
		else if(false !== $status) {
			$tConn .= "&isnew=N&status=".$status;
			$this->assign('cur', $status);
		} else {
			$this->assign('cur', -2);
		}
		$this->_list('trust_Btc', $tConn);
	}

	# 比特委托撤销
	public function trustcancelAction($id){
		if(!$id = abs($id)) $this->ajax('参数错误');
		$tMO = new Trust_BtcModel();
		if(!empty($id)){
			$tMO->adminCancel($id);
			$this->showMsg('操作成功');
		}
	}
	# 成交
	public function orderAction(){
		$this->_list('order_Btc', 'OB=id DESC');
	}
}
