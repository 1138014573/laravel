<?php
class Cli_TradeController extends Ctrl_Cli{

	private $coin = array();
    /*
     * 缓存刷新uid
     */
    private $uids_refresh = array();
    /*
     * 需要使用的model
     */
    private $user_mo;
    private $trust_coin_mo;
    private $order_coin_mo;
    private $mo = array('User', 'Trust_Coin', 'Order_Coin');

    /**
     * 判断币种是否存在
     */
    private function getCoin($pair = 'goc_cny') {
        $coin_mo = new Coin_PairModel();
        if(!$coin = $coin_mo->field('id,rate,rate_buy,price_float')->where("name='{$pair}'")->fRow()){
            return false;
        }
        $pair_arr = explode('_', $pair);
        $pair_arr[2] = $coin['id'];
        $pair_arr[3] = $coin['rate'];
        $pair_arr[4] = $coin['price_float'];
        $pair_arr[5] = $coin['rate_buy'];
        return  $pair_arr;
	}

    /**
     * @desc model初始化
     */
    private function initMo($refresh=false){
        foreach($this->mo as $mo){
            if($refresh === false && $this->{strtolower($mo).'_mo'}){
                continue;
            }
            $model = $mo.'Model';
            $this->{strtolower($mo).'_mo'} = new $model;
        }
    }
    /**
     * @desc 币币交易入口
     */
    public function doCoinAction($pair){
        if(!$this->coin = $this->getCoin($pair)){
            exit('参数有误');
        }
        while(true){
            $this->initMo();
            $this->coin2coin($pair);
        }
        exit;
    }

    /**
     * @desc 币币交易主要逻辑
     */
   	public function coin2coin($pair){
        $this->order_coin_mo->ajaxcoinTrustList($pair);
        $this->order_coin_mo->ajaxcoinOrder($pair);

        $this->user_mo->begin();
        $trust_id = $this->trust_coin_mo->where("isnew='Y' and coin_from='{$this->coin[0]}' and coin_to='{$this->coin[1]}' and numberover>1E-9")->order("id asc")->fOne('id');
        //没有委托，sleep 3s
        if(empty($trust_id)){
            $this->user_mo->back();
            sleep(3);
            return ;
        }
        $trust = $this->trust_coin_mo->lock()->where("id={$trust_id}")->fRow();

        //todo
        if('N' == $trust['isnew'] || !$trust['numberover'] = floatval($trust['numberover'])){
            $this->user_mo->back();
            return ;
        }
        //查询委托列表
        $list = array();
        if($trust['flag'] == 'buy'){
            $list = $this->trust_coin_mo->getListByPrice($trust['price'], $this->coin, 'sale');
        } elseif ($trust['flag'] == 'sale') {
            $list = $this->trust_coin_mo->getListByPrice($trust['price'], $this->coin, 'buy');
        } else {
            $this->user_mo->back();
            return ;
        }

        //没有符合价格的列表，更新委托并返回
        if(empty($list)){
            $this->trust_coin_mo->update(array('id'=>$trust['id'],'isnew'=>'N'));
            $this->order_coin_mo->ajaxcoinTrustList($pair);
            $this->user_mo->commit();
            return ;
        }
        //主动用户
        $user = array('uid'=>$trust['uid']);
        //订单
        $orders = array();
        //主动用户数据变化
        $user_active = array("{$this->coin[0]}_over"=>0, "{$this->coin[0]}_lock"=>0, "{$this->coin[1]}_over"=>0, "{$this->coin[1]}_lock"=>0);
        foreach($list as $v){
            //加锁数据获取失败
            if(!$v = $this->trust_coin_mo->lock()->where("id={$v['id']}")->fRow()){
                //$this->user_mo->back();
                continue;
            }
            if($v['numberover'] < 1E-9) {
                continue;
            }
            $min = $trust['numberover'] > $v['numberover'] ? $v['numberover'] : $trust['numberover'];
            $min10000 = $min;
            /*
            //yby 10000:1
            if($this->coin[0] == 'yby'){
                $min10000 = $min*10000;
            }*/
            //更新被动委托
            if(!$this->trust_coin_mo->updateNumber($v['id'], ($v['numberover']-$min), $min)){
                $this->user_mo->back();
                return false;
            }
            //主动用户数据
            $trust['numberover'] -= $min;
            //卖
            $price_total = $min*$v['price'];
            $price_total_rate = $price_total*(1 - $this->coin[3]);
            $over_total_active = bcmul($price_total_rate, 1, $this->coin[4]);
            $over_total_v = bcmul($price_total_rate, 1, $this->coin[4]);
            // exit();
            //买 todo
            $min_real_active = $min10000;
            $min_real_v = $min10000;
            if($this->coin[5] > 0){
                $min_real_active = $min10000 * (1 - $this->coin[5]);
                $min_real_v = $min10000 * (1 - $this->coin[5]);
            }
            //主动用户免手续费
            /*if(in_array($trust['uid'], $uids_nofee)){
                $over_total_active = bcmul($price_total, 1, $this->coin[4]);
                $min_real_active = $min10000;
            }
            //被动用户免手续费
            if(in_array($v['uid'], $uids_nofee)){
                $over_total_v = bcmul($price_total, 1, $this->coin[4]);
                $min_real_v = $min10000;
            }*/
            //被动用户数据变化
            $user_passive = array();
            if($v['uid'] == $trust['uid']){
                $v_user = false;
            } else {
                $v_user = $this->user_mo->where("uid={$v['uid']}")->fRow();
            }
            if('sale' == $trust['flag']){
				$orders[] = array('buy'=>$v, 'sale'=>$trust,'price'=>$v['price'], 'min'=>$min, 'opt'=>Order_CoinModel::OPT_TRADE);
                $user_active[$this->coin[1].'_over'] += $over_total_active;
                $user_active[$this->coin[0].'_lock'] -= $min10000;
                if($v_user){
                    $user_passive[$this->coin[1].'_lock'] = -$price_total;
                    $user_passive[$this->coin[0].'_over'] = $min_real_v;
                    if($this->coin[5] > 0){
				        $orders[] = array('buy'=>$v, 'sale'=>$trust,'price'=>1, 'min'=>$min10000*$this->coin[5], 'opt'=>Order_CoinModel::OPT_FEE_BUY);
                    }
                } else {
                    $user_active[$this->coin[1].'_lock'] -= $price_total;
                    $user_active[$this->coin[0].'_over'] += $min_real_v;
                    if($this->coin[5] > 0){
				        $orders[] = array('buy'=>$v, 'sale'=>$trust,'price'=>1, 'min'=>$min10000*$this->coin[5], 'opt'=>Order_CoinModel::OPT_FEE_BUY);
                    }
                }
                //卖家手续费
                if($this->coin[3] > 0){
				    $orders[] = array('buy'=>$v, 'sale'=>$trust,'price'=>bcsub($price_total,$over_total_active,8), 'min'=>1, 'opt'=>Order_CoinModel::OPT_FEE);
                }
            } elseif ('buy' == $trust['flag']) {
				$orders[] = array('buy'=>$trust, 'sale'=>$v,'price'=>$v['price'], 'min'=>$min, 'opt'=>Order_CoinModel::OPT_TRADE);
                //差价还给用户
                $price_diff = $min*$trust['price']-$price_total;
                $price_diff = $price_diff < 0 ? 0 : $price_diff;
                $user_active[$this->coin[1].'_lock'] += -$price_total - $price_diff;
                $user_active[$this->coin[1].'_over'] += $price_diff;
                $user_active[$this->coin[0].'_over'] += $min_real_active;
                if($v_user){
                    $user_passive[$this->coin[1].'_over'] = $over_total_v;
                    $user_passive[$this->coin[0].'_lock'] = -$min10000;
                    //卖家手续费
                    if($this->coin[3] > 0){
                        $orders[] = array('buy'=>$trust, 'sale'=>$v,'price'=>bcsub($price_total, $over_total_v, 8), 'min'=>1, 'opt'=>Order_CoinModel::OPT_FEE);
                    }
                } else {
                    $user_active[$this->coin[1].'_over'] += $over_total_v;
                    $user_active[$this->coin[0].'_lock'] -= $min10000;
                    //卖家手续费
                    if($this->coin[3] > 0){
                        $orders[] = array('buy'=>$trust, 'sale'=>$v,'price'=>bcsub($price_total, $over_total_v, 8), 'min'=>1, 'opt'=>Order_CoinModel::OPT_FEE);
                    }
                }
                //买家手续费
                if($this->coin[5] > 0){
				    $orders[] = array('buy'=>$trust, 'sale'=>$v,'price'=>1, 'min'=>$min10000*$this->coin[5], 'opt'=>Order_CoinModel::OPT_FEE_BUY);
                }
            } else {
                continue;
            }
            //更新被动用户
            if($v_user){
                if(!$this->user_mo->safeUpdate($v_user, $user_passive, true, true)){
                    echo "v_user: {$v_user['uid']}";
                    print_r($user_passive);
                    $this->user_mo->back();
                    $this->trust_coin_mo->update(array('id'=>$v['id'],'isnew'=>'N','numberover'=>0,'status'=>3,'updated'=>time(),'updateip'=>'6.4.6.4'));
                    return ;
                }
                $this->uids_refresh[] = $v_user['uid'];
            }
            if(!$trust['numberover']) break;
        }
        //更新主动委托
        if(!$this->trust_coin_mo->updateNumber($trust['id'], $trust['numberover'], $min)){
            echo "updateNumber fail {$trust['id']}\n";
            $this->user_mo->back();
            return ;
        }
        //更新主动用户
        if(!$this->user_mo->safeUpdate($user, $user_active, true)){
            echo "safe active fail {$user['uid']}\n";
            print_r($user_active);
            $this->user_mo->back();
            $this->trust_coin_mo->update(array('id'=>$trust['id'],'isnew'=>'N','numberover'=>0,'status'=>3,'updated'=>time(),'updateip'=>'6.7.6.7'));
            return ;
        }
        $this->uids_refresh[] = $user['uid'];
        //插入订单
        if(!$this->insertOrder($orders)){
            $this->user_mo->back();
            print_r($orders);
            echo "insert order fail \n";
            return ;
        }
        $this->user_mo->commit();
        foreach($this->uids_refresh as $v){
            Tool_Session::mark($v);
        }
        $this->uids_refresh = array();
        $this->order_coin_mo->ajaxcoinTrustList($pair);
        $this->order_coin_mo->ajaxcoinOrder($pair);
        //$this->order_coin_mo->ajaxcoinAllOrder();
        return true;
    }

    /**
     * @desc 成交脚本order插入
     */
    public function insertOrder($datas){
		$time = time();
		$values = array();
		$sql = "INSERT INTO `order_coin` (`price`, `number`, `buy_tid`, `buy_uid`, `sale_tid`, `sale_uid`, `opt`, `created`, coin_from, coin_to) VALUES ";
        $data_7466 = array("{$this->coin[0]}_over"=>0, "{$this->coin[1]}_over"=>0);
        $fees = "insert into log_finance(from_uid,to_uid,coin,number,type,bak_id,created,coin_from,coin_to,flag) values";
		foreach($datas as $v){
            //手续费加到uid7466
            if($v['opt'] == Order_CoinModel::OPT_FEE){
                $data_7466["{$this->coin[1]}_over"] = bcadd($data_7466["{$this->coin[1]}_over"], $v['price'], 8);
                $fees .= "({$v['sale']['uid']},".User_AdminModel::COIN_FEE.",'{$this->coin[1]}',{$v['price']},5,{$v['sale']['id']},".time().",'{$v['sale']['coin_from']}','{$v['sale']['coin_to']}',0),";
                continue;
            } elseif($v['opt'] == Order_CoinModel::OPT_FEE_BUY){

                $data_7466["{$this->coin[0]}_over"] = bcadd($data_7466["{$this->coin[0]}_over"], $v['min'], 8);
                $fees .= "({$v['buy']['uid']},".User_AdminModel::COIN_FEE.",'{$this->coin[0]}',{$v['min']},5,{$v['buy']['id']},".time().",'{$v['buy']['coin_from']}','{$v['buy']['coin_to']}',1),";
                continue;
            }
			empty($v['buy']) && $v['buy'] = array('id'=>0, 'uid'=>0);
			empty($v['sale']) && $v['sale'] = array('id'=>0, 'uid'=>0);
			$values[] = "('{$v['price']}', '{$v['min']}', '{$v['buy']['id']}', '{$v['buy']['uid']}', '{$v['sale']['id']}', '{$v['sale']['uid']}', '{$v['opt']}', '$time', '{$this->coin[0]}', '{$this->coin[1]}')";
		}
        $sql .= implode(',', $values);
        if($this->order_coin_mo->exec($sql)){
            if($data_7466["{$this->coin[0]}_over"] > 0 || $data_7466["{$this->coin[1]}_over"] > 0){
                $uid_7466 = array('uid'=>User_AdminModel::COIN_FEE);
                if($this->user_mo->safeUpdate($uid_7466, $data_7466, true, true)){
                    $fees = rtrim($fees, ',');
                    $this->user_mo->exec($fees);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * @desc 更新委单/订单数据
     * todo 以后考虑刷新单开进程
     */
    public function refreshTrust(){
    }
}
