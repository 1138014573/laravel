<?php
/**
 * 币
 */
class Manage_CoinsController extends Ctrl_Admin
{
	/**
	 * 每日涨跌幅
	 */
	public function floatAction($coin_from='', $coin_to=''){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_float') ){
			exit('权限不足');
		}


		$where = '';
		if($coin_from) {
			$where = '&coin_from='.$coin_from.'&coin_to='.$coin_to;
		}

		$this->_list('coin_float', 'L=20&OB=updated DESC '.$where);
	}


	/**
	 * 持有量前20名
	 */
	public function haveAction($status=0, $coin='goc'){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'ybc_top') ){
			exit('权限不足');
		}

		$field = 'uid,name,email,mo,'.$coin.'_over as `over`,'.$coin.'_lock as `lock`,('.$coin.'_over+'.$coin.'_lock) as `total`,created';
		$where = '1=1';
		if($status) {
			$where = "role='user'";
		}
		$datas = UserModel::getInstance()->field($field)->where($where)->order('total desc')->limit(20)->fList();
		$this->assign('coin', $coin);
		$this->assign('datas', $datas);
		$this->assign('status', $status);
	}
}
