<?php
class Tool_Str{
	static function safestr($pStr, $pDefault=false){
		if(!$pStr = htmlspecialchars($pStr)){
			return $pDefault;
		}
		return $pStr;
	}

	/**
	 * 替换危险字符串
	 *
	 * @param str $pStr 危险字符
	 * @param array $pTrans 自定义替换规则
	 * @return str 安全字符
	 */
	static function filter($pStr, $pTrans=array()){
		$tTrans = array("'"=>'', '"'=>'', '`'=>'', '\\'=>'', '<'=>'＜', '>'=>'＞');
		return strtr(trim($pStr), array_merge($tTrans, $pTrans));
	}

	/**
	 * 获得 KEY 对应的 数组值
	 *
	 * @param array $pArr
	 * @param str $pKey
	 * @param str $pDefault
	 */
	static function arr2str($pArr, $pKey, $pDefault=''){
		return isset($pArr[$pKey])? $pArr[$pKey]: $pDefault;
	}

	/**
	 * ID 转为 图片文件路径
	 * @param int $pId
	 * @return str
	 */
	static function id2path($pId){
		$tPid = str_pad($pId, 9, 0, 0);
		return array(substr($tPid, 0, 3).'/'.substr($tPid, 3, 3).'/', substr($tPid, 6));
	}

	/**
	 * 格式化数字
	 * @param $pNum
	 * @param int $pLen
	 * @param int $pRule 规则 0:四舍五入, 1:全入, 2:全舍
	 * @return int | float
	 */
	static function format($pNum, $pLen = 2, $pRule = 0){
		# 整数部分
        //$pNum   = str_replace(array('e','E','+'), '.', $pNum);
        $pNum = floatval($pNum);
		$tInt = intval($pNum);
		# 无小数直接返回
		if(!$tPos = strpos($pNum, '.')) return $tInt;#return $pNum;
		# 小数部分
		$tNum = substr($pNum, $tPos+1);
		# 指定长度
		$tReturn = (float)('0.'.substr($tNum, 0, $pLen));
		# 四舍五入
		if(((0 == $pRule) && (isset($tNum{$pLen}) && ($tNum{$pLen} > 4))) || ((1 == $pRule) && intval(substr($tNum, $pLen)))){
			$tReturn = (float)bcadd($tReturn, (float)('0.'.str_pad('', $pLen-1, 0).'1'), $pLen);
		}
		return bcadd($tInt, $tReturn, $pLen);
	}

	/**
	 * 生成key
     * 1 ybex转币
	 */
	static function generate_key($type, $uid, $randstr='Ybc!1900#$@'){
        if(!$type = intval($type)){
            return FALSE;
        }
        $result = '';
        switch($type){
            case 1:
                $result = md5($randstr.$uid.'uid2');
                break;
            default:
                break;
        }
        return $result;
	}

	 /**
     * 字符串截取，支持中文和其他编码
     * @param  [string]  $str     [字符串]
     * @param  integer $start   [起始位置]
     * @param  integer $length  [截取长度]
     * @param  string  $charset [字符串编码]
     * @param  boolean $suffix  [是否有省略号]
     * @return [type]           [description]
     */
    static function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true) 
    {
        if (function_exists("mb_substr")) {
            return mb_substr($str, $start, $length, $charset);
        } elseif (function_exists('iconv_substr')) {
            return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if ($suffix) {
            return $slice."…";
        }
        return $slice;
    }

}
