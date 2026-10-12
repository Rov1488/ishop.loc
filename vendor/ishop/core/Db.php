<?php
/**
соединения с базы данных
 */

namespace ishop;


class Db{
    use TSingletone;


    protected function __construct(){
        $db = require_once CONF . '/config_db.php';
    class_alias('\RedBeanPHP\R', 'R');
    \R::setup($db['dsn'], $db['user'], $db['pass']);

    //Проверка соединения с БД
        if( !\R::testConnection()){
            throw new \Exception("Нет соединения с БД", 500);
        }
        //Данная команда зомараживаеть внесение изменения в БД
        \R::freeze(true);

        //Параметры запросов передаём строками. Иначе RedBean отправляет '0'/'1' как числа, а MySQL
        //понимает число в ENUM('0','1') как номер элемента: 1 сохраняется как '0', 0 даёт ошибку
        //"Data truncated" (product.status/hit, order.status, currency.base)
        \R::getDatabaseAdapter()->getDatabase()->setUseStringOnlyBinding(true);

        //Включаем режим отклатки
        if(DEBUG){
            \R::debug(true, 1);
        }
        \R::ext('xdispense', function ($type){
            return \R::getRedBean()->dispense($type);
        });

    }
}