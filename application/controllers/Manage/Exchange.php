<?php
/**
 * 流水记录操作
 */
class Manage_ExchangeController extends Ctrl_Admin{
	/**
	 * 更改短信状态状态
	 */
	 public function ajaxStatusAction(){
		$msmId = $_POST['msmId'];
		$msmMo = new SmsPayModel;
        if(empty($msmId) && !is_numeric($msmId)){
            echo '流水ID不能为空';
            exit;
        }
		$msm = $msmMo->field('message')->where('id='.$msmId.' AND status = 1')->fRow();
		if(!$msm['message']){
			echo '此订单不存在';
			exit(0);
		}
		//更改状态
		if($msmMo->update(array('id'=>$msmId,'status'=>2,'updated'=>time() ))){
			echo '更改成功';
		    exit(0);
		}else{
			echo '更改失败';
			exit(0);
		}

	 }

	/**
     * 更改短信充值不到账状态
     */
    public function ajaxPayAction(){
        // 权限校验
		if( !$this->getAuth($_SESSION['user']['uid'], 'user_msg') || !$this->getAuth($_SESSION['user']['uid'], 'user_msg_look')){
			exit('权限不足');
		}

        $orderId = $_POST['orderId'];
        $msmId = $_POST['msmId'];
        if(empty($orderId) && !is_numeric($orderId)){
            echo '订单ID不能为空';
            exit;
        }
		if(empty($msmId) && !is_numeric($msmId)){
            echo '短信ID不能为空';
            exit;
        }
        //获取短信信息
		$msmMo = new SmsPayModel;
		$cny_mo = new Exchange_CnyModel;
		$msm = $msmMo->field('message')->where('id='.$msmId.' AND status = 1')->fRow();
		if(!$msm['message']){
			echo '此订单不存在';
			exit(0);
		}
		# 匹配金额
		if (preg_match('/^.*?收入人民币(.*?)元.*?$/', $msm['message'], $result)) {
			$money = $result[1];
		}else if(preg_match('/^.*?(存入|转入)(.*?)元.*?$/', $msm['message'], $result)){
			$money = $result[2];
		}
		if($money>0){
			//查询充值等待表中的数据
            $where = "status = '等待' and accounttype in('支付宝', '银行卡') and opt_type = 'in' and id=".$orderId;
            $exchange_data = $cny_mo->where($where)->fRow();
			if(!empty($exchange_data)){
				$pay = array('out_trade_no'=>$orderId,
					         'total_fee'=>$money,
					         'trade_no' => '',
                             'buyer_email' => '',
                             'account' => $exchange_data['account']);
				# 更新用户余额
				$judge = $cny_mo->pay($pay);
				if ($judge === 0) {
					if(!$msmMo->update(array('id' => $msmId,'status' => 2, 'updated' => time()))){
						echo '更新短信状态失败';
						exit(0);
					}
					$orderArr = array(
						'id' => $exchange_data['id'],
						'status' => '成功',
						'bak'=>'web_'.$this->mCurUser['uid'],
						'updated'=>time(),
						'updateip'=>Tool_Fnc::realip()
						);
					$orderflag = $cny_mo->update($orderArr);

					$flag = $cny_mo->where("id={$orderId}")->fOne('status');
					if($flag != '成功'){
						echo '更新订单状态失败';
						exit(0);
					}
				}
			}else{
				echo '此订单不存在';
				exit(0);
			}
		}
        echo '短信充值成功';
        exit;
    }

}
