<?php
/**
 * 委托
 */
class User_TrustController extends Ctrl_Base{
	protected $_auth = 3;
	/**
	 * 委托 列表
	 */
	public function listAction($type='all',$current=1){
        $this->layout('seot', '委托管理-币交所-数字货币交易平台');

        # 所有币种
        $pairs = Coin_PairModel::getInstance()->getList();
        $this->assign('pairs', $pairs);

        # 查询币种条件
        $cWhere = '';
        if($type != 'all'){
            $cWhere .= " and coin_from='{$type}'";
        }
        if($current == 2){
            $cWhere .= ' and status in (0,1) ';
        }
        # 查询委托信息
        $mo = new Trust_CoinModel();
        $count = $mo->where("uid=".$this->mCurUser['uid'].$cWhere)->count();

        if(!$tCnt = $count){
            $datas['trustlist'] = array();
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 10);
            $datas['trustlist'] = $mo->where("uid=".$this->mCurUser['uid'].$cWhere)->limit($tPage->limit())->order('id DESC')->fList();
            $datas['pageinfo'] = $tPage->show();
        }

        $cData = array();
        foreach ($datas['trustlist'] as $k => &$v) {
            # 查询币种信息
            $coInfo = Coin_PairModel::getInstance()->field('price_float, number_float, display')->where("coin_from='{$v['coin_from']}' and coin_to='{$v['coin_to']}'")->fRow();
            if( !array_key_exists($v['coin_from'], $cData) ){
                foreach ($coInfo as $k1 => $v1) {
                    $cData[$v['coin_from']][$k1] = $v1;
                }
            }
        }
        $this->assign('status', $type.'_'.$current);
        $this->assign('cData', $cData);
        $this->assign('datas', $datas);
    }
}
