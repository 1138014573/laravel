<?php
# coin in/out
class Cli_TransController extends Ctrl_Cli
{
	# open debug
	private $debug = false;
	# ignore time
	private $ignore = 172800;
	# log dir
	private $logdir = '';

	# coin in
    public function inAction($coinname = 'goc')
	{
        $rpcurl = Yaf_Registry::get("config")->api->rpcurl->$coinname;
		if (!$rpcurl) {
			exit('coin error');
		}

		# build model
		$rpcMo = new Api_Rpc_Client($rpcurl);
		$model = "Exchange_".ucfirst($coinname)."Model";
		$exchangeMo = new $model();
		$userMo = new UserModel();
		$addressMo = new AddressModel();

		$cnt = 0;
		while (true) {
			# log dir
			$this->logdir = $coinname.'in'.date('Ymd');

			# get new in message
			if (!$this->debug) {
				sleep(15);
			}
			$cnt++;
			if ($cnt  == 100000) {
				$cnt = 0;
			}
			$from = 0;
			$size = 50;
			$finished = 0;
			while (true) {
				sleep(2);
				# get 10 transactions
				$trans_all = $rpcMo->listtransactions('*', $size, $from);
				# empty transactions break
				if (count($trans_all) == 0) {
					break;
				}
				$trans_all = array_reverse($trans_all);
				foreach ($trans_all as $trans) {
					# no receive continue
					if ($trans['category'] != "receive") {
						continue;
					}

					# get uid
					$uid = $addressMo->where("address = '{$trans['address']}'")->fOne('uid');
					if (!$uid) {
						Tool_Log::wlog('parse uid error, address:'.$trans['address'].",txid:".$trans['txid'], $this->logdir, true);
						continue;
					}

					# ignore 2 days ago transactions
					if (time() - $trans['time'] > $this->ignore) {
						Tool_Log::wlog('the tx has past 2 days, address:'.$trans['address'].",txid:".$trans['txid'], $this->logdir, true);
						$finished = 1;
						continue;
					}

					# search txid log
					$exchange_id = $exchangeMo->where("`txid` = '{$trans['txid']}' and wallet = '{$trans['address']}' and opt_type = 'in'")->fOne('id');
					if ($exchange_id) {
						Tool_Log::wlog('old tx, check finished, break:'.$trans['txid'], $this->logdir, true);
						$finished = 1;
						break;
					} else {
						# new transactions insert mysql
						$in_data = array(
							'uid'		=> $uid,
							'admin'		=> 5,
							'wallet'	=> $trans['address'],
							'txid'		=> $trans['txid'],
							'opt_type'	=> 'in',
							'number'	=> $trans['amount'],
							'status'	=> '确认中',
							'created'	=> $trans['time']
						);
						if (!$exchangeMo->insert($in_data)) {
							Tool_Log::wlog('insert new tx failed:'.$trans['txid'], $this->logdir, true);
						} else {
							Tool_Log::wlog('insert new tx:'.$trans['txid'], $this->logdir, true);
						}
					}
				}

				if ($finished) {
					break;
				}
				$from += $size;
			}

			# confirm
			$exchange = $exchangeMo->where("`status` = '确认中' and opt_type = 'in' and admin = 5")->fList();
			if ($exchange) {

				foreach ($exchange as $v2) {
					$trans = $rpcMo->gettransaction($v2['txid']);
					# one tx include multi transactions，search current transactions
					$tx_data = null;
					if (empty($trans['details'])) {
						continue;
					}

					foreach ($trans['details'] as $tx ) {
						if ($tx['address'] == $v2['wallet'] && $tx['category'] == 'receive') {
							$tx_data = $tx;
							break;
						}
					}

					if (!$tx_data) {
						Tool_Log::wlog('not found the tx:'.$trans['txid'].", wallet:".$v2['wallet'], $this->logdir, true);
						continue;
					}

					# check number
					if (abs($tx_data['amount'] - $v2['number']) > 1E-4) {
						Tool_Log::wlog('btc amount is not equal:'.$trans['txid'], $this->logdir, true);
						continue;
					}

					# confirm
					if ($trans['confirmations'] > $this->checkconfirm($coinname)) {

						$userMo->begin();
						$uid_data = array('uid' => $v2['uid']);
						$up_data = array($coinname.'_over' => $v2['number']);

						if (!$userMo->safeUpdate($uid_data, $up_data, true)) {
							$userMo->back();
							Tool_Log::wlog('update user over error, uid :'.$v2['uid'], $this->logdir, true);
							continue;
						}

						$exchange_data = array('id' => $v2['id'], 'status' => '成功', 'confirm' => $trans['confirmations'], 'updated' => time());
						if (!$exchangeMo->update($exchange_data)) {
							$userMo->back();
							Tool_Log::wlog('update exchange error, id :'.$v2['id'], $this->logdir, true);
							continue;
						}

						$userMo->commit();

						Tool_Session::mark($v2['uid']);
						Tool_Log::wlog("user:".$v2['uid'].",转入:".$v2['number'].",tx:".$v2['txid'], $this->logdir, true);

					} else if ($v2['confirm'] < $this->checkconfirm($coinname) && $trans['confirmations'] != $v2['confirm']) {

						$exchange_data = array('id' => $v2['id'], 'confirm' => $trans['confirmations']);
						$exchangeMo->update($exchange_data);

					}
                }

			} else {
				Tool_Log::wlog("all tx are comfirmed.", $this->logdir, true);
			}
		}

		exit('finish');
	}

	# coin out
	public function outAction($coinname = 'goc')
	{
		$rpcurl = Yaf_Registry::get("config")->api->rpcurl->$coinname;
		if (!$rpcurl) {
			exit('coin error');
		}

		# build model
		$rpcMo = new Api_Rpc_Client($rpcurl);
		$model = "Exchange_".ucfirst($coinname)."Model";
		$exchangeMo = new $model();
		$userMo = new UserModel();
		$addressMo = new AddressModel();
		$coinMo = new CoinModel();

		while (true) {
			# log dir
			$this->logdir = $coinname.'out'.date('Ymd');

			if (!$this->debug) {
				sleep(15);
			}
			sleep(2);

			$out_data = $exchangeMo->where("opt_type = 'out' and status = '等待' and admin = 6")->fList();
			if (empty($out_data)) {
				continue;
			}

			foreach ($out_data as $v1) {

				# out limit check
				if (!$v1['confirm']) {
					if ($v1['number'] > User_CoinModel::outlimit($coinname)) {
						continue;
					}
				}

				$fee = 0;
				$rate = $coinMo->where("name = '{$coinname}'")->fOne('rate_out');
				$out = bcmul($v1['number'], (1 - $rate), 8);
				$fee = bcsub($v1['number'], $out, 8);

				# wallet validate
				$valid = $rpcMo->validateaddress($v1['wallet']);
				if (empty($valid) || (substr($v1['wallet'], 0, 1) != 3 && !$valid['isvalid'])) {
					$exchange_data = array('id' => $v1['id'], 'txid' => '地址错误');
					$exchangeMo->update($exchange_data);
					continue;
				}

				# wallet balance
				$info = $rpcMo->getinfo();
				if (empty($info)) {
					break;
				}

				if (!$info['balance'] || $info['balance'] <= $out) {
					Tool_Log::wlog('balance :'.$info['balance'], $this->logdir, true);
					continue;
				}

				$userMo->begin();

				$out_data = array("{$coinname}_lock" => -$v1['number']);
				$uid_out = array('uid' => $v1['uid']);
				if (!$userMo->safeUpdate($uid_out, $out_data, true)) {
					$userMo->back();
					Tool_Log::wlog('update user lock fail :'.$v1['uid'], $this->logdir, true);
					continue;
				}

				# fee
				if ($fee != 0) {
					$fee_exchange = array(
						'uid' => 2,
						'wallet' => '提现手续费',
						'number' => Tool_Str::format($fee, 5),
						'opt_type' => 'in',
						'status' => '成功',
						'created' => time(),
						'updated' => time()
					);

					if (!$exchangeMo->insert($fee_exchange)) {
						$userMo->back();
						Tool_Log::wlog('insert out fee list fail :'.$v1['uid'], $this->logdir, true);
						continue;
					}

					$fee_data = array("{$coinname}_over" => $fee);
					$uid_fee = array('uid' => 2);
					if (!$userMo->safeUpdate($uid_fee, $fee_data, true)) {
						$userMo->back();
						Tool_Log::wlog('update user fee fail : 2', $this->logdir, true);
						continue;
					}
				}

				# send fail
                if (!$txid = $rpcMo->sendtoaddress($v1['wallet'], floatval($out))) {
					Tool_Log::wlog('send fail: '.$txid, $this->logdir, true);
                    $txid = 'fail';
                }

				# out
				$out_exchange = array('id' => $v1['id'], 'txid' => $txid, 'status' => '成功', 'updated' => time());

				if ('fail' == $txid) {
					$exchangeMo->update($out_exchange);
					$userMo->commit();
					Tool_Log::wlog('send fail id: '.$v1['id'], $this->logdir, true);
					continue;
				}

				if (!$exchangeMo->update($out_exchange)) {
					$userMo->back();
					Tool_Log::wlog('update exchange fail :'.$v1['id'], $this->logdir, true);
					continue;
				}

				$userMo->commit();

				Tool_Session::mark($v1['uid']);
				Tool_Log::wlog("user:".$v1['uid'].",转出: ".$out.",tx:".$txid, $this->logdir, true);
			}

		}

		exit('finish');
	}

	# create address
	public function addressAction($coinname = 'goc', $for = 100)
	{
		$now = time();

        $rpcurl = Yaf_Registry::get("config")->api->rpcurl->$coinname;
		$rpcMo = new Api_Rpc_Client($rpcurl);

		$sql = "insert into address(address,coin,created) values ";
		while ($for--) {
			echo $for."\n";
			$addr = $rpcMo->getnewaddress();
			if (empty($addr) || strlen($addr) < 31) {
				$sql = rtrim($sql, ',').';';
				error_log($sql, 3, "/tmp/coin{$coinname}{$now}.log");
				exit('address length error');
			}
			$sql .= "('{$addr}', '{$coinname}', {$now}),";
		}

		$sql = rtrim($sql, ',').';';
		error_log($sql, 3, "/tmp/coin{$coinname}{$now}.log");

		exit('finish');
	}

	# checkconfirm
	private function checkconfirm($coin)
	{
		$confirm = array('goc' => 3, 'btc' => 1, 'ltc' => 1);
		if (in_array($coin, array_keys($confirm))) {
			return $confirm[$coin];
		} else {
			return 3;
		}
	}

}
