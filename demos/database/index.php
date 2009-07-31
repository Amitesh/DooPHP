<?php
include './protected/config/common.conf.php';
include './protected/config/routes.conf.php';
include './protected/config/db.conf.php';

include $config['BASE_PATH'].'Doo.php';
include $config['BASE_PATH'].'app/DooConfig.php';

Doo::conf()->set($config);

//database usage
Doo::db()->setMap($dbmap);
Doo::db()->setDb($dbconfig, $config['APP_MODE']);
Doo::db()->sql_tracking = true;

Doo::app()->route = $route;
Doo::app()->run();
?>
