<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_base.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
abstract class controller_base {


    protected $registry;
    protected $template;
    protected $model;


    public function __construct($registry) {
        $this->registry = $registry;
        $this->template = singleton::getinstance('template',$this->registry);
        $this->model = singleton::getinstance('model_main',$this->registry);
    }


    abstract function index();
}

?>