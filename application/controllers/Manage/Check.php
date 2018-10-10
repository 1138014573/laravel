<?php
/**
 * 用户相关管理
 * @role admin
 */
class Manage_CheckController extends Ctrl_Admin {

  	# 资金汇总
  	public function walletAction() {
		//$dict = array();
		$tRedis = &Cache_Redis::instance();
		$tARC = new Api_Rpc_Client(Yaf_Application::app()->getConfig()->api->rpcurl->ybcin);
    		for($i=1;$i<=2633; $i++){	
			$pKey = "uid_".$i;
			echo $pKey.": ";
			$addr = Api_Rpc_Client::getAddrByCache('uid_'.$i, 1);
			if ( !$addr ) echo "null";
			else{
				echo $addr;
			    /*
			    $addr_info = $tARC->validateaddress($addr);
					if ( $addr_info['account'] != "uid_".$i )
					{
						echo "  error address, get new: ";
						$tAddr = $tARC->getnewaddress($pKey);
						echo $tAddr;
						$tRedis->hset('btcaddr', $pKey, $tAddr);
					}
			    */
			    //if ( $dict[$addr] ) echo "    same with uid_".$dict[$addr];
					//else $dict[$addr] = $i;
			}
			echo "<br>";
    		}
 	}

}
