<?php
class Cli_OrderController extends Ctrl_Cli {

	/**
	 * 撤销2天前等待中的充值
	 * 定时任务：每天05:00:00执行一次
	 * php Cli.php request_uri=/cli_order/cny_in_revoke
	 */
	public function cny_in_revokeAction() {
		$time_step 		= strtotime("-2 day");
		$cancel_time 	= date("Y-m-d 23:59:59", $time_step);
		$run_time 		= strtotime(date("Y-m-d 05:00:00", time()));
		$sql 			= "update exchange_cny set status='已取消',bak='订单已失效',updated={$run_time},updateip='3.3.3.3' where opt_type='in' and status='等待' and created<UNIX_TIMESTAMP('" . $cancel_time . "')";
		$cny_mo = new Exchange_CnyModel();
		$cny_mo->query($sql);

		exit('Success');
	}

	/**
	 * UC自动单
	 * 
	 * nohup php Cli.php request_uri=/cli_order/uc_order &
	 */
	public function uc_orderAction() {
		# 官方UID
		$uid 		= 102960;
		// $uid 		= 100815;
		# 每日释放总量
		$total 		= 10000;
		# 每日回收比率
		$buy_rate 	= 0.7;
        $trust_mo 	= new Trust_CoinModel;
        $user_mo 	= new UserModel;
        $pair_mo	= new Coin_PairModel;
        $float_mo 	= new Coin_FloatModel;
        while(true) {
        	sleep(3);
	        $time 		= time() - 23*3600;
	        # 检查今天是否已经操作
	        $is_exist 	= $trust_mo->where("uid={$uid} and coin_from='uc' and flag='buy' and created>{$time}")->fOne('id');
	        if($is_exist) {
	        	// exit('今天已买入');
	        	continue;
	        }

			$pair 		= $pair_mo->getPair('uc_cny');
			// 闭市
	        if( $pair['rule_open'] == 1 ){
	            $now_hi = intval(date('Hi'));
	            if( $now_hi < intval($pair['open_start']) || $now_hi > intval($pair['open_end']) ){
	                // exit('休市中');
	        		continue;
	            }
	        }

	        //价格限制
	        if($pair['price_limit'] == 1){
	            if (intval(date('Hi')) >= $pair['open_end']) {
	                $period = date('Ymd');
	            } else {
	                $period = date('Ymd', strtotime('-1 day'));
	            }

	            $dayprice 	= $float_mo->field('price_up, price_down')
	            			->where("coin_from='{$pair['coin_from']}' and day = {$period}")
	            			->fRow();
	            if(empty($dayprice)) {
	            	$dayprice['price_up'] = 1.1;
	            	$dayprice['price_down'] = 0.9;
	            }
	            $price_up 	= bcmul($dayprice['price_up'], 1, $pair['price_float']);
	            $price_down = bcmul($dayprice['price_down'], 1, $pair['price_float']);
	           	$user 		= $user_mo->where('uid='.$uid)->fRow();
	            $_POST['coin_from']	='uc';
	            $_POST['type']		='in';
	            $_POST['number']	= $total*$buy_rate;
	            $_POST['price'] 	= $price_up;
	        	$trust_mo->btc($_POST, $user);
	        }
    	}

        exit('success');
	}
}
