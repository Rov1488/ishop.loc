<?php
/**
 Класс для меню
 */

namespace app\widgets\menu;


use ishop\App;
use ishop\Cache;

class Menu{

    protected $data;
    protected $tree;
    protected $menuHtml;
    protected $tpl;
    protected $container = 'ul';
    protected $class = 'menu';
    protected $table = 'category';
    protected $cache = 3600;
    protected $cachekey = 'ishop_menu';
    protected $attrs = [];
    protected $prepend = '';


    //Публичный конструктор будет запольнят не достоющие опции и свойства
    public function __construct($options = []){
        $this->tpl = __DIR__ . '/menu_tpl/menu.php';
        $this->getOptions($options);
        $this->run();
    }

    //Метод который запольняет указаный свойства передаваемы пользователм
    protected function getOptions($options){
        foreach ($options as $k => $v){
            if(property_exists($this, $k)){
                $this->$k = $v;
            }
        }
    }

    //Метод который формирует менюшку
    protected function run(){
        $cache = Cache::instance();
        $this->menuHtml = $cache->get($this->cachekey);
        if(!$this->menuHtml){
            $this->data = App::$app->getProperty('cats');
            if(!$this->data){
                $this->data = $cats = \R::getAssoc("SELECT * FROM {$this->table}");
            }
            $this->tree = $this->getTree();
            $this->menuHtml = $this->getMenuHtml($this->tree);
            if($this->cache){
                $cache->set($this->cachekey, $this->menuHtml, $this->cache);
            }
        }
        $this->output();
    }

    //Метод для вывода менюшки
    protected function output(){
        $attrs = '';
        if (!empty($this->attrs)){
            foreach ($this->attrs as $k => $v){
                $attrs .= " $k = '$v' ";
            }
        }
        echo "<{$this->container} class='{$this->class}' $attrs>";
            echo $this->prepend;
            echo $this->menuHtml;
        echo "</{$this->container}>";
    }

    //Метод для формирования дерева меню
    protected function getTree(){
        $tree = [];
        $data = $this->data;
        foreach ($data as $id => &$node){
            if(!$node['parent_id']){
                $tree[$id] = &$node;
            } else{
                $data[$node['parent_id']]['childs'][$id] = &$node;
            }
        }
        return $tree;
    }

    //Метод который будет получат HTML код виде дерево
    protected function getMenuHtml($tree, $tab = ''){
        $str = '';
        foreach ($tree as $id => $category){
            $str .= $this->catToTemplate($category, $tab, $id);
        }
        return $str;
    }

    //Метод для подключения шаблона по переданым кусочкам кода из метода getMenuHtml()
    protected function catToTemplate($category, $tab, $id){
        ob_start();
        require $this->tpl;
        return ob_get_clean();
    }


}