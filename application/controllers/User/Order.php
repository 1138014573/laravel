<?php
/**
 * 成交
 */
class User_OrderController extends Ctrl_Base{
	protected $_auth = 3;

	/**
	 * 成交查询
	 */
	public function listAction($type='all'){
        $this->layout('seot', '成交查询-币交所-数字货币交易平台');

        # 所有币种
        $pairs = Coin_PairModel::getInstance()->getList();
        $this->assign('pairs', $pairs);

        # 查询所有成交
        $cWhere = '';
        if($type != 'all'){
            $cWhere .= " and coin_from='{$type}'";
        }

        $mo = new Order_CoinModel();
        $count = $mo->where('(buy_uid='.$this->mCurUser['uid'].' OR sale_uid='.$this->mCurUser['uid'].')'.$cWhere)->count();

        if(!$tCnt = $count){
            $datas['orderlist'] = array();
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 10);
            $datas['orderlist'] = $mo->where('(buy_uid='.$this->mCurUser['uid'].' OR sale_uid='.$this->mCurUser['uid'].')'.$cWhere)->limit($tPage->limit())->order('id DESC')->fList();
            $datas['pageinfo'] = $tPage->show();
        }

        $cData = array();
        foreach ($datas['orderlist'] as $k => &$v) {
            # 查询币种信息
            $coInfo = Coin_PairModel::getInstance()->field('price_float, number_float, display')->where("coin_from='{$v['coin_from']}' and coin_to='{$v['coin_to']}'")->fRow();
            if( !array_key_exists($v['coin_from'], $cData) ){
                foreach ($coInfo as $k1 => $v1) {
                    $cData[$v['coin_from']][$k1] = $v1;
                }
            }
        }

        $this->assign('type', $type);
        $this->assign('cData', $cData);
        $this->assign('datas', $datas);
	}
}
