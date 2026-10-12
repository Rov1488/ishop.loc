<?php
/**
Класс обработки ошибок
 */

namespace ishop;


class ErrorHandler
{
    public function __construct(){
        // ошибки пишем в лог всегда, а на экран выводим только в режиме разработки
        error_reporting(E_ALL);
        ini_set('display_errors', DEBUG ? '1' : '0');
        ini_set('log_errors', '1');
        ini_set('error_log', ROOT . '/tmp/errors.log');
        set_error_handler([$this, 'errorHandler']);
        set_exception_handler([$this, 'exceptionHandler']);
    }

    // Устаревшие вызовы в сторонних библиотеках (swiftmailer на PHP 8.2+) мы исправить не можем.
    // Их вывод ломает header()/redirect, поэтому такие сообщения пропускаем; остальное обрабатывает PHP
    public function errorHandler($errno, $errstr, $errfile, $errline){
        $file = str_replace('\\', '/', $errfile);
        $isVendor = strpos($file, '/vendor/') !== false && strpos($file, '/vendor/ishop/') === false;
        if ($isVendor && ($errno & (E_DEPRECATED | E_USER_DEPRECATED))){
            return true;
        }
        return false;
    }

    public function exceptionHandler($e){
        $this->logErrors($e->getMessage(), $e->getFile(), $e->getLine());
        // код исключения PDO/PHP (например 23000) не является HTTP-статусом
        $code = (int)$e->getCode();
        if ($code < 400 || $code > 599){
            $code = 500;
        }
        $this->displayError('Исключение', $e->getMessage(), $e->getFile(), $e->getLine(), $code);
    }

    //Метод для логирования ошибки
    protected function logErrors($message = '', $file = '', $line = ''){
        error_log("[". date('Y-m-d H:i:s') ."] Текст ошибки: {$message} | Файл: {$file} | Сторка: {$line}\n-------------------------\n", 3, ROOT . '/tmp/errors.log');
    }

    protected function displayError($erron, $errstr, $errfile, $errline, $responce = 404){
        http_response_code($responce);
        if($responce == 404 && !DEBUG){
            require WWW . '/errors/404.php';
            die;
        }
        if(DEBUG){
            require WWW . '/errors/dev.php';
        }
        else{
            require WWW . '/errors/prod.php';
        }
    }
}