<?php
/**
 * 角色管理
 */
class Manage_RoleController extends Ctrl_Admin {
	protected $_auth = 3;

	public function indexAction() {
		if( !in_array($_SESSION['user']['uid'], Manage_IndexController::$arr['admin']['角色管理']) ){
			exit('权限不足');
		}

		$this->_list('role', 'status=0OB=role_id DESC');
	}

	/**
	 * 创建角色
	 *
	 */
	public function createAction() {
		if ($this->getRequest()->isPost()) {
			$role_name	= trim($_POST['role_name']);
			$explains	= trim($_POST['explains']);
			// $status		= (int)trim($_POST['status']);
			$created	= $_SERVER['REQUEST_TIME'];

			$data		= array(
					'role_name'=>$role_name,
					'explains'=>$explains,
					'status'=>0,
					'created'=>$created,
			);

			$role_mo	= new RoleModel;
			$role_id	= $role_mo->insert($data);
			if (!$role_id) {
				return $this->showMsg('添加失败', '/manage_role/index');
			}

			return $this->showMsg('添加成功', '/manage_role/index');
		}
	}


	/**
	 * 修改角色
	 *
	 */
	public function pushAction() {
		if ($this->getRequest()->isPost()) {
			$role_id	= (int)$_POST['role_id'];
			$status		= (int)trim($_POST['status']);
			$status		= ($status+1)%2;
			$data		= array();

			if($role_id<1) {
				return Tool_Response::show(0, '', '非法请求');
			}

			$data		= array(
					'role_id'=>$role_id,
					'status'=>$status,
			);

			$role_mo	= new RoleModel;
			if ( !$role_mo->update($data) ) {
				return Tool_Response::show(1, '', '更新失败');
			}

			return Tool_Response::show(0, '', '更新成功');
		}
	}


	/**
	 * 绑定用户
	 *
	 */
	public function bindAction() {
		if ($this->getRequest()->isPost()) {
			$role_id	= (int)$_POST['role_id'];
			$uid		= (int)$_POST['uid'];
			$admin_id	= $this->mCurUser['uid'];
			$created	= $_SERVER['REQUEST_TIME'];
			$updated	= $_SERVER['REQUEST_TIME'];

			$u_mo 	= new UserModel();
			$tmp 	= $u_mo->where("uid={$uid} and role='admin'")->fOne('uid');
			if (!$tmp) {
				return Tool_Response::show(1, '', '该用户不是管理员');
			}
			$r_mo 	= new RoleModel;
			$rid 	= $r_mo->where("role_id={$role_id} and status=0")->fOne('role_id');
			if (!$rid) {
				return Tool_Response::show(1, '', '角色不存在或者已被关闭');
			}
			# 判断是否已绑定过
			$ur_mo	= new UserRoleModel;
			$where	= "uid={$uid} and role_id={$role_id} and is_bind=0";
			$is_bind= $ur_mo->where($where)->fOne('id');
			if ($is_bind) {
				return Tool_Response::show(1, '', '已经绑定过该用户');
			}

			$data		= array(
					'uid'=>$uid,
					'role_id'=>$role_id,
					'admin_id'=>$admin_id,
					'created'=>$created,
					'updated'=>$updated,
			);

			$ur_id	= $ur_mo->insert($data);
			if (!$ur_id) {
				return Tool_Response::show(1, '', '绑定失败');
			}

			return Tool_Response::show(0, '', '绑定成功');
		}
	}


	/**
	 * 解除绑定
	 *
	 */
	public function unbindAction() {
		if ($this->getRequest()->isPost()) {
			$role_id	= (int)$_POST['role_id'];
			$uid		= (int)$_POST['uid'];
			$admin_id	= $this->mCurUser['uid'];
			$updated	= $_SERVER['REQUEST_TIME'];

			$u_mo 	= new UserModel();
			$tmp 	= $u_mo->where("uid={$uid} and role='admin'")->fOne('uid');
			if (!$tmp) {
				return Tool_Response::show(1, '', '该用户不是管理员');
			}
			$r_mo 	= new RoleModel;
			$rid 	= $r_mo->where("role_id={$role_id} and status=0")->fOne('role_id');
			if (!$rid) {
				return Tool_Response::show(1, '', '角色不存在或者已被关闭');
			}
			# 判断是否已绑定过
			$ur_mo	= new UserRoleModel;
			$where	= "uid={$uid} and role_id={$role_id} and is_bind=0";
			$id 	= $ur_mo->where($where)->fOne('id');
			if (!$id) {
				return Tool_Response::show(1, '', '未绑定过该用户');
			}

			$data	= array(
					'id'=>$id,
					'is_bind'=>1,
					'admin_id'=>$admin_id,
					'updated'=>$updated,
				);

			if ( !$ur_mo->update($data) ) {
				return Tool_Response::show(1, '', '解绑失败');
			}

			return Tool_Response::show(0, '', '解除绑定');
		}
	}


	# 查看改角色下权限
	public function rightsAction() {
		$role_id = (int)$_POST['role_id'];
		$sql = "select rights.rights_id as rights_id,rights.rights_name as rights_name from role_rights,rights where role_rights.role_id={$role_id} and role_rights.rights_id=rights.rights_id";
		$r_mo = new RoleModel;
		$data = $r_mo->query($sql);
		if(empty($data)) {
			$data = array();
		}

		return Tool_Response::show(0, $data, '成功');
	}


	/**
	 * 设置权限
	 */
	public function newroleAction($role_id, $hasSet = 2){
		$role_id = (int)$role_id;

		# 查询角色是否存在
		$role_mo = new RoleModel;
		if( !$roleInfo = $role_mo->where("role_id = {$role_id}")->fRow() ){
			$this->showMsg('参数错误');
		}

		$this->assign('role_id', $role_id);
		$this->assign('hasSet', $hasSet);
		$this->assign('roleInfo', $roleInfo);

		$rights_mo = new RoleRightsModel;
		# 查询权限详情
		if( $hasSet == 1 ){
			$rightsData = $rights_mo->where("role_id = {$role_id}")->fRow();
			$rightsData['rights'] = explode(',', $rightsData['content']);
			// Tool_Fnc::dump($rightsData);exit();
			$this->assign('rights', $rightsData);
		}

		if('POST' == $_SERVER['REQUEST_METHOD']){
			if( !$_POST['rights'] ){
				$this->showMsg('请选择权限');
			}

			$act = ($_POST['hasSet'] == 1) ? 'update' : 'insert';

			if( $act == 'update' ){
				$updateData = array(
					'id' => $rightsData['id'],
					'content' => implode(',', $_POST['rights']),
					'admin_id' => $this->mCurUser['uid'],
					'updated' => time()
				);

				if( !$rights_mo->update($updateData) ){
					$this->showMsg('更新失败，请重新操作');
				}
			}else{
				$newData = array(
					'role_id' => trim($_POST['role_id']),
					'content' => implode(',', $_POST['rights']),
					'admin_id' => $this->mCurUser['uid'],
					'created' => time()
				);

				if( !$rights_mo->insert($newData) ){
					$this->showMsg('添加失败，请重新操作');
				}
			}

			$this->showMsg('操作成功', '/manage_role/index');
		}
	}

}
