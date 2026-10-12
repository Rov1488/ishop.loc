<?php
/*
Модел оформления заказов
 */

namespace app\models;


use ishop\App;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;


class Order extends AppModel{

    //Метод для сохранения заказов в таблицу order в БД
    public static function saveOrder($data){
        $order = \R::dispense('order');
        $order->user_id = $data['user_id'];
        $order->note = $data['note'];
        $order->currency = $_SESSION['cart.currency']['code'];
        $order_id = \R::store($order);
        self::saveOrderProduct($order_id);
        return $order_id;
    }

    //Метод для сохранетия в таблицу order_product в БД
    public static function saveOrderProduct($order_id){
        $values = [];
        foreach ($_SESSION['cart'] as $product_id => $product){
            array_push($values, $order_id, (int)$product_id, (int)$product['qty'], $product['title'], $product['price']);
        }
        $placeholders = implode(', ', array_fill(0, count($_SESSION['cart']), '(?, ?, ?, ?, ?)'));
        \R::exec("INSERT INTO order_product (order_id, product_id, qty, title, price) VALUES $placeholders", $values);
    }

    public static function mailOrder($order_id, $user_email){
        //Транспорт SMTP: при smtp_protocol = 'ssl' сразу шифрованное соединение (порт 465), иначе STARTTLS
        $transport = new EsmtpTransport(
            App::$app->getProperty('smtp_host'),
            (int)App::$app->getProperty('smtp_port'),
            App::$app->getProperty('smtp_protocol') === 'ssl' ? true : null
        );
        $transport->setUsername(App::$app->getProperty('smtp_login'));
        $transport->setPassword(App::$app->getProperty('smtp_password'));
        $mailer = new Mailer($transport);

        //Тело письма
        ob_start();
        require APP . '/views/mail/mail_order.php';
        $body = ob_get_clean();

        // Заказ уже сохранён, поэтому ошибка почты не должна прерывать оформление
        try {
            $from = new Address(App::$app->getProperty('smtp_login'), App::$app->getProperty('shop_name'));
            if ($user_email){
                $mailer->send((new Email())
                    ->from($from)
                    ->to($user_email)
                    ->subject("Вы совершили заказ №{$order_id} на сайте " . App::$app->getProperty('shop_name'))
                    ->html($body));
            }
            $mailer->send((new Email())
                ->from($from)
                ->to(App::$app->getProperty('admin_email'))
                ->subject("Сделан заказ №{$order_id}")
                ->html($body));
        } catch (\Exception $e){
            error_log("[" . date('Y-m-d H:i:s') . "] Ошибка отправки письма по заказу №{$order_id}: {$e->getMessage()}\n", 3, ROOT . '/tmp/errors.log');
        }
        unset($_SESSION['cart']);
        unset($_SESSION['cart.qty']);
        unset($_SESSION['cart.sum']);
        unset($_SESSION['cart.currency']);
        $_SESSION['success'] = 'Спасибо за Ваш заказ. В ближайшее время с Вами свяжется менеджер для согласования заказа';

    }
}