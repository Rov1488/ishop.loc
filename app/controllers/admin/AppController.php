<?php
/*Базовый конроллер
 */

namespace app\controllers\admin;


use app\models\AppModel;
use app\models\User;
use ishop\base\Controller;
use ishop\Cache;

class AppController extends Controller{

    public $layout = 'admin';

    public function __construct($route){
        parent::__construct($route);
        if (!User::isAdmin() && $route['action'] != 'login-admin'){
            redirect(ADMIN . '/user/login-admin'); //UserController::loginAdminAction
        }
        new AppModel();
    }

    //Сброс кэша после изменений в админке, чтобы сайт сразу показал новые данные
    protected function clearCategoryCache(){
        Cache::instance()->delete('cats');
        Cache::instance()->delete('ishop_menu');
    }

    protected function clearFilterCache(){
        Cache::instance()->delete('filter_group');
        Cache::instance()->delete('filter_attrs');
    }

    protected function clearCurrencyCache(){
        Cache::instance()->delete('currencies');
    }

    //Действия, меняющие данные (удаление, смена статуса), принимаем только через POST:
    //тогда их защищает CSRF-токен, который App проверяет у каждого POST-запроса
    protected function requirePost(){
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'){
            throw new \Exception('Действие доступно только через POST-запрос', 405);
        }
    }

    public function getRequestID($get = true, $id = 'id'){
        if($get){
            $data = $_GET;
        }else{
            $data = $_POST;
        }
        $id = !empty($data[$id]) ? (int)$data[$id] : null;
        if(!$id){
            throw new \Exception('Страница не найдена', 404);
        }
        return $id;
    }
}