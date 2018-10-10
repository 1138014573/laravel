<?php
/**
 * 邮件认证
 */

class User_EmailverifyController extends Ctrl_Base{

    #普通用户
    protected $_auth = 3;

    public function indexAction(){
        $tMO = new EmailactivateModel;
        $uMO = new UserModel;
        $uData = $uMO->fRow('SELECT created,updated FROM user WHERE uid = ' . $this->mCurUser['uid'] . ' LIMIT 1');

        $pData = $tMO->field('activate_time')->where("uid={$this->mCurUser['uid']}")->fRow();
        if(!empty($pData['activate_time'])){
            Tool_Fnc::showMsg('激活成功' , '/'); 
        }
		$login = $uMO->fRow('select count(*) as num from user_login where uid='. $this->mCurUser['uid']);
        $pUsertype = 1;
        if(count($pData) != 0){
            (true === $pData['activate_time'])?Tool_Fnc::showMsg('' , '/user_index/'):'';
        }

        $pEmaillist = array(
            '163.com' => 'mail.163.com',
            '126.com' => 'www.126.com',
            'yeah.net' => 'www.yeah.net',
            'gmail.com' => 'www.gmail.com',
            'qq.com' => 'mail.qq.com',
            'sina.com' => 'mail.sina.com',
            'sohu.com' => 'mail.sohu.com',
            'sogou.com' => 'mail.sogou.com',
            'hotmail.com' => 'www.hotmail.com',
            'chinaren.com' => 'mail.chinaren.com',
            'aliyun.com' => 'mail.aliyun.com',
            'mail.foxmail.com' => 'mail.foxmail.com',
        );
        $pEmaillistkey = array_keys($pEmaillist);
        $pEmailsuffix = substr($this->mCurUser['email'],strpos($this->mCurUser['email'] , '@')+1);
        $pEmailurl = empty($pEmaillist[$pEmailsuffix])?'':$pEmaillist[$pEmailsuffix];
        
        $this->assign('pUsertype' , $pUsertype);
        $this->assign('pEmailurl' , $pEmailurl);
        $this->assign('mCurUser' , $this->mCurUser);
		$this->assign('ga', $tGA = Api_Google_Authenticator::getByUid($this->mCurUser['uid']));
    }
    
    #重新发送邮件
    public function retrysentAction(){
        
        $eMO = new EmailactivateModel;
        $uMO = new UserModel;

        $eData = $eMO->fRow('SELECT * FROM email_activate WHERE uid = ' . $this->mCurUser['uid']);
        if(!empty($eData['activate_time'])){Tool_Fnc::showMsg('邮箱邮箱已经被激活');}#激活成功
             
        //发送邮件时间判断    
        $pNowtime = time();
        if(isset($_SESSION['sentemail_time'])){
            $pTime = $pNowtime-$_SESSION['sentemail_time'];
            if($pTime <= 30){
               echo '请等30秒再试';exit;  
            }  
        }
        $_SESSION['sentemail_time'] = $pNowtime;

        $pTime = time();
        $uData = $uMO->fRow('SELECT created,name  FROM user WHERE uid = ' . $this->mCurUser['uid']);
        $pKey = Tool_Md5::emailActivateKey($this->mCurUser['email'] , $pTime);
        $pData = array(
            'uid' => $this->mCurUser['uid'],
            'email' => $this->mCurUser['email'],
            'reg_time' => $uData['created'],
            'senttime' => $pTime,
            'key' => $pKey, 
        ); 
        if(!count($eData)){
            $eMO->insert($pData);
        }else{
            $eMO->where("uid={$this->mCurUser['uid']}")->update(array("key"=>$pKey,"senttime"=>time()));
        }
        $pData['name'] = $uData['name'];
        $uMO->saveEmailRedis($pData);
        echo 'succeed';
    }
}
