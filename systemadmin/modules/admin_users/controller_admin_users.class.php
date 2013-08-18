<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_users.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_users extends controller_base {


    private $model_admin_users;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_users = new admin_users($registry);
    }


    public function index() {
        $this->model_admin_users->index();
    }


    public function user_add() {
        $this->model_admin_users->user_add();
    }


    public function user_add_inbase() {
        $this->model_admin_users->user_add_inbase();
    }


    public function user_status() {
        $this->model_admin_users->user_status();
    }


    public function user_edit() {
        $this->model_admin_users->user_edit();
    }


    public function user_edit_inbase() {
        $this->model_admin_users->user_edit_inbase();
    }
}

?>