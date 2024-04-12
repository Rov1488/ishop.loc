<?php
/*
Виджет Фильтра
 */

namespace app\widgets\filter;


use ishop\Cache;

class Filter{

    public $groups;
    public $attrs;
    public $tpl;
    public $filter;

    public function __construct($filter = null, $tpl = ''){
        $this->filter = $filter;
        $this->tpl = $tpl ?: __DIR__ . '/filter_tpl.php';
        $this->run();
    }
    protected function run(){
        $cache = Cache::instance();
        $this->groups = $cache->get('filter_group');
        if(!$this->groups){
            $this->groups = $this->getGroups();
            $cache->set('filter_group', $this->groups, 30);
        }
        $this->attrs = $cache->get('filter_attrs');
        if(!$this->attrs){
            $this->attrs = self::getAttrs();
            $cache->set('filter_attrs', $this->attrs, 30);
        }
        $filters = $this->getHtml();
        echo $filters;
    }

    //Метод для вывода фильтров в Html шаблоне
    protected function getHtml(){
        ob_start();
        $filter = self::getFilter();
        if (!empty($filter)){
            $filter = explode(',', $filter);
        }
        require $this->tpl;
        return ob_get_clean();
    }
//Метод для выбора групп из БД
    protected function getGroups(){
        return \R::getAssoc('SELECT id, title FROM attribute_group');
    }

    //Метод для выбора аттрибутов
    protected static function getAttrs(){
        $data = \R::getAssoc('SELECT * FROM attribute_value');
        $attrs = [];
        foreach($data as $k => $v){
            $attrs[$v['attr_group_id']][$k] = $v['value'];
        }
        return $attrs;
    }

    //Метод для вывода в $_GET параметре путь аттрибутов и заменить лишный запетый
    public static function getFilter(){
        $filter = null;
        if (!empty($_GET['filter'])){
            $filter = preg_replace("#[^\d,]+#", '', $_GET['filter']);
            $filter = trim($filter, ',');
        }
        return $filter;
    }

//Метод фильтратци группы
    public static function getCountGroups($filter){
        $filters = explode(',', $filter);
        $cache = Cache::instance();
        $attrs = $cache->get('filter_attrs');
        if(!$attrs){
            $attrs = self::getAttrs();
        }
        $data = [];
        foreach($attrs as $key => $item){
            foreach($item as $k => $v){
                if(in_array($k, $filters)){
                    $data[] = $key;
                    break;
                }
            }
        }
        return count($data);
    }

}