<?php
class Tool_Session {
    private $redis;
    private $expire_time = 30;

    public static $map = array(
        'session' => array('bijiaosuo.com'),
    );

    public function __construct(){
        $this->redis = Cache_Redis::instance('session');
		$this->expire_time = Yaf_Registry::get("config")->session_time;
        $handle = session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        session_start();
    }

    public function open($path, $name){
        return true;
    }

    public function close(){
        return true;
    }

    public function read($id){
        $val = $this->redis->get($id);
        if($val){
            return json_decode($val);
        } else {
            return '';
        }
    }

    public function write($id, $data){
        if(empty($data)){
            return false;
        }
        $data   = json_encode($data);
        if($this->redis->set($id, $data)){
            $this->redis->expire($id, $this->expire_time);
            return true;
        }
        return false;
    }

    public function destroy($id){
        if($this->redis->delete($id)){
            return true;
        }
        return false;
    }

    public function gc($lifetime){
        return true;
    }

    public function __destruct(){
        session_write_close();
    }

     /**
     * user余额，各平台刷新管理,标记需更新session
     *
     */
    public static function mark($uid)
    {
        /*if(session_id() && $uid == $_SESSION[$_COOKIE['PHPSESSID']]['uid']){
            $_SESSION[$_COOKIE['PHPSESSID']] = array_merge($_SESSION[$_COOKIE['PHPSESSID']], UserModel::getInstance()->getBalance($uid));
        }*/
        Cache_Redis::instance()->hSet('usersession', $uid, 1);
        Cache_Redis::instance()->hSet('msession', $uid, 1);
        return true;
    }
}
