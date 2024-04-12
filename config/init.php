<?php
/**
Конфигуратсионные файл
 * Определяем константы
 */
define("DEBUG", 1); //для отображениея ошибок в режиме разработчика(1) а промышленая эксплуататция(0)
define("ROOT", dirname(__DIR__)); // путь к корьню папки сайта
define("WWW", ROOT . '/public'); //указываем к папке public
define("APP", ROOT . '/app'); //указываем к папке app
define("CORE", ROOT . '/vendor/ishop/core');//указываем к ядру сайта core
define("LIBS", ROOT . '/vendor/ishop/core/libs');//указываем к папке libs
define("CACHE", ROOT . '/tmp/cache');//указываем к папке cache
define("CONF", ROOT . '/config');//указываем к папке config
define("LAYOUT", 'watches'); //константа для подключения шаблона сайта

//http://ishop.loc/public/index.php
$app_path = "http://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
//http://ishop.loc/public/
$app_path = preg_replace("#[^/]+$#", '', $app_path);
//http://ishop.loc
$app_path = str_replace('/public/', '', $app_path);
define("PATH", $app_path);
define("ADMIN", PATH . '/admin');

require_once ROOT . '/vendor/autoload.php';