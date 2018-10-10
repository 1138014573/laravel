<?php
class Tool_Log
{
    /**
     * 读日志
     */
    public static function rlog($tip){
        $file = Yaf_Registry::get("config")->log->dir."{$tip}.log";
        if(!file_exists($file)){
            return '';
        }
        return file_get_contents($file);
    }
    /**
     * 写日志
     */
    public static function wlog($msg, $tip, $repeat=false){
        $file = Yaf_Registry::get("config")->log->dir."{$tip}.log";
        if(!$repeat){
            $content = self::rlog($tip);
            if(!empty($content) && false !== strpos($content, $msg)){
                return false;
            }
        }
        $msg = date('[H:i:s]')." {$msg} \n";
        file_put_contents($file, $msg, FILE_APPEND);
        return true;
    }
}
