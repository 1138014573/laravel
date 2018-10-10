<?php
class NewsModel extends Orm_Base{
	public $table = 'news';
	public $field = array(
		'id' => array('type' => "int(3) unsigned", 'comment' => 'id'),
		'title' => array('type' => "varchar(100)", 'comment' => '主题'),
		'content' => array('type' => "text", 'comment' => '邮箱'),
	    'receive' => array('type' => "varchar(50)", 'comment' => '终端类型'),
		'created' => array('type' => "int(10)", 'comment' => '发布时间'),
	    'expired' => array('type' => "int(10)", 'comment'=> '过期时间'),
	    'is_new' => array('type' => "smallint(2)", 'comment' => '是否新发布'),
	    'sort' => array('type' => "smallint(2)", 'comment' => '排序'),
	    'category' => array('type' => "tinyint(2)", 'comment' => '新闻类型')
	);
	public $pk = 'id';

}
