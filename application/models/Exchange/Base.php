<?php
class Exchange_BaseModel extends Orm_Base{
	public $table = 'exchange_goc';
	public $field = array(
		'id' => array('type' => "int(11) unsigned", 'comment' => 'id'),
		'uid' => array('type' => "int(11) unsigned", 'comment' => '用户id'),
		'email' => array('type' => "char(60)", 'comment' => '用户名'),
		'admin' => array('type' => "int(11) unsigned", 'comment' => '管理员'),
		'wallet' => array('type' => "char(32)", 'comment' => '钱包地址'),
		'txid' => array('type' => "char", 'comment' => ''),
		'confirm' => array('type' => "char", 'comment' => ''),
		'number' => array('type' => "decimal(7,3) unsigned", 'comment' => '数量'),
		'opt_type' => array('type' => "enum('in','out')", 'comment' => '类型'),
		'status' => array('type' => "enum('等待','确认中','成功','已取消')", 'comment' => '状态'),
		'created' => array('type' => "int(11) unsigned", 'comment' => '创建时间'),
		'createip' => array('type' => "char(15)", 'comment' => '创建ip'),
		'updated' => array('type' => "int(11) unsigned", 'comment' => '修改时间'),
		'updateip' => array('type' => "char(15)", 'comment' => '修改ip'),
        'bak'   => array('type'=>"char", 'comment'=>'')
	);
	public $pk = 'id';

	/**
     * 转出goc
	 * @param $pKey 币地址
	 * @param $coin 币种
	 * @param $pUser 用户
	 * @return array 富途币数据
	 */
	public function post($pKey, $pNum, $coin, &$pUser){
        #update user
        $tMO = new UserModel();
        $tMO->begin();
        $coin_data  = array($coin.'_lock' => $pNum, $coin.'_over' => -$pNum);
        if(!$tMO->safeUpdate($pUser, $coin_data)){
            $tMO->back();
            Tool_Fnc::ajaxMsg($tMO->error[2]);
        }
		if(!$tId = $this->insert(array(
			'uid'=>$pUser['uid'],
			'admin' => 6,
			'email'=>$pUser['email'],
			'wallet'=>$pKey,
			'opt_type'=>'out',
			'number'=>$pNum,
			'created'=>$_SERVER['REQUEST_TIME'],
            'createip'=>Tool_Fnc::realip()
		))){
			$tMO->back();
			Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:S_EB_001]');
		}
		PhoneCodeModel::updateCode($pUser,2,$tId);
		# 提交数据
		$tMO->commit();
		# 转出GOC，操作用户表
		$_SESSION['user'] = $pUser;
		return array('id'=>$tId, 'created'=>date('Y年m月d日 H:i:s'), 'number'=>$pNum);
		//  $coin.'key'=>$pKey,
	}
}
