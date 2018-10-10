<?php
class UserLoginModel extends Orm_Base{
	public $table = 'user_login';
	public $field = array(
		'id' => array('type' => "int(11) unsigned", 'comment' => 'id'),
		'uid' => array('type' => "int(11) unsigned", 'comment' => 'UID'),
		'updated' => array('type' => "int(11) unsigned", 'comment' => ''),
		'fqy' => array('type' => "int(11) unsigned", 'comment' => ''),
		'updateip' => array('type' => "char(15) unsigned", 'comment' => '')
	);
	public $pk = 'id';
}
