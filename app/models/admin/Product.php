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

    //Загружает и проверяет данные формы товара (общая часть добавления и редактирования).
    //При ошибке пишет её в сессию, возвращает загруженную картинку в сессию и возвращает false
    public function loadForm(array $data){
        $this->load($data);
        // колонки status и hit — ENUM('0','1'): нужны строки. Число MySQL понимает как номер элемента
        // (1 -> '0', 0 -> ошибка "Data truncated")
        $this->attributes['status'] = !empty($this->attributes['status']) ? '1' : '0';
        $this->attributes['hit'] = !empty($this->attributes['hit']) ? '1' : '0';
        $this->getImg();
        if (!$this->validate($data)){
            $this->getErrors();
            if (!empty($this->attributes['img'])){
                $_SESSION['single'] = $this->attributes['img'];
            }
            return false;
        }
        return true;
    }

    //Сохраняет всё, что связано с товаром, после записи в таблицу product:
    //галерею, alias, фильтры, связанные товары и модификации
    public function saveRelations($id, array $data){
        $this->saveGallery($id);
        $product = \R::load('product', $id);
        $product->alias = self::createAlias('product', 'alias', $data['title'], $id);
        \R::store($product);
        $this->editFilter($id, $data);
        $this->editRelatedProduct($id, $data);
        $this->editModificationProduct($id, $data);
    }

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

    //метод для свянанных товаров
    public function editRelatedProduct($id, $data){
        self::replaceLinks('related_product', 'product_id', 'related_id', $id, $data['related'] ?? []);
    }

    //Метод для добавления, управления фильтрами и атрибутами
    public function editFilter($id, $data){
        self::replaceLinks('attribute_product', 'product_id', 'attr_id', $id, $data['attrs'] ?? []);
    }

    //Перезаписывает связи товара в таблице: удаляет старые и вставляет новые id
    protected static function replaceLinks($table, $productField, $linkField, $id, $ids){
        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$ids))));
        \R::exec("DELETE FROM $table WHERE $productField = ?", [$id]);
        if (empty($ids)){
            return;
        }
        $values = [];
        foreach ($ids as $v){
            $values[] = $id;
            $values[] = $v;
        }
        $placeholders = implode(', ', array_fill(0, count($ids), '(?, ?)'));
        \R::exec("INSERT INTO $table ($productField, $linkField) VALUES $placeholders", $values);
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
            $values = [];
            foreach($_SESSION['multi'] as $v){
                $values[] = $v;
                $values[] = $id;
            }
            $placeholders = implode(', ', array_fill(0, count($_SESSION['multi']), '(?, ?)'));
            \R::exec("INSERT INTO gallery (img, product_id) VALUES $placeholders", $values);
            unset($_SESSION['multi']);
         }
    }

    //Загруженные через админку картинки называются 32 hex-символами (см. uploadImg).
    //Файлы шаблона и общую заглушку no_image.jpg удаление не трогает никогда
    const UPLOAD_PATTERN = '/^[0-9a-f]{32}\.(jpe?g|png|gif)$/';

    //Используется ли картинка: товары, галерея, бренды или вставка в описание товара
    public static function isImageUsed($file){
        return \R::count('product', 'img = ?', [$file])
            || \R::count('gallery', 'img = ?', [$file])
            || \R::count('brand', 'img = ?', [$file])
            || \R::count('product', 'content LIKE ?', ['%' . $file . '%']);
    }

    //Удаляет загруженную картинку из public/images, если на неё больше никто не ссылается
    public static function deleteImage($file){
        if (!is_string($file) || !preg_match(self::UPLOAD_PATTERN, $file) || self::isImageUsed($file)){
            return false;
        }
        $path = WWW . '/images/' . $file;
        return is_file($path) && unlink($path);
    }

    //Загруженные картинки, на которые никто не ссылается.
    //Файлы моложе $minAge секунд пропускаем: их могут прямо сейчас загружать для нового товара
    public static function getUnusedImages($minAge = 86400){
        $used = array_flip(array_merge(
            \R::getCol('SELECT img FROM product'),
            \R::getCol('SELECT img FROM gallery'),
            \R::getCol('SELECT img FROM brand'),
            (array)($_SESSION['multi'] ?? []),
            [$_SESSION['single'] ?? '']
        ));
        $content = implode(' ', \R::getCol("SELECT content FROM product WHERE content LIKE '%images/%'"));
        $unused = [];
        foreach (scandir(WWW . '/images') as $file){
            if (!preg_match(self::UPLOAD_PATTERN, $file) || isset($used[$file]) || strpos($content, $file) !== false){
                continue;
            }
            if (time() - filemtime(WWW . '/images/' . $file) < $minAge){
                continue;
            }
            $unused[] = $file;
        }
        return $unused;
    }

    //Отдаёт JSON-ответ загрузчику и завершает скрипт.
    //AjaxUpload читает ответ из iframe, поэтому отдаём text/html и экранируем <, >, & внутри JSON
    public static function jsonExit(array $data){
        while (ob_get_level()){
            ob_end_clean();
        }
        header('Content-Type: text/html; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);
        exit;
    }

    //Метод для загрузки изображение

    public function uploadImg($name, $wmax, $hmax){
        // буферизуем вывод, чтобы notice/warning не испортили JSON
        ob_start();
        $uploaddir = WWW . '/images/';

        if (empty($_FILES[$name]['tmp_name'])){
            self::jsonExit(['error' => 'Файл не загружен']);
        }
        if($_FILES[$name]['error']){
            self::jsonExit(["error" => "Ошибка! Возможно, файл слишком большой."]);
        }
        if($_FILES[$name]['size'] > 1048576){
            self::jsonExit(["error" => "Ошибка! Максимальный вес файла - 1 Мб!"]);
        }
        // тип определяем по содержимому файла, а не по данным браузера и имени файла
        $types = [IMAGETYPE_GIF => 'gif', IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg'];
        $info = @getimagesize($_FILES[$name]['tmp_name']);
        if(!$info || !isset($types[$info[2]])){
            self::jsonExit(["error" => "Допустимые расширения - .gif, .jpg, .png"]);
        }
        $ext = $types[$info[2]];
        $new_name = bin2hex(random_bytes(16)) . ".$ext";
        $uploadfile = $uploaddir.$new_name;
        if(!move_uploaded_file($_FILES[$name]['tmp_name'], $uploadfile)){
            self::jsonExit(["error" => "Не удалось переместить загруженный файл."]);
        }
        if($name == 'single'){
            // предыдущая загрузка, ещё не сохранённая в товаре, больше не нужна
            if(!empty($_SESSION['single'])){
                self::deleteImage($_SESSION['single']);
            }
            $_SESSION['single'] = $new_name;
        }else{
            $_SESSION['multi'][] = $new_name;
        }
        self::resize($uploadfile, $uploadfile, $wmax, $hmax, $ext);
        self::jsonExit(["file" => $new_name]);
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