<?php
// Maret
class MarketController extends Ctrl_Base
{
	# kline show
	public function indexAction()
    {
    }

    # kline data
    public function klineAction()
    {
        echo header('Content-Type:application/json;charset=utf-8');
        $type = isset($_POST['type']) ? $_POST['type'] : false;

        if (!$type) {
            exit('type error.');
        }

        $arr = array(
            '1week' => '1w',
            '3day' => '3d',
            '1day' => '1d',
            '12hour' => '12h',
            '6hour' => '6h',
            '4hour' => '4h',
            '2hour' => '2h',
            '1hour' => '1h',
            '30min' => '30m',
            '15min' => '15m',
            '5min' => '5m',
            '3min' => '3m',
            '1min' => '1m'
        );

        $type = $arr[$type];
        $name = $_POST['symbol'].'tradeline';
        $j = $name.'_'.$type;
        $json = Cache_Memcache::get($j);
        exit($json);
    }

	# get depth
	public function depthsAction()
	{
        $coin = $_GET['depth'];
        echo header('Content-Type:application/json;charset=utf-8');
        $json = Cache_Memcache::get($coin.'_depth');
        exit($json);
    }

	# get json
	public function outputAction($name='ybc_hour_price')
	{
        if(!Tool_Validate::az09($name)){
            exit(json_encode(array('code'=>-1,'msg'=>'fail')));
        }
        $json = '';
        if(!$json = Cache_Memcache::get($name)){
            exit(json_encode(array('code'=>0, 'msg'=>'fail')));
        }
        // exit($json);
        echo $json;exit();
    }

    /**
     * 所有币种的当前价格
     */
    public function coinpriceAction(){
        exit(Coin_PairModel::getInstance()->getCoinPrice());
    }

	# dayprice
	public function daypriceAction($name){
        if(!$json = Cache_Redis::instance('common')->hGet('dayprice', $name)){
            exit(json_encode(array('code'=>0, 'msg'=>'fail')));
        }
        exit($json);
    }

}
