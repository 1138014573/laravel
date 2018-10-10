<?php
class Trust_CoinModel extends Orm_Base{
	public $table = 'trust_coin';
	public $field = array(
		'id' => array('type' => "int(11) unsigned", 'comment' => 'id'),
		'uid' => array('type' => "int(11) unsigned", 'comment' => '用户id'),
		'price' => array('type' => "decimal(8,2) unsigned", 'comment' => '单价'),
		'number' => array('type' => "decimal(7,3) unsigned", 'comment' => '数量'),
		'numberover' => array('type' => "decimal(7,3) unsigned", 'comment' => '剩余数量'),
		'numberdeal' => array('type' => "decimal(7,3) unsigned", 'comment' => '成交数量'),
		'flag' => array('type' => "enum('buy','sale')", 'comment' => '买卖标志'),
		'isnew' => array('type' => "enum('Y','N')", 'comment' => '新委托'),
		'status' => array('type' => "tinyint(1) unsigned", 'comment' => '状态'),
		'coin_from' => array('type' => "varchar(10)", 'comment' => '要兑换的币'),
		'coin_to' => array('type' => "varchar(10)", 'comment' => '目标兑换'),
		'created' => array('type' => "int(11) unsigned", 'comment' => '创建时间'),
		'createip' => array('type' => "char(15)", 'comment' => '创建ip'),
		'updated' => array('type' => "int(11) unsigned", 'comment' => '更新时间'),
		'updateip' => array('type' => "char(15)", 'comment' => '更新ip'),
		'trust_type' => array('type' => "int(11) unsigned", 'comment' => 'trust_type'),
	);
	public $pk = 'id';

	const STATUS_UNSOLD = 0; # 未成交
	const STATUS_PART   = 1; # 部分成交
	const STATUS_ALL    = 2; # 全部成交
	const STATUS_CANCEL = 3; # 已经撤销

	static $status = array('未成交', '部分成交', '全部成交', '已经撤销');

    public function getList($sql){
        return $this->query($sql);
    }
    public function getOne($sql){
        return $this->fRow($sql);
    }
	/**
	 * 撤销委托
	 * @param $pId
	 * @param $pUser
     * @param $ctype 1富途币，2融资
	 */
	public function cancel($pId, &$pUser, $ctype = 1, $api =false){
		# 开始事务
		$this->begin();
		# 查询委托
		if(!$tTrust = $this->lock()->fRow("SELECT uid,number,numberover,price,flag,isnew,status,coin_from,coin_to FROM {$this->table} WHERE id=$pId")){
			$this->back();
			Tool_Fnc::ajaxMsg('委托记录不存在');
		}
		# 用户验证
		if($tTrust['uid'] != $pUser['uid']) {
			$this->back();
			Tool_Fnc::ajaxMsg('您无权进行此操作');
		}
		# 剩余查询
		if(($tTrust['numberover'] < 0.0001) || ($tTrust['status'] > 1)){
			$this->back();
			Tool_Fnc::ajaxMsg('撤消失败，您的委托已经被处理过');
		}
        //买卖
        if($tTrust['flag']=='buy'){
            $tMoney = $tTrust['numberover'] * $tTrust['price'];
            $tUserData = array($tTrust['coin_to'].'_lock' => -$tMoney, $tTrust['coin_to'].'_over' => $tMoney);
        } else {
            $tUserData = array($tTrust['coin_from'].'_lock' => -$tTrust['numberover'], $tTrust['coin_from'].'_over' => $tTrust['numberover']);
        }
        # 更新用户
        $tMO = new UserModel();
        if(TRUE !== $tMO->safeUpdate($pUser, $tUserData, $api)){
            $this->back();
            Tool_Fnc::ajaxMsg($tMO->error[2]);
        }
		# 更新委托
		if(!$this->update(array('id'=>$pId, 'numberover'=>0, 'isnew'=>'N', 'status'=>self::STATUS_CANCEL, 'updated'=>time(), 'updateip'=>Tool_Fnc::realip()))){
			$this->back();
			Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:T_C_001]');
		}
		$this->commit();
	}

	/**
	 * 后台撤销委托
	 * @param $pId
	 */
	public function adminCancel($pId){
		# 开始事务
		$this->begin();
		# 查询委托
		if(!$tTrust = $this->lock()->fRow("SELECT uid,number,numberover,price,flag,isnew,status,coin_from,coin_to FROM {$this->table} WHERE id=$pId")){
			$this->back();
			Tool_Fnc::ajaxMsg('委托记录不存在');
		}
		# 状态验证
		if(!in_array($tTrust['status'], array(0, 1))) {
			$this->back();
			Tool_Fnc::ajaxMsg('您已不能进行此操作');
		}
		# 剩余查询
		if(($tTrust['numberover'] < 0.0001) || ($tTrust['status'] > 1)){
			$this->back();
			Tool_Fnc::ajaxMsg('撤消失败，委托已经被处理过');
		}

        # 撤销用户信息
        $pUser = array('uid'=>$tTrust['uid']);
        # 买卖
        if($tTrust['flag']=='buy'){
            $tMoney = $tTrust['numberover'] * $tTrust['price'];
            $tUserData = array($tTrust['coin_to'].'_lock' => -$tMoney, $tTrust['coin_to'].'_over' => $tMoney);
        } else {
            $tUserData = array($tTrust['coin_from'].'_lock' => -$tTrust['numberover'], $tTrust['coin_from'].'_over' => $tTrust['numberover']);
        }
        # 更新用户
        $tMO = new UserModel();
        if(!$tMO->safeUpdate($pUser, $tUserData)){
            $this->back();
            Tool_Fnc::ajaxMsg($tMO->error[2]);
        }
		# 更新委托
		if(!$this->update(array('id'=>$pId, 'numberover'=>0, 'isnew'=>'N', 'status'=>self::STATUS_CANCEL, 'updated'=>time(), 'updateip'=>Tool_Fnc::realip()))){
			$this->back();
			Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:T_C_001]');
		}
		$this->commit();
		Cache_Redis::instance()->hSet('usersession', $tTrust['uid'], 1);
	}
	/**
	 * 交易
	 * @param $pKey 币地址
	 * @param $pUser 用户
	 * @return array 富途币数据
	 */
	public function btc($pData, &$pUser, $api = false){
		# 保存DB
		$this->begin();
		# 买入YBC
		if($pData['type']=='in'){
			$tRMB = $pData['price']*$pData['number'];
			$tData = array('cny_lock' => $tRMB, 'cny_over' => -$tRMB);
			$pData['type'] = 'buy';
            if($tRMB < 1E-3){
			    Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:S_TB_001]');
            }
		}
		# 卖出YBC
		else {
			$tBTC = $pData['number'];
			$tData = array($pData['coin_from'].'_lock' => $tBTC, $pData['coin_from'].'_over' => -$tBTC);
			$pData['type'] = 'sale';
            if($tBTC < 1E-3){
			    Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:S_TB_002]');
            }
		}
		# 写入
		$tMO = new UserModel();
		if(!$tMO->safeUpdate($pUser, $tData, $api)){
			$this->back();
			Tool_Fnc::ajaxMsg($tMO->error[2]);
		}
		# 写入委托
		if(!$tId = $this->insert(array(
			'uid'=>$pUser['uid'],
			'price'=>$pData['price'],
			'number'=>$pData['number'],
			'numberover'=>$pData['number'],
			'flag'=>$pData['type'],
			'status'=>0,
			'coin_from'=>$pData['coin_from'],
			'coin_to'=>'cny',
			'created'=>time(),
			'createip'=>Tool_Fnc::realip()
		))){
			$this->back();
			Tool_Fnc::ajaxMsg('系统错误，请通知管理员 [错误编号:S_TB_001]');
		}
		# 提交数据
		$this->commit();
		$_SESSION['user'] = $pUser;
		Cache_Redis::instance()->hSet('TTask', 'user', 1);
		return $tId;
	}

	 /**
     * 获取符合对应价格的list
     * @param pair array(0=>coin_from,1=>coin_to,2=>pair_id)
     * @param flag buy获取买单list,sale获取卖单list
     *
     * return array()|bool
     */
    public function getListByPrice($price, $pair=array(), $flag='buy') {
        if(!is_numeric($price) || !is_array($pair)){
            return false;
        }
        $where = array(
            'buy' => "coin_from='{$pair[0]}' and coin_to='{$pair[1]}' and flag='{$flag}' and isnew='N' and price>={$price} and status<2",
            'sale' => "coin_from='{$pair[0]}' and coin_to='{$pair[1]}' and flag='{$flag}' and isnew='N' and price<={$price} and status<2"
        );
        $order = array(
            'buy' => 'price desc,id asc',
            'sale' => 'price asc,id asc'
        );
        $list = $this->field('id')->where($where[$flag])->order($order[$flag])->limit(50)->fList();
        if(empty($list)){
            return false;
        }
        return $list;
    }

    /**
     * 成交更改numberover
     *
     */
    public function updateNumber($id, $number, $numberdeal){
        if($number < 1E-9){
            $status = self::STATUS_ALL;
        } else {
            $status = self::STATUS_PART;
        }
        $oldnumberdeal = $this->where("id={$id}")->fOne('numberdeal');
        $data = array('id'=>$id, 'numberover'=>$number, 'numberdeal'=>$oldnumberdeal+$numberdeal, 'isnew'=>'N', 'status'=>$status, 'upadted'=>time());
        return $this->update($data);
    }

    /**
     * 根据币名撤销挂单
     * @param  [string]  $coin_from [原来]
     * @param  [string]  $coin_to   [目标]
     * @param  integer $uid       [uid]
     * @return [boolean]             [true/false]
     */
    public function cancleAllByCoin($coin_from, $coin_to, $uid=0) {
    	$where = '';
    	if($uid) {
    		$where .= 'uid='.$uid.' and ';
    	}

    	$where .= "coin_from='".$coin_from."' and coin_to='".$coin_to."' and status in(0, 1) and numberover>0.0001";
		# 查询委托
		// $sql = "SELECT uid,number,numberover,price,flag,isnew,status,coin_from,coin_to FROM {$this->table} WHERE ".$where;
		if(!$tTrustAll = $this->lock()->where($where)->fList()){
			return true;
		}

	    $tMO 	= new UserModel();
	    $ip 	= Tool_Fnc::realip();
	    $redis 	= Cache_Redis::instance();
	    $time 	= time();

	    # 开始事务
		$this->begin();
		foreach ($tTrustAll as $tTrust) {
			# 撤销用户信息
	        $pUser = array('uid'=>$tTrust['uid']);
	        # 买卖
	        if($tTrust['flag']=='buy'){
	            $tMoney 	= $tTrust['numberover'] * $tTrust['price'];
	            $tUserData 	= array($tTrust['coin_to'].'_lock' => -$tMoney, $tTrust['coin_to'].'_over' => $tMoney);
	        } else {
	            $tUserData 	= array($tTrust['coin_from'].'_lock' => -$tTrust['numberover'], $tTrust['coin_from'].'_over' => $tTrust['numberover']);
	        }
	        # 更新用户
	        if(!$tMO->safeUpdate($pUser, $tUserData, true)){
	            $this->back();
	            return false;
	        }
			# 更新委托
			if(!$this->update(array('id'=>$tTrust['id'], 'numberover'=>0, 'isnew'=>'N', 'status'=>self::STATUS_CANCEL, 'updated'=>$time, 'updateip'=>$ip))){
				$this->back();
				return false;
			}
			$redis->hSet('usersession', $tTrust['uid'], 1);
		}

		$this->commit();

		return true;

    }


    /**
     * 查询用户是否冻结禁止交易
     */
    public static function getTradeStatus($uid){
    	$forMo = new UserForbiddenModel;
        $fdata = $forMo->lock()->where("uid = {$uid} and status = 0")->fRow();

        if( $fdata ){
        	return $fdata;
        }else{
        	return false;
        }
    }
}
