<?php
/*
Контроллер всех заказов в админке
 */

namespace app\controllers\admin;


use ishop\libs\Pagination;

class OrderController extends AppController{

    //Метод для получения id заказов и пагинации
    public function indexAction(){
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = 3;
        $count = \R::count('order');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();

        $orders = \R::getAll("SELECT `order`.`id`, `order`.`user_id`, `order`.`status`, `order`.`date`, `order`.`update_at`, `order`.`currency`, `user`.`name`, ROUND(SUM(`order_product`.`price` * `order_product`.`qty`), 2) AS `sum` FROM `order` JOIN `user` ON `order`.`user_id` = `user`.`id`
JOIN `order_product` ON `order`.`id` = `order_product`.`order_id` GROUP BY `order`.`id` ORDER BY `order`.`status`, `order`.`id` LIMIT $start, $perpage");

        $this->setMeta('Список заказов');
        $this->set(compact('orders', 'pagination', 'count'));
    }
    //Метод для просмотра заказов
    public function viewAction(){
        $order_id = $this->getRequestID();
        $order = \R::getRow("SELECT `order`.*, `user`.`name`, ROUND(SUM(`order_product`.`price` * `order_product`.`qty`), 2) AS `sum` FROM `order`
  JOIN `user` ON `order`.`user_id` = `user`.`id`
  JOIN `order_product` ON `order`.`id` = `order_product`.`order_id`
  WHERE `order`.`id` = ?
  GROUP BY `order`.`id` ORDER BY `order`.`status`, `order`.`id` LIMIT 1", [$order_id]);
        if(!$order){
            throw new \Exception('Страница не найдена', 404);
        }
        $order_products = \R::findAll('order_product', "order_id = ?", [$order_id]);
        $this->setMeta("Заказ №{$order_id}");
        $this->set(compact('order', 'order_products'));
    }

    //Метод для изменения статуса заказа
    public function changeAction(){
        $order_id = $this->getRequestID();
        $status = !empty($_GET['status']) ? '1' : '0';
        $order = \R::load('order', $order_id);
        if(!$order){
            throw new \Exception('Страница не найдена', 404);
        }
        $order->status = $status;
        $order->update_at = date("Y-m-d H:i:s");
        \R::store($order);
        $_SESSION['success'] = 'Изменения сохранены';
        redirect();


    }
    //Метод для удаления заказа
    public function deleteAction(){
        $order_id = $this->getRequestID();
        $order = \R::load('order', $order_id);
        \R::trash($order);
        if (isset($order)){
            $_SESSION['success'] = 'Заказ успешно удалено';
            redirect(ADMIN . '/order');
        }else{
            $_SESSION['error'] = 'Заказ не удалено возникла внутреная ошибка!';
        }
    }
}