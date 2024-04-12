<?php
/*
Контроллер продуктов администраторского раздела
 */

namespace app\controllers\admin;

use app\models\admin\Product;
use app\models\AppModel;
use ishop\App;
use ishop\libs\Pagination;


class ProductController extends AppController{

    //Метод для получения id товаров и пагинации
    public function indexAction(){
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perpage = 10;
        $count = \R::count('product');
        $pagination = new Pagination($page, $perpage, $count);
        $start = $pagination->getStart();
        $products = \R::getAll("SELECT product.*, category.title AS cat FROM product JOIN category ON category.id = product.category_id ORDER BY product.title LIMIT $start, $perpage");
        $this->setMeta('Список товаров');
        $this->set(compact('products', 'pagination', 'count'));
    }

    //Метод для загрузки картинок
    public function addImageAction(){
        if(isset($_GET['upload'])){
            if($_POST['name'] == 'single'){
                $wmax = App::$app->getProperty('img_width');
                $hmax = App::$app->getProperty('img_height');
            }else{
                $wmax = App::$app->getProperty('gallery_width');
                $hmax = App::$app->getProperty('gallery_height');
            }
            $name = $_POST['name'];
            $product = new Product();
            $product->uploadImg($name, $wmax, $hmax);
        }
    }

    //Метод для добавления товаров
    public function addAction(){
        if(!empty($_POST)){
            $product = new Product();
            $data = $_POST;
            $product->load($data);
            $product->attributes['status'] = $product->attributes['status'] ? '1' : '0';
            $product->attributes['hit'] = $product->attributes['hit'] ? '1' : '0';
            $product->getImg();
            if(!$product->validate($data)){
                $product->getErrors();
                $_SESSION['form_data'] = $data;
                redirect();
            }

            if($id = $product->save('product')){
                $product->saveGallery($id);
                $alias = AppModel::createAlias('product', 'alias', $data['title'], $id);
                $p = \R::load('product', $id);
                $p->alias = $alias;
                \R::store($p);
                $product->editFilter($id, $data);
                $product->editRelatedProduct($id, $data);
                $product->editModificationProduct($id);

                $_SESSION['success'] = 'Товар добавлен';
            }
            redirect();
        }

        $this->setMeta('Новый товар');
    }


    //Метод для редактирования товаров
    public function editAction(){
        if(!empty($_POST)){
            $id = $this->getRequestID(false);
            $product = new Product();
            $data = $_POST;
            $product->load($data);
            $product->attributes['status'] = $product->attributes['status'] ? '1' : '0';
            $product->attributes['hit'] = $product->attributes['hit'] ? '1' : '0';
            $product->getImg();
            if(!$product->validate($data)){
                $product->getErrors();
                redirect();
            }
            if ($product->update('product', $id)){
                $product->editFilter($id, $data);
                $product->editRelatedProduct($id, $data);
                $product->saveGallery($id);
                $alias = AppModel::createAlias('product', 'alias', $data['title'], $id);
                $product = \R::load('product', $id);
                $product->alias = $alias;
                \R::store($product);
                $_SESSION['success'] = 'Изменение успешно сохранены';
                redirect();
            }

        }

        $id = $this->getRequestID();
        $product = \R::load('product', $id);
        App::$app->setProperty('parent_id', $product->category_id);
        $filter = \R::getCol('SELECT attr_id FROM attribute_product WHERE product_id = ?', [$id]);
        $related_product = \R::getAll("SELECT related_product.related_id, product.title FROM related_product JOIN product ON product.id = related_product.related_id WHERE  related_product.product_id = ?", [$id]);
        $gallery = \R::getCol('SELECT img FROM gallery WHERE product_id = ?', [$id]);
        $this->setMeta("Редактирования товара {$product->title}");
        $this->set(compact('product','filter', 'related_product', 'gallery'));
    }

    //Метод для управление товарами и связанные товары.
    public function relatedProductAction(){
        /*$data = [
          'items' => [
               [
                  'id' => 1,
                  'text' => 'Товар 1',
              ],
                  [
                  'id' => 2,
                  'text' => 'Товар 2',
              ],

          ]
        ];*/
        $q = isset($_GET['q']) ? $_GET['q'] : '';
        $data['items'] = [];
        $products = \R::getAssoc('SELECT id, title FROM product WHERE title LIKE ? LIMIT 10', ["%{$q}%"]);
        if($products){
            $i = 0;
            foreach($products as $id => $title){
                $data['items'][$i]['id'] = $id;
                $data['items'][$i]['text'] = $title;
                $i++;
            }
        }
        echo json_encode($data);
        die;
    }

    //Метод удаление добавленых мофификаторов товара
    public function deleteModificationAction(){
        $id = $id = isset($_POST['id']) ? $_POST['id'] : null;
        $title = isset($_POST['mod-title']) ? $_POST['mod-title'] : null;
        $price = isset($_POST['mod-price']) ? $_POST['mod-price'] : null;
        if (!$id || !$title || !$price){
            return;
        }else{
            \R::exec("DELETE FROM modification WHERE product_id = ? ", [$id, $title, $price]);
            return;
        }

    }

    //Метод удаления изображеней из Галлерей
    public function deleteGalleryAction(){
        $id = isset($_POST['id']) ? $_POST['id'] : null;
        $src = isset($_POST['src']) ? $_POST['src'] : null;
        if (!$id || !$src){
            return;
        }
        if (\R::exec("DELETE FROM gallery WHERE product_id = ? AND img = ?", [$id, $src])){
            @unlink(WWW . "/images/$src");
            exit('1');
        }
        return;
    }




    //Метод удаления продуктов
    public function deleteAction(){
        $id = $this->getRequestID();
        \R::exec("DELETE FROM product WHERE id = ? ", [$id]);
        \R::exec("DELETE FROM related_product WHERE product_id = ? ", [$id]);
        \R::exec("DELETE FROM attribute_product WHERE product_id = ? ", [$id]);
        \R::exec("DELETE FROM modification WHERE product_id = ? ", [$id]);
        $gallery = \R::getAll('SELECT img FROM gallery WHERE product_id = ?', [$id]);
             if (!empty($gallery->img)){
                 foreach ($gallery as $item){
                     $filename = $item->img;
                 }
                 @unlink(WWW . "/images/$filename");
                 exit();
             }

        \R::exec("DELETE FROM gallery WHERE product_id = ?", [$id]);

              $_SESSION['success'] = 'Товар и связонный все товары успешно удалены';
            redirect(ADMIN . 'product');

    }


}