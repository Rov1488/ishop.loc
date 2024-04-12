<?php
/**
Класс виджета для выорки курса валют
 */

namespace app\widgets\currency;


use ishop\App;

class Currency{

    protected $tpl;//запис пути нужных шаблонов
    protected $currencies;
    protected $currency;

    public function __construct(){
        $this->tpl = __DIR__ . '/currency_tpl/currency.php';
        $this->run();
    }

    //Метод для вывода щаблона
    protected function run(){
       $this->currencies = App::$app->getProperty('currencies');
        $this->currency = App::$app->getProperty('currency');
        echo $this->getHtml();

    }

//Метод для выбора из таблици currency все валюты по указоном полям

    public static function getCurrencies(){
    return \R::getAssoc("SELECT code, title, symbol_left, symbol_right, value, base FROM currency ORDER BY base DESC");

    }

//Метод для выбора активного валюту записеного в $_COOKIE

    public static function getCurrency($currencies){
    if (isset($_COOKIE['currency']) && array_key_exists($_COOKIE['currency'], $currencies)){
        $key = $_COOKIE['currency'];
    } else{

        $key = key($currencies); //функция key() возврашаеть текущие элемент массива
    }

    $currency = $currencies[$key];
    $currency['code'] = $key;
    return $currency;

    }

    //Метод для выбора указонного шаблона
    protected function getHtml(){
        ob_start();
        require_once $this->tpl;
        return ob_get_clean();

    }



}