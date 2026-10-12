<?php
/**
 Класс Model будет работат с БД, с данными, валидация данных, фунуции для оработки данных и т.д.
 */

namespace ishop\base;


use ishop\Db;
use Valitron\Validator;

abstract class Model{

    public $attributes = [];
    public $errors = [];
    public $rules = [];

    public function __construct(){
       Db::instance();
    }

    //Метод который будет загружат данные из формы регистрации и проверят соответствуюет данные с свойству $attributes

    public function load($data){
        foreach ($this->attributes as $name => $value){
            if (isset($data[$name])){
                $this->attributes[$name] = $data[$name];
            }
        }
    }

    //Метод для сохранения в БД пользовательских данных при регистрации
    public function save($table, $valid = true){
        if ($valid){
            $tbl = \R::dispense($table);
        } else{
            $tbl = \R::xdispense($table);
        }

        foreach ($this->attributes as $name => $value){
            $tbl->$name = $value;
        }
        return \R::store($tbl);
    }

    //Метод для обнавления в БД пользовательских данных

    public function update($table, $id){
        $bean = \R::load($table, $id);
        foreach ($this->attributes as $name => $value){
            $bean->$name = $value;
        }
        return \R::store($bean);
    }

    //Метод для валидаци и проверки данных
    public function validate($data){
        Validator::langDir(WWW . '/validator/lang');
        Validator::lang('ru');
        $v = new Validator($data);
        $v->rules($this->rules);

        if ($v->validate()){
            return true;
        }
        $this->errors = $v->errors();
        return false;


    }

    //Метод для вывода ошибок
    public function getErrors(){
        $errors = '<ul>';
        foreach ($this->errors as $error){
            foreach ($error as $item){
                $errors .= "<li>$item</li>";
            }

        }
        $errors .= '</ul>';
        $_SESSION['error'] = $errors;
    }



}