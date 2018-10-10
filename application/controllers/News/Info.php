<?php
/**
 * 用户相关管理
 * @role admin
 */
class News_infoController extends Ctrl_Admin {

  # 消息列表
  public function newsAction() {
  	# 查询栏目权限
	if( !$this->getAuth($_SESSION['user']['uid'], 'message_news') || !$this->getAuth($_SESSION['user']['uid'], 'message_news_look')){
		exit('权限不足');
	}
	# 按钮权限
	$btnArr = array('message_news_edit', 'message_news_del');
	foreach ($btnArr as $vb) {
		$btnAuth[$vb] = $this->getAuth($_SESSION['user']['uid'], $vb);
	}
	$this->assign('btnAuth', $btnAuth);


	//exit($this->getRequest()->getMethod());
	if($this->getRequest()->getMethod() == 'POST'){
		if(empty($_POST['id'])){
			exit(json_encode(array('err'=>0,'msg'=>'id为空')));
		}
		if($_POST['val'] == ""){
			exit(json_encode(array('err'=>0,'msg'=>'value为空')));
		}
		$sort = $_POST['val'];
		$tMo = new NewsModel;
		if($tMo->exec("update {$tMo->table} set sort={$sort} where id={$_POST['id']}")){
			exit(json_encode(array('err'=>1,'msg'=>'ok')));
		}
	}

   $this->_list('news', 'OB=id DESC');
  }

  # 发布消息
 public function releaseAction(){
 	# 查询栏目权限
	if( !$this->getAuth($_SESSION['user']['uid'], 'message_release') ){
		exit('权限不足');
	}


  	$tMO = new NewsModel;

  	if(!empty($_GET['id']))
  	{
  		if($pNews = $tMO->lock()->fRow($_GET['id'])){
  			$this->assign('data', $pNews);
  		}
  	}
  	if ('POST' == $_SERVER['REQUEST_METHOD']) {
			# 验证
			if(empty($_POST['title'])){
				$this->showMsg('主题不能为空');
			}
			if(empty($_POST['content'])){
				$this->showMsg('内容不能为空');
			}
			if(empty($_POST['category'])){
				$this->showMsg('新闻分类不能为空');
			}
			$content = $_POST['content'];
			//exit;
			$act = (!empty($_POST['id']))?'update':'insert';
			$receive = implode(',',$_POST['receive']);
			$tNews = array('id'=>$_POST['id'],'title'=>$_POST['title'], 'content'=>$content,'receive'=>$receive,'created'=>time(),'is_new'=>'Y','category'=>$_POST['category']);
  		    if(!$tMO->$act($tNews)){
				$this->showMsg('插入记录出错,请重新发布');
			}
		    $this->showMsg('操作成功', '/news_info/news');
		}

  }


  #删除消息
  public function newsdelAction()
  {
  	$id = $_GET['id'];
  	$tMo = new NewsModel;
  	if($tMo->del('news',$id)){
  		$this->showMsg('已成功删除');
  	}
  }

	public function to2toAction() {
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'message_to2to') ){
			exit('权限不足');
		}


        $userlevel = new UserLevelModel();
        $level = $userlevel->fList();
        $this->assign('lv', $level);
		$w = '';
        if($this->getRequest()->getMethod()=="POST"){
            $user = new UserModel();
            $sql = "select uid from user";
            $where = array();
            if(isset($_POST['title']) && !empty($_POST['title'])){
                $title = $_POST['title'];
            }else{
                $this->showMsg("标题不能空");
            }
            if(isset($_POST['message']) && !empty($_POST['message'])){
                $message = $_POST['message'];
            }else{
                $this->showMsg("消息不能空");
            }
            if(isset($_POST['lv']) && $_POST['lv']!=''){
                $lv = $_POST['lv'];
                $where[]="credit>={$lv}";
				$w.="大于{$lv}积分，";
            }
            if(isset($_POST['ybc']) && !empty($_POST['ybc'])){
                $ybc = $_POST['ybc'];
                $where[]="ybc_over+ybc_lock>=1";
				$w.="大于1富途币，";
            }
            if(isset($_POST['rmb']) && !empty($_POST['rmb'])){
                $rmb = $_POST['rmb'];
                $where[]="rmb_over+rmb_lock>=100";
				$w.="大于100元人民币，";
            }
            if(isset($_POST['uid']) && !empty($_POST['uid'])){
                $uid1 = $_POST['uid'];
				$w.="UID为{$uid1}，";
                if(strpos($uid1, '|')){
                    $uid = explode('|', $uid1);
                }else{
					$uid[] = $uid1;
				}
            }
			if(isset($_POST['user']) && $_POST['user']==0){
				if(!empty($where)){
					$wsql = implode(' and ',$where);
					$sql = $sql." where ".$wsql;
					$userdata = $user->query($sql);
					$uidarr= array();
					if(!empty($userdata)){
						foreach($userdata as $val){
							$uidarr[]=$val['uid'];
						}
					}
					if(isset($uid) && !empty($uid)){
						if(is_array($uid)){
							foreach($uid as $vu){
								 if(!in_array($vu, $uidarr)){
									$uidarr[]=$vu;
								 }
							}
						}else{
							if(!in_array($uid, $uidarr)){
								$uidarr[]=$uid;
							}
						}
					}
					$w = rtrim($w,'，').'的用户';
					$data = array('title'=>$title, 'message'=>$message,'where'=>$w, 'ctime'=>$_SERVER['REQUEST_TIME'],'utime'=>$_SERVER['REQUEST_TIME']);
				}elseif(isset($uid) && !empty($uid)){
					$w = rtrim($w,'，').'的用户';
					$data = array('title'=>$title, 'message'=>$message,'where'=>$w, 'ctime'=>$_SERVER['REQUEST_TIME'],'utime'=>$_SERVER['REQUEST_TIME']);
					$uidarr=$uid;
				}else{
					$this->showMsg('条件不能空');
				}
			}else if(isset($_POST['user']) && $_POST['user']==1){
				$userdata = $user->query($sql);
				$uidarr= array();
				if(!empty($userdata)){
					foreach($userdata as $val){
						$uidarr[]=$val['uid'];
					}
				}
				$data = array('title'=>$title, 'message'=>$message,'where'=>'全部用户', 'ctime'=>$_SERVER['REQUEST_TIME'],'utime'=>$_SERVER['REQUEST_TIME']);
			}
			$data['num'] = count($uidarr);
			$msg = new MessageModel();
			$msguid = new MessageUidModel();
			$id = $msg->insert($data);
			$allsql = '';
			if($id){
				foreach($uidarr as $uid){
					$allsql.="('',{$id},{$uid},0,{$_SERVER['REQUEST_TIME']},{$_SERVER['REQUEST_TIME']}),";
				}
				$msguid->exec("insert into {$msguid->table} values ".rtrim($allsql,',').';');
			}
			$this->showMsg('发送成功');
        }
    }
	public function messagelistAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'message_list') ){
			exit('权限不足');
		}


		$this->_list('message', 'OB=id DESC');
	}
	public function msglistAction(){
		# 查询栏目权限
		if( !$this->getAuth($_SESSION['user']['uid'], 'message_msglist') ){
			exit('权限不足');
		}


		$msg = new MessageModel();
		$sqlnum = "select count(*) num from message m, message_uid mu where mu.mid=m.id";
		if(isset($_GET['kw']) && !empty($_GET['kw'])){
			$sqlnum.=" and mu.".$_GET['field']."={$_GET['kw']}";
		}
		$num = $msg->fRow($sqlnum);
		$tPage = new Tool_Page($num['num'], 15);
		$sql = "select mu.id id,m.id mid, mu.uid uid, m.ctime ctime, mu.utime utime, mu.status status from message m, message_uid mu where mu.mid=m.id";
		if(isset($_GET['kw']) && !empty($_GET['kw'])){
			$sql.=" and mu.".$_GET['field']."={$_GET['kw']} order by id desc limit {$tPage->limit()}";
		}else{
			$sql.=" order by id desc limit {$tPage->limit()}";
		}
		$data = $msg->query($sql);
		$this->assign('datas', $data);
		$this->assign('pageinfo', $tPage->show());
	}

}
