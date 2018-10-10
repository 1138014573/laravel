<?php

class ErrorController extends yaf_controller_abstract {
	public function errorAction($exception) {
		if(empty($_GET['yafphp_errormsg'])) {
			self::page403();
		}
		$this->getView()->assign("exception", $exception);
	}

	static function page403(){
		header("HTTP/1.1 403 Forbidden");
		empty($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] = '/';
		if(false !== strpos($_SERVER['REDIRECT_URL'], '<')){
			$_SERVER['REDIRECT_URL'] = '/';
		}
		exit("<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">
<html><head>
<title>403 Forbidden</title>
</head><body>
<h1>Forbidden</h1>
<p>You don't have permission to access {$_SERVER['REDIRECT_URL']}
on this server.</p>
<hr>
</body></html>
");
	}
}
