<?php
/**
 * 数字货币相关管理
 * @role admin
 */
class Manage_YbcController extends Ctrl_Admin {
   	//protected $disableAction = array('usergaclose', 'usertpchange', 'rmbout', 'rmbpay',
	//				 'btcout', 'trustcancel', 'betcancel');
	//protected $disableMethodPost = array('rmbcsv', 'rmbin');

	# 数字货币
	public function ybcAction($coin='goc', $type='out', $status='等待'){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc') || !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('ybc_ybc_out');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		$status = urldecode($status);

		$where = " opt_type='{$type}' and status='{$status}'";

		$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'uid';
		$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : '';
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$order = " order by id desc";
		// 时间标识，判断是否选择了时间
		$hasTime = isset($_REQUEST['hasTime']) ? $_REQUEST['hasTime'] : '';

		if(isset($_REQUEST['stime'])){
			$stime = trim($_REQUEST['stime']);
		}
		if(isset($_REQUEST['etime'])){
			$etime = trim($_REQUEST['etime']);
		}

		if(empty($stime) && empty($etime)){
			$stime = strtotime(date('Y-m-d', time()).'00:00:00');
			$etime = strtotime(date('Y-m-d', time()).'23:59:00');
		}else if(!empty($stime) && empty($etime)){
			$stime = strtotime($stime.':00');
			if( $hasTime ){
				if( $status=='成功' ){
					$where .= " and updated>={$stime} ";
				}else{
					$where .= " and created>={$stime} ";
				}
			}
		}else if(empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			if( $hasTime ){
				if( $status=='成功' ){
					$where .= " and updated<={$etime} ";
				}else{
					$where .= " and created<={$etime} ";
				}
			}
		}else if(!empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			$stime = strtotime($stime.':00');
			if($etime < $stime){
				$this->showMsg('开始时间不能大于结束时间');
			}else{
				if( $hasTime ){
					if( $status =='成功' ){
						$where .= " and updated between {$stime} and {$etime} ";
					}else{
						$where .= " and created between {$stime} and {$etime} ";
					}
				}
			}
		}
		$sql = "select * from exchange_{$coin} where {$where} {$order}";
		$countSql = "select count(id) as total from exchange_{$coin} where {$where} {$order}";
		$sqltotal = "select count(*) num, sum(number) number from exchange_{$coin} where {$where} {$order}";
		$mo = new Exchange_CnyModel();
		$arr = $mo -> query($countSql);

		if(!$tCnt = $arr[0]['total']){
			$datas['list'] = array();
			$datas['total'] = array(array('num'=>0, 'total'=>0));
			$datas['pageinfo'] = '';
		}else{
			$tPage = new Tool_Page($tCnt, 15);
			$datas['list'] = $mo->query($sql.' limit '.$tPage->limit());
			$datas['total'] = $mo->query($sqltotal);
			$datas['pageinfo'] = $tPage->show();
		}

		$this->assign('coin', $coin);
		$this->assign('type', $type);
		$this->assign('status', $status);
		$this->assign('stime', $stime);
		$this->assign('etime', $etime);
		$this->assign('field', $field);
		$this->assign('kw', $kw);
		$this->assign('hasTime', $hasTime);
		$this->assign('datas', $datas);
	}

    # 导出数字货币记录
	public function ybccsvAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc_out') ){
			exit('权限不足');
		}


		$coin = $_POST['coin'] ? $_POST['coin'] : 'goc';
		$type = $_POST['type'] ? $_POST['type'] : 'out';
		$status = $_POST['status'] ? $_POST['status'] : '等待';
		$kw = $_POST['kw'] ? $_POST['kw'] : '';
		$field = $_POST['field'] ? $_POST['field'] : 'uid';
		$hasTime = $_POST['hasTime'] ? $_POST['hasTime'] : 0;
		$stime = $_POST['stime'] ? $_POST['stime'] : '';
		$etime = $_POST['etime'] ? $_POST['etime'] : '';

		$where = " opt_type='{$type}' and status='{$status}'";
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$order = " order by id desc";

		if( $hasTime ){
			if(empty($stime) && empty($etime)){

			}else if(!empty($stime) && empty($etime)){
				$where .= " and created>={$stime} ";
			}else if(empty($stime) && !empty($etime)){
				$where .= " and created<={$etime} ";
			}else if(!empty($stime) && !empty($etime)){
				if($etime < $stime){
					$this->showMsg('开始时间不能大于结束时间');
				}else{
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}

		$sql = "select * from exchange_{$coin} where {$where} {$order}";
		$mo = new Exchange_CnyModel();
		$arr = $mo -> query($sql);

		if(!$tCnt = count($arr)){
			$this->showMsg('暂无数据');
		}else{
			$data = $arr;
		}

		$this->csvout($coin, $status, $type, $data);
	}

    /**
     * 数字货币导出
     */
    private function csvout($coin, $status, $type, $data){
    	header("Content-type:application/csv");
    	header("Content-Disposition:filename=".date('YmdHi').'_'.$coin.'_'.$type.$status.".csv");
    	$str = "ID,用户id,邮箱,钱包地址,数量,确认数,创建时间,创建ip,备注";
    	$str .= "\n";

    	foreach($data as &$v){
    		$created = date('Y-m-d H:i:s', $v['created']);

    		$str .= $v['id'].",".$v['uid'].",".$v['email'].",".$v['wallet'].",".$v['number'].",".$v['confirm'].",".$created.",".$v['createip'].",".$v['bak']."\n";
    	}
    	exit($str);
    }

    // 是否允许转出
    public function coinoutAction($id, $coin='goc', $cancel = 0){
    	# 查询记录
    	$mo = 'Exchange_'.ucfirst($coin).'Model';
    	$ExchangeMO = new $mo;
		# 验证
    	if(!$tExCoin = $ExchangeMO->lock()->fRow($id)){
    		$this->showMsg('没有找到记录');
    	}
    	if($tExCoin['opt_type'] != 'out' || $tExCoin['status'] != '等待'){
    		$this->showMsg('您已经操作过了');
    	}
    	$tUserMO = new UserModel();
    	if($cancel){
			# 事务开始
    		$ExchangeMO->begin();
    		$tUData = array($coin.'_over'=>$tExCoin['number'], $coin.'_lock'=>-$tExCoin['number']);
    		$tOutData = array('id' => $id, 'status' => '已取消');
    		$tUser = array('uid' => $tExCoin['uid']);
    		$tUserMO = new UserModel();
    		if(TRUE !== $tUserMO->safeUpdate($tUser, $tUData)){
    			$ExchangeMO->back();
    			return $this->showMsg('用户信息更新失败');
    		}
    		if(!$ExchangeMO->save($tOutData)){
    			$ExchangeMO->back();
    			return $this->showMsg('转出信息更新失败');
    		}
    		$ExchangeMO->commit();
    	} else {
    		$exData = array('id'=>$tExCoin['id'], 'confirm'=>1);
    		if(!$ExchangeMO->save($exData)){
    			return $this->showMsg('转出信息更新失败，请联系客服');
    		}
    	}
    	$this->showMsg('操作成功');
    }


	# BTC：状态改为成功
    public function ybcoutAction($id, $cancel = 0, $autosend = 0){
    	if ( $autosend ) {
			# 验证双重验证
    		if( !isset($_POST['pass']) || !Api_Google_Authenticator::verify_key("P2HUDRQV3NU2LV4I", $_POST['pass']) ){
    			$this->showMsg('code error');
    		}
    	}
		# 查询记录
    	$ExchangeMO = new Exchange_GocModel();
		# 事务开始
    	$ExchangeMO->begin();
		# 验证
    	if(!$tExCoin = $ExchangeMO->lock()->fRow($id)){
    		$this->showMsg('没有找到记录');
    	}
    	if($tExCoin['opt_type'] != 'out' || $tExCoin['status'] != '等待'){
    		$this->showMsg('您已经操作过了');
    	}
    	$tUserMO = new UserModel();
    	$level = new UserLevelModel();
    	$leveldata = $level->fList();
    	$leveluser = level($tUserMO->getById($tExCoin['uid']),$leveldata);
		# 数据
    	$tUData = array('ybc_lock'=>-$tExCoin['number']);
    	$tOutData = array('id' => $id, 'status' => '成功');
    	if($cancel){
    		$tUData['ybc_over'] = $tExCoin['number'];
    		$tOutData['status'] = '已取消';
    	} else {
			//手续费转入uid 1
    		if($leveluser[0]['level']<10){
    			$tDatas = array('number'=>Tool_Str::format($tExCoin['number']*$leveluser[0]['fee_ybcout'], 5),'price'=>1,'buy_uid'=>$tExCoin['uid'],'sale_uid'=>1,'opt'=>2,'created'=>time());
    		}else{
    			$tDatas = array('number'=>Tool_Str::format($leveluser[0]['fee_ybcout'], 5),'price'=>1,'buy_uid'=>$tExCoin['uid'],'sale_uid'=>1,'opt'=>2,'created'=>time());
    		}

    		$oMO    = new Order_coinModel();
    		if(!$oMO->insert($tDatas)){
    			return $this->showMsg('手续费扣取失败[1]，请联系技术');
    		}
    		$feeData    = array('ybc_over'=>$tDatas['number']);
    		$feeUser    = array('uid'=>1);
    		if(!$tUserMO->safeUpdate($feeUser, $feeData)){
    			return $this->showMsg('手续费扣取失败[2]，请联系技术');
    		}
    	}
		# 操作用户
    	$tUser = array('uid' => $tExCoin['uid']);
		# 更新用户
    	if(!$tUserMO->safeUpdate($tUser, $tUData)){
    		$ExchangeMO->back();
    		$this->showMsg($tUserMO->error[2]);
    	}
		# 进行自动转出
    	if(!$cancel && $autosend){
    		if($leveluser[0]['level']<10){
    			$newtDatas =  round($tExCoin['number']*(1-$leveluser[0]['fee_ybcout']), 5);
    		}else{
    			$newtDatas =  round($tExCoin['number']-$leveluser[0]['fee_ybcout'], 5);
    		}
    		try{
    			if(!$tOutData['txid'] = Api_Rpc_Client::sendToUserAddress($tExCoin['ybckey'], $newtDatas, 'ybcout')){
    				$ExchangeMO->back();
    				$this->showMsg($tOutData['txid']);
    			}
    		}catch (Exception $e) {
    			$ExchangeMO->back();
    			$this->showMsg($e->getMessage());
    		}
    	}
		# 更新转出请求
    	if(!$ExchangeMO->save($tOutData)){
    		$ExchangeMO->back();
    		$this->showMsg($ExchangeMO->error[2]);
    	}
    	$ExchangeMO->commit();
    	Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
    	$this->showMsg(($cancel? '撤消': '转出') . ' 操作已成功');
    }

	# 委托
    public function trustAction(){
    	$coin = isset($_GET['coin']) ? $_GET['coin'] : 'all';
    	$status = isset($_GET['status']) ? $_GET['status'] : 'all';
		# 查询栏目权限
    	if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_trust') || !$this->getAuth($_SESSION['user']['uid'], 'ybc_trust_look')){
    		exit('权限不足');
    	}
		# 按钮权限
    	$btnArr = array('ybc_trust_out', 'ybc_trust_cancel');
    	foreach ($btnArr as $vb) {
    		$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
    	}
    	$this->assign('btnAuth', $btnAuth);


		#单一币种
    	$where = " 1=1 and coin_from = '{$coin}'";
		#全部币种
    	if($coin == 'all'){$where = " 1=1 ";}

    	if(isset($_GET['isnew'])) {
    		$where .= " and isnew = 'Y' ";
    		$cur = -1;
    	}elseif('all' != $status) {
    		$where .= " and isnew = 'N' and status = {$status} ";
    		$cur = $status;
    	} else {
    		$cur = -2;
    	}

    	$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'uid';
    	$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : '';
    	if( $kw ){
    		$where .= " and {$field} like '%{$kw}%' ";
    	}
    	$order = " order by id desc";
		// 时间标识，判断是否选择了时间
    	$hasTime = isset($_REQUEST['hasTime']) ? $_REQUEST['hasTime'] : '';

    	if(isset($_REQUEST['stime'])){
    		$stime = trim($_REQUEST['stime']);
    	}
    	if(isset($_REQUEST['etime'])){
    		$etime = trim($_REQUEST['etime']);
    	}

    	if( $hasTime ){
    		if(empty($stime) && empty($etime)){
    			$stime = strtotime(date('Y-m-d', time()).'00:00:00');
    			$etime = strtotime(date('Y-m-d', time()).'23:59:00');
    		}else if(!empty($stime) && empty($etime)){
    			$stime = strtotime($stime.':00');
    			$where .= " and created>={$stime} ";
    		}else if(empty($stime) && !empty($etime)){
    			$etime = strtotime($etime.':59');
    			$where .= " and created<={$etime} ";
    		}else if(!empty($stime) && !empty($etime)){
    			$etime = strtotime($etime.':59');
    			$stime = strtotime($stime.':00');
    			if($etime < $stime){
    				$this->showMsg('开始时间不能大于结束时间');
    			}else{
    				$where .= " and created between {$stime} and {$etime} ";
    			}
    		}
    	}else{
    		$stime = strtotime(date('Y-m-d', time()).'00:00:00');
    		$etime = strtotime(date('Y-m-d', time()).'23:59:00');
    	}
    	$sql = "select * from trust_coin where {$where} {$order}";
    	$countSql = "select count(id) as total from trust_coin where {$where} {$order}";
    	$sqltotal = "select count(*) num, sum(number) number from trust_coin where {$where} {$order}";

    	$mo = new Trust_CoinModel();
    	$arr = $mo->query($countSql);

    	if(!$tCnt = $arr[0]['total']){
    		$datas['list'] = array();
    		$datas['total'] = array(array('num'=>0, 'total'=>0));
    		$datas['pageinfo'] = '';
    	}else{
    		$tPage = new Tool_Page($tCnt, 15);
    		$datas['list'] = $mo->query($sql.' limit '.$tPage->limit());
    		$datas['total'] = $mo->query($sqltotal);
    		$datas['pageinfo'] = $tPage->show();
    	}
    	$this->assign('coin', $coin);
    	$this->assign('cur', $cur);
    	$this->assign('kw', $kw);
    	$this->assign('field', $field);
    	$this->assign('stime', $stime);
    	$this->assign('etime', $etime);
    	$this->assign('hasTime', $hasTime);
    	$this->assign('datas', $datas);
    }

	/**
	 * 要导出的委托数据
	 */
	public function trustcsvAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_trust_out') ){
			exit('权限不足');
		}

		$status = $_POST['status'];
		$coin = $_POST['coin'] ? $_POST['coin'] : 'all';
		$kw = $_POST['kw'] ? $_POST['kw'] : '';
		$field = $_POST['field'] ? $_POST['field'] : 'uid';
		$hasTime = $_POST['hasTime'] ? $_POST['hasTime'] : 0;
		$stime = $_POST['stime'] ? $_POST['stime'] : '';
		$etime = $_POST['etime'] ? $_POST['etime'] : '';

		$where = " 1=1 ";

		if($status>=0) {
			$where .= " and isnew = 'N' and status = {$status} ";
		}else if($status == -1) {
			$where .= " and isnew = 'Y' ";
		}
		if( $coin != 'all' ){
			$where .= " and coin_from = '{$coin}' ";
		}
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$order = " order by id desc";

		if( $hasTime ){
			if(empty($stime) && empty($etime)){

			}else if(!empty($stime) && empty($etime)){
				$where .= " and created>={$stime} ";
			}else if(empty($stime) && !empty($etime)){
				$where .= " and created<={$etime} ";
			}else if(!empty($stime) && !empty($etime)){
				if($etime < $stime){
					$this->showMsg('开始时间不能大于结束时间');
				}else{
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}

		$sql = "select * from trust_coin where {$where} {$order}";
		$sqltotal = "select count(*) num from trust_coin where {$where} {$order}";
		$mo = new Trust_CoinModel();
		$arr = $mo->query($sqltotal);

		if(!$tCnt = $arr[0]['num']){
			$this->showMsg('暂无数据');
		}else{
			$data = $mo->query($sql);
		}

		$this->trustcsvout($status, $data);
	}

	/**
	 * 导出委托数据
	 */
	private function trustcsvout($type, $data){
		$status = Trust_CoinModel::$status;
		if($type == -2){
			$name = '全部';
		}else if($type == -1){
			$name = '未处理';
		}else{
			$name = $status[$type];
		}

		header("Content-type:application/csv");
		header("Content-Disposition:filename=trust_".date('YmdHi').'_'.$name.".csv");
		$str = "ID,用户id,币种,单价,数量,剩余数量,买卖标志,新委托,状态,创建时间,更新时间";
		$str .= "\n";

		foreach($data as &$v){
			$created = date('Y-m-d H:i:s', $v['created']);
			$updated = date('Y-m-d H:i:s', $v['updated']);

			$str .= $v['id'].",".$v['uid'].",".$v['coin_from'].",".$v['price'].",".$v['number'].",".$v['numberover'].",".$v['flag'].",".$v['isnew'].",".$status[$v['status']].",".$created.','.$updated."\n";
		}
		exit($str);
	}



	# 委托
	public function oldtrustAction($status=false){
		$tConn = 'OB=id DESC';
		# 新
		if(isset($_GET['isnew'])) {
			$tConn .= "&isnew=Y";
			$this->assign('cur', -1);
		}
		# 按状态查询
		else if(false !== $status) {
			$tConn .= "&isnew=N&status=".$status;
			$this->assign('cur', $status);
		} else {
			$this->assign('cur', -2);
		}

		$this->_list('trust_Ybc2014', $tConn);
	}

	# 委托撤销
	public function trustcancelAction($id){
		if(!$id = abs($id)) $this->ajax('参数错误');
		$tMO = new Trust_CoinModel();
		if(!empty($id)){
			$tMO->adminCancel($id);
			$this->showMsg('操作成功');
		}
	}
	# 成交
	public function orderAction( $coin ='all' ){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_order') || !$this->getAuth($_SESSION['user']['uid'], 'ybc_order_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('ybc_order_out');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		#单一币种
		$where = " 1=1 and coin_from = '{$coin}'";
		#全部币种
		if($coin =='all'){$where = " 1=1 ";}

		$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'buy_tid';
		$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : '';
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$order = " order by created desc";
		// 时间标识，判断是否选择了时间
		$hasTime = isset($_REQUEST['hasTime']) ? $_REQUEST['hasTime'] : '';

		if(isset($_REQUEST['stime'])){
			$stime = trim($_REQUEST['stime']);
		}
		if(isset($_REQUEST['etime'])){
			$etime = trim($_REQUEST['etime']);
		}

		if( $hasTime ){
			if(empty($stime) && empty($etime)){
				$stime = strtotime(date('Y-m-d', time()).'00:00:00');
				$etime = strtotime(date('Y-m-d', time()).'23:59:00');
			}else if(!empty($stime) && empty($etime)){
				$stime = strtotime($stime.':00');
				$where .= " and created>={$stime} ";
			}else if(empty($stime) && !empty($etime)){
				$etime = strtotime($etime.':59');
				$where .= " and created<={$etime} ";
			}else if(!empty($stime) && !empty($etime)){
				$etime = strtotime($etime.':59');
				$stime = strtotime($stime.':00');
				if($etime < $stime){
					$this->showMsg('开始时间不能大于结束时间');
				}else{
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}else{
			$stime = strtotime(date('Y-m-d', time()).'00:00:00');
			$etime = strtotime(date('Y-m-d', time()).'23:59:00');
		}
		$sql = "select * from order_coin where {$where} {$order}";
		$countSql = "select count(id) as total from order_coin where {$where} {$order}";
		$sqltotal = "select count(*) num, sum(number) number from order_coin where {$where} {$order}";
		$usertotal = "select count(*) total from (select buy_uid from order_coin where {$where} group by buy_uid union select sale_uid from order_coin where {$where} group by sale_uid) as total";
		$mo = new Order_CoinModel();
		$arr = $mo->query($countSql);
		if(!$tCnt = $arr[0]['total']){
			$datas['list'] = array();
			$datas['total'] = array(array('num'=>0, 'total'=>0));
			$datas['usertotal'] = array(array('total'=>0));
			$datas['pageinfo'] = '';
		}else{
			$tPage = new Tool_Page($tCnt, 15);
			$datas['list'] = $mo->query($sql.' limit '.$tPage->limit());
			$datas['total'] = $mo->query($sqltotal);
			$datas['usertotal'] = $mo->query($usertotal);
			$datas['pageinfo'] = $tPage->show();
		}
        // Tool_Fnc::dump($datas);exit();
		$this->assign('coin', $coin);
		$this->assign('kw', $kw);
		$this->assign('field', $field);
		$this->assign('stime', $stime);
		$this->assign('etime', $etime);
		$this->assign('hasTime', $hasTime);
		$this->assign('datas', $datas);
	}

	/**
	 * 要导出的成交数据
	 */
	public function ordercsvAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_order_out') ){
			exit('权限不足');
		}

		$coin = $_POST['coin'] ? $_POST['coin'] : 'all';
		$kw = $_POST['kw'] ? $_POST['kw'] : '';
		$field = $_POST['field'] ? $_POST['field'] : 'buy_tid';
		$hasTime = $_POST['hasTime'] ? $_POST['hasTime'] : 0;
		$stime = $_POST['stime'] ? $_POST['stime'] : '';
		$etime = $_POST['etime'] ? $_POST['etime'] : '';

		$where = " 1=1 ";

		if( $coin != 'all' ){
			$where .= " and coin_from = '{$coin}' ";
		}
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$order = " order by created desc";

		if( $hasTime ){
			if(empty($stime) && empty($etime)){

			}else if(!empty($stime) && empty($etime)){
				$where .= " and created>={$stime} ";
			}else if(empty($stime) && !empty($etime)){
				$where .= " and created<={$etime} ";
			}else if(!empty($stime) && !empty($etime)){
				if($etime < $stime){
					$this->showMsg('开始时间不能大于结束时间');
				}else{
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}
		$sql = "select * from order_coin where {$where} {$order}";
		$sqltotal = "select count(*) num from order_coin where {$where} {$order}";

		$mo = new Order_CoinModel();
		$arr = $mo->query($sqltotal);

		if(!$tCnt = $arr[0]['num']){
			$this->showMsg('暂无数据');
		}else{
			$data = $mo->query($sql);
		}
        // Tool_Fnc::dump($data);exit();
		$this->ordercsvout($data);
	}

	/**
	 * 导出成交数据
	 */
	private function ordercsvout($data){
		header("Content-type:application/csv");
		header("Content-Disposition:filename=order_".date('YmdHi').".csv");
		$str = "ID,币种,单价,数量,购买委托id,购买用户id,出售委托id,出售用户id,创建时间";
		$str .= "\n";

		foreach($data as &$v){
			$created = date('Y-m-d H:i:s', $v['created']);

			$str .= $v['id'].",".$v['coin_from'].",".$v['price'].",".$v['number'].",".$v['buy_tid'].",".$v['buy_uid'].",".$v['sale_tid'].",".$v['sale_uid'].",".$created."\n";
		}
		exit($str);
	}


	/**
	 * 一键撤单
	 */
	public function cancelAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_cancel') ){
			exit('权限不足');
		}


		$pairs = Coin_PairModel::getInstance()->getList();
		$this->assign('pairs', $pairs);

		if('POST' == $_SERVER['REQUEST_METHOD']){
			$tMo = new Trust_CoinModel;

			// 判断uid是否存在
			if( $_POST['uid'] ){
				$user = new UserModel;
				$data = $user->fRow("select * from user where uid={$_POST['uid']}");
				if( !$data ){
					$this->showMsg('此用户id不存在');
				}

				// 撤单
				if( $tMo->cancleAllByCoin($_POST['coin'], 'cny', $_POST['uid']) ){
					$this->showMsg('操作成功');
				}else{
					$this->showMsg('操作失败');
				}
			}

			// 撤单
			if( $tMo->cancleAllByCoin($_POST['coin'], 'cny') ){
				$this->showMsg('操作成功');
			}else{
				$this->showMsg('操作失败');
			}

		}
	}
	/**
	 *交易日
	**/
	public function tradingdayAction() {
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc') || !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_user_modify');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		$field = 'id,coin_from,display,rule_open,open_start,open_end,open_week,open_date';

		$datas = Coin_PairModel::getInstance()->field($field)->fList();

		$this->assign('datas', $datas);

	}
	/**
	 *交易日修改
	**/
	public function tradingdayruleAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc') || !$this->getAuth($_SESSION['user']['uid'], 'ybc_ybc_look')){
			exit('权限不足');
		}
		# POST 数据处理
		$coin_pair = new Coin_PairModel();
		$data = $coin_pair->fRow("select * from coin_pair where id={$_GET['id']}");
		$this->assign('data',$data);
		$time = time();$set = "";$val1 = "";$val2="";

		if ('POST' == $_SERVER['REQUEST_METHOD'] ) {
			if(!($_POST['rule_open'] ==0 || $_POST['rule_open'] ==1)){
				$this->showMsg('休市规则总开关只能为0关闭或者1开启');
			}else if(strlen($_POST['open_start'])!=4||strlen($_POST['open_end'])!=4){
				$this->showMsg('时间设置不符合规则，请设置4位数的时间');
			}else{
				foreach($_POST as $k=>$val){
					$set.="{$k}='{$val}',";
				}
				$coin_pair->exec("update coin_pair set ".rtrim($set,',')."where id={$_POST['id']}");
				$this->showMsg('修改成功','/manage_ybc/tradingday');
			}
		}
	}

}
