<?php
/**
 * 文章管理
 */
class Manage_ArticleController extends Ctrl_Admin {
	public function indexAction() {
	
	}

	
	/**
	* 文章列表
	*/
	public function listsAction() {
		$article_mo = new ArticleModel();

		if(isset($_GET['ac'])) {
			$id = trim($_GET['id']);
			$ac = trim($_GET['ac']);
			switch ($ac) {
				case 'rm':
					$sql = 'update article set is_delete=1 where id=' . $id;
					$article_mo->query($sql);
					break;
				case 'orderno':
					$orderno = trim($_POST['val']);
					$sql = 'update article set orderno='.$orderno.' where id=' . $id;
					$article_mo->query($sql);
				
				default:
					# code...
					break;
			}

		}

		$this->_list('article', 'OB=orderno DESC &is_delete=0');
		$categorys=Article_CategoryModel::getCategorys();
		$this->assign('categorys', $categorys);
	}

	/**
	* 修改文章页面
	*/
	public function modifyAction() {

		if(isset($_POST)) {
			$id = trim($_POST['id']);
			$opt = (int)($_POST['opt'] ? $_POST['opt'] : 0);
			$title = trim($_POST['title']);
			$category = (int)trim($_POST['category_id']);
			$content = trim($_POST['content']);

			$article_mo = new ArticleModel();
			$sql = 'update article set title="'. $title .'",is_top=' . $opt . ', category_id=' . $category . ',content="' . $content . '"  where id=' . $id;
			$article_mo->query($sql);
		}


		$id = isset($_GET['id']) ? $_GET['id'] : $id;

		$article_mo = new ArticleModel();
		$sql = 'select a.*,ac.name as category from article as a,article_category as ac where a.id=' . $id . ' and a.category_id=ac.id limit 1';
		$artArr= $article_mo->query($sql);
		$this->assign('data', $artArr[0]);
		$categorys=Article_CategoryModel::getCategorys();
		$this->assign('categorys', $categorys);
	}


	/**
	* 删除文章
	*/
	public function rmAction() {

		if(isset($_GET)) {
			$id = trim($_GET['id']);
			$article_mo = new ArticleModel();
			$sql = 'update article set is_delete=1 where id=' . $id;
			$article_mo->query($sql);
		}


	}


	/**
	* 发布文章
	*/
	public function releaseAction() {
		if($_POST) {
			$_POST['opt'] = $_POST['opt'] ? $_POST['opt'] : 0;
			$data = array(
				'category_id'=>(int)trim($_POST['category_id']),
				'title'=>trim($_POST['title']),
				'content'=>trim($_POST['content']),
				'is_top'=>trim($_POST['opt']),
				'orderno'=>0,
				'created'=>time(),
				);
			$aMo = new ArticleModel();
			$aMo->insert($data);
		}

		$categorys=Article_CategoryModel::getCategorys();
		$this->assign('categorys', $categorys);
	}

	/**
	* 文章分类列表
	*/
	public function categoriesAction() {
		if(isset($_GET['ac'])) {
			$id = trim($_GET['id']);
			$ac = trim($_GET['ac']);
			$article_mo = new ArticleModel();
			$ac_mo = new Article_CategoryModel();
			switch ($ac) {
				case 'rm':
					$sql1 = 'select count(id) as num from article where is_delete=0 and category_id=' . $id;
					$res  = $article_mo->query($sql1);
					if($res[0]['num']>0) {
						$this->showMsg('该分类下有文章不能删除');
					} else {
						$sql2 = 'update article_category set is_delete=1 where id=' . $id;
						if($ac_mo->query($sql2) ) {
							$this->showMsg('删除失败');
						}
					}

					
					break;
				
				default:
					# code...
					break;
			}

		}

		$this->_list('article_category', 'OB=id DESC &is_delete=0');
	}

	public function addCategoryAction() {
		$name = trim($_POST['name']);
		$ac_mo = new Article_CategoryModel();
		if( $ac_mo->insert(array('name'=>$name)) ) {
			exit(json_encode(array('code'=>0, 'msg'=>'添加成功')));
		} else {
			exit(json_encode(array('code'=>1, 'msg'=>'添加失败')));
		}

	}


}
