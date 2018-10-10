<?php
class Manage_InfoController extends Ctrl_Admin{
	protected $disableAction = array('cancel', 'betcancel');
	protected $disableMethodPost = array('cardedit');

	//统计导出
	public function totallistAction(){
		if($this->getRequest()->getMethod() == 'POST'){
			$stime = isset($_POST['stime'])&&!empty($_POST['stime']) ? $_POST['stime'] : '';
			$etime = isset($_POST['etime'])&&!empty($_POST['etime']) ? $_POST['etime'] : '';
			if(!empty($stime) && !empty($etime)){
				$stime = strtotime($stime." 00:00:00");
				$etime = strtotime($etime." 23:59:59");
				for($time=$stime; $time<=$etime; $time+=3600*24){
					$stime1 = $time;
					$etime1 = $time + (3600*24 - 1);
					$tdata[] = $this->datestatis($stime1, $etime1);
				}
			}else{
				$this->showMsg("日期都不能为空");
			}
			header("Content-type:application/vnd.ms-excel");
			header("Content-Disposition:filename=每日统计".date('Y-m-d', $etime).".xls");
			$str = "统计日期\t人民币提现笔数\t富途币转出笔数\t富途币买入笔数\t富途币卖出笔数\t每天新注册人数\n";
			foreach($tdata as $v){
				$str .= $v;
			}
			exit($str);
		}
	}

	private function datestatis($stime, $etime){
		//每天注册量
		$user = new UserModel();
		$usernum = $user->fRow("select count(*) as num from {$user->table} where created between {$stime} and {$etime}");
		$usernum = !empty($usernum) ? $usernum : array('num' => 0);

		//提现
		$rmb = new Exchange_CnyModel();
		$rmbout = $rmb->fRow("select count(*) as num, sum(money) as total from {$rmb->table} where opt_type='out' and created between {$stime} and {$etime}");
		//$rmbout = !empty($rmbout) ? $rmbout : array('num' => 0, 'total' => 0);
		if(!empty($rmbout)){
			if(empty($rmbout['num'])){
				$rmbout['num'] = 0;
			}
			if(empty($rmbout['total'])){
				$rmbout['total'] = 0;
			}
		}
		//富途币转出
		$ybb = new Exchange_GocModel();
		$ybbout = $ybb->fRow("select count(*) as num, sum(number) as total from {$ybb->table} where opt_type='out' and created between {$stime} and {$etime}");
		//$ybbout = !empty($ybbout) ? $ybbout : array('num' => 0, 'total' => 0);
		if(!empty($ybbout)){
			if(empty($ybbout['num'])){
				$ybbout['num'] = 0;
			}
			if(empty($ybbout['total'])){
				$ybbout['total'] = 0;
			}
		}
		//富途币买入
		$trust = new Trust_YbcModel();
		$tin = $trust -> fRow("select count(*) as num from {$trust->table} where flag='buy' and created between {$stime} and {$etime}");
		$tin = !empty($tin) ? $tin : array('num' => 0);
		$tout = $trust -> fRow("select count(*) as num from {$trust->table} where flag='sale' and created between {$stime} and {$etime}");
		$tout = !empty($tout) ? $tout : array('num' => 0);
		$str = date('Y-m-d', $etime)."\t".$rmbout['num']."\t".$ybbout['num']."\t".$tin['num']."\t".
				$tout['num']."\t".$usernum['num']."\n";
		return $str;
	}
	//会员等级列表
	public function memberAction(){
		$this->_list('UserLevelLog', 'OB=id DESC');
	}
	//登陆日志
	public function userloginAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_userlogin') ){
			exit('权限不足');
		}


		$orm = new LoginLogModel();
		$d = $orm -> fRow("select count(*) as num from {$orm->table} where logout_time=0");
		$this->assign('num',$d['num']);
		$this->_list('LoginLog', 'L=15&OB=id DESC');
	}
	//添加黑名单ip
	public function addhipAction(){
		$data = IpDataModel::getdata(0);
		$this->assign('data', $data);
		if($this->getRequest()->isPost()){
			$data['status'] = 0;
			$data['uid'] = null;
			$data['auid'] = $this->mCurUser['uid'];
			$data['ip'] = trim($_POST['ip']);
			if(IpDataModel::addIp($data)){
				$this->showMsg("成功");
			}else{
				$this->showMsg("失败");
			}
		}
	}
	//添加白名单 ip
	public function addbipAction(){
		$data = IpDataModel::getdata(1);
		$this->assign('data', $data);
		if($this->getRequest()->isPost()){
			$data['status'] = 1;
			$data['auid'] = $this->mCurUser['uid'];
			$data['uid'] = trim($_POST['uid']);
			if(IpDataModel::addIp($data)){
				$this->showMsg("成功");
			}else{
				$this->showMsg("失败");
			}
		}
	}
	//短信记录查询
	public function msglistAction(){
		$msg = new Tool_Message('http://api.fissoft.com/pubservice/SMSAccountInfo');
		$msg = $msg->getmsmnum();
		$msg = json_decode($msg['Value'],true);
		$this->assign('num',$msg['SMSNum']);
		$this->_list('PhoneCode', 'L=15&OB=id DESC');
	}
	//平台每日统计记录
	public function loglistAction(){
		$this->_list('CountLog', 'L=15&OB=id DESC');
	}

    //非富途币短信记录查询
    public function getcodeAction(){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_getcode') ){
			exit('权限不足');
		}


        $message = '';
        $platform = '';
        $type = '';
        if(isset($_POST['phone']) && !empty($_POST['phone'])){
            $tMO = new Orm_base();
            $phone = $_POST['phone'];
            $data = $tMO->frow("select * from message_log where `phone` = '$phone' order by id desc limit 1");
            if(!empty($data)){
                $message = $data['content'];
                $platform = $data['platform'];
                $type = $data['type'];
            }
        }
        if($type == 1){
            $type = '短信验证码';
        }elseif($type == 2){
            $type = '语音验证码';
        }
        $this->assign('message', $message);
        $this->assign('platform', $platform);
        $this->assign('type', $type);
    }

    //转出币链接生成
    public function coinoutAction(){
    	# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'log_coinout') ){
			exit('权限不足');
		}


        $this->assign('ok_str', '');
        $this->assign('cancel_str', '');

        if( !empty($_POST['uid']) && !empty($_POST['coin']) && !empty($_POST['oid']) ){
            $uid = $_POST['uid'];
            $coin = $_POST['coin'];
            $oid = $_POST['oid'];
            $cMO = new Orm_Base();
            $data = $cMO->query("select * from session_id where uid = {$uid} and eid = {$oid} order by id desc limit 1");
            if($data){
            	$base_url = 'http://';
		        if(isset($_SERVER['HTTPS'])){
		            $base_url = 'https://';
		        }
		        $url = $base_url.$_SERVER['HTTP_HOST'];

                $ok_str = $url.'/user_exchange/coinoutconfirm/coin/'.$coin.'/k/'.$data[0]['ekey'];
                $cancel_str = $url.'/user_exchange/coinoutconfirm/coin/'.$coin.'/k/'.$data[0]['ekey'].'/cancel/1';

                $this->assign('ok_str', $ok_str);
                $this->assign('cancel_str', $cancel_str);
            }
        }
    }
}
