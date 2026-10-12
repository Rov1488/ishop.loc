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
        $count = \R::count('order', 'user_id = ?', [$user_id]);
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        //Пагинация

        $orders = \R::getAll("SELECT `order`.`id`, `order`.`user_id`, `order`.`status`, `order`.`date`, `order`.`update_at`, `order`.`currency`,  ROUND(SUM(`order_product`.`price` * `order_product`.`qty`), 2) AS `sum` FROM `order`
JOIN `order_product` ON `order`.`id` = `order_product`.`order_id`
WHERE user_id = ? GROUP  BY `order`.`id` ORDER BY `order`.`status`, `order`.`id` LIMIT $start, $perpage", [$user_id]);

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
        $this->requirePost();
        $user_id = $this->getRequestID();
        if ($user_id == $_SESSION['user']['id']){
            $_SESSION['error'] = 'Нельзя удалить свою учётную запись';
            redirect();
        }
        // удаляем заказы пользователя вместе с их товарами, затем самого пользователя
        $order_ids = \R::getCol('SELECT id FROM `order` WHERE user_id = ?', [$user_id]);
        if ($order_ids){
            \R::exec('DELETE FROM order_product WHERE order_id IN (' . \R::genSlots($order_ids) . ')', $order_ids);
            \R::exec('DELETE FROM `order` WHERE user_id = ?', [$user_id]);
        }
        \R::exec('DELETE FROM user WHERE id = ?', [$user_id]);
        $_SESSION['success'] = 'Пользователь и заказаные товары пользователем успешно удалены';
        redirect(ADMIN . '/user');
    }

}