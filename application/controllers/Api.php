<?php
class ApiController extends Ctrl_Base{
	# ticker
	public function tickerAction(){
		$tOrder = json_decode(file_get_contents('./json/ybc_order.js'), true);
		$tSum = json_decode(file_get_contents('./json/ybc_sum.js'), true);
		$tTicker = array('high'=>$tOrder['max'], 'low'=>$tOrder['min'], 'buy'=>$tSum['buy'][0]['p'], 'sell'=>$tSum['sale'][0]['p'], 'last'=>$tOrder['d'][0]['p'], 'vol'=>$tOrder['sum']);
		exit(json_encode($tTicker));
	}

	# depth
	public function depthAction(){
		$tData = array('asks'=>array(), 'bids'=>array());
		$tDepth = json_decode(file_get_contents('./json/ybc_sum.js'), true);
		usort($tDepth['sale'], 'depth_sort');
		foreach($tDepth['buy'] as $v1){
			$tData['bids'][] = array($v1['p'], $v1['n']);
		}
		foreach($tDepth['sale'] as $v1){
			$tData['asks'][] = array($v1['p'], $v1['n']);
		}
		exit(json_encode($tData));
	}

	# trades
	public function tradesAction(){
		$tSince = isset($_GET['since'])? abs($_GET['since']): -1;
		$tMO = new Orm_Base();
		if($tSince > -1){
			$tDatas = $tMO->query('SELECT id,created,price,number,buy_tid,sale_tid FROM `order_ybc` WHERE id>'.$tSince.' ORDER BY id ASC LIMIT 100');
		} else {
			$tDatas = array_reverse($tMO->query('SELECT id,created,price,number,buy_tid,sale_tid FROM `order_ybc` ORDER BY id DESC LIMIT 100'));
		}
		$tTrades = array();
		foreach($tDatas as $v1){
			$tTrades[] = array('date'=>$v1['created'], 'price'=>$v1['price'], 'amount'=>$v1['number'], 'tid'=>$v1['id'], 'type'=>$v1['buy_tid']>$v1['sale_tid']?'buy':'sell');
		}
		exit(json_encode($tTrades));
	}
	
  	
	public function messageAction(){
		$err = array('Code'=>0, 'Msg'=>'');
		$keyArray = array('gyh');
		if(isset($_GET['key']) && !empty($_GET['key'])){
			if(!in_array($_GET['key'],$keyArray)){
				$err['Msg'] = '非法操作';exit(json_encode($err));
			}
		}else{
			$err['Msg'] = '非法操作';exit(json_encode($err));
		}
		if(isset($_GET['phone']) && !empty($_GET['phone'])){
		}else{
			$err['Code'] = -1;$err['Msg'] = '手机号为空';exit(json_encode($err));
		}
		if(isset($_GET['message']) && !empty($_GET['message'])){
		}else{
			$err['Code'] = -2;$err['Msg'] = '信息为空';exit(json_encode($err));
		}
        $msg = new Tool_Message('http://api.fissoft.com/pubservice/SMSSend','【币交所】');
		$smsg = $msg -> sendmsg($_GET['phone'],$_GET['message']);
        if($smsg['Code'] == 1){
			$err['Code'] = 1;$err['Msg'] = '成功';exit(json_encode($err));
		}
		if($smsg['Code'] == -1 || $smsg['Code'] == -2){
			$err['Code'] = -3;$err['Msg'] = '账户非法';exit(json_encode($err));
		}
		if($smsg['Code'] == -3){
			$err['Code'] = -4;$err['Msg'] = '短信存量不足';exit(json_encode($err));
		}
	}

    private function exitjson($code='001', $msg='非法请求', $data=array()){
        echo json_encode(array('Code'=>$code, 'Description'=>$msg, 'Data'=>$data));
        exit;
    }
	
	function qrimagesAction()
	{
		$text = isset($_GET['text'])?$_GET['text']:'null';
		$size = isset($_GET['size'])?$_GET['size']:6;
		$margin= isset($_GET['margin'])?$_GET['margin']:4;
	    Tool_Qrcode::png($text,false, QR_ECLEVEL_L, $size, $margin, false);
	    exit(0);
	}


}

function depth_sort($a, $b) {
	return $a['p']>$b['p']? -1: 1;
}
