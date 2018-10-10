<?php
/**
 * Kline
 *
 */
class Cli_KlineController extends Ctrl_Cli
{
	# Run
	public function runAction($type = '5m')
	{
		$db = new Orm_Base();
		// 获取要生成的币K线图
		$cDatas = $db->query("select * from coin_pair where status=1");
	    foreach($cDatas as $v){
			$coin_from = $v['coin_from'];
			$coin_to   = $v['coin_to'];
			$coin  = $v['name'];
			$k = Cache_Memcache::get("{$coin}tradeline_{$type}_cache");
		    // 判断缓存中是否有记录
			if (!empty($k)) {
				// 有,追加数据
				$time = end(array_keys(json_decode($k, true)));
			} else {
				// 没有,重新获取.重新获取开始时间
				$time = 0;
			}
			// 获取交易流水
			$data = $db->query("select * from order_coin where created>={$time} and coin_from='{$coin_from}' and coin_to='{$coin_to}' and opt=1");

			$tOrders = array();
		    // 格式化交易流水信息
			foreach($data as $v1){
				$tTime = strtotime($this->ctime($v1['created'], $type));
				if(time() < $tTime){
					break;
				}
				// format $tOrders['unix_time'] = ['unix_time','成交量','open价格','high价格','low价格','close价格'];
				if(empty($tOrders[$tTime])){
					# open
					$tOrders[$tTime] = array(($tTime).'000', $v1['price'], $v1['price'], $v1['price'], $v1['price'], $v1['number']);
				} else {
					$tOrders[$tTime][5] += $v1['number'];
					# high
					$v1['price'] > $tOrders[$tTime][2] && $tOrders[$tTime][2] = $v1['price'];
					# low
					$v1['price'] < $tOrders[$tTime][3] && $tOrders[$tTime][3] = $v1['price'];
					# close
					$tOrders[$tTime][4] = $v1['price'];
				}
			}
			// K线数据
			$kArray = json_decode($k, true);
		    // 判断是追加数据还是重新生成
			 $tOrders = is_array($kArray) ? ($kArray + $tOrders) : $tOrders;
		    // 截取最后80条数据
			$tOrders = array_slice($tOrders, -1000, 1000, true);
			if (!empty($tOrders)) {
				// 插入到缓存中去
				Cache_Memcache::set("{$coin}tradeline_{$type}_cache", json_encode($tOrders));
			}
	 		$tJS = array();
			foreach($tOrders as $v1) {
				$tJS[] = '['.implode(',', $v1).']';
				$mstJS[] = array('time' => $v1[0],'open'=>$v1[1],'high'=>$v1[2],'low'=>$v1[3],'close'=>$v1[4],'num'=>$v1[5]);
			}

			Cache_Memcache::set("{$coin}tradeline_{$type}", '{"des" : "" , "isSuc" : true  , "datas" : {"USDCNY":6.5746,"contractUnit":"'.strtoupper($coin_from).'","data" :['.trim(implode(',', $tJS),',').']}, "marketName": "QQIBTC", "moneyType": "'.$coin_to.'", "symbol": "'.$coin.'", "url": "https://www.bijiaosuo.com"}');
		}
		exit($type);
	}

	//判断时间的范围 确定时间
	private function ctime($time, $min = "30m")
	{
		switch ($min) {
			case '1m':
				$tTime = date('Y-m-d H:i:00', strtotime('+1 minute', $time));
			break;
			case '3m':
			case '5m':
			case '15m':
			case '30m':
				$in = intval($min);
				$i = date('i', $time);
				$c = $i - $i%$in + $in;
				if ($c == 60) {
				    $tTime = date('Y-m-d H:i:00', strtotime('+1 hour', strtotime(date('Y-m-d H:00:00', $time))));
				} else {
				    if ($c < 10) {
				        $c = '0'.$c;
				    }
				    $tTime = date('Y-m-d H', $time).':'.$c.':00';
				}
				break;
			case '1h':
			case '2h':
			case '4h':
			case '6h':
			case '12h':
				$in = intval($min);
				$h = date('H', $time);
				$c = $h - $h%$in + $in;
				if ($c == 24) {
				    $tTime = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime(date('Y-m-d', $time))));
				} else {
				    if ($c < 10) {
				        $c = '0'.$c;
				    }
				    $tTime = date('Y-m-d', $time).' '.$c.':00:00';
				}
				break;
			case '1d':
				$c = date('Y-m-d', $time);
				$tTime = date('Y-m-d 00:00:00', strtotime('+1 day', strtotime($c)));
				break;
		}
		return $tTime;
	}

	public function clearAction($type = '5m')
	{
		$mem = Cache_Memcache::instance();
		$db = new Orm_Base();
		// 获取要生成的币K线图
		$cDatas = $db->query("select * from coin_pair where status=1");
	    foreach($cDatas as $v){
			$coin  = $v['name'];
			$mem->delete("{$coin}tradeline_{$type}_cache");
		}

		exit($type);

	}

	public function depthsAction()
	{
		$db = new Orm_Base();
		$cDatas = $db->query("select * from coin_pair where status=1");
		foreach ($cDatas as $v1) {
			$coin  = $v1['name'];

			$asks = '{"asks":[';
			$sale_data = $db->query("select price, sum(numberover) number from trust_coin where coin_from = '{$v1['coin_from']}' and coin_to = '{$v1['coin_to']}' and status in(0, 1) and flag = 'sale' group by price order by price limit 100");
			$count = count($sale_data) - 1;
			for ($i = $count; $i >= 0; $i--) {
					$asks .= "[{$sale_data[$i]['price']}, {$sale_data[$i]['number']}],";
			}
			$asks = rtrim($asks, ',');
			$asks .= ']';

			$bids = '"bids":[';
			$buy_data = $db->query("select price, sum(numberover) number from trust_coin where coin_from = '{$v1['coin_from']}' and coin_to = '{$v1['coin_to']}' and status in(0, 1) and flag = 'buy' group by price order by price desc limit 100");
			foreach ($buy_data as $v3) {
					$bids .= "[{$v3['price']}, {$v3['number']}],";
			}
			$bids = rtrim($bids, ',');
			$bids .= '],"date":'.time().'}';

			Cache_Memcache::set("{$coin}_depth", $asks.','.$bids, 0);
		}
		exit('success');
	}

}
