<?php
/**
Служебные функции
 */

function debug($arr, $die = false){
    echo '<pre>'.print_r($arr, true).'</pre>';
   if ($die) die;
}

function redirect($http = false){
    if($http){
        $redirect = $http;
    } else{
        $redirect = PATH;
        // возвращаем на предыдущую страницу только в пределах своего сайта
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        if ($referer && parse_url($referer, PHP_URL_HOST) === parse_url(PATH, PHP_URL_HOST)){
            $redirect = $referer;
        }
    }
    header("Location: $redirect");
    exit;
}


function h($str){
    return htmlspecialchars((string)$str, ENT_QUOTES);
}

//CSRF-токен: один на сессию, передаётся с каждым POST-запросом
function csrf_token(){
    if (empty($_SESSION['_csrf'])){
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

//Скрытое поле с токеном для форм
function csrf_field(){
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

//Проверка токена из поля формы или заголовка X-CSRF-Token (AJAX)
function csrf_check(){
    $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($token) && !empty($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}