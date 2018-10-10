<?php
/**
 * 数据验证
 */
class Tool_Validate{

	/**
   * 验证 EMAIL
   */
	static function email($pStr){
		return preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/", $pStr);
	}

	/**
	 * 验证 数值
	 */
	static function int($pStr, $pMin = false, $pMax = false){
		if(!is_numeric($pStr)) return false;
		if(false !== $pMin && $pStr < $pMin) return false;
		if(false !== $pMax && $pStr > $pMax) return false;
		return true;
	}

	/**
	 * 验证 手机
	 */
	static function mo($pStr){
		if(11 != strlen($pStr)) return false;
		return preg_match("/13[0-9]{9}|15[0-9]{9}|18[0-9]{9}|147[0-9]{8}/", $pStr);
	}

    /**
     * 验证 身份证
     */
    static function identify($pStr){
        $id15   = '/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/';
        $id18   = '/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}/';
        if(preg_match($id15, $pStr)){
            return TRUE;
        } else if(preg_match($id18, $pStr)){
            return TRUE;
        }
        return FALSE;
    }

	/**
	 * 验证 用户名
	 */
	static function name($pStr){
		return preg_match("/^[0-9a-zA-Z\x80-\xff]+$/", $pStr);
	}

	/**
	 * 验证 字母数字
	 */
	static function az09($pStr){
		return preg_match("/^[0-9a-zA-Z_]+$/", $pStr);
	}

	/**
	 * 危险字符(XSS, 注入)
	 */
	static function safe($pStr){
		return preg_match("/^[0-9a-zA-Z\x80-\xff@_?&=.\-]+$/", $pStr);
	}
}
?>
