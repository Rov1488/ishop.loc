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
       session_start();
       self::$app = Registry::instance();
       $this->getParams();
       new ErrorHandler();
       Router::dispatch($query);
    }

    protected function getParams(){
        $params = require_once CONF . '/params.php';
        if(!empty($params)){
            foreach ($params as $k => $v){
                self::$app->setProperty($k, $v);
            }
        }
    }
}