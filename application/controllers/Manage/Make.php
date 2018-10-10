<?php
/**
 * 生成
 */
class Manage_MakeController extends Ctrl_Admin {
	//protected $_auth = 5;
	protected $disableMethodPost = array('cache', 'card', 'datestatis', 'cardcsv','datestatis');

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
	public function cardAction(){
		//return $this->showMsg('生成请联系开发');
		if('POST' == $this->getRequest()->getMethod()){
			if(isset($_POST['num']) && !empty($_POST['num']) && is_numeric($_POST['num']) && $_POST['num']>0){
				$num = $_POST['num'];
			}else{
				$this->showMsg('数量有错误');
			}	
			if(isset($_POST['money']) && !empty($_POST['money']) && is_numeric($_POST['money']) && $_POST['money']>0){
				$money = trim($_POST['money']);
			}else{
				$this->showMsg('金额有错误');
			}
			/* if(isset($_POST['time']) && !empty($_POST['time'])){
				$date = $_POST['time'].'23:59:59';
			}else{
				$this->showMsg('时间有错误');
			} */
			//$type = array('1' => 'YY', '10' => 'YS', '20' => 'ES', '50' => 'WS', '100' => 'YB', '500' => 'WB', '1000' => 'YQ');
			/* $type = array('3' => 'YBG', '10' => 'YS', '20' => 'ES', '50' => 'WS', '100' => 'YB', '500' => 'WB', '1000' => 'YQ');
			if(!in_array($money, array_keys($type))){
				$this->showMsg('不支持此金额的生成');
			} */
			$time = time();
			$cardModel = new CardModel();
			$cardidArray = array();
			$cardArray = array();
			$pwdArray = array();
			$olddata = $cardModel -> find($money,2);
			if(!empty($olddata)){
				$maxdata = $cardModel->fRow("select max(cardid) max from {$cardModel->table} where money={$money} and opt=2");
				$cardid = $maxdata['max'];
				for($i = 0; $i < $num; $i++){
					$cardid += 1;	
					if(!in_array($cardid, $cardidArray) && !in_array($cardid, $olddata['cardid'])){
						$cardidArray[] = $cardid;
						$cardStr = '';
						$code="3456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKMNPQRSTUVWXY";
						$codeNum = strlen($code)-1;
						$ascii = '02';
						for($j = 0; $j < 13; $j++) {
							$char = $code{rand(0, $codeNum)};
							$ascii .= $char;
						}
						if(!in_array($ascii, $pwdArray)){
							$pwdArray[] = $ascii;
						}
						$ascii = '';
					}
				}

			}else{
				$cardid = 0;
				for($i = 0; $i < $num; $i++){
					$cardid += 1;	
					if(!in_array($cardid, $cardidArray)){
						$cardidArray[] = $cardid;
						$cardStr = '';
						$code="3456789abcdefghijkmnpqrstuvwxyABCDEFGHIJKMNPQRSTUVWXY";
						$codeNum = strlen($code)-1;
						$ascii = '02';
						for($j = 0; $j < 13; $j++) {
							$char = $code{rand(0, $codeNum)};
							$ascii .= $char;
						}	
						if(!in_array($ascii, $pwdArray)){
							$pwdArray[] = $ascii;
						}
						$ascii = '';
					}
				}
			}
			$value = array();
			$pid = date('mds',time()).str_pad($money, 4, '0', STR_PAD_LEFT);
			foreach($cardidArray as $k => $v){
				$value[$k]['pid'] 	= $pid;
				$value[$k]['cardid']= $cardidArray[$k];
				$value[$k]['card']	= 'YBG'.str_pad($money, 3, '0', STR_PAD_LEFT).str_pad($cardidArray[$k], 4, '0', STR_PAD_LEFT);
				$value[$k]['pwd']	= $pwdArray[$k];
				$value[$k]['passwd']	= md5($pwdArray[$k]);
				$value[$k]['money']	= $money;
				$value[$k]['status']	= 0;
				$value[$k]['ctime']	= $time;
				$value[$k]['utime'] 	= $time;
				$value[$k]['etime'] 	= 0;
				$value[$k]['uid']	= 0;
				$value[$k]['opt']	= 2;
            }

			if($cardModel->insertAll($value)){
				$this->showMsg('生成成功');
			}else{
				$this->showMsg('生成失败');
			}  
 
                
                echo "<pre>";
                print_r($value);
                echo "</pre>";    
				exit;
		}	
    }
	public function cardcsvAction(){
		//return $this->showMsg('请联系开发');
		$cardModel = new CardModel();
		$pidarray = $cardModel -> query("select pid from {$cardModel->table} where opt=2 and status=0 group by pid order by id desc");
		$this->assign('pid', $pidarray);
		
		if(isset($_GET['pid']) && !empty($_GET['pid'])){
			$this->_list('card', "pid={$_GET['pid']}&opt=2 OB=id DESC");
		}else{
			$this->_list('card', "opt=2 OB=id DESC");
        }
        $time = time();
		if($this->getRequest()->getMethod() == 'POST'){
			if(isset($_POST['pid']) && !empty($_POST['pid'])){
				$data = $cardModel -> query("select * from {$cardModel->table} where opt=2 and status=0 and pid={$_POST['pid']}");
			}else{
				$data = $cardModel -> query("select * from {$cardModel->table} where opt=2 and status=0");
			}
			if(!empty($data)){
				header("Content-type:application/vnd.ms-excel");
				header("Content-Disposition:filename=游戏赠卡".date('Ymds')."_".$data[0]['money']."_.xls");
				$str = "批次号\t卡号\t密码\t金额\n";
				foreach($data as $val){
					$str .= $val['pid']."\t".$val['card']."\t".$val['pwd']."\t".$val['money']."\n";
					$cardModel -> exec("update {$cardModel->table} set status=4, utime={$time} where id={$val['id']}");
				}
				exit($str); 
			}else{
				$this->showMsg("没有数据导出");
			}
		}
	}

    public function datestatisAction(){
		if($this->getRequest()->getMethod() == 'POST'){
			$time = isset($_POST['time']) ? $_POST['time']: '';
			$user = new UserModel();
			if(!empty($time)){
				$stime = strtotime($time." 00:00:00");
				$etime = strtotime($time." 23:59:59");
				$tdata[] = $this->datestatis($user, $stime, $etime);
			}else if($time == '全部'){
				//$mintime = $user->fRow("select min(created) mintime from {$user->table}");
				//echo $mtime = strtotime(date("Y-m-d",$mintime['mintime'])." 00:00:00");
				$mtime = strtotime("2013-07-05 00:00:00");
				for($time=$mtime; $time<=time(); $time+=3600*24){
					$stime = $time;
					$etime = $time + (3600*24 - 1);
					$tdata[] = $this->datestatis($user, $stime, $etime);
				} 
			}else{
				$this->showMsg('日期不能为空');
			}
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=每日统计".date('Y-m-d', $etime).".xls");
			$str = "统计日期\t注册用户数\t充值成功笔数\t充值成功总额\t提现成功笔数\t提现成功总额\t富途币转入笔数\t富途币转入总数\t富途币转出笔数\t富途币转出总数\t富途币买入用户数\t富途币成功卖出用户数\t比特币转入笔数\t比特币转入总数\t比特币转出笔数\t比特币转出总数\t比特币买入用户数\t比特币成功卖出用户数";
			$str .= "\tRMB大于100用户数\tYbc大于1用户数\tRMB总额\tRMB可用余额\tRMB冻结总额\tYBC总额\tYBC可用余额\tYBC冻结总额\t指数交易价格\n";
			foreach($tdata as $v){
				$str .= $v;
			}
			exit($str); 
		}
    }
	private function datestatis($user, $stime, $etime){
		//每天注册量
		$usernum = $user->fRow("select count(*) as num from {$user->table} where created between {$stime} and {$etime}");
		$usernum = !empty($usernum) ? $usernum : array('num' => 0);
		//充值成功数量
		$rmb = new Exchange_CnyModel();
		$rmbin = $rmb->fRow("select count(*) as num, sum(money) as total from {$rmb->table} where status='成功' and opt_type='in' and created between {$stime} and {$etime}");
		//$rmbin = !empty($rmbin) ? $rmbin : array('num' => 0, 'total' => 0);
		if(!empty($rmbin)){
			if(empty($rmbin['num'])){
				$rmbin['num'] = 0;
			}
			if(empty($rmbin['total'])){
				$rmbin['total'] = 0;
			}
		}
		//提现成功        
		$rmbout = $rmb->fRow("select count(*) as num, sum(money) as total from {$rmb->table} where status='成功' and opt_type='out' and created between {$stime} and {$etime}");
		//$rmbout = !empty($rmbout) ? $rmbout : array('num' => 0, 'total' => 0);
		if(!empty($rmbout)){
			if(empty($rmbout['num'])){
				$rmbout['num'] = 0;
			}
			if(empty($rmbout['total'])){
				$rmbout['total'] = 0;
			}
		}
		//富途币转入
		$ybb = new Exchange_GocModel();
		$ybbin = $ybb->fRow("select count(*) as num, sum(number) as total from {$ybb->table} where status='成功' and opt_type='in' and created between {$stime} and {$etime}");
		//$ybbin = !empty($ybbin) ? $ybbin : array('num' => 0, 'total' => 0);
		if(!empty($ybbin)){
			if(empty($ybbin['num'])){
				$ybbin['num'] = 0;
			}
			if(empty($ybbin['total'])){
				$ybbin['total'] = 0;
			}
		}
		//富途币转出
		$ybbout = $ybb->fRow("select count(*) as num, sum(number) as total from {$ybb->table} where status='成功' and opt_type='out' and created between {$stime} and {$etime}");
		//$ybbout = !empty($ybbout) ? $ybbout : array('num' => 0, 'total' => 0);
		if(!empty($ybbout)){
			if(empty($ybbout['num'])){
				$ybbout['num'] = 0;
			}
			if(empty($ybbout['total'])){
				$ybbout['total'] = 0;
			}
		}
		//富途币买入总数
		$ybbtru = new Order_YbcModel();
		$ybbbuy = $ybb->query("select buy_uid from {$ybbtru->table} where opt=1 and created between {$stime} and {$etime} group by buy_uid");
		if(!empty($ybbbuy)){
			if(empty($ybbbuy)){
				$ybbbuy['num'] = 0;
			}else{
				$ybbbuy['num'] = count($ybbbuy);
			}
		}
		//富途币卖出总数        
		$ybbsale = $ybb->query("select sale_uid from {$ybbtru->table} where opt=1 and created between {$stime} and {$etime} group by sale_uid");
		if(!empty($ybbsale)){
			if(empty($ybbsale)){
				$ybbsale['num'] = 0;
			}else{
				$ybbsale['num'] = count($ybbsale);
			}
		}
		//比特币转入
		$btc = new Exchange_BtcModel();
		$btcin = $btc->fRow("select count(*) as num, sum(number) as total from {$btc->table} where status='成功' and opt_type='in' and created between {$stime} and {$etime}");
		//$ybbin = !empty($ybbin) ? $ybbin : array('num' => 0, 'total' => 0);
		if(!empty($btcin)){
			if(empty($btcin['num'])){
				$btcin['num'] = 0;
			}
			if(empty($btcin['total'])){
				$btcin['total'] = 0;
			}
		}
		//比特币转出
		$btcout = $btc->fRow("select count(*) as num, sum(number) as total from {$btc->table} where status='成功' and opt_type='out' and created between {$stime} and {$etime}");
		//$ybbout = !empty($ybbout) ? $ybbout : array('num' => 0, 'total' => 0);
		if(!empty($btcout)){
			if(empty($btcout['num'])){
				$btcout['num'] = 0;
			}
			if(empty($btcout['total'])){
				$btcout['total'] = 0;
			}
		}
		//比特币买入总数
		$btctru = new Order_BtcModel();
		$btcbuy = $btctru->query("select buy_uid from {$btctru->table} where opt=1 and created between {$stime} and {$etime} group by buy_uid");
		if(!empty($btcbuy)){
			if(empty($btcbuy)){
				$btcbuy['num'] = 0;
			}else{
				$btcbuy['num'] = count($btcbuy);
			}
		}
		//比特币卖出总数        
		$btcsale = $btctru->query("select sale_uid from {$btctru->table} where opt=1 and created between {$stime} and {$etime} group by sale_uid");
		if(!empty($btcsale)){
			if(empty($btcsale)){
				$btcsale['num'] = 0;
			}else{
				$btcsale['num'] = count($btcsale);
			}
		}
		//每天rmb大于100的用户数
		$ybbrmb = $ybb->fRow("select count(*) as num from `user` where rmb_over+rmb_lock>=100");
		if(empty($ybbrmb)){
			$ybbrmb['num'] = 0;
		}
		$ybbybc = $ybb->fRow("select count(*) as num from `user` where ybc_over+ybc_lock>=1");
		if(empty($ybbybc)){
			$ybbybc['num'] = 0;
		}
		
		// 总资金
		$sql = "select sum(rmb_over) as rmb_over,sum(rmb_lock) as rmb_lock,sum(btc_over) as btc_over, sum(btc_lock) as btc_lock,sum(ybc_over) as ybc_over, sum(ybc_lock) as ybc_lock from user";
		$result = $ybb->fRow($sql);
		//$betprice = $ybb->fRow("select price from bet_log where hourtime=".strtotime(date('Y-m-d',$stime)." 17:59:59"));
		$start_time = strtotime(date("Y-m-d ", $stime)."17:00:00");//mktime($hour,0,0);
		$end_time   = strtotime(date("Y-m-d ", $stime)."17:59:59");
		$sql    = "select sum(price*number) sum1,sum(number) sum2 from `order_ybc` where opt=1 and created>={$start_time} and created<={$end_time}";
		$result1 = $ybb->fRow($sql);
		if(empty($result1) || empty($result1['sum1']) || empty($result1['sum2'])){
			$betprice['price']  = 0;
		} else {
			$perPrice   = $result1['sum1'] / $result1['sum2'];
			$betprice['price']   = Tool_Str::format($perPrice);
		}
		if(empty($betprice)){
			$betprice['price'] = 0;
		}
		$str = date('Y-m-d', $etime)."\t".$usernum['num']."\t".$rmbin['num']."\t".$rmbin['total']."\t".$rmbout['num']."\t".$rmbout['total']."\t".
				$ybbin['num']."\t".$ybbin['total']."\t".$ybbout['num']."\t".$ybbout['total']."\t".$ybbbuy['num']."\t".
				$ybbsale['num']."\t".$btcin['num']."\t".$btcin['total']."\t".$btcout['num']."\t".$btcout['total']."\t".$btcbuy['num']."\t".$btcsale['num']."\t".$ybbrmb['num']."\t".$ybbybc['num']
				."\t".($result['rmb_over']+$result['rmb_lock'])."\t".($result['rmb_over'])."\t".($result['rmb_lock'])."\t".($result['ybc_over']+$result['ybc_lock'])."\t".($result['ybc_over'])."\t".($result['ybc_lock'])."\t".$betprice['price']."\n";
		return $str;
	}
	public function carduseAction(){
		$cardsell = new CardSellModel();
		$card = new CardModel();
		$data = $cardsell -> query("select uid, cardinfo, name from {$cardsell->table} where status<>3");
		if(!empty($data)){
			foreach($data as $val){
				$cardinfo = json_decode($val['cardinfo'], true);
				foreach($cardinfo['cardid'] as $value){
					foreach($value as $v){
						$cardid[$val['uid']]['name'] = $val['name'];
						$cardid[$val['uid']]['id'][] = $v['id'];
					}
				}	
            }
            foreach($cardid as $kk => $vv){
                foreach($vv['id'] as $vvv){
				    $uidata = $card->fRow("select u.uid as uid, c.card as card, c.money as money, u.name as sname from card as c, user as u where c.id={$vvv} and c.uid<>0 and c.uid=u.uid");
				    if(!empty($uidata)){
						$uidata['name'] = $vv['name'];
						$uidata['gid'] = $kk;
					    if($kk == 1){
						    $uidata['k'] = 1;
					    }else{
						    $uidata['k'] = 0;
					    }
				    	$ucard[] = $uidata;
                    }
                }
			}
			//unset($cardid);
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=赠卡使用记录".date('Ymds').".xls");
			$str = "类型\t卡号\t购买者ID\t购买者\t使用者ID\t使用者\t金额\n";
            foreach($ucard as $val){
                if($val['k'] == 1){
				    $str .= "赠卡\t".$val['card']."\t".$val['gid']."\t".$val['name']."\t".$val['uid']."\t".$val['sname']."\t".$val['money']."\n";
                }else{
				    $str .= "卖卡\t".$val['card']."\t".$val['gid']."\t".$val['name']."\t".$val['uid']."\t".$val['sname']."\t".$val['money']."\n";
                }    
            }
			//iconv("utf-8", "gb2312", $str);
			exit($str); 
			/* echo "<pre>";
			print_r($ucard);
			print_r($cardid);
			echo "</pre>"; */
		}else{
			echo "没有数据";
        }
        exit;
	}
	
	public function cardSellAction(){
		//$cardsell = new CardSellModel();
		$card = new CardModel();
		$data = $card -> query("select * from {$card->table} where status in(4,5)");
		if(!empty($data)){
			/* foreach($data as $val){
				$cardinfo = json_decode($val['cardinfo'], true);
				foreach($cardinfo['cardid'] as $value){
					foreach($value as $v){
						$cardid[$val['uid']]['name'] = $val['name'];
						$cardid[$val['uid']]['id'][] = $v['id'];
					}
				}	
            }
            foreach($cardid as $kk => $vv){
                foreach($vv['id'] as $vvv){
				    $uidata = $card->fRow("select u.uid as uid, c.card as card, c.money as money, u.name as sname from card as c, user as u where c.id={$vvv} and c.uid<>0 and c.uid=u.uid");
				    if(!empty($uidata)){
						$uidata['name'] = $vv['name'];
						$uidata['gid'] = $kk;
					    if($kk == 1){
						    $uidata['k'] = 1;
					    }else{
						    $uidata['k'] = 0;
					    }
				    	$ucard[] = $uidata;
                    }
                }
			} */
			//unset($cardid);
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=卡售出记录".date('Ymds').".xls");
			$str = "卡号\t金额\t状态\n";
            foreach($data as $val){
                if($val['status'] == 4){
				    $str .= $val['card']."\t".$val['money']."\t未使用\n";
                }else{
				    $str .= $val['card']."\t".$val['money']."\t已使用\n";
                }    
            }
			//iconv("utf-8", "gb2312", $str);
			exit($str); 
			/* echo "<pre>";
			print_r($ucard);
			print_r($cardid);
			echo "</pre>"; */
		}else{
			echo "没有数据";
        }
        exit;
	}
}




















