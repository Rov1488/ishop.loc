<?php
/*
 Контроллер роли пользователь администратор
 */

namespace app\controllers\admin;


use app\models\User;
use ishop\libs\Pagination;

class UserController extends AppController {

 //Метод для вывода пагинации и списка вид
    public function indexAction(){
        $page = isset($_GET['page'])? (int)$_GET['page'] : 1;
        $perpage = 3;
        $count = \R::count('user');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        $users = \R::findAll('user', "LIMIT $start, $perpage");
        $this->setMeta('Список пользователей');
        $this->set(compact('users', 'pagination', 'count'));
    }

    //Метод для добавление пользователя

    public function addAction(){
        $this->setMeta('Новый пользователь');
    }


    //Метод для редактирования пользователей сайта

    public function editAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $user = new \app\models\admin\User();
            $data = $_POST;
            $user->load($data);
            if(!$user->attributes['password']){
                unset($user->attributes['password']);
            }else{
                $user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT);
            }
            if(!$user->validate($data) || !$user->checkUnique()){
                $user->getErrors();
                redirect();
            }
            if($user->update('user', $id)){
                $_SESSION['success'] = 'Изменения сохранены';
            }
            redirect();
        }

        $user_id = $this->getRequestID();
        $user = \R::load('user', $user_id);

        //Пагинация
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = 3;
        $count = \R::count('order');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        //Пагинация

        $orders = \R::getAll("SELECT `order`.`id`, `order`.`user_id`, `order`.`status`, `order`.`date`, `order`.`update_at`, `order`.`currency`,  ROUND(SUM(`order_product`.`price` * `order_product`.`qty`), 2) AS `sum` FROM `order`
JOIN `order_product` ON `order`.`id` = `order_product`.`order_id`
WHERE user_id = {$user_id} GROUP  BY `order`.`id` ORDER BY `order`.`status`, `order`.`id` LIMIT $start, $perpage");

        $this->setMeta('Редактирование профиля пользоватлея');
        $this->set(compact('user', 'orders', 'pagination', 'count'));
    }

    //Метод для проверки при аунтефикации пользователя администраторского раздела сайта
    public function loginAdminAction(){
        if (!empty($_POST)){
            $user = new User();
            if (!$user->login(true)){
               $_SESSION['error'] = 'Логин или пароль введены неверно';
            }
            if (User::isAdmin()){
                redirect(ADMIN);
            } else{
                redirect();
            }
        }
        $this->layout = 'login';
    }

    //Метод для удаления пользователя
    public function deleteAction(){
        $user_id = $this->getRequestID();
        $order_id = $this->getRequestID();
        $user = \R::load('user', $user_id);
        $order = \R::load('order', $order_id);
        $order_products = \R::load('order_product', $order_id);
        \R::trash($user, $order, $order_products);
        \R::trash($order);
        \R::trash($order_products);

        if (isset($user, $order, $order_products)){
            $_SESSION['success'] = 'Пользователь и заказаные товары пользователем успешно удалены';
            redirect();
        }else{
            $_SESSION['error'] = 'Пользователь заказаные товары пользователем не удалены возникла внутреная ошибка обратитес к Администратору!';
        }
    }

}