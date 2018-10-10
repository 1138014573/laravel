<?php
/**
 * 权限管理
 */
class Manage_RightsController extends Ctrl_Admin {
	public function indexAction() {
		$where = '';
		if(isset($_GET['rights_id'])) {
			$rights_id = (int)$_GET['rights_id'];
			$where = "rights_id={$rights_id}";
		}
		$this->_list('rights', $where.'OB=rights_id DESC');
	}
	
	/**
	 * 添加权限
	 *
	 */
	public function createAction() {
		if ($this->getRequest()->isPost()) {
			$rights_name= Tool_Str::safestr(trim($_POST['rights_name']));
			$status		= (int)trim($_POST['status']);
			$code		= Tool_Str::safestr(strtolower(trim($_POST['code'])));
			$readme		= Tool_Str::safestr(trim($_POST['readme']));
			$created	= $_SERVER['REQUEST_TIME'];
			$updated	= $_SERVER['REQUEST_TIME'];
			if (!$code) {
				return $this->showMsg('权限编码不能为空', '/manage_rights/index');
			}
			if (!$rights_name) {
				return $this->showMsg('权限名称不能为空', '/manage_rights/index');
			}
			$data	= array(
					'rights_name'=>$rights_name,
					'code'=>$code,
					'status'=>$status,
					'readme'=>$readme,
					'created'=>$created,
					'updated'=>$updated,
			);

			$rights_mo	= new RightsModel;
			$rights_id	= $rights_mo->insert($data);
			if (!$rights_id) {
				return $this->showMsg('添加失败,请检查是否重复添加', '/manage_rights/index');
			}
			
			return $this->showMsg('添加成功', '/manage_rights/index');
		}
	}


	/**
	 * 修改权限
	 *
	 */
	public function pushAction() {
		if ($this->getRequest()->isPost()) {
			$rights_id	= (int)$_POST['rights_id'];
			$status		= (int)trim($_POST['status']);
			$updated	= $_SERVER['REQUEST_TIME'];

			$data		= array(
					'rights_id'=>$rights_id,
					'status'=>$status,
					'updated'=>$updated,
			);

			$rights_mo	= new RightsModel;
			if ( !$rights_mo->update($data) ) {
				return Tool_Response::show(1, '', '更新失败');
			}
			
			return Tool_Response::show(0, '', '更新成功');
		}
	}


	/**
	 * 关联角色
	 *
	 */
	public function bindAction() {
		if ($this->getRequest()->isPost()) {
			$role_id	= (int)$_POST['role_id'];
			$code		= trim($_POST['code']);
			$admin_id	= $this->mCurUser['uid'];
			$created	= $_SERVER['REQUEST_TIME'];
			$updated	= $_SERVER['REQUEST_TIME'];
			$r_mo 		= new RightsModel;
			$rights_id  = $r_mo->where("code='{$code}' and status=0")->fOne('rights_id');
			if (!$rights_id) {
				return Tool_Response::show(1, '', '权限编码不存在');
			}

			# 判断是否已绑定过
			$rr_mo	= new RoleRightsModel;
			$where	= "rights_id={$rights_id} and role_id={$role_id} and is_delete=0";
			$is_bind= $rr_mo->where($where)->fOne('id');
			if ($is_bind) {
				return Tool_Response::show(1, '', '已经绑定过该用户');
			}
			
			$data	= array(
					'rights_id'=>$rights_id,
					'role_id'=>$role_id,
					'admin_id'=>$admin_id,
					'created'=>$created,
					'updated'=>$updated,
			);

			$rr_id	= $rr_mo->insert($data);
			if (!$rr_id) {
				return Tool_Response::show(1, '', '绑定失败');
			}

			return Tool_Response::show(0, '', '绑定成功');
		}
	}


	/**
	 * 解除关联
	 *
	 */
	public function unbindAction() {
		if ($this->getRequest()->isPost()) {
			$role_id	= (int)$_POST['role_id'];
			$code	= (int)$_POST['code'];
			$admin_id	= $this->mCurUser['uid'];
			$updated	= $_SERVER['REQUEST_TIME'];

			$rights_mo = new RightsModel;
			$rights_id = $rights_mo->where("code='{$code}'")->fOne('rights_id');
			# 判断是否已绑定过
			$rr_mo	= new RoleRightsModel;
			$where	= "rights_id={$rights_id} and role_id={$role_id} and is_delete=0";
			$id		= $rr_mo->where($where)->fOne('rights_id');
			if (!$id) {
				return Tool_Response::show(1, '', '不存在的绑定关系');
			}

			$data	= array(
					'id'=>$id,
					'is_delete'=>1,
					'admin_id'=>$admin_id,
					'updated'=>$updated,
				);

			if ( !$rr_mo->update($data) ) {
				return Tool_Response::show(1, '', '解绑失败');
			}
			
			return Tool_Response::show(0, '', '解除绑定');
		}
	}		

}
