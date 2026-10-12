<?php
/*
Модел пользователя
 */

namespace app\models;


class User extends AppModel{

 public $attributes = [
     'login' => '',
     'password' => '',
     'name' => '',
     'email' => '',
     'address' => '',
     'role' => 'user',
 ];

 public $rules = [
     'required' => [
         ['login'],
         ['password'],
         ['name'],
         ['email'],
         ['address'],
     ],
     'email' => [
         ['email'],
     ],
     'lengthMin' => [
         ['password', 6],
     ]
 ];

 //Метод для проверки уникальности пользователя
    public function checkUnique(){
        $user = \R::findOne('user', 'login = ? OR email = ?', [$this->attributes['login'], $this->attributes['email']]);
        if ($user){
            if ($user->login == $this->attributes['login']){
                $this->errors['unique'][] = 'Этот логин уже занят выберите другой логин';
            }
            if ($user->email == $this->attributes['email']){
                $this->errors['unique'][] = 'Этот email уже занят выберите другой email';
            }
            return false;
        }
        return true;
    }
//Метод для проверки логин и пароля пользователя в моделе преред авторизации
    public function login($isAdmin = false){
        $login = trim($_POST['login'] ?? '');
        // пароль не обрезаем: при регистрации он хешируется как есть
        $password = $_POST['password'] ?? '';
        if ($login && $password){
            if ($isAdmin){
                $user = \R::findOne('user', "login = ? AND role = 'admin'", [$login]);
            }else{
                $user = \R::findOne('user', "login = ? ", [$login]);
            }
            if ($user){
                if (password_verify($password, $user->password)){
                   // новый id сессии после входа защищает от фиксации сессии
                   session_regenerate_id(true);
                   foreach ($user as $k => $v){
                       if ($k != 'password') $_SESSION['user'][$k] = $v;
                   }
                   return true;
                }
            }
        }
        return false;
    }

    public static function checkAuth(){
        return isset($_SESSION['user']);
    }

    public static function isAdmin(){
        return (isset($_SESSION['user']) && $_SESSION['user']['role'] == 'admin');
    }

}