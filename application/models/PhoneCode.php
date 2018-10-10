<?php
class PhoneCodeModel extends Orm_Base{
	public $table = 'phone_code';
	public $field = array(
		'id' => array('type' => "int(11) unsigned", 'comment' => 'id'),
		'code' => array('type' => "int(6) unsigned", 'comment' => ''),
		'uid' => array('type' => "int(11) unsigned", 'comment' => 'UID'),
		'message' => array('type' => "varchar(255) unsigned", 'comment' => 'UID'),
		'mo' => array('type' => "char(11) unsigned", 'comment' => 'UID'),
		'action' => array('type' => "tinyint(1) unsigned", 'comment' => '1:rmb转出,2ybc转出,3绿色通道'),
		'aid' => array('type' => "int(11) unsigned", 'comment' => ''),
		'status' => array('type' => "tinyint(1) unsigned", 'comment' => ''),
		'ctime' => array('type' => "int(11) unsigned", 'comment' => ''),
		'utime' => array('type' => "int(11) unsigned", 'comment' => '')
	);
	public $pk = 'id';

	public static function sendCode($user, $aid, $name='人民币', $num = 0){
		$code = rand(100000,999999);
		$data = array('code'=>$code, 'uid'=>$user['uid'], 'action'=>$aid, 'status'=>0,'ctime'=>$_SERVER['REQUEST_TIME']);
		$pc = new PhoneCodeModel();
		if($pc->fRow("select * from {$pc->table} where uid={$user['uid']} and action={$aid} and status=0")){
			$pc->exec("update {$pc->table} set status=2,utime={$_SERVER['REQUEST_TIME']} where uid={$user['uid']} and action={$aid} and status=0");
		}
		$message = '';
		switch($aid){
			case 1:
				$message = '（币交所账户UID:'.$user['uid'].'申请'.$name.'提现，请确认是本人操作），本次验证码5分钟内有效。';
				$data['action'] = 1;
				break;
			case 2:
				$message = '（币交所账户UID:'.$user['uid'].'申请'.$name.'提现，请确认是本人操作），本次验证码5分钟内有效。';
				$data['action'] = 2;
				break;
			case 3:
				$message = '（币交所账户UID:'.$user['uid'].'申请YBEX唯一绑定码验证码，请确认是本人操作），本次验证码5分钟内有效。';
				$data['action'] = 3;
                break;
            case 4:
                $message = '（币交所账户UID:'.$user['uid'].'申请YBEX唯一绑定码语音验证码，请确认是本人操作），本次验证码5分钟内有效。';
                $data['action'] = 4;
				break;
            case 5:
                $message = '（币交所账户UID:'.$user['uid'].'申请'.$name.'提现请语音验证码，请确认是本人操作），本次验证码5分钟内有效。';
                $data['action'] = 5;
                break;
            case 6:
                $message = '（币交所账户UID:'.$user['uid'].'申请'.$name.'提现语音验证码，请确认是本人操作），本次验证码5分钟内有效。';
                $data['action'] = 6;
                break;
            case 7:
            	$message = '【币交所】您的验证码是'.$code.'，如非本人操作，请忽略本短信。';
            	$data['action'] = 7;
                break;
            case 8:
            	$message = '【币交所】您的验证码是'.$code.'，如非本人操作，请忽略本短信。';
            	$data['action'] = 8;
                break;
		}
		if($aid < 7){
			$data['message'] = '【币交所】验证码：'.$code.$message;
		}else{
			$data['message'] = $message;
		}

		$data['mo'] = $user['mo'];
		$mo = $user['mo'];

		if($user['mo']){
			if($id = $pc->insert($data)){
				if($aid == 4 || $aid == 5 || $aid == 6 || $aid == 8) {
					$returnMsg = Tool_Message::run('voice_send', $mo , $code);
					$code = array();
					// $code['Code'] = $returnMsg['code'] ? 0 : 1;
					$code['Code'] = $returnMsg['code'];
		            return $code;
                }else{
					$returnMsg = Tool_Message::run('send', $mo, $data['message']);
					$code = array();
					$code['Code'] = $returnMsg['code'];
					return $code;
                }
			}
		}
		return false;
	}
	public static function getCode($user){
		$pc = new PhoneCodeModel();
		if($code = $pc->fRow("select * from {$pc->table} where uid={$user['uid']} and status=0 order by id desc")){
			if($code['ctime'] + PHONE_TIME > $_SERVER['REQUEST_TIME']){
				$code['time'] = PHONE_TIME-($_SERVER['REQUEST_TIME']-$code['ctime']);
				return $code;
			}
		}
		return false;
	}
	public static function verifiCode($user, $aid, $c){
		$pc = new PhoneCodeModel();
		if($code = $pc->fRow("select * from {$pc->table} where uid={$user['uid']} and code={$c} and action={$aid} and status=0 order by id desc")){
			if($code['ctime'] + 300 < time()){
				return false;
			}
			if($pc->exec("update {$pc->table} set status=1,utime={$_SERVER['REQUEST_TIME']} where id={$code['id']}")){
				return true;
			}
		}
		return false;
	}

	public static function updateCode($user, $aid, $id){
		$pc = new PhoneCodeModel();
		if($code = $pc->fRow("select * from {$pc->table} where uid={$user['uid']} and action={$aid} and status=1 order by id desc")){
			if($pc->exec("update {$pc->table} set aid={$id},utime={$_SERVER['REQUEST_TIME']} where id={$code['id']}")){
				return true;
			}
		}
		return false;
	}

	public static function verifiTime($user, $aid){
		$pc = new PhoneCodeModel();
		if($code = $pc->fRow("select * from {$pc->table} where uid={$user['uid']} and action={$aid} and status=0 order by id desc")){
			if($code['ctime'] + 40 < time()){
				return true;
			}
		}else{
			return true;
		}
		return false;
	}

	public function voiceVerify($verifyCode,$to,$playTimes = 3,$displayNum = '',$respUrl = ''){
        $err = array('Code'=>0, 'Msg'=>'');
        $accountSid= '8a48b55147aab17f0147aac4c2ed0008';    //主账号
        $accountToken= 'b9a2c5e5aad149b98b61ee9559501363';  //主账号Token
        $appId='aaf98f8947ae19300147af8e50150446';          //应用ID
        $serverIP='app.cloopen.com';                        //请求地址，格式如下，不需要写https://
        $serverPort='8883';                                 //请求端口
        $softVersion='2013-12-26';                          //REST版本号
        $rest = new CcprestsdkModel($serverIP,$serverPort,$softVersion);    //初始化 REST SDK
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);

        //调用语音/文字验证码接口
        if(!empty($displayNum)){
            $result = $rest->voiceVerify($verifyCode,$playTimes,$to,$displayNum,$respUrl);
        }else{
            $result = $rest->sendTemplateSMS($to,$verifyCode,1);
        }
        if($result == NULL ) {
            $err['Code'] = 'yuntong'; $err['Msg'] = 'yuntong result error'; exit(json_encode($err));
        }
        if($result->statusCode!=0) {
            $err['Code'] = $result->statusCode; $err['Msg'] = $result->statusMsg; exit(json_encode($err));
        } else{
            return 1;
        }
    }
}
