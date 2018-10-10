<?php
class Manage_SettingController extends Ctrl_Admin {

  /**
   * 分类 列表
   */
  public function categoryAction() {
    $this->assign('datas', Tool_Fnc::catdata());
  }

  /**
   * 分类 添加、编辑
   */
  public function categorysaveAction() {
    $tId = $this->_save($tMO = new CategoryModel, $_POST);
    if (false === $tId) {
      $this->assign('data', $_POST);
      $this->assign('msg', array('添加失败，可能您的英文名有重复！', 'error'));
    }
    if (!$tId) return;
    # 处理 id1-5
    $tData = array('cid'=>$tId, 'id1'=>$tId, 'id2'=>0, 'id3'=>0, 'id4'=>0, 'id5'=>0);
    if ($_POST['pid'] && $tPC = $tMO->fRow($_POST['pid'])) {
      for ($i = 1; $i < 6; $i++) {
        if($tPC['id'.$i]) $tData['id'.$i] = $tPC['id'.$i];
        else {
          $tData['id'.$i] = $tId;
          break;
        }
      }
    }
    $tMO->update($tData);
    $this->assign('msg', array('操作成功，分类信息：'.$tId.','.$_POST['py'].','.$_POST['name'], 'ok'));
    $_GET['pid'] = $_POST['pid'];
		# 写入缓存
		$this->_category_cache($tId, $tMO);
  }

  /**
   * 分类 删除
   */
  public function categorydelAction($id = 0) {
		# 内容是否存在
		$tCategory = new CategoryModel($id);
		if(!$tCategory->py){
			$this->showMsg('删除错误');
		}
		# 删除缓存
		$tMem = Cache_Redis::instance();
		$tMem->hDel('category', $id);
		$tMem->hDel('cgpy', $tCategory->py);
		# 删除DB内容
		$this->_del('category', $id);
  }

	/**
	 * 分类 ajax保存
	 */
	public function categoryajaxsaveAction() {
		if ('POST' == $_SERVER['REQUEST_METHOD']) {
			$tMO = new CategoryModel();
			$tMO->update($_POST);
			# 写入缓存
			$this->_category_cache($_POST['cid'], $tMO);
		}
		exit;
	}

	/**
	 * 写入分类缓存
	 * @param $pId
	 */
	private function _category_cache($pId, $pMO = false){
		$pMO || $pMO = new CategoryModel();
		$tMem = Cache_Redis::instance();
		$tData = json_encode($tCategory = $pMO->fRow($pId));
		$tMem->hset('category', $pId, $tData);
		$tMem->hset('cgpy', $tCategory['py'], $tData);
	}
}