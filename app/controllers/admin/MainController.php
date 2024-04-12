<?php
/*
 Контроллер админки
 */

namespace app\controllers\admin;


class MainController extends AppController {

    public function indexAction(){
        $countNewOrders = \R::count('order', "status = '0'");
        $countUsers = \R::count('user');
        $countProducts = \R::count('product');
        $countCategories = \R::count('category');
        $this->setMeta('Панель управления', 'Администраторский раздел', 'admin');
        $this->set(compact('countNewOrders', 'countProducts', 'countCategories', 'countUsers'));

    }
}