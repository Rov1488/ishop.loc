<?php
/**
Контроллер корзинки товара
 */

namespace app\controllers;


use app\models\Cart;
use app\models\Order;
use app\models\User;

class CartController extends AppController{

    public function addAction(){
        $this->requirePost();
        $id = !empty($_GET['id']) ? (int)$_GET['id'] : null;
        $qty = !empty($_GET['qty']) ? max(1, (int)$_GET['qty']) : 1;
        $mod_id = !empty($_GET['mod']) ? (int)$_GET['mod'] : null;
        $mod = null;
        if (!$id){
            redirect();
        }
        $product = \R::findOne('product', "id = ? AND status = '1'", [$id]);
        if (!$product){
            redirect();
        }
        if ($mod_id){
            $mod = \R::findOne('modification', 'id = ? AND product_id = ?', [$mod_id, $id]);
        }
        //Создадим объект карточки товара
        $cart = new Cart();
        $cart->addToCart($product, $qty, $mod);
        //Проверка Ajax запрос
        if ($this->isAjax()){
            $this->loadView('cart_modal');
        }
        redirect();



    }

    //Метод для показа модалного окно
    public function showAction(){
        $this->loadView('cart_modal');
    }

    //Метод для удаления из модального окна корзины
    public function deleteAction(){
        $this->requirePost();
        $id = !empty($_GET['id']) ? $_GET['id'] : null;
        if(isset($_SESSION['cart'][$id])){
            $cart = new Cart();
            $cart->deleteItem($id);
        }
        //Проверка Ajax запрос
        if ($this->isAjax()){
            $this->loadView('cart_modal');
        }
        redirect();
    }

    //Метод очистки модального окно
    public function clearAction(){
        $this->requirePost();
        unset($_SESSION['cart']);
        unset($_SESSION['cart.qty']);
        unset($_SESSION['cart.sum']);
        unset($_SESSION['cart.currency']);
        $this->loadView('cart_modal');

    }

    //Метод для оформления заказа
    public function viewAction(){
        $this->setMeta('Корзина');
    }

public function checkoutAction(){
     if (!empty($_POST) && !empty($_SESSION['cart'])) {
         //регистрация пользователя
        if (!User::checkAuth()){
            $user =new User();
            $data = $_POST;
            $user->load($data);
            $user->attributes['role'] = 'user'; // роль нельзя задать из формы

            if (!$user->validate($data) || !$user->checkUnique()){
                $user->getErrors();
                unset($data['password']); // пароль в сессии не храним
                $_SESSION['form_data'] = $data;
                redirect();
            } else{
                $user->attributes['password'] = password_hash($user->attributes['password'], PASSWORD_DEFAULT);
                if (!$user_id = $user->save('user')){
                   $_SESSION['error'] = 'Ошибка при сохранений пользователя!';
                    redirect();
                }

            }

        }
        //сохранения заказа
         $data['user_id'] = isset($user_id) ? $user_id : $_SESSION['user']['id'];
        $data['note'] = !empty($_POST['note']) ? $_POST['note'] : '';
        $user_email = isset($_SESSION['user']['email']) ? $_SESSION['user']['email'] : ($_POST['email'] ?? '');
        $order_id = Order::saveOrder($data);
        Order::mailOrder($order_id, $user_email);
     }
     redirect();
}

}