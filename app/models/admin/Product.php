<?php
/*
 Модул продуктов администраторского раздела
 */

namespace app\models\admin;


use app\models\AppModel;

class Product extends AppModel{

    public $attributes = [
        'title' => '',
        'category_id' => '',
        'keywords' => '',
        'description' => '',
        'price' => '',
        'old_price' => '0',
        'content' => '',
        'status' => '',
        'hit' => '',
        'alias' => '',
    ];

    public $rules = [
        'required' => [
            ['title'],
            ['category_id'],
            ['price'],
        ],
        'integer' => [
            ['category_id'],
        ],
    ];

  public $modification = [
        [
            'title' => '',
            'price' => '',
        ]
    ];

    //Метод для записи мофификаторов
    public function editModificationProduct($id, $data){
        $modifications = [];
        if(!empty($data['mod']['title']) && is_array($data['mod']['title'])){
            foreach($data['mod']['title'] as $key => $title){
                $title = trim($title);
                $price = isset($data['mod']['price'][$key]) ? trim($data['mod']['price'][$key]) : '';
                if ($title === '' || $price === ''){
                    continue;
                }
                $price = str_replace(',', '.', $price);
                $modifications[] = [
                    'product_id' => $id,
                    'title' => $title,
                    'price' => $price,
                ];
            }
        } elseif(!empty($data['mod']) && is_array($data['mod'])) {
            foreach($data['mod'] as $item){
                if(empty($item['title']) || empty($item['price'])){
                    continue;
                }
                $title = trim($item['title']);
                $price = str_replace(',', '.', trim($item['price']));
                if ($title === '' || $price === ''){
                    continue;
                }
                $modifications[] = [
                    'product_id' => $id,
                    'title' => $title,
                    'price' => $price,
                ];
            }
        }

        \R::exec("DELETE FROM modification WHERE product_id = ?", [$id]);

        if (!empty($modifications)){
            $values = [];
            $placeholders = [];
            foreach ($modifications as $mod){
                $placeholders[] = '(?, ?, ?)';
                $values[] = $mod['product_id'];
                $values[] = $mod['title'];
                $values[] = $mod['price'];
            }
            \R::exec(
                "INSERT INTO modification (product_id, title, price) VALUES " . implode(', ', $placeholders),
                $values
            );
        }
    }

    // CHatGPT метод для записи модификаторов товара
    public function saveOrUpdateModifications($id, array $data) {
        $this->editModificationProduct($id, $data);
    }


    //метод для свянанных товаров
    public function editRelatedProduct($id, $data){
        $related_product = \R::getCol('SELECT related_id FROM related_product WHERE product_id = ?', [$id]);
        //если менеджер убрал свянанных товаров - удаляем их
        if (empty($data['related']) && !empty($related_product)){
            \R::exec("DELETE FROM related_product WHERE product_id = ?", [$id]);
            return;
        }

        //если добавляются свянанные товаров
        if (empty($related_product) && !empty($data['related'])){
            $sql_part = '';
            foreach ($data['related'] as $v){
                $v = (int)$v;
                $sql_part .= "($id, $v),";
            }
            $sql_part = rtrim($sql_part, ',');
            \R::exec("INSERT INTO related_product (product_id, related_id) VALUES $sql_part");
            return;
        }

        //если изменились свянанные товаров - удалим и запишем новые
        if (!empty($data['related'])){
            $result = array_diff($related_product, $data['related']);
            if (!empty($result) || count($related_product) != count($data['related'])){
                \R::exec("DELETE FROM related_product WHERE product_id = ?", [$id]);
                $sql_part = '';
                foreach ($data['related'] as $v){
                    $sql_part .= "($id, $v),";
                }
                $sql_part = rtrim($sql_part, ',');
                \R::exec("INSERT INTO related_product (product_id, related_id) VALUES $sql_part");
                return;
            }
        }

    }

    //Метод для добавления, управления фильтрами и атрибутами
    public function editFilter($id, $data){
        $filter = \R::getCol('SELECT attr_id FROM attribute_product WHERE product_id = ?', [$id]);
        //если менеджер убрал фильтер - удаляем их
        if (empty($data['attrs']) && !empty($filter)){
            \R::exec("DELETE FROM attribute_product WHERE product_id = ?", [$id]);
            return;
        }
        //если фильтры добавляются
        if (empty($filter) && !empty($data['attrs'])){
            $sql_part = '';
            foreach ($data['attrs'] as $v){
                $sql_part .= "($v, $id),";
            }
            $sql_part = rtrim($sql_part, ',');
            \R::exec("INSERT INTO attribute_product (attr_id, product_id) VALUES $sql_part");
            return;
        }

        //если изменились фильтры - удалим и запишем новые
        if (!empty($data['attrs'])){
            $result = array_diff($filter, $data['attrs']);
            if (!$result || count($filter) != count($data['attrs'])){
                \R::exec("DELETE FROM attribute_product WHERE product_id = ?", [$id]);
                $sql_part = '';
                foreach ($data['attrs'] as $v){
                    $sql_part .= "($v, $id),";
                }
                $sql_part = rtrim($sql_part, ',');
                \R::exec("INSERT INTO attribute_product (attr_id, product_id) VALUES $sql_part");
                return;
            }
        }
    }

//Метод для записи иконки товара
    public function getImg(){
        if(!empty($_SESSION['single'])){
            $this->attributes['img'] = $_SESSION['single'];
            unset($_SESSION['single']);
        }
    }

    //Метод для сохранеие изображение товара в галлерею
    public function saveGallery($id){
        if(!empty($_SESSION['multi'])){
            $sql_part = '';
            foreach($_SESSION['multi'] as $v){
               $sql_part .= "('$v', '$id'),";
            }
            $sql_part = rtrim($sql_part, ',');
            \R::exec("INSERT INTO gallery (img, product_id) VALUES $sql_part");
            unset($_SESSION['multi']);
         }
    }

    //Метод для загрузки изображение

    public function uploadImg($name, $wmax, $hmax){
        $uploaddir = WWW . '/images/';
        $ext = strtolower(preg_replace("#.+\.([a-z]+)$#i", "$1", $_FILES[$name]['name'])); // расширение картинки
        $types = array("image/gif", "image/png", "image/jpeg", "image/pjpeg", "image/x-png"); // массив допустимых расширений
        if($_FILES[$name]['size'] > 1048576){
            $res = array("error" => "Ошибка! Максимальный вес файла - 1 Мб!");
            exit(json_encode($res));
        }
        if($_FILES[$name]['error']){
            $res = array("error" => "Ошибка! Возможно, файл слишком большой.");
            exit(json_encode($res));
        }
        if(!in_array($_FILES[$name]['type'], $types)){
            $res = array("error" => "Допустимые расширения - .gif, .jpg, .png");
            exit(json_encode($res));
        }
        $new_name = md5(time()).".$ext";
        $uploadfile = $uploaddir.$new_name;
        if(@move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile)){
            if($name == 'single'){
                $_SESSION['single'] = $new_name;
            }else{
                $_SESSION['multi'][] = $new_name;
            }
            self::resize($uploadfile, $uploadfile, $wmax, $hmax, $ext);
            $res = array("file" => $new_name);
            exit(json_encode($res));
        }
    }

    /**
     * Метод для проверки ширину и высоту изображение
     * @param string $target путь к оригинальному файлу
     * @param string $dest путь сохранения обработанного файла
     * @param string $wmax максимальная ширина
     * @param string $hmax максимальная высота
     * @param string $ext расширение файла
     */
    public static function resize($target, $dest, $wmax, $hmax, $ext){
        list($w_orig, $h_orig) = getimagesize($target);
        $ratio = $w_orig / $h_orig; // =1 - квадрат, <1 - альбомная, >1 - книжная

        if(($wmax / $hmax) > $ratio){
            $wmax = $hmax * $ratio;
        }else{
            $hmax = $wmax / $ratio;
        }

        $img = "";
        // imagecreatefromjpeg | imagecreatefromgif | imagecreatefrompng
        switch($ext){
            case("gif"):
                $img = imagecreatefromgif($target);
                break;
            case("png"):
                $img = imagecreatefrompng($target);
                break;
            default:
                $img = imagecreatefromjpeg($target);
        }
        $newImg = imagecreatetruecolor($wmax, $hmax); // создаем оболочку для новой картинки

        if($ext == "png"){
            imagesavealpha($newImg, true); // сохранение альфа канала
            $transPng = imagecolorallocatealpha($newImg,0,0,0,127); // добавляем прозрачность
            imagefill($newImg, 0, 0, $transPng); // заливка
        }

        imagecopyresampled($newImg, $img, 0, 0, 0, 0, $wmax, $hmax, $w_orig, $h_orig); // копируем и ресайзим изображение
        switch($ext){
            case("gif"):
                imagegif($newImg, $dest);
                break;
            case("png"):
                imagepng($newImg, $dest);
                break;
            default:
                imagejpeg($newImg, $dest);
        }
        imagedestroy($newImg);
    }
}