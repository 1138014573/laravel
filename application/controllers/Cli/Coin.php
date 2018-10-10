<?php
class Cli_CoinController extends Ctrl_Cli
{
    /**
     * @desc 涨跌幅数据更新
     */
    public function indexAction()
    {
        //上币初始值
        $default_price = 6.6;
        $floatMo = new Coin_FloatModel();
        if(!$pairs = Coin_PairModel::getInstance()->where("status = 1")->fList()){
            exit('no limit coin');
        }

        foreach ($pairs as $v1) {
			$division = $v1['open_end'];
			if (intval(date('Hi')) >= $division) {
				$period = date('Ymd');
				$period_1 = date('Ymd', strtotime('-1 day'));
			} else {
				$period = date('Ymd', strtotime('-1 day'));
				$period_1 = date('Ymd', strtotime('-2 day'));
			}

			$up_percent = $v1['up_percent'];
			$down_percent = $v1['down_percent'];
			$round = $v1['price_float'];

            $float = $floatMo->getFloat($period, $v1['coin_from'], $v1['coin_to']);
            $float_1 = $floatMo->getFloat($period_1, $v1['coin_from'], $v1['coin_to']);

            //最后一条成交数据
            $order_last = Order_CoinModel::getInstance()->where("coin_from='{$v1['coin_from']}' and coin_to='{$v1['coin_to']}'")->order('id desc')->limit(1)->fRow();
            //初始价格
            $last_price = isset($order_last['price']) ? $order_last['price'] : $default_price;

            if (empty($float)) {

                // 09:30交割数据
                $data = array(
                    'coin_from' => $v1['coin_from'],
                    'coin_to' => $v1['coin_to'],
                    'day' => $period,
                    'price_close' => $last_price,
                    'price_up' => Tool_Str::format($last_price * (1+$up_percent), $round, 2),
                    'price_down' => Tool_Str::format($last_price * (1-$down_percent), $round, 1),
                    'updated' => time()
                );
                $floatMo->insert($data);
                //前一周期
                if (!empty($float_1)) {
                    $floatMo->update(array('id' => $float_1['id'], 'price_close' => $last_price, 'updated' => time()));
                }

            } else {

                //更新当期数据
                if (empty($float_1)) {
                    $first_price = $default_price;
                } else {
                    $first_price = $float_1['price_close'];
                }

                $data = array(
                    'id' => $float['id'],
                    'price_float' => $last_price - $first_price,
                    'price_close' => $last_price,
                    'percent' => Tool_Str::format(($last_price - $first_price)/$first_price, 4, 2),
                    'updated' => time()
                );
                $floatMo->update($data);

            }
            $floatMo->ajaxcoinOrder($v1['name']);
        }

        exit('finish');
    }
}
