<?php
/**
 * Created by PhpStorm.
 * User: r.pulatov
 * Date: 23.11.2019
 * Time: 20:54
 */

namespace ishop;


trait TSingletone
{
    private static $instance;

    public static function instance(){
        if(self::$instance === null){
            self::$instance = new self;
        }
        return self::$instance;
    }
}