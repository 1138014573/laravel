<?php
/**
 * 管理员管理
 */
class Manage_AdminController extends Ctrl_Admin {
	public function indexAction() {
		if( !in_array($_SESSION['user']['uid'], Manage_IndexController::$arr['admin']['管理员']) ){
			exit('权限不足');
		}

		$user_mo = new UserModel;
		$datas   = $user_mo->where("role='admin'")->fList();

		# 查询管理员所属的角色
		$role_mo = new RoleModel;
		$user_role_mo = new UserRoleModel;
		foreach ($datas as &$v) {
			$role = $user_role_mo->where("uid = {$v['uid']}")->fRow();
			$v['role'] = $role ? $role['role_id'] : 0;
			if( $v['role'] ){
				$v['role_name'] = $role_mo->where("role_id = {$v['role']}")->fOne('role_name');
				$v['is_bind'] = $role['is_bind'];
				$v['user_role_id'] = $role['id'];
			}
		}

		$this->assign('datas', $datas);

		if('POST' == $_SERVER['REQUEST_METHOD']){
			$uid = trim($_POST['uid']);
			if( !$uid ){
				$this->showMsg('请输入用户ID');
			}
			if( !$uData = $user_mo->where("uid = {$uid}")->fRow() ){
				$this->showMsg('此用户不存在');
			}
			if( $uData['role'] == 'admin' ){
				$this->showMsg('此用户已经是管理员');
			}
			if( !$user_mo->update(array('uid'=>$uid, 'role'=>'admin')) ){
				$this->showMsg('操作失败，请重新操作');
			}
			$this->showMsg('添加管理员成功');
		}
	}


	/**
	 * 设置角色
	 */
	public function roleAction(){
		$role_mo = new RoleModel;
		$role_select = $role_mo->field('role_id,role_name')->where('status=0')->fList();
		$this->assign('role_select', $role_select);

		$uid = $this->getRequest()->get("uid", 0);
		$this->assign('uid', $uid);

		$user_role_mo = new UserRoleModel;
		$roleData = $user_role_mo->where("uid = {$uid}")->fRow();
		if( $roleData ){
			$this->assign('roleData', $roleData);
		}

		if('POST' == $_SERVER['REQUEST_METHOD']){
			if( !$_POST['role'] ){
				$this->showMsg('请选择角色');
			}

			$act = $roleData ? 'update' : 'insert';

			if( $act == 'update' ){
				$updateData = array(
					'id' => $roleData['id'],
					'role_id' => trim($_POST['role']),
					'admin_id' => $this->mCurUser['uid'],
					'updated' => time()
				);

				if( !$user_role_mo->update($updateData) ){
					$this->showMsg('更新失败，请重新操作');
				}
			}else{
				$newData = array(
					'uid' => $uid,
					'role_id' => trim($_POST['role']),
					'admin_id' => $this->mCurUser['uid'],
					'created' => time()
				);

				if( !$user_role_mo->insert($newData) ){
					$this->showMsg('添加失败，请重新操作');
				}
			}

			$this->showMsg('操作成功', '/manage_admin/index');
		}
	}

	/**
	 * 解除绑定，恢复绑定
	 */
	public function rolebindAction(){
		$id = $this->getRequest()->get("id", 0);
		$bind = $this->getRequest()->get("bind", 1);

		$data = array('id'=>$id, 'is_bind'=>$bind, 'updated'=>time());

		$user_role_mo = new UserRoleModel;
		if( !$user_role_mo->update($data) ){
			$this->showMsg('操作失败，请重新操作');
		}
		$this->showMsg('操作成功');
	}


}
