<?php
/**
 * 交易中心
 */
class TradeController extends Ctrl_Base
{

	protected $_auth = 1;
	/**
	 * 首页
	 */
	public function indexAction($name="goc_cny"){
		$this->seo('交易中心-币交所-数字货币交易平台');

		// 判断当前币对是否存在
		if(!Coin_PairModel::getInstance()->isPair($name)){
			exit('Parameter error');
		}

		// 币信息
		$cData = Coin_PairModel::getByName($name);
		if( $cData['status'] == 2 ){
			exit('Parameter error');
		}
		$this->assign('coin', $name);
		$this->assign('cData', $cData);

		@setcookie("COINTYPE", $name, (time() + 3600 * 24), '/', $this->domain);
		$this->assign('trade_cookie', $name);

		if ($this->domain == 'qqibtc.com') {
			$isonline = 1;
		} else if ($this->domain == 'bijiaosuo.com') {
			$isonline = 2;
		} else {
			$isonline = 0;
		}
		$this->assign('isonline', $isonline);

		if( $this->mCurUser ){
			if(Tool_Md5::pwdTradeCheck($this->mCurUser['uid'])){
				$this->assign('pwdFlag', 1);
	        }else{
	        	$this->assign('pwdFlag', 2);
	        }
		}else{
			$this->assign('pwdFlag', 2);
		}
	}

}
