<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_UserController extends Ctrl_Admin {

   	protected $disableAction = array('usergaclose', 'usertpchange', 'rmbout', 'rmbpay',
					 'btcout', 'trustcancel', 'betcancel');
	protected $disableMethodPost = array('rmbcsv', 'rmbin');

	# 用户列表
	public function userAction() {
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_user') || !$this->getAuth($_SESSION['user']['uid'], 'user_user_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_user_2fa', 'user_user_pwd', 'user_user_modify', 'user_user_rmbin');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		# 查询条件
		$where = ' 1=1 ';
		$field = isset($_POST['field']) ? $_POST['field'] : 'uid';
		$hasTime = isset($_REQUEST['hasTime']) ? $_REQUEST['hasTime'] : '';
		$kw = isset($_POST['kw']) ? $_POST['kw'] : '';
		$kw = trim($kw);
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}

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
				$where .= " and created>={$stime} ";
			}
		}else if(empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			if( $hasTime ){
				$where .= " and created<={$etime} ";
			}
		}else if(!empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			$stime = strtotime($stime.':00');
			if($etime < $stime){
				$this->showMsg('开始时间不能大于结束时间');
			}else{
				if( $hasTime ){
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}

		$this->assign('stime', $stime);
		$this->assign('etime', $etime);
		$this->assign('field', $field);
		$this->assign('kw', $kw);
		$this->assign('hasTime', $hasTime);

        $mo = new UserModel();
        $count = $mo->where($where)->count();

        if(!$tCnt = $count){
            $datas['list'] = array();
            $datas['total'] = 0;
            $datas['auth'] = 0;
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 15);
            $datas['list'] = $mo->where($where)->limit($tPage->limit())->order('uid DESC')->fList();
            $datas['total'] = $mo->where($where)->count();
            $datas['pageinfo'] = $tPage->show();
            $datas['auth'] = $mo->where($where." and idcard != '' and mo != '' and name != '' ")->count();

            foreach ($datas['list'] as &$v) {
            	$v['isMod'] = UserModel::isModify($v['uid']) ? true : false;
            }
        }
        $this->assign('datas', $datas);
	}

	// 用户资产
	public function userassetAction($uid){
		$mo = new UserModel();
        $data = $mo->where("uid = {$uid}")->fRow();

        $this->assign('data', $data);
	}

	# 用户 增、改、查
	public function usersaveAction() {
		# POST 数据处理
		$user = new UserModel();
		$data = $user->fRow("select * from user where uid={$_GET['uid']}");
		$this->assign('data',$data);
		$time = time();$set = "";$val1 = "";$val2="";

		if ('POST' == $_SERVER['REQUEST_METHOD'] ) {
			if( !isset($_POST['email']) || empty($_POST['email']) ){
				$this->showMsg('邮箱不能为空');
			}
			if( $_POST['email'] != $data['email'] ){
				if( $user->fRow("select * from user where email='{$_POST['email']}'") ){
					$this->showMsg('邮箱已存在');
				}
			}
			if(isset($_POST['message']) && empty($_POST['message'])){
				$this->showMsg('原因不能为空');
			}
			foreach($_POST as $k=>$val){
				if(!in_array(trim($val), $data)){
					if($k!='message'){
						$set.="{$k}='{$val}',";
						$val1 .= $data[$k].'|';
						$val2 .= $_POST[$k].'|';
					}
				}
			}
			$sql = "('',{$_POST['uid']},".$this->mCurUser['uid'].",1,'".rtrim($val1,'|')."','{$_POST['message']}','".rtrim($val2,'|')."',$time)";
				$user->exec("insert into user_edit values ".$sql);
				$user->exec("update user set ".rtrim($set,',')."where uid={$_POST['uid']}");
				if(trim($_POST['email']) != $data['email']){
					Cache_Redis::instance()->hSet('useremail', trim($_POST['email']), $data['pwd'] .','.$data['uid'] .','. $data['role'].','. $data['prand']);
					Cache_Redis::instance()->hDel('useremail' , $data['email']);
				}
				$email = trim($_POST['email']);
				if($eData = $user->fRow('SELECT * FROM email_activate WHERE uid = ' . $data['uid'] )){
					$user->exec("update email_activate set email='{$email}' where uid={$data['uid']} and id={$eData['id']}");
				}
				Cache_Redis::instance()->hSet('usersession', $_POST['uid'], 1);
				$this->showMsg('修改成功','/manage_user/usersave?uid='.$_POST['uid']);

        }
  	}

	# 用户 重置交易密码
	public function usertpchangeAction($uid=0){
		if(!$uid = abs($uid)) exit;
		$tMO = new UserModel();
		$prand = $tMO->where("uid = {$uid}")->fOne('prand');
		//$tMO->update(array('uid'=>$uid, 'pwdtrade'=>md5($tTradePW = rand(100000, 999999))));
		$tMO->update(array('uid'=>$uid, 'pwdtrade'=>Tool_Md5::encodePwdTrade($tTradePW = rand(100000, 999999), $prand)));
		Cache_Redis::instance()->hSet('usersession', $uid, 1);
		$this->exitMsg('交易密码重置为：'.$tTradePW);
	}

	# RMB：充值
	public function rmbAction($type='out', $status='等待'){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_rmb') || !$this->getAuth($_SESSION['user']['uid'], 'user_rmb_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_rmb_outsuccess', 'user_rmb_outcancel', 'user_rmb_incancel');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		$status = urldecode($status);
		# UID:2手续费收取
		$where = " opt_type='{$type}' and status='{$status}' and uid<>2 ";
		$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'uid';
		$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : '';
		$kw = trim($kw);
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}else{
			$where .= ' ';
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

		if( isset($_POST['cancle_all']) && '批量撤销'==$_POST['cancle_all'] ) {
			$run_time 		= time();
			$sql 			= "update exchange_cny set status='已取消',bak='订单已失效',updated={$run_time},updateip='3.3.3.3',admin=".$this->mCurUser['uid']." where ".$where;
			$cny_mo = new Exchange_CnyModel();
			$cny_mo->query($sql);
		}

		$sql = "select * from exchange_cny where {$where} {$order}";
		$countSql = "select count(id) as total from exchange_cny where {$where} {$order}";
		$sqltotal = "select count(*) num, sum(money) total, sum(money_u) total_u from exchange_cny where {$where} {$order}";

		$this->assign('type', $type);
		$this->assign('status', $status);
		$this->assign('stime', $stime);
		$this->assign('etime', $etime);
		$this->assign('field', $field);
		$this->assign('kw', $kw);
		$this->assign('hasTime', $hasTime);

		$mo = new Exchange_CnyModel();
		$arr = $mo -> query($countSql);

        if(!$tCnt = $arr[0]['total']){
            $datas['list'] = array();
            $datas['total'] = array(array('num'=>0, 'total'=>0, 'total_u'=>0));
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 10);
            $datas['list'] = $mo->query($sql.' limit '.$tPage->limit());
            $datas['total'] = $mo->query($sqltotal);
            $datas['pageinfo'] = $tPage->show();
        }

		$this->assign('datas', $datas);
	}

	# 修改充值订单
	public function modrmbinAction($id){
		$tRmbMO = new Exchange_CnyModel;
		if( !$data = $tRmbMO->fRow($id) ){
			$this->showMsg('没有找到记录');
		}

		$this->assign('data', $data);

		if( 'POST' == $_SERVER['REQUEST_METHOD'] ){
			if( $id != $_POST['id'] || $_POST['uid'] != $data['uid'] || $_POST['name'] != $data['name'] ){
				$this->showMsg('参数错误', '/manage_user/rmb/type/in/status/等待');
			}

			if( !$tRmbMO->update( array('id'=>$id, 'money'=>$_POST['money']) ) ){
				$this->showMsg('系统错误，请重新操作');
			}

			$this->showMsg('充值订单修改成功', '/manage_user/rmb/type/in/status/等待');
		}

	}

	# RMB：充值
	public function rmbinAction($uid=0){
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$this->assign('data', $_POST);
			# 验证
			if(!$_POST['uid'] = abs($_POST['uid'])){
				$this->showMsg('用户错误');
			}
			if(!$_POST['money'] = (float)$_POST['money']){
				$this->showMsg('金额错误');
			}
			# 数据
			$tUser = array('uid'=>$_POST['uid'], 'showold'=>1);
			# 更新用户表
			$tUserMO = new UserModel();
			$tUserMO->begin();
			if(!$tUserMO->safeUpdate($tUser, array('cny_over'=>$_POST['money']))){
				$tUserMO->back();
				$this->showMsg($tUserMO->error[2]);
			}
			# 记录转入
			$tRmbMO = new Exchange_CnyModel();
			if(!$tRmbMO->insert(array('uid'=>$_POST['uid'], $tUser['email'], 'bank'=>$_POST['bank'], 'accounttype'=>$_POST['accounttype'], 'money'=>$_POST['money'], 'order'=>$_POST['order'], 'account'=>$_POST['account'], 'name'=>$_POST['name'], 'opt_type'=>'in', 'status'=>'成功', 'created'=>time(), 'createip'=>USER_IP, 'admin'=>$this->mCurUser['uid']))){
				$this->showMsg('插入记录出错，数据已经回滚');
			}
			$tUserMO->commit();
			Cache_Redis::instance()->hSet('usersession', $_POST['uid'], 1);
			echo "<div>操作后：RMB余额[{$tUser['cny_over']}], RMB冻结[{$tUser['cny_lock']}], BTC余额[{$tUser['btc_over']}], RMB冻结[{$tUser['btc_lock']}]</div>";;
			exit("<h1>充值成功</h1><div><a href='/manage_user/rmb/type/in/status/".urlencode('成功')."'>点击返回充值列表</a></div>");
		}
		$this->assign('uid', $uid);
	}

	# 手动处理自动充值中有问题的记录
	public function rmbpayAction($id, $cancel=0) {
		$tRmbMO = new Exchange_CnyModel();
		if(!$tRmb = $tRmbMO->fRow($id)){
			$this->showMsg('没有找到记录');
		}

		if($cancel){
			$tOrder = array(
				'id'=>$tRmb['id'],
				'status'=>'已取消',
				'admin' => $this->mCurUser['uid'],
				'updated'=>time(),
			);

			$msg = '充值操作已取消';

			if( !$tRmbMO->update($tOrder) ) {
				$msg = '撤销失败';
			}

		} else {
			$tPay = array(
				'out_trade_no' => $tRmb['id'],
				'total_fee' => $tRmb['money'],
				'trade_no' => '',
				'buyer_email' => '',
				'account' => $tRmb['account'],
			);

			$res = $tRmbMO->pay($tPay, $this->mCurUser['uid']);
			if ( $res ) { // 更新数据库失败
				$this->showMsg('处理失败:'.$res);
			}

			$msg = '充值操作已成功';
		}

		$this->showMsg( $msg );
	}

	# RMB：状态改为成功
	public function rmboutAction($id, $cancel=0) {
		$tRmbMO = new Exchange_CnyModel();
		# 事务开始
		$tRmbMO->begin();
		# 验证
		if(!$tRmb = $tRmbMO->lock()->fRow($id)){
			$this->showMsg('没有找到记录');
		}
		if($tRmb['status']!='等待'){
			$this->showMsg('您已经操作过了');
		}
		# 操作用户
		$tUser = array('uid'=>$tRmb['uid']);
		//等级计算
		$tUserMO = new UserModel();
		/*$level = new UserLevelModel();
		$leveldata = $level->fList();
		$leveluser = level($tUserMO->getById($tRmb['uid']),$leveldata);*/
		# 数据
		$tUData = array('cny_lock'=>-$tRmb['money']);
		$tOutData = array('id'=>$id, 'status'=>'成功', 'updated'=>time());
		if($cancel){
			$tUData['cny_over'] = $tRmb['money'];
			$tOutData['status'] = '已取消';
		} else {
		    //手续费转入uid 2
		    $tDatas = array(
			'uid'=>User_AdminModel::OUT_FEE,
		    	'email'=>User_AdminModel::$email[User_AdminModel::OUT_FEE],
		    	'admin'=>$this->mCurUser['uid'],
		    	'money'=>$tRmb['money']*Exchange_CnyModel::RMBOUT_FEE,
		    	'money_u'=>$tRmb['money']*Exchange_CnyModel::RMBOUT_FEE,
		    	'opt_type'=>'in',
		    	'status'=>'成功',
		    	'created'=>time(),
		    	'updated'=>time(),
			'bak'=>$id,
		    	);
		    if(!$tRmbMO->insert($tDatas)){
                $tRmbMO->back();
                return $this->showMsg('手续费扣取失败[3]，请联系技术');
		    }
		    $feeData    = array('cny_over'=>$tDatas['money_u']);
		    $feeUser    = array('uid'=>2);
		    if(!$tUserMO->safeUpdate($feeUser, $feeData, true)){
                $tRmbMO->back();
                return $this->showMsg('手续费扣取失败[4]，请联系技术');
		    }
		}
		# 更新用户
		if(!$tUserMO->safeUpdate($tUser, $tUData, true)){
			$tRmbMO->back();
			$this->showMsg($tUserMO->error[2]);
		}
		# 更新转出请求
		if(!$tRmbMO->save($tOutData)){
			$tRmbMO->back();
			$this->showMsg($tRmbMO->error[2]);
		}
		$tRmbMO->commit();

        #用户常用银行卡
        #$tRmb
        if(empty($cancel)){
            $pUbcMO = new UserBankCardsModel;
            $pWhereData = array(
                'field' => 'id',
                'where' => 'uid='.$tRmb['uid'] . ' AND account = \'' .$tRmb['account'] . '\' AND bank = \''.$tRmb['bank'].'\' AND name = \''.$tRmb['name'].'\''
            );
            $tWhereData = ' where uid='.$tRmb['uid'] . ' AND account = \'' .$tRmb['account'] . '\' AND bank = \''.$tRmb['bank'].'\' AND name = \''.$tRmb['name'].'\'';
            $tSql = 'SELECT id FROM user_bank_cards ' . $tWhereData;
            $pId = $pUbcMO->fRow($tSql);
            if(empty($pId['id'])){
                $pData = array(
                    'uid' => $tRmb['uid'],
                    'account' => $tRmb['account'],
                    'bank' => $tRmb['bank'],
                    'name' => $tRmb['name'],
                    'created' => $tRmb['created'],
                    'email' => $tRmb['email'],
                    'province' => $tRmb['province'],
                    'city' => $tRmb['city'],
                );
                $pUbcMO->insert($pData);
            }else{
               $pUbcMO->exec('UPDATE user_bank_cards SET created = ' .time() . $tWhereData);
            }
        }

		Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
		$this->showMsg(($cancel? '撤消': '转出').' 操作已成功');
	}
	# 导出人民币充值记录
	public function rmbcsvAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_rmbcsv') ){
			exit('权限不足');
		}


		$sql = "";
		if(isset($_POST['sub'])){
			if(isset($_POST['stime'])){
				$stime = trim($_POST['stime']);
			}
			if(isset($_POST['etime'])){
				$etime = trim($_POST['etime']);
			}
			if(isset($_POST['status'])){
				$status = trim($_POST['status']);
			}
			if(isset($_POST['type'])){
				$type = trim($_POST['type']);
			}

			# UID:2手续费收取
			$where = " and uid<>2";
			$field_time = 'created';
			if('成功'==$_POST['status']) {
				$field_time = 'updated';
			}
			if(empty($stime) && empty($etime)){
				$sql = "select * from exchange_cny where opt_type='{$type}' and status='{$status}'".$where;
			}else if(!empty($stime) && empty($etime)){
				$stime = strtotime($stime.':00');
				$sql = "select * from exchange_cny where opt_type='{$type}' and status='{$status}' and {$field_time}>={$stime}".$where;
			}else if(empty($stime) && !empty($etime)){
				$etime = strtotime($etime.':59');
				$sql = "select * from exchange_cny where opt_type='{$type}' and status='{$status}' and {$field_time}<={$etime}".$where;
			}else if(!empty($stime) && !empty($etime)){
				$etime = strtotime($etime.':59');
				$stime = strtotime($stime.':00');
				if($etime < $stime){
					$this->showMsg('开始时间不能大于结束时间');
				}else{
					$order = " order by id desc";
					// if('in'==$type && '成功'==$status) {
					// 	$order = " order by updated desc,id desc";
					// }
					$sql = "select * from exchange_cny where opt_type='{$type}' and status='{$status}' and {$field_time} between {$stime} and {$etime} {$where} {$order}";
				}
			}

			//echo $sql;
			$erm = new Exchange_CnyModel();
			$ermdata = $erm -> query($sql);
			//$this->assign('datas', $ermdata);
			if ('in'==$_POST['type']) {
				$this->csvin($ermdata);
			} else {
				$this->csvout($ermdata);
			}
		}else{
		//	$this->_list('exchange_rmb', 'OB=id DESC');
		}
	}


	/**
	 * 转出
	 * @param  [array] $data
	 */
	private function csvout($data){
		header("Content-type:application/csv");
		header("Content-Disposition:filename=ebank".date('YmdHi').".csv");
		$str = "ID,用户ID,姓名,邮箱,金额,实付金额,手续费,汇款单号,汇款账号,开户行,收款类别,开户行地址,创建时间";
		$str .= "\n";

		foreach($data as &$v){
			$name = $v['name'];
			$bank = $v['bank'];
			$bpye = $v['accounttype'];
			$account = $this->formatIdCard($v['account']);
			if (!$v['province']) {
				$v['province'] = '';
			}
			if (!$v['city']) {
				$v['city'] = '';
			}
			if (!$v['district']) {
				$v['district'] = '';
			}
			if (!$v['subbranch']) {
				$v['subbranch'] = '';
			}

			$bank_address = $v['province'] . ' ' . $v['city'] . ' ' . $v['district'] . ' ' . $v['subbranch'];
			$bank_address = trim($bank_address);
			if (((int)$bank_address)!=0) {
				$bank_address = '';
			}
			$shou = $v['money_u']==0?Tool_Str::format($v1['money']*(1-0.995), 2):Tool_Str::format($v['money'] - $v['money_u'], 2);
			$str .= $v['id'].",".$v['uid'].",".$name.",".$v['email'].",".(Tool_Str::format($v['money'], 2)).",".(Tool_Str::format($v['money_u'], 2)).",".$shou.",".$v['order'].",".$account.",".$bank.",".$bpye.",".$bank_address.",".date('Y-m-d H:i:s',$v['created']) . "\n";
		}
		exit($str);
	}

	private function formatIdCard($str){
		$arr=str_split($str,4);
		$str=implode(' ',$arr);
		$str = iconv('gb2312', 'utf-8', $str);
		return $str;
	}


	/**
	 * 转入
	 * @param  [array] $data
	 */
	private function csvin($data){
		header("Content-type:application/csv");
		header("Content-Disposition:filename=ebank".date('YmdHi').".csv");
		$str = "ID,用户ID,姓名,邮箱,金额,汇款单号,汇款账号,开户行,收款类别,创建时间";
		if('成功'==trim($_POST['status'])) {
			$str .= ",到账时间";
		}
		$str .= "\n";

		foreach($data as $v){
			$name = $v['name'];
			$bank = $v['bank'];
			$bpye = $v['accounttype'];
			$account = $this->formatIdCard($v['account']);
			$str .= $v['id'].",".$v['uid'].",".$name.",".$v['email'].",".(Tool_Str::format($v['money'],2)).",".$v['order'].",".$account.",".$bank.",".$bpye.",".date('Y-m-d H:i:s',$v['created']);
			if('成功'==$_POST['status']) {
				$str .= ",".date('Y-m-d H:i:s',$v['updated']);
			}
			$str .= "\n";
		}
		exit($str);
	}

	# 清除双重验证密码
	public function usergacloseAction($uid){
		Cache_Redis::instance()->hDel('user_ga', $uid);
		$this->showMsg('您已成功清除用户：'.$uid.' 的双重认证密码！请通知用户重新登录！');
	}
	#用户邀请关系
	public function userrelationAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_relation') ){
			exit('权限不足');
		}


        $user = new UserModel();
        $sql = "select count(*) num from user ";
        if(isset($_GET['kw']) && !empty($_GET['kw']) && isset($_GET['id']) && !empty($_GET['id'])){
            if($_GET['id'] == 'uid'){
                $sql .= " where pid={$_GET['kw']}";
            }else if($_GET['id'] == 'pid'){
                $sql .= " where uid={$_GET['kw']}";
            }else if($_GET['id'] == 'femail' || $_GET['id'] == 'temail'){
                $sql .= " where email like '%{$_GET['kw']}%'";
            }
        }
        $count = $user -> query($sql);
        $sqld = "select y.uid yid, y.email ymail, b.uid bid, b.email bmail, b.created btime from user as y join user as b where y.uid=b.pid";
        if(!empty($count) && $count[0]['num'] > 0){
            $page = new Tool_Page($count[0]['num'], 15);
            if(isset($_GET['kw']) && !empty($_GET['kw']) && isset($_GET['id']) && !empty($_GET['id'])){
                if($_GET['id'] == 'uid'){
                    $sqld .= " and b.pid={$_GET['kw']}";
                }else if($_GET['id'] == 'pid'){
                    $sqld .= " and b.uid={$_GET['kw']}";
                }else if($_GET['id'] == 'femail'){
	                $sqld .= " and y.email like '%{$_GET['kw']}%'";
	            }else if($_GET['id'] == 'temail'){
	                $sqld .= " and b.email like '%{$_GET['kw']}%'";
	            }
            }
            $sqld .= " order by btime desc limit {$page->limit()}";
            $this->assign('pageinfo', $page->show());
        }else{
            $sqld .= " and uid=0";
            $this->assign('pageinfo', '');
        }
        $udata = $user -> query($sqld);
        $this->assign('datas', $udata);
	}
	#用户奖励关系
    public function userrewardAction(){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_reward') ){
			exit('权限不足');
		}


        $this->_list('reward', 'OB=id desc&type=0');
	}
	public function usereditAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_useredit') ){
			exit('权限不足');
		}


        $this->_list('UserEdit','L=15&OB=id DESC');
    }

    public function emailAction(){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_email') ){
			exit('权限不足');
		}


        $user = new UserModel();
        if(!empty($_POST['uid'])){
            $email = $user->fRow("select email from email_activate where uid={$_POST['uid']}");
            if(empty($email['email'])){
                $this->showMsg('没有此用户');
            }else{
                $senttime = $_SERVER['REQUEST_TIME'];
                $key = Tool_Md5::emailActivateKey($email['email'] , $senttime);
                $pUrlparam = array(
                    'uid' => $_POST['uid'],
                    'email' => $email['email'],
                    'key' => $key,
                );
                $pActivateurl = 'http://'.$_SERVER['HTTP_HOST'].'/user/emailactivate?id='.base64_encode(serialize($pUrlparam));
                $user->exec("update email_activate set `senttime` ='".$senttime."', `key`='".$key."' where `uid` = ".$_POST['uid']);
                $this->assign('url', $pActivateurl);
            }
        }else{
            $this->assign('url','');
        }

    }


    # 增减用户资产 - 可用
    public function modifyassetsAction() {
	   	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_modifyassets') ){
			exit('权限不足');
		}


		$pairs = User_CoinModel::getInstance()->getList();
		$this->assign('pairs', $pairs);

    	if(isset($_POST['uid'])) {
    		$uid = (int)$_POST['uid'];
    		if($uid==$this->mCurUser['uid']) {
    			exit(json_encode(array('code'=>1, 'msg'=>'管理员不能给自己修改资产')));
    		}
    		$coin 	= $_POST['coin'];
    		$num 	= (float)$_POST['num'];
    		$log_num = (float)$_POST['num'];
    		$bak 	= trim($_POST['bak']);
    		$adminid= (int)$_POST['admin'];
    		if(!$bak) exit(json_encode(array('code'=>1, 'msg'=>'备注信息不能不填')));
    		if(!$num) exit(json_encode(array('code'=>1, 'msg'=>'金额不正确')));

    		$user_mo = new UserModel;
    		$user_info_arr = $user_mo->where("uid=".$uid)->fRow();
    		if(empty($user_info_arr)) {
    			exit(json_encode(array('code'=>1, 'msg'=>'用户不存在')));
    		}
    		if($adminid && !isset(User_AdminModel::$email[$adminid])) {
    			exit(json_encode(array('code'=>1, 'msg'=>'可供使用的官方账户不存在')));
    		}

    		$user_mo->begin();
    		$udt = array('uid'=>$uid);
    		$admin = array('uid'=>$adminid);
    		$coin_arr = explode('_', $coin);

    		if('cny' == $coin_arr[0]) {
    			$tdt = array($coin=>$num);
    			if( !$user_mo->safeUpdate($udt, $tdt) ) {
    				$user_mo->back();
    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
				}
				# 需要操作官方
				if($admin['uid']) {
					$admin_dt = array($coin=>-$num);
	    			if( !$user_mo->safeUpdate($admin, $admin_dt) ) {
	    				$user_mo->back();
	    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
					}
				}
				$ex = new Exchange_CnyModel;
				if($num>0) {
					$opt_type = 'in';
					$admin_opt_type = 'out';
				} else {
					$opt_type = 'out';
					$num = -$num;
					$admin_opt_type = 'in';
				}

				$data = array(
						'uid'=>$uid,
						'name'=>$user_info_arr['name'],
						'email'=>$user_info_arr['email'],
						'admin'=>$this->mCurUser['uid'],
						'money'=>$num,
						'money_u'=>$num,
						'opt_type'=>$opt_type,
						'status'=>'成功',
						'created'=>time(),
						'createip'=>Tool_Fnc::realip(),
						'bak'=>$bak,
					);
				if(!$ex->insert($data)) {
					$user_mo->back();
    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
				}
				# 需要操作官方
				if($admin['uid']) {
					$data = array(
							'uid'=>$admin['uid'],
							'email'=>User_AdminModel::$email[$admin['uid']],
							'admin'=>$this->mCurUser['uid'],
							'money'=>$num,
							'money_u'=>$num,
							'opt_type'=>$admin_opt_type,
							'status'=>'成功',
							'created'=>time(),
							'createip'=>Tool_Fnc::realip(),
							'bak'=>$bak,
						);
					if(!$ex->insert($data)) {
						$user_mo->back();
	    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
					}
				}
    		} else{
    			$tdt = array($coin=>$num);
    			if( !$user_mo->safeUpdate($udt, $tdt) ) {
    				$user_mo->back();
    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
				}

				# 需要操作官方
				if($admin['uid']) {
					$admin_dt = array($coin=>-$num);
	    			if( !$user_mo->safeUpdate($admin, $admin_dt) ) {
	    				$user_mo->back();
	    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
					}
				}
				$model = 'Exchange_'.ucfirst($coin_arr[0]).'Model';
				$ex = new $model;
				if($num>0) {
					$opt_type = 'in';
					$admin_opt_type = 'out';
				} else {
					$opt_type = 'out';
					$num = -$num;
					$admin_opt_type = 'in';
				}

				$data = array(
						'uid'=>$uid,
						'email'=>$user_info_arr['email'],
						'admin'=>$this->mCurUser['uid'],
						'number'=>$num,
						'wallet'=>'官方',
						'opt_type'=>$opt_type,
						'status'=>'成功',
						'created'=>time(),
						'createip'=>Tool_Fnc::realip(),
						'bak'=>$bak,
					);
				if(!$ex->insert($data)) {
					$user_mo->back();
    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
				}

				# 需要操作官方
				if($admin['uid']) {
					$data = array(
							'uid'=>$admin['uid'],
							'email'=>User_AdminModel::$email[$admin['uid']],
							'admin'=>$this->mCurUser['uid'],
							'number'=>$num,
							'wallet'=>'官方',
							'opt_type'=>$admin_opt_type,
							'status'=>'成功',
							'created'=>time(),
							'createip'=>Tool_Fnc::realip(),
							'bak'=>$bak,
						);
					if(!$ex->insert($data)) {
						$user_mo->back();
	    				exit(json_encode(array('code'=>1, 'msg'=>'update user assets failed')));
					}
				}
    		}

    		// 保存资产变更记录记录到数据库
    		$logMo = new AssetLogModel;
    		$logData = array(
    			'admin' => $this->mCurUser['uid'],
    			'uid' => $uid,
    			'coin' => $coin,
    			'num' => $log_num,
    			'bak' => $bak,
    			'official' => $adminid,
    			'created' => time(),
    			'createip' => Tool_Fnc::realip()
    		);

    		if( !$logMo->insert($logData) ){
    			$user_mo->back();
	    		exit(json_encode(array('code'=>1, 'msg'=>'insert asset log failed')));
    		}

    		$user_mo->commit();
    		# Refresh Cache @todo
    		$data = array('code'=>0, 'msg'=>'success');
    		exit(json_encode($data));
    	}

    }

    # 获取用户资产 - 可用
    public function getassestsAction() {
    	$data = array('code'=>1, 'msg'=>'failed');
    	if(isset($_POST['uid'])) {
    		$uid = (int)$_POST['uid'];
			$user_mo = new UserModel;
			$user = $user_mo->where("uid=" . $uid)->fRow();
			if(empty($user)) {
				$data = array('code'=>1, 'msg'=>'用户不存在');
			} else {
				foreach ($user as $key => $val) {
					if( strpos($key, '_over') !== false || strpos($key, '_lock') !== false ){
						$user[$key] = number_format($user[$key], 4);
					}
				}
				$data = array('code'=>0, 'msg'=>'success', 'data'=>$user);

				/*$user['cny_over'] = number_format($user['cny_over'], 2);
				$user['cny_lock'] = number_format($user['cny_lock'], 2);
				$user['goc_over'] = number_format($user['goc_over'], 3);
				$user['goc_lock'] = number_format($user['goc_lock'], 3);*/
			}

    	}

    	exit(json_encode($data));
    }


    /**
     * 变更用户资产记录
     */
    public function assetlogAction(){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_assets') ){
			exit('权限不足');
		}


    	# 查询条件
		$where = ' 1=1 ';
		$field = isset($_POST['field']) ? $_POST['field'] : 'uid';
		$hasTime = isset($_REQUEST['hasTime']) ? $_REQUEST['hasTime'] : '';
		$kw = isset($_POST['kw']) ? $_POST['kw'] : '';
		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}

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
				$where .= " and created>={$stime} ";
			}
		}else if(empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			if( $hasTime ){
				$where .= " and created<={$etime} ";
			}
		}else if(!empty($stime) && !empty($etime)){
			$etime = strtotime($etime.':59');
			$stime = strtotime($stime.':00');
			if($etime < $stime){
				$this->showMsg('开始时间不能大于结束时间');
			}else{
				if( $hasTime ){
					$where .= " and created between {$stime} and {$etime} ";
				}
			}
		}

		$this->assign('stime', $stime);
		$this->assign('etime', $etime);
		$this->assign('field', $field);
		$this->assign('kw', $kw);
		$this->assign('hasTime', $hasTime);

        $mo = new AssetLogModel;
        $count = $mo->where($where)->count();

        if(!$tCnt = $count){
            $datas['list'] = array();
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 15);
            $datas['list'] = $mo->where($where)->limit($tPage->limit())->order('created DESC')->fList();
            $datas['pageinfo'] = $tPage->show();
        }
        $this->assign('datas', $datas);
    }

    /**
     * 短信到账
     */
    public function msgAction($status = 1){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_msg') || !$this->getAuth($_SESSION['user']['uid'], 'user_msg_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_msg_pay', 'user_msg_cancel');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		$this->assign('status', $status);

		$where = 'status = '.$status;
		$mo = new SmsPayModel();
        $count = $mo->where($where)->count();

		if(!$tCnt = $count){
            $datas['msglist'] = array();
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 10);
            $datas['msglist'] = $mo->where($where)->limit($tPage->limit())->order('id DESC')->fList();
            $datas['pageinfo'] = $tPage->show();
        }

        $this->assign('datas', $datas);
    }

    /**
     * 用户银行卡列表
     */
    public function bankcardsAction($status = '0') {
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_bankcards') || !$this->getAuth($_SESSION['user']['uid'], 'user_bankcards_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_bankcards_del');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


		$where = " 1 = 1 and status = '{$status}'";

		$field = isset($_REQUEST['field']) ? $_REQUEST['field'] : 'uid';
		$kw = isset($_REQUEST['kw']) ? $_REQUEST['kw'] : '';

		if( $kw ){
			$where .= " and {$field} like '%{$kw}%' ";
		}
		$mo = new UserBankCardsModel();

		$order = " order by id desc";
		$sql = "select * from user_bank_cards where {$where} {$order}";
		$countSql = "select count(id) as ids from user_bank_cards  where {$where} {$order}";
		$arr = $mo -> query($countSql);

        if(!$tCnt = $arr[0]['ids']){
            $datas['list'] = array();
            $datas['pageinfo'] = '';
        }else{
            $tPage = new Tool_Page($tCnt, 15);
            $datas['list'] = $mo->query($sql.' limit '.$tPage->limit());
            $datas['pageinfo'] = $tPage->show();
        }

		$this->assign('status', $status);
		$this->assign('datas' , $datas);
    }

    /**
     * 删除银行卡
     */
    public function delcardAction(){
        $bid = empty($_GET['id'])?'':$_GET['id'];
        if(!Tool_Validate::int($bid)){
            $this->showMsg('删除失败', '/manage_user/bankcards');
        }
        $user_bank_mo = new UserBankCardsModel;
        $where = "id={$bid} ";
        $data = array('status'=>1);
        if(!$user_bank_mo->where($where)->update($data)){
            $this->showMsg('删除失败', '/manage_user/bankcards');
        }

        $this->showMsg('已删除', '/manage_user/bankcards');
    }

    /**
     * 冻结用户列表
     */
    public function forbideAction( $status = 0 ){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_forbidden') || !$this->getAuth($_SESSION['user']['uid'], 'user_forbidden_look')){
			exit('权限不足');
		}
		# 按钮权限
		$btnArr = array('user_forbidden_edit', 'user_forbidden_del');
		foreach ($btnArr as $vb) {
			$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
		}
		$this->assign('btnAuth', $btnAuth);


    	$this->assign('status', $status);

    	$fMo = new UserForbiddenModel;
    	$datas = $fMo->where("status = {$status}")->order('created desc')->fList();
    	$this->assign('datas', $datas);
    }

    /**
     * 添加冻结用户
     */
    public function newforbideAction(){

    	$forMo = new UserForbiddenModel;

    	if(!empty($_GET['id'])){
	  		if($fData = $forMo->lock()->fRow($_GET['id'])){
	  			$this->assign('data', $fData);
	  		}
	  	}

    	if ('POST' == $_SERVER['REQUEST_METHOD'] ) {
    		$user = new UserModel;
			$udata = $user->fRow("select * from user where uid={$_POST['uid']}");
			if( !$udata ){
				$this->showMsg('此用户id不存在');
			}

    		$arr = array('canbuy', 'cansale', 'canrmbout', 'cancoinout');
    		foreach ($arr as $v) {
    			$_POST[$v] = isset($_POST[$v]) ? $_POST[$v] : 0;
    		}

    		$logMo = new UserForbiddenLogModel;

    		$forMo->begin();

    		$act = (!empty($_POST['id']))?'update':'insert';
    		if( $act == 'insert' ){
    			# 插入数据到user_forbidden表
	    		$forbide = array(
	    			'uid' => $_POST['uid'],
	    			'admin' => $this->mCurUser['uid'],
	    			'bak' => $_POST['bak'],
	    			'canbuy' => $_POST['canbuy'],
	    			'cansale' => $_POST['cansale'],
	    			'canrmbout' => $_POST['canrmbout'],
	    			'cancoinout' => $_POST['cancoinout'],
	    			'status' => 0,
	    			'created' => time(),
	    			'updated' => time()
	    		);
	    		if( !$forMo->insert($forbide) ){
	    			$forMo->back();
	    			$this->showMsg('添加信息失败，请重新操作');
	    		}

	    		# 插入数据到user_forbidden_log表
	    		$log = array(
	    			'uid' => $_POST['uid'],
	    			'admin' => $this->mCurUser['uid'],
	    			'content' => "添加冻结用户{$_POST['uid']}, 冻结原因：{$_POST['bak']}, 允许买：{$_POST['canbuy']}, 允许卖：{$_POST['cansale']}, 允许人民币提现：{$_POST['canrmbout']}, 允许数字货币提现：{$_POST['cancoinout']}",
	    			'created' => time()
	    		);
	    		if( !$logMo->insert($log) ){
	    			$forMo->back();
	    			$this->showMsg('添加记录信息失败，请重新操作');
	    		}
    		}else{
    			# 更新数据到user_forbidden表
	    		$forbide = array(
	    			'id' => $_POST['id'],
	    			'admin' => $this->mCurUser['uid'],
	    			'bak' => $_POST['bak'],
	    			'canbuy' => $_POST['canbuy'],
	    			'cansale' => $_POST['cansale'],
	    			'canrmbout' => $_POST['canrmbout'],
	    			'cancoinout' => $_POST['cancoinout'],
	    			'updated' => time()
	    		);
	    		if( !$forMo->update($forbide) ){
	    			$forMo->back();
	    			$this->showMsg('保存信息失败，请重新操作');
	    		}

	    		# 更改的信息
	    		$updateArr = array('bak', 'canbuy', 'cansale', 'canrmbout', 'cancoinout');
	    		$updateStr = '';
	    		foreach ($updateArr as $v) {
	    			if( $_POST[$v] != $fData[$v] ){
	    				$updateStr .= " , {$v}由 '{$fData[$v]}' 修改为 '{$_POST[$v]}'";
	    			}
	    		}
	    		# 插入数据到user_forbidden_log表
	    		$log = array(
	    			'uid' => $_POST['uid'],
	    			'admin' => $this->mCurUser['uid'],
	    			'content' => "修改冻结用户{$_POST['uid']}信息 {$updateStr}",
	    			'created' => time()
	    		);
	    		if( !$logMo->insert($log) ){
	    			$forMo->back();
	    			$this->showMsg('添加记录信息失败，请重新操作');
	    		}
    		}

    		$forMo->commit();
    		$this->showMsg('操作成功', '/manage_user/forbide');
    	}
    }


    /**
     * 解除用户冻结
     */
    public function delforbideAction( $id ){
    	$forMo = new UserForbiddenModel;

    	if( !$fData = $forMo->lock()->fRow($id) ){
    		$this->showMsg('参数错误');
    	}

    	$forMo->begin();

    	# 更改状态
    	if( !$forMo->update(array('id'=>$id, 'status'=>1)) ){
			$forMo->back();
			$this->showMsg('操作失败，请重新尝试');
    	}
    	# 保存记录到数据库
    	$logMo = new UserForbiddenLogModel;
		$log = array(
			'uid' => $fData['uid'],
			'admin' => $this->mCurUser['uid'],
			'content' => "管理员{$this->mCurUser['uid']}解除了记录id为 {$id}, 即用户ID为“{$fData['uid']}” 的冻结状态 ",
			'created' => time()
		);
		if( !$logMo->insert($log) ){
			$forMo->back();
			$this->showMsg('添加记录信息失败，请重新操作');
		}

		$forMo->commit();
    	$this->showMsg('操作成功', '/manage_user/forbide');
    }
}
