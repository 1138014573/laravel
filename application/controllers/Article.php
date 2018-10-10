<?php
/**
 * 文章管理
 */
class ArticleController extends Ctrl_Base {
	protected $_auth = 1;
	public function indexAction() {
		$categorys=Article_CategoryModel::getCategorys();
		$this->assign('categorys', $categorys);

		$where='';
		$cname='所有文章';
		if(isset($_GET['cid'])) {
			$id 	= (int)$_GET['cid'];
			$where 	= ' & category_id=' . $id;
			foreach ($categorys as $value) {
				if($value['id'] == $id) {
					$cname 	= $value['name'];
				}
			}
			
		}

		$this->assign('cname', $cname);
		$this->_list('article', 'OB=is_top DESC,orderno DESC,created DESC &is_delete=0' . $where);
	}


	public function detailAction() {
		$categorys=Article_CategoryModel::getCategorys();
		$this->assign('categorys', $categorys);

		$id= (int)$_GET['id'];
		$article_mo = new ArticleModel();
		$row = $article_mo->where("id={$id} and is_delete=0")->fRow();
		if(empty($row)) {
			$this->showMsg('文章不存在');
		} else {
			foreach ($categorys as $v) {
				if($v['id'] == $row['category_id']) {
					$row['category'] = $v['name'];
					break;
				}
			}
		}

		$this->assign('data', $row);
	}

}
