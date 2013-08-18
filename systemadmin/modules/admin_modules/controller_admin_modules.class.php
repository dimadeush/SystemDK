<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_modules.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_modules extends controller_base {


    private $model_admin_modules;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_modules = new admin_modules($registry);
    }


    public function index() {
        $this->model_admin_modules->index();
    }


    public function edit_module() {
        $this->model_admin_modules->edit_module();
    }


    public function edit_module_save() {
        $this->model_admin_modules->edit_module_save();
    }


    public function status_module() {
        $this->model_admin_modules->status_module();
    }
}
?>