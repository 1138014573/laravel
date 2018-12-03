<?php
# 全局
define("APPLICATION_PATH", realpath((phpversion() >= "5.3"? __DIR__: dirname(__FILE__)).'/../'));
date_default_timezone_set("Asia/Shanghai");
# 域名前缀(cdn)
function host(){return '';}
function host_img(){return '';}
function api_js(){return '';}

# 加载配置文件
$app = new Yaf_Application(APPLICATION_PATH . "/conf/application.ini", 'common');
$app->bootstrap()->run();

