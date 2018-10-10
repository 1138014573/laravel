<?php
class User_InfoController extends Ctrl_Base{
	protected $_auth = 3;
	
	public function indexAction(){
		$url = '';
		$http = $_SERVER['HTTPS'] ? 'https://' : 'http://';
		$url = $http.$_SERVER['SERVER_NAME'].'/user/reg/pid/'.$this->mCurUser['uid'];
		$data = UserModel::userPid($this->mCurUser['uid']);
		if($unum = count($data) > 0){
			$this->assign('data', $data);
		}
		$rwd = new RewardModel();
		$rmb_total = $rwd -> getData($this->mCurUser['uid']);
		$total = $rwd -> getDataMap($this->mCurUser['uid']);
		if(!empty($rmb_total)){
			$this->assign('datas', $total);
			$this->assign('total', $rmb_total['sum']);
			$this->assign('count', $rmb_total['count']);
		}else{
			$this->assign('count',0);
		}
		$this->assign('unum', $unum);
		$this->assign('url', $url);
    }
	public function memberAction(){
		$userlevel = new UserLevelModel();
		$uld = $userlevel->limit('18')->fList();
		foreach($uld as $k=>$val){
			$uld[$k]['fee_rmbout_b'] = ($val['fee_rmbout']*100).'%';
			if($val['level']<10){
				$uld[$k]['fee_ybcout_b'] = ($val['fee_ybcout']*100).'%';
			}else{
				$uld[$k]['fee_ybcout_b'] = number_format($val['fee_ybcout'], 2).'（币）';
			}
			if($val['level']<5){
				$uld[$k]['ybh_ybex'] = ($val['ybh_ybex']*100).'%';
			}else{
				$uld[$k]['ybh_ybex'] = '免费';
			}
			if($val['level']<8){
				$uld[$k]['vip_service'] = '无';
				$uld[$k]['rmb_out'] = '无';
				$uld[$k]['customize'] = '无';
			}else{
				$uld[$k]['vip_service'] = '有';
				$uld[$k]['rmb_out'] = '有';
				$uld[$k]['customize'] = '有';
			}
			$uld[$k]['loan_profit_b'] = ($val['loan_profit']*100).'%';
		}
		$this->assign('data', $uld);
		$this->_list(new UserLevelLogModel(), "OB=id DESC&uid={$this->mCurUser['uid']}");
	}
}
