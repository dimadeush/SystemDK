<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_main_pages extends controller_base {


    private $model_main_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_main_pages = new main_pages($registry);
    }


    public function index() {
        $this->model_main_pages->index();
    }
}

?>