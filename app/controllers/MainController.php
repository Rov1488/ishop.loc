<?php
/**
 * Created by PhpStorm.
 * User: r.pulatov
 * Date: 25.11.2019
 * Time: 18:54
 */

namespace app\controllers;

use ishop\App;
use ishop\Cache;

class MainController extends AppController{

    public  function indexAction(){

        /*Вывод данных из таблици БД
        $posts = \R::findAll('test');
        $post = \R::findOne('test', 'id = ?', [2]);

        $name = 'Ravshan';
        $age = 31;
        $names = ['Andrey', 'Vitya', 'Ali', 'Sanjar'];
        //Кеширования данных
        $cache = Cache::instance();
        //$cache->set('test', $names);
        //$cache->delete('test');
        $data = $cache->get('test');
        if(!$data){
            $cache->set('test', $names);
        }
        debug($data);
        $this->set(compact('name', 'age', 'names', 'posts'));*/
        $brands = \R::find('brand', 'LIMIT 3');
        $hits = \R::find('product', "hit = '1' AND status = '1' LIMIT 8");

        $this->setMeta(App::$app->getProperty('shop_name'), 'Описание...', 'Ключевие слова..');
        $this->set(compact('brands', 'hits'));
    }
}