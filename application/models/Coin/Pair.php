<?php
class Coin_PairModel extends Orm_Base{
	public $table = 'coin_pair';
	public $field = array(
		'id' => array('type' => "int", 'comment' => 'id'),
		'name' => array('type' => "char", 'comment' => 'B名称'),
		'coin_from' => array('type' => "char", 'comment' => ''),
		'coin_to' => array('type' => "char", 'comment' => ''),
		'describe' => array('type' => "char", 'comment' => '描述'),
		'display' => array('type' => "char", 'comment' => '显示名字'),
		'display_to' => array('type' => "char", 'comment' => '显示名字'),
        'status' => array('type'=>"int", 'comment'=>''),
        'rate' => array('type'=>"float", 'comment'=>''),
        'rate_buy' => array('type'=>"float", 'comment'=>''),
        'url' => array('type'=>"char", 'comment'=>''),
        'price_float' => array('type'=>"int", 'comment'=>''),
        'number_float' => array('type'=>"int", 'comment'=>''),
        'order_by' => array('type'=>"int", 'comment'=>''),
        'price_limit' => array('type'=>"int", 'comment'=>''),
		'up_percent' => array('type'=>"char", 'comment'=>''),
		'down_percent' => array('type'=>"char", 'comment'=>''),
        'rule_open' => array('type'=>"int", 'comment'=>''),
        'open_start' => array('type'=>"int", 'comment'=>''),
        'open_end' => array('type'=>"int", 'comment'=>''),
		'open_date'=> array('type'=>'char','comment'=>'闭市日期'),
        'min_trade' => array('type'=>"int", 'comment'=>'每笔最小交易数量'),
        'max_trade' => array('type'=>"int", 'comment'=>'每笔最大交易数量')
	);

	public $pk = 'id';// 主键

    const STATUS_ON = 1;
    const STATUS_OFF = 2;
    /**
     * 当前已上线币列表
     */
    public function getList(){
        return $this->where("status=".self::STATUS_ON)->order("order_by asc,id asc")->fList();
    }
    /**
     * 币信息
     * @param $pName
     * @return array
     */
	public static function getByName($name){
		if(!$coin = Coin_PairModel::getInstance()->ffName($name)){
			return array();
		}
		return $coin;
	}
	public static function getAllCoinName() {
		if(!$list = Coin_PairModel::getInstance()->field('id,name,display')->fList()) {
			return array();
		}
		$res = array();
		foreach( $list as $k1 => $v1){
			$res[$v1['id']] = $v1['display'];
		}
		return $res;
	}
    /**
     * 判断当前币对是否存在
     */
    public function isPair($name){
        return $this->where("name='{$name}' and status = 1")->count();
    }
    /**
     * 币对信息
     */
    public function getPair($name){
        return $this->where("name='{$name}'")->fRow();
    }

    /**
     * 获取币种最新成交价、24小时成交量/价、总市值等信息
     */
    public function getCoInfo($coin){
        $caArr = json_decode(Cache_Memcache::get($coin.'_sum'), true);
        if($caArr){
            foreach ($caArr as $k => $v) {
                if($v){
                    if( $v[0]['p'] ){
                        $arr[$k.'_one'] = $v[0]['p'];
                    }else{
                        $arr[$k.'_one'] = 0;
                    }
                }else{
                    $arr[$k.'_one'] = 0;
                }
            }

            $coinArr = explode('_', $coin);
            $pair = Coin_PairModel::getByName($coin);
            $arr['price_float'] = $pair['price_float'];
            $arr['number_float'] = $pair['number_float'];

            // 最新成交价
            $orderArr = json_decode(Cache_Memcache::get($coin."_order"), true);
            $arr['now_price'] = $orderArr['d'][0]['p'] ? $orderArr['d'][0]['p'] : 0;

            // 24H成交量和成交额
            $nMO = new Order_CoinModel();
            $time = time() - 86400;
            $numberArr = $nMO->query("select sum(number) number, sum(number*price) money from {$nMO->table} where coin_from='{$pair['coin_from']}' and opt=1 and created >= {$time}");
            $arr['day_total'] = round($numberArr[0]['number'], $pair['number_float']);
            $arr['day_money'] = round($numberArr[0]['money'], $pair['price_float']);

            // 总市值
            $total = User_CoinModel::getByName($coinArr[0]);
            // $arr['total_money'] = round( ($arr['now_price'] * $total['total']) / 1E+8 , $pair['price_float']);
            $arr['total_money'] = $arr['now_price'] * $total['total'] ;

            // 日涨跌，周涨跌
            $open_end = Coin_PairModel::getInstance()->where("status = 1 and name = '{$coin}'")->fOne('open_end');
            if (intval(date('Hi')) >= $open_end) {
                $period = date('Ymd', strtotime('-1 day'));
                $period_7 = date('Ymd', strtotime('-7 day'));
            } else {
                $period = date('Ymd', strtotime('-2 day'));
                $period_7 = date('Ymd', strtotime('-8 day'));
            }
            // 昨日收盘价
            $close = Coin_FloatModel::getInstance()->where("coin_from='{$pair['coin_from']}' and day = {$period}")->fOne('price_close');
            // 一周前收盘价
            $close_7 = Coin_FloatModel::getInstance()->where("coin_from='{$pair['coin_from']}' and day = {$period_7}")->fOne('price_close');
            if( !$close_7 ){
                $close_7 = Coin_FloatModel::getInstance()->where("coin_from='{$pair['coin_from']}'")->order('id asc')->limit(1)->fOne('price_close');
            }

            if( $arr['now_price'] && $close ){
                $arr['day_float'] = round(($arr['now_price'] - $close) / $close, $pair['price_float']);
            }else{
                $arr['day_float'] = 0;
            }

            if( $arr['now_price'] && $close_7 ){
                $arr['week_float'] = round(($arr['now_price'] - $close_7) / $close_7, $pair['price_float']);
            }else{
                $arr['week_float'] = 0;
            }

            return json_encode($arr);
        }
    }

    /**
     * 所有币种的当前价格
     */
    public function getCoinPrice(){
        $pairs = User_CoinModel::getInstance()->field('name')->getList();

        foreach ($pairs as $k => $v) {
            $priceArr[$v['name'].'_cny_rmb'] = Cache_Memcache::get($v['name'].'_cny_rmb');
        }
        return json_encode($priceArr);
    }
}
