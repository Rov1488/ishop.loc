<?php
/**
 Контроллер категории
 */

namespace app\controllers;

use app\models\Breadcrumbs;
use app\models\Category;
use app\widgets\filter\Filter;
use ishop\App;
use ishop\Cache;
use ishop\libs\Pagination;

class CategoryController extends AppController{

    public function viewAction(){
        $alias = $this->route['alias'];
        $category = \R::findOne('category', 'alias = ?', [$alias]);
        if(!$category){
            throw new \Exception('Страница не найдено', 404);
        }

        //хлебные крошки
        $breadcrumbs = Breadcrumbs::getBreadcrumbs($category->id);

        //Объект категорий товаров по id
        $cat_model = new Category();
        $ids = $cat_model->getIds($category->id);
        $ids = !$ids ? $category->id : $ids . $category->id;

        //Пагинатция страницы

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1; //номер текущие страницы
        $perpage = App::$app->getProperty('pagination'); //Количество записей на странице
        $sql_part = '';
        if (!empty($_GET['filter'])){
            /*
           SELECT `product`.*  FROM `product`  WHERE category_id IN (6) AND id IN
           (
           SELECT product_id FROM attribute_product WHERE attr_id IN (1,5) GROUP BY product_id HAVING COUNT(product_id) = 2
           )
           */
            $filter = Filter::getFilter();
            if ($filter){
                $cnt = Filter::getCountGroups($filter);
                $sql_part = "AND id IN (SELECT product_id FROM attribute_product WHERE attr_id IN ($filter) GROUP BY product_id HAVING COUNT(product_id) = $cnt)";
            }

        }


        $total = \R::count('product', "category_id IN ($ids) $sql_part");//Подсчет кол-во товаров из БД
        $pagination = new Pagination($page, $perpage, $total); //создаем объект пагинации
        $start = $pagination->getStart();

        $products = \R::find('product', "status = '1' AND category_id IN ($ids) $sql_part LIMIT $start, $perpage");
        if ($this->isAjax()){
            $this->loadView('filter', compact('products','pagination', 'total'));
        }

        $this->setMeta($category->title, $category->description, $category->keywords);
        $this->set(compact('products', 'breadcrumbs', 'pagination', 'total'));

    }

}