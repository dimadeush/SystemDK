<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_system_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_system_pages extends controller_base {


    private $model_admin_system_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_system_pages = new admin_system_pages($registry);
    }


    public function index() {
        $this->model_admin_system_pages->index();
    }


    public function systempage_add() {
        $this->model_admin_system_pages->systempage_add();
    }


    public function systempage_add_inbase() {
        $this->model_admin_system_pages->systempage_add_inbase();
    }


    public function systempage_status() {
        $this->model_admin_system_pages->systempage_status();
    }


    public function systempage_edit() {
        $this->model_admin_system_pages->systempage_edit();
    }


    public function systempage_edit_inbase() {
        $this->model_admin_system_pages->systempage_edit_inbase();
    }
}

?>