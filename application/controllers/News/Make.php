<?php
/**
 * 生成
 */
class Manage_MakeController extends Ctrl_Base {
	# protected $_auth = 5;

	/**
	 * 更新缓存
	 * @param int $opt 二进制参数
	 */
	public function cacheAction($opt = 0){
		isset($_POST['opt']) && $opt = array_sum($_POST['opt']);
		if(!$opt) return;
		$tMem = & Cache_Redis::instance();
		# 更新用户(user)
		if(1 & $opt){
			foreach($tMem->hkeys('useremail') as $v1) $tMem->hDel('useremail', $v1);
			$tMO = new UserModel();
			foreach($tMO->fList() as $v1) UserModel::saveRedis($v1, $tMem);
		}
		# 更新分类(category)
		if(2 & $opt){
			# 删除已有缓存
			foreach($tMem->hkeys('category') as $v1) $tMem->hDel('category', $v1);
			foreach($tMem->hkeys('cgpy') as $v1) $tMem->hDel('cgpy', $v1);
			# 生成缓存
			$tMO = new CategoryModel();
			foreach($tMO->order('ob desc')->fList() as $v1){
				$tMem->hset('category', $v1['cid'], $tData = json_encode($v1));
				$tMem->hset('cgpy', $v1['py'], $tData);
			}
		}
		# 更新K线数据
		if(4 & $opt){
			$tOrders = array();
			$tTime = strtotime(date('Y-m-d H').(date('i') < 30? ':00': ':30').':00');
			# 处理数据
			$tMO = new OrderModel();
			foreach($tMO->where('created>'.($tTime-172801).' AND created<'.$tTime.' AND opt=1')->field('price,number,created')->fList() as $v1){
				$tTime = strtotime(date('Y-m-d H', $v1['created']).(date('i', $v1['created']) < 30? ':00': ':30').':00');
				if(empty($tOrders[$tTime])){
					# open
					$tOrders[$tTime] = array(($tTime+8*3600).'000', $v1['number'], $v1['price'], $v1['price'], $v1['price'], $v1['price']);
				} else {
					$tOrders[$tTime][1] += $v1['number'];
					# high
					$v1['price'] > $tOrders[$tTime][3] && $tOrders[$tTime][3] = $v1['price'];
					# low
					$v1['price'] < $tOrders[$tTime][4] && $tOrders[$tTime][4] = $v1['price'];
					# close
					$tOrders[$tTime][5] = $v1['price'];
				}
			}
			# 写入缓存
			$tJS = array();
			foreach($tOrders as $v1) 
			{
				 $tJS[] = '['.implode(',', $v1).']';
				 $mstJS[] = array('time' => $v1[0],'num'=>$v1[1],'open'=>$v1[2],'high'=>$v1[3],'low'=>$v1[4],'close'=>$v1[5]);
			}
			file_put_contents('js/mstradetimeline.js', json_encode($mstJS));
			file_put_contents('js/tradetimeline.js', 'trade_global = {symbol:"BTC_CNY",symbol_view:"BTC/CNY",ask:1.2,time_line:['.implode(',', $tJS).']};');
		}
		# 机器人(虚拟成交)
		if(8 & $opt){
			# 单价
			$tPrice = json_decode(file_get_contents('btc_sum'), true);
			isset($tPrice['buy'][0], $tPrice['sale'][0]) || exit;
			$tPrice = rand($tPrice['buy'][0]['p'] * 100, $tPrice['sale'][0]['p'] * 100) / 100;
			# 时间
			$tHour = date('H');
			# 成交量
			$tBtc = (($tHour > 8 && $tHour < 23)? rand(1000, 3000): rand(10, 1000)) / 1000;
			# 写入成交
			$tMO = new Orm_Base();
			$saleid = ($buyid = rand(0, 1))? 0: 1;
			$tMO->exec("INSERT INTO `order`(`id`, `price`, `number`, `buy_tid`, `buy_uid`, `sale_tid`, `sale_uid`, `created`) VALUES (NULL, '$tPrice', '$tBtc', '$buyid', '5', '$saleid', '5', '".($_SERVER['REQUEST_TIME'] - rand(0, 180))."')");
			$tMO->ajaxOrder();
		}
		$this->showMsg('更新缓存成功');
	}
}