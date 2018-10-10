<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

	/**
	 * 把配置存到注册表
	 */
	function _initConfig(){
		define('LANG', isset($_COOKIE['LANG']) ? $_COOKIE['LANG'] : 'cn');
		Yaf_Registry::set("config", $config = Yaf_Application::app()->getConfig());
		define('PATH_APP', $config->application->directory);
		define('PATH_TPL', PATH_APP . '/views/'.LANG);
		define('USER_IP', Tool_Fnc::realip());
		//session 和 cookie 过期时间
		define('SESSION_TIME', Yaf_Registry::get("config")->session_time);
		define('PHONE_TIME', 90);
	}

	/**
	* route
	*/
	function _initRoute(){
		# 路由
		$router = Yaf_Dispatcher::getInstance()->getRouter();
		# 静态页面
		$router->addRoute('html', new Yaf_Route_Regex('/([a-z]+)\.html$/', array('controller' => 'Index', 'action' => 'html'), array(1 => 'page')));
		# PHP
		$router->addRoute('json', new Yaf_Route_Rewrite('json/:name', array('controller' => 'Market', 'action' => 'output')));
		$router->addRoute('json1', new Yaf_Route_Rewrite('dayprice/:name', array('controller' => 'Market', 'action' => 'dayprice')));
		$router->addRoute('json2', new Yaf_Route_Rewrite('ybc_hour_price', array('controller' => 'Market', 'action' => 'output')));
		$router->addRoute('reset', new Yaf_Route_Rewrite('reset', array('controller' => 'User', 'action' => 'reset')));
		$router->addRoute('trade', new Yaf_Route_Rewrite('trade/:name', array('controller' => 'Trade', 'action' => 'index')));
		$router->addRoute('fulltrade', new Yaf_Route_Rewrite('fulltrade/:name', array('controller' => 'Trade', 'action' => 'fullTrade')));
	}

	/**
	 * 采用布局
	 * @param Yaf_Dispatcher $dispatcher
	 */
	function _initLayout(Yaf_Dispatcher $dispatcher){
		define('REDIRECT_URL', empty($_SERVER['REQUEST_URI'])? '/': strtolower($_SERVER['REQUEST_URI']));
        if(false !== strpos(REDIRECT_URL, '/user_emailverify')){ return ;}
		if(false !== strpos(REDIRECT_URL, '/user_')){
			$layout = new LayoutPlugin('user/tpl.layout.phtml');
			Yaf_Registry::set('layout', $layout);
			$dispatcher->registerPlugin($layout);
        }
	}
}

