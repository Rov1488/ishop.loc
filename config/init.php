<?php
/**
Конфигуратсионные файл
 * Определяем константы
 */
//Режим отладки задаётся в config/params.local.php ('debug' => 1 на компьютере разработчика).
//Без этой настройки DEBUG = 0, чтобы на сервере посетители не видели подробностей ошибок
$local_params = is_file(__DIR__ . '/params.local.php') ? require __DIR__ . '/params.local.php' : [];
define("DEBUG", (int)($local_params['debug'] ?? 0)); //для отображениея ошибок в режиме разработчика(1) а промышленая эксплуататция(0)
unset($local_params);
define("ROOT", dirname(__DIR__)); // путь к корьню папки сайта
define("WWW", ROOT . '/public'); //указываем к папке public
define("APP", ROOT . '/app'); //указываем к папке app
define("CORE", ROOT . '/core');//указываем к ядру сайта core
define("LIBS", ROOT . '/core/libs');//указываем к папке libs
define("CACHE", ROOT . '/tmp/cache');//указываем к папке cache
define("CONF", ROOT . '/config');//указываем к папке config
define("LAYOUT", 'watches'); //константа для подключения шаблона сайта

//http://ishop.loc/public/index.php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$app_path = "{$scheme}://{$_SERVER['HTTP_HOST']}{$_SERVER['PHP_SELF']}";
//http://ishop.loc/public/
$app_path = preg_replace("#[^/]+$#", '', $app_path);
//http://ishop.loc
$app_path = str_replace('/public/', '', $app_path);
$app_path = rtrim($app_path, '/');
define("PATH", $app_path);
define("ADMIN", PATH . '/admin');

require_once ROOT . '/vendor/autoload.php';