<?php
/**
Базовый класс для вида
 */

namespace ishop\base;


class View{

    public $route;
    public $controller;
    public $model;
    public $view = 'main';
    public $prefix;
    public $layout = LAYOUT;
    public $data = [];
    public $meta = [];

    public function __construct($route, $layout, $view, $meta){
        $this->route = $route;
        $this->controller = $route['controller'];
        $this->model = $route['controller'];
        $this->view = $view;
        $this->prefix = $route['prefix'];
        $this->meta = $meta;
        if($layout === false){
            $this->layout = false;
        } else{
            $this->layout = $layout ?: LAYOUT;
        }
    }

    //Метод рендор будет формировать данные для странице пользователя

    public function render($data){

        //Проверка является ли переданные данны в переменой $data массивом

        if(is_array($data)) extract($data);

        //Выбераем нужный шаблон и папки views
      $viewFile = APP . "/views/{$this->prefix}{$this->controller}/{$this->view}.php";

      if(is_file($viewFile)){
          ob_start();
          require_once $viewFile;
         $content = ob_get_clean();

      } else {
          throw new \Exception("Не найден вид {$viewFile}", 500);
      }

      if( false !== $this->layout){
          $layoutFile = APP . "/views/layouts/{$this->layout}.php";
          if(is_file($layoutFile)){
              require_once $layoutFile;
          } else{
              throw new \Exception("Не найден шаблон {$layoutFile}", 500);
          }
      }


    }
    //Метод для описание мета тегов в шаблоне
    public function getMeta(){
        $output = '<meta name="description" content="' . $this->meta['desc'] .'">'. PHP_EOL;
        $output .= '<meta name="keywords" content="' . $this->meta['keywords'] .'">'. PHP_EOL;
        $output .= '<title>'. $this->meta['title'] .'</title>'. PHP_EOL;
        return $output;

    }

}