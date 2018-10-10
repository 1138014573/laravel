<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_UserController extends Ctrl_Admin {

  # 用户列表
  public function userAction() {
    $this->_list('user', 'OB=uid DESC');
  }

  # 用户 增、改、查
  public function usersaveAction() {
    # POST 数据处理
    if ('POST' == $_SERVER['REQUEST_METHOD']) {
			exit;
    }
    # 保存用户
    if ($this->_save('user', $_POST)) {
      $this->showMsg('操作成功', '/manage_user/userlist');
    }
  }

	# 用户 重置交易密码
	public function usertpchangeAction($uid=0){
		if(!$uid = abs($uid)) exit;
		$tMO = new UserModel();
		$tMO->update(array('uid'=>$uid, 'pwdtrade'=>md5($tTradePW = rand(100000, 999999))));
		$this->exitMsg('交易密码重置为：'.$tTradePW);
	}

	# RMB：充值
	public function rmbAction($type='out', $status='等待'){
		$this->assign('type', $type);
		$this->assign('status', $status = urldecode($status));
		$this->_list('exchange_rmb', 'OB=id DESC&opt_type='.$type.'&status='.$status);
	}

	# RMB：充值
	public function rmbinAction($uid=0){
        exit;
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
			if(!$tUserMO->safeUpdate($tUser, array('rmb_over'=>$_POST['money']))){
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
			echo "<div>操作后：RMB余额[{$tUser['rmb_over']}], RMB冻结[{$tUser['rmb_lock']}], BTC余额[{$tUser['btc_over']}], RMB冻结[{$tUser['btc_lock']}]</div>";;
			exit("<h1>充值成功</h1><div><a href='/manage_user/rmb/type/in/status/".urlencode('成功')."'>点击返回充值列表</a></div>");
		}
		$this->assign('uid', $uid);
	}

	# 手动处理自动充值中有问题的记录
	public function rmbpayAction($id)
	{
		$tRmbMO = new Exchange_CnyModel();
		if(!$tRmb = $tRmbMO->fRow($id)){
			$this->showMsg('没有找到记录');
		}
		$tPay = array(
				'out_trade_no' => $tRmb['id'],
				'total_fee' => $rRmb['money'],
				'trade_no' => '',
				'buyer_email' => ''
			);
		$res = $tRmbMO->pay($tPay, 1);
		if ( $res ) { // 更新数据库失败
			$this->showMsg('处理失败:'.$res);
		}
		$this->showMsg('充值操作已成功');
	}
	
	# RMB：状态改为成功
	public function rmboutAction($id, $cancel=0){
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
		$tUserMO = new UserModel();
		# 数据
		$tUData = array('rmb_lock'=>-$tRmb['money']);
		$tOutData = array('id'=>$id, 'status'=>'成功');
		if($cancel){
			$tUData['rmb_over'] = $tRmb['money'];
			$tOutData['status'] = '已取消';
		}
		# 更新用户
		if(!$tUserMO->safeUpdate($tUser, $tUData)){
			$tRmbMO->back();
			$this->showMsg($tUserMO->error[2]);
		}
		# 更新转出请求
		if(!$tRmbMO->save($tOutData)){
			$tRmbMO->back();
			$this->showMsg($tRmbMO->error[2]);
		}
		$tRmbMO->commit();
		Cache_Redis::instance()->hSet('usersession', $tUser['uid'], 1);
		$this->showMsg(($cancel? '撤消': '转出').' 操作已成功');
	}

	# 委托
	public function trustAction($status=false){
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
		$this->_list('trust', $tConn);
	}

	# 成交
	public function orderAction(){
		$this->_list('order', 'OB=id DESC');
	}

	# 清除双重验证密码
	public function usergacloseAction($uid){
		Cache_Redis::instance()->hDel('user_ga', $uid);
		$this->showMsg('您已成功清除用户：'.$uid.' 的双重认证密码！请通知用户重新登录！');
	}
}
