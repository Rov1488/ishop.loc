<?php
/*
 Конроллер поиска
 */

namespace app\controllers;


class SearchController extends AppController{

    public function typeaheadAction(){
        if ($this->isAjax()){
            $query = trim($_GET['query'] ?? '');
            if ($query){
                $products = \R::getAll("SELECT id, title FROM product WHERE title LIKE ? AND status = '1' LIMIT 11", ["%{$query}%"]);
                echo json_encode($products);
            }
        }
        die;
    }

    public function indexAction(){
        $query = trim($_GET['s'] ?? '');
        $products = [];
        if ($query){
            $products = \R::find('product', "title LIKE ? AND status = '1'", ["%{$query}%"]);
        }
        // setMeta сам экранирует значения
        $this->setMeta('Поиск по: ' . $query);
        $this->set(compact('products', 'query'));
    }

}