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