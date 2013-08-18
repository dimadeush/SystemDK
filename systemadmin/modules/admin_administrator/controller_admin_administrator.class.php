<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_administrator.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_administrator extends controller_base {


    private $model_admin_administrator;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_administrator = new admin_administrator($registry);
    }


    public function index() {
        $this->model_admin_administrator->index();
    }


    public function mainadmin() {
        $this->model_admin_administrator->index();
    }


    public function logout() {
        $this->model_admin_administrator->logout();
    }


    public function mainadmin_edit() {
        $this->model_admin_administrator->mainadmin_edit();
    }


    public function mainadmin_edit_inbase() {
        $this->model_admin_administrator->mainadmin_edit_inbase();
    }
}

?>