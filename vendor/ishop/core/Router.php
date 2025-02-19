<?php
/**
 Класс для маршрутирования по запросу пользователя
 */

namespace ishop;


class Router
{
    protected static $routes = [];
    protected static $route = [];

    //Метод для обработки по регулярном выражение
    public static function add($regexp, $route = []){
        self::$routes[$regexp] = $route;
    }

    //Методы get для возврашение результата
    public static function getRoutes(){
        return self::$routes;
    }
    public static function getRoute(){
        return self::$route;
    }

    //Метод который принимает url адрес указоный в адресном строке
    public static function dispatch($url){
        $url = self::removeQueryString($url);
        if(self::matchRoute($url)){
            $controller = 'app\controllers\\' . self::$route['prefix'] . self::$route['controller'] . 'Controller';
            //проверяем сушествуетли конроллери и методи
            if(class_exists($controller)){
                $controllerObject = new $controller(self::$route);
                $action = self::lowerCamelCase(self::$route['action']) . 'Action';
                if(method_exists($controllerObject, $action)){
                    $controllerObject->$action();
                    $controllerObject->getView();

                }else{
                    throw new \Exception("Метод $controller::$action не найден", 404);
                }
            }else{
                throw new \Exception("Контроллер $controller не найден", 404);
            }
        }else{
            throw new \Exception("Страница не найдена", 404);
        }
    }

    //Метод который проветяет соответствия url адрес в маршрутизаторе
    public static function matchRoute($url){
        foreach(self::$routes as $pattern => $route){
            if(@preg_match("#{$pattern}#i", $url, $matches)){
                foreach($matches as $k => $v){
                    if(is_string($k)){
                        $route[$k] = $v;
                    }
                }
                if(empty($route['action'])){
                    $route['action'] = 'index';
                }
                if(!isset($route['prefix'])){
                    $route['prefix'] = '';
                }else{
                    $route['prefix'] .= '\\';
                }
                $route['controller'] = self::upperCamelCase($route['controller']);
                self::$route = $route;
                return true;
            }
        }
        return false;
    }

    //Метод для изменения имён контролеров в формате CamelCase
    protected static function upperCamelCase($name){
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));
    }

    //Метод для изменения имён action(название страниц указоных в адресном строке) в формате camelCase
    protected static function lowerCamelCase($name){
        return lcfirst(self::upperCamelCase($name));
    }

    //Метод который обрезает get параметры передаваемые в url адресе

    protected static function removeQueryString($url){
        if($url){
            $params = explode('&', $url, 2);
            if(false === strpos($params[0], '=')){
                return rtrim($params[0], '/');
            } else {
                return '';
            }
        }

    }


}
