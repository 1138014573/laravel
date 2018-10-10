<?php
class Tool_Fnc{

    public static function httpRequest($url, $param=array(), $input_charset = '') {
        if (trim($input_charset) != '') {
            $url = $url."_input_charset=".$input_charset;
        }
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
        curl_setopt($curl,CURLOPT_POST,true); // post传输数据
        curl_setopt($curl,CURLOPT_POSTFIELDS,$param);// post传输数据
        $responseText = curl_exec($curl);
        curl_close($curl);
        return $responseText;
    }
	/**
	 * 获取以父ID为KEY的分类
	 *
	 * @param int $pPid 父ID
	 * @param int $pMeId 输出一个类别
	 */
	public static function catdata($pPid = false, $pMeId = 0){
		static $datas = array();
		if(!$datas) foreach(Cache_Redis::hget('category') as $v1){
			$v1 = json_decode($v1, true);
			$datas[$v1['pid']][$v1['cid']] = $v1;
		}
		if(false === $pPid) return $datas;
		return $pMeId? $datas[$pPid][$pMeId]: $datas[$pPid];
	}

	/**
	 * 显示树状分类
	 * @param string $pBoxId 容器ID
	 * @param int $pPid 父ID (0:全部)
	 */
	public static function cattree($pBoxId, $pPid = 0){
		$tDatas = self::catdata(false, 0);
		echo '<select id="yaf_', $pBoxId, '" name="', $pBoxId, '">';
		if(false !== strpos(strtolower($_SERVER['REQUEST_URI']), 'manage')){
			echo '<option value="0">顶级</option>';
		}
		self::cattreeIterate($tDatas, $pPid, 0);
		echo '</select>';
	}

	/**
	 * cattree 迭代函数
	 *
	 * @param array $datas 分类数组
	 * @param int $i 层级
	 * @param int $count 占位符个数
	 */
	static function cattreeIterate(&$datas, $i, $count){
		if(isset($datas[$i])) foreach($datas[$i] as $v1){
			echo "<option value='{$v1['cid']}'", $i == 0? " class='option'": "", ">", str_repeat('　　', $count), $v1['name'], "</option>";
			self::cattreeIterate($datas, $v1['cid'], $count + 1);
		}
	}

	/**
	 * 真实IP
	 * @return string 用户IP
	 */
	static function realip(){
		foreach(array('HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR') as $v1){
			if(isset($_SERVER[$v1])){
				$tIP = ($tPos = strpos($_SERVER[$v1], ','))? substr($_SERVER[$v1], 0, $tPos): $_SERVER[$v1];
				break;
			}
			if($tIP = getenv($v1)){
				$tIP = ($tPos = strpos($tIP, ','))? substr($tIP, 0, $tPos): $tIP;
				break;
			}
		}
		return $tIP;
	}

	/**
	 * 发送邮件
	 * @param $pAddress 地址
	 * @param $pSubject 标题
	 * @param $pBody 内容
	 */
	static function mailto($pAddress, $pSubject, $pBody, $pCcAddress = NULL){
		static $mail;
		if(!$mail){
			require preg_replace( '/Tool/' ,'' , dirname(__FILE__)) . 'Source/PHPMailer/PHPmailer.php';
			$mail = new PHPMailer();
			$mail->IsSMTP();
			$mail->CharSet = 'utf-8';
			$mail->SMTPAuth = true;
			$mail->Port = 25;
			$mail->Host = "smtp.exmail.qq.com";
			$mail->From = "sys1@bijiaosuo.com";
			$mail->Username = "sys1@bijiaosuo.com";
			$mail->Password = "nO2jvv2AV878";
			$mail->FromName = "币交所";
			$mail->IsHTML(true);
		}
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
        if(is_array($pAddress)){
            foreach($pAddress as $v){
		        $mail->AddAddress($v);
            }
            unset($v);
        } else {
		    $mail->AddAddress($pAddress);
        }
		$pCcAddress && $mail->AddBCC($pCcAddress);
		$mail->Subject = $pSubject;
		$mail->MsgHTML(preg_replace('/\\\\/', '', $pBody));
		if($mail->Send()){
			return 1;
		}else{
			return $mail->ErrorInfo;
		}
	}

	/**
	 * 提示信息
	 * @param string $pMsg 信息
	 * @param bool $pUrl 跳转到
	 */
	static function showMsg($pMsg, $pUrl = false){
		is_array($pMsg) && $pMsg = join('\n', $pMsg);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        if('.' == $pUrl){
            $pUrl = $_SERVER['REQUEST_URI'];
        }
		echo '<script type="text/javascript">';
		if($pMsg) echo "alert('$pMsg');";
        if($pUrl){
            echo "self.location='{$pUrl}'";
        }elseif(empty($_SERVER['HTTP_REFERER'])){
            echo 'window.history.back(-1);';
        }else{
            echo "self.location='{$_SERVER['HTTP_REFERER']}';";
        }
		exit('</script>');
	}

	/**
	 * AJAX返回
	 *
	 * @param string $pMsg 提示信息
	 * @param int $pStatus 返回状态
	 * @param mixed $pData 要返回的数据
	 * @param string $pStatus ajax返回类型
	 */
	static function ajaxMsg($pMsg = '', $pStatus = 0, $pData = '', $pType = 'json'){
		# 信息
		$tResult = array('status' => $pStatus, 'msg' => $pMsg, 'data' => $pData);
		# 格式
		'json' == $pType && exit(json_encode($tResult));
		'xml' == $pType && exit(xml_encode($tResult));
		'eval' == $pType && exit($pData);
	}

    /**
     * 邮件模版赋值
     *
     */
    static function emailTemplate($pData , $pTemplatename){
        $pDir = preg_replace('/library\/Tool/' , '' , dirname(__FILE__));
        $pTemplatedir = $pDir . 'views/'.LANG.'/email_template/' . $pTemplatename . '.phtml';
        if(!is_file($pTemplatedir)){
            return false;
        }
        $pHtml = file_get_contents($pTemplatedir);

        $pKeys = array_keys($pData);
        if(!count($pKeys)){ return false;}

        foreach($pKeys as $pKey){
           $pHtml = preg_replace('/{'.$pKey.'}/' , $pData[$pKey] , $pHtml);
        }
        return $pHtml;
    }
	public static function isMobile(){
		$useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';
		function CheckSubstrs($substrs,$text){
			foreach($substrs as $substr)
				if(false!==strpos($text,$substr)){
					return true;
				}
				return false;
		}
		$mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
		$mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

		$found_mobile=CheckSubstrs($mobile_os_list,$useragent_commentsblock) ||
				  CheckSubstrs($mobile_token_list,$useragent);

		if ($found_mobile){
			return true;
		}else{
			return false;
		}
    }

    /**
    * 浏览器友好的变量输出
    * @param mixed $var 变量
    * @param boolean $echo 是否输出 默认为True 如果为false 则返回输出字符串
    * @param string $label 标签 默认为空
    * @param boolean $strict 是否严谨 默认为true
    * @return void|string
    */
    static function dump($var, $echo=true, $label=null, $strict=true) {
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            } else {
                $output = $label . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
}
