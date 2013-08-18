<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_main_pages extends controller_base {


    private $model_admin_main_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_main_pages = new admin_main_pages($registry);
    }


    public function index() {
        $this->model_admin_main_pages->index();
    }


    public function mainpage_add() {
        $this->model_admin_main_pages->mainpage_add();
    }


    public function mainpage_add_inbase() {
        $this->model_admin_main_pages->mainpage_add_inbase();
    }


    public function mainpage_status() {
        $this->model_admin_main_pages->mainpage_status();
    }


    public function mainpage_edit() {
        $this->model_admin_main_pages->mainpage_edit();
    }


    public function mainpage_edit_inbase() {
        $this->model_admin_main_pages->mainpage_edit_inbase();
    }
}
?>