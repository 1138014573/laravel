<?php
/**
 * 前台首页
 */
class IndexController extends Ctrl_Base{
	protected $_auth = 1;
	/**
	 * 首页
	 */
	public function indexAction(){
		$this->seo('币交所-数字货币交易平台');

         $tMO = new NewsModel;
         // 系统公告
		 $data = $tMO->query("select * from {$tMO->table} where category=1 and receive=2 and sort>0 order by sort desc, id desc limit 5");
		 if(!empty($data)){
			$this->assign('data', $data);
		 }
		 // 行业新闻
		 $newsdata = $tMO->query("select * from {$tMO->table} where category=2 and receive=2 and sort>0 order by sort desc, id desc limit 5");
		 if(!empty($newsdata)){
			$this->assign('newsdata', $newsdata);
		 }

		// 累计交易额
		$arr = $tMO->query("select sum(number*price) total from order_coin where opt=1");

		if( $arr[0]['total'] ){
			$tmpNum = (int)$arr[0]['total'];
		}else{
			$tmpNum = 0;
		}
		$total_24 = number_format($tmpNum, 0, '.', ' ');
		$this->assign('total_24', $total_24);

		$pairs = Coin_PairModel::getInstance()->getList();
		$this->assign('pairs', $pairs);
	}

	public function newsidAction()
	{
		$nid = $this->getRequest()->get("nid", 0);
		$type = $this->getRequest()->get("type", 1);
		if( $type == 1 ){
			$this->seo('公告详情-币交所-数字货币交易平台');
		}else{
			$this->seo('新闻详情-币交所-数字货币交易平台');
		}

		$tMO = new NewsModel;
		$data = $tMO->query("select * from {$tMO->table} where id = {intval($nid)}");
		$this->assign('data', $data);
		$this->assign('type', $type);
	}
	/**
	 * 静态页面
	 */
	public function htmlAction($page){
		switch ($page) {
			case 'guide':
				$title = '新手引导';
				break;
			case 'faq':
				$title = '常见问题';
				break;
			case 'contactus':
				$title = '联系我们';
				break;
			default:
				$title = '政策说明';
				break;
		}
		$title = $title.'-币交所-数字货币交易平台';
		$this->seo($title);

		$this->assign('pages', Yaf_Registry::get("config")->page->toArray());
		isset($this->_view->pages[$page]) || exit;
		$this->assign('page', $page);
		$this->display('html/'.$page);
		exit;
	}

	/**
	 * 验证码
	 */
	public function captchaAction(){
		$captcha = new Tool_Captcha(80, 35, 4);
		$captcha->showImg();
        exit;
	}
    /**
     * 阿里云监控使用
     */
    public function httpAction(){
        echo '222';exit;
    }

   	/**
   	 * 首页币种信息刷新
   	 */
   	public function infofreshAction(){
   		$pairs = json_decode($this->getRequest()->get("pairs"), true);
   		foreach ($pairs as $v) {
   			$info[$v] = Coin_PairModel::getInstance()->getCoInfo($v);
   		}

   		exit(json_encode($info));
   	}
}
