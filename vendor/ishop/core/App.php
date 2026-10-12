<?php
/**
 Класс App контроллер
 */

namespace ishop;


class App
{
    public static $app;
    public function __construct()
    {
         // Prefer the REQUEST_URI path when available so routing works
         // with nginx try_files (which doesn't necessarily populate QUERY_STRING).
         $requestUri = $_SERVER['REQUEST_URI'] ?? '';
         $query = trim(parse_url($requestUri, PHP_URL_PATH), '/');
       new ErrorHandler();
       session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Lax']);
       self::$app = Registry::instance();
       $this->getParams();
       // любой POST (формы, AJAX, загрузка картинок) должен прийти с CSRF-токеном
       if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && !csrf_check()){
           throw new \Exception('Неверный CSRF-токен. Обновите страницу и повторите действие', 403);
       }
       Router::dispatch($query);
    }

    protected function getParams(){
        $params = require_once CONF . '/params.php';
        // секреты (пароли SMTP и т.п.) лежат в params.local.php, который не попадает в git
        if(is_file(CONF . '/params.local.php')){
            $params = array_merge($params, require CONF . '/params.local.php');
        }
        if(!empty($params)){
            foreach ($params as $k => $v){
                self::$app->setProperty($k, $v);
            }
        }
    }
}