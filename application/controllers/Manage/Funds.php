<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_FundsController extends Ctrl_Admin {

	# 资金汇总
	public function summaryAction() {
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_summary') ){
			exit('权限不足');
		}

		$tRmbMO = new Exchange_CnyModel();

		if ( isset($_GET['kw']) && Tool_Validate::safe($_GET['kw'])) $today = $_GET['kw'];
		else $today = date("Y-m-d");

		$this->assign("today", $today);
		$time_start = strtotime($today);
		$time_end = $time_start + 24*60*60;
		// 人民币入资
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign('rmb_in', $result['total']);
		// 三种方式分别的金额
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='支付宝'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign('rmb_in_alipay', $result['total']);
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='财付通'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign('rmb_in_tenpay', $result['total']);
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='in' and status='成功' and accounttype='银行卡'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign('rmb_in_bank', $result['total']);


		// 人民币提现
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign("rmb_out", $result['total']);
		$this->assign("fee_money", $result['total']*Exchange_CnyModel::RMBOUT_FEE);
		// 三种方式分别的金额
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='支付宝'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign("rmb_out_alipay", $result['total']);
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='财付通'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign("rmb_out_tenpay", $result['total']);
		$sql = "select sum(money) as total from {$tRmbMO->table} where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功' and accounttype='银行卡'";
		$result = $tRmbMO->fRow($sql);
		if ( !$result['total'] ) $result['total'] = 0;
		$this->assign("rmb_out_bank", $result['total']);

		// 人民币总资金
		$sql = "select sum(cny_over) as cny_over,sum(cny_lock) as cny_lock from user";
		$result = $tRmbMO->fRow($sql);
		$this->assign("cny_over", $result['cny_over']);
		$this->assign("cny_lock", $result['cny_lock']);

		// 查询所有的币种
		$pairs = Coin_PairModel::getInstance()->getList();

		foreach ($pairs as $k => &$v) {
			// 币种转入
			$sql = "select sum(number) as total from `exchange_{$v['coin_from']}` where created>=".$time_start." and created<".$time_end." and opt_type='in'";
			$result = $tRmbMO->fRow($sql);
			if ( !isset($result['total']) ) $result['total'] = 0;
			$v[$v['coin_from'].'_in'] = $result['total'];

			// 币种转出
			$sql = "select sum(number) as total from `exchange_{$v['coin_from']}` where created>=".$time_start." and created<".$time_end." and opt_type='out' and status='成功'";
			$result = $tRmbMO->fRow($sql);
			if ( !isset($result['total']) ) $result['total'] = 0;
			$v[$v['coin_from'].'_out'] = $result['total'];

			// 币种当天成交量
			$sql = "select sum(number) as total from `order_coin` where coin_from='{$v['coin_from']}' and created>=".$time_start." and created<".$time_end." and opt=1";
			$result = $tRmbMO->fRow($sql);
			if ( !isset($result['total']) ) $result['total'] = 0;
			$v[$v['coin_from'].'_vol'] = $result['total'];

			// 币种24小时成交量
			$time_start_24 = time() - 24*60*60;
			$sql = "select sum(number) as total from `order_coin` where coin_from='{$v['coin_from']}' created>=".$time_start_24." and opt=1";
			$result = $tRmbMO->fRow($sql);
			if ( !isset($result['total']) || !$result['total'] ) $result['total'] = 0;
			$v[$v['coin_from'].'_vol_24'] = $result['total'];

			// 币种总资金
			$sql = "select sum({$v['coin_from']}_over) as {$v['coin_from']}_over, sum({$v['coin_from']}_lock) as {$v['coin_from']}_lock from user";
			$result = $tRmbMO->fRow($sql);
			$v[$v['coin_from'].'_over'] = $result[$v['coin_from'].'_over'];
			$v[$v['coin_from'].'_lock'] = $result[$v['coin_from'].'_lock'];

			// 币种手续费
			$sql = "select sum(number) as {$v['coin_from']}_fee from `log_finance` where created>=".$time_start." and created<".$time_end." and coin_from='{$v['coin_from']}' and coin='{$v['coin_from']}'";
			$result = $tRmbMO->fRow($sql);
			isset($result[$v['coin_from'].'_fee'])?$result[$v['coin_from'].'_fee']:0;
			$v[$v['coin_from'].'_fee'] = $result[$v['coin_from'].'_fee'];
		}

		$this->assign('pairs', $pairs);


		//融资利息
		/*$sql = "select sum(price) as interest_now from `order_ybc` where opt=4 and created>={$time_start} and created<{$time_end}";
		$result = $tRmbMO->fRow($sql);
		if(empty($result['interest_now'])){
			$result['interest_now'] = 0;
		}
		$this->assign('interest_now', $result['interest_now']);
		//总利息
		$sql = "select sum(price) as loan_interest from `order_ybc` where opt=4";
		$result = $tRmbMO->fRow($sql);
		$this->assign('loan_interest', $result['loan_interest']);
		//比特
		//融资利息
		$sql = "select sum(price) as interest_now from `order_btc` where opt=4 and created>={$time_start} and created<{$time_end}";
		$result = $tRmbMO->fRow($sql);
		if(empty($result['interest_now'])){
			$result['interest_now'] = 0;
		}
		$this->assign('interest_now', $result['interest_now']);
		//总利息
		$sql = "select sum(price) as loan_interest from `order_btc` where opt=4";
		$result = $tRmbMO->fRow($sql);
		$this->assign('loan_interest', $result['loan_interest']);*/

		//钱包实时数据
		// $this->assign('balancein', Api_Rpc_Client::getBalance('goc'));
	}
    /**
     * every day fund
     */
    public function dayAction(){
		$this->_list('userfund', 'OB=id DESC');
    }

}
