<?php
/*
Контроллер для очистке кэша
 */

namespace app\controllers\admin;


use app\models\admin\Product;
use ishop\Cache;

class CacheController extends AppController{
    public function indexAction(){
        $unusedImages = count(Product::getUnusedImages());
        $this->setMeta('Очистка кэша');
        $this->set(compact('unusedImages'));
    }
    public function deleteAction(){
        $this->requirePost();
        $key = isset($_GET['key']) ? $_GET['key'] : null;
        $cache = Cache::instance();
        switch ($key){
            case 'category':
                $cache->delete('cats');
                $cache->delete('ishop_menu');
                break;
            case 'filter':
                $cache->delete('filter_group');
                $cache->delete('filter_attrs');
                break;
            case 'images':
                $deleted = 0;
                foreach (Product::getUnusedImages() as $file){
                    if (Product::deleteImage($file)) $deleted++;
                }
                $_SESSION['success'] = "Удалено неиспользуемых картинок: {$deleted}";
                redirect();
        }
        $_SESSION['success'] = "Выбранный кэш удален";
        redirect();
    }
}