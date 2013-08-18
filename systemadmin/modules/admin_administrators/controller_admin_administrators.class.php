<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_administrators.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_administrators extends controller_base {


    private $model_admin_administrators;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_administrators = new admin_administrators($registry);
    }


    public function index() {
        $this->model_admin_administrators->index();
    }


    public function admin_add() {
        $this->model_admin_administrators->admin_add();
    }


    public function admin_add_inbase() {
        $this->model_admin_administrators->admin_add_inbase();
    }


    public function admin_status() {
        $this->model_admin_administrators->admin_status();
    }


    public function admin_edit() {
        $this->model_admin_administrators->admin_edit();
    }


    public function admin_edit_inbase() {
        $this->model_admin_administrators->admin_edit_inbase();
    }
}

?>