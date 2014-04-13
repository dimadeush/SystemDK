<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      singleton.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class singleton {


    static private $objects = array();


    private function __construct() {
    }


    private function __clone() {
    }


    private function __wakeup() {
    }


    public static function getinstance($class,$registry) {
        $instance = $class;
        if(!array_key_exists($instance,self::$objects)) {
            if($class === 'controller_theme') {
                $class = $class.'_'.SITE_THEME;
            }
            self::$objects[$instance] = new $class($registry);
        }
        return self::$objects[$instance];
    }
}