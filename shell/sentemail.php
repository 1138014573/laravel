<?php
# 加载类库
include '../application/library/Cache/Redis.php';
include '../application/library/Tool/Fnc.php';
# 记日志
function email_log($pMsg, $pCode= 'notice', $pClose = false){
	static $handle;
	$handle || $handle = fopen('./log/sentemail.'.date('Ymd').'.log', 'a');
	fwrite($handle, date('[H:i:s]')." [$pCode] ".$pMsg."\n");
	$pClose && fclose($handle);
	echo $pMsg, "\n";
}
# 全局
date_default_timezone_set("Asia/Shanghai");
if($tConfig = new Yaf_Config_Ini('../conf/application.ini', 'common')){
	Yaf_Registry::set("config", $tConfig);
} else {
	exit('config error');
}
    $tRedis = Cache_Redis::instance('default');
while(true){
    $pData = $tRedis->rpop('sentemaillist'); 
	if(!$pData){
	 sleep(5);
 	 continue;
	}
    $pData = json_decode($pData, true);
	$msg = Tool_Fnc::mailto($pData['email'] , $pData['title'] , $pData['body']);
    if($msg != 1){
        email_log($pData['email'] . ' sent failed' , $msg); 
    }
    //print_r($msg);
//	sleep(1);
}
