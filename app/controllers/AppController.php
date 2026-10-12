<?php
/*
App контроллер
 */

namespace app\controllers;


use app\models\AppModel;
use app\widgets\currency\Currency;
use ishop\App;
use ishop\base\Controller;
use ishop\Cache;

class AppController extends Controller {

    public function __construct($route)
    {
        parent::__construct($route);
        new AppModel();
        App::$app->setProperty('currencies', self::cacheCurrencies());
        App::$app->setProperty('currency', Currency::getCurrency(App::$app->getProperty('currencies')));

        App::$app->setProperty('cats', self::cacheCategory());

    }

    //Метод для записи категории меню в кеш из БД
    public static function cacheCategory(){
        $cache = Cache::instance();
        $cats = $cache->get('cats');
        if(!$cats){
            $cats = \R::getAssoc("SELECT * FROM category");
            $cache->set('cats', $cats);
        }
        return $cats;

    }

    //Список валют нужен на каждой странице, поэтому берём его из кеша.
    //Админка сбрасывает кеш 'currencies' при изменении валют
    public static function cacheCurrencies(){
        $cache = Cache::instance();
        $currencies = $cache->get('currencies');
        if(!$currencies){
            $currencies = Currency::getCurrencies();
            $cache->set('currencies', $currencies);
        }
        return $currencies;
    }


}