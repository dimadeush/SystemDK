<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_menu.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_menu extends controller_base {


    private $model_admin_menu;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_menu = new admin_menu($registry);
    }


    public function index() {
        $this->model_admin_menu->index();
    }


    public function menu_save() {
        $this->model_admin_menu->menu_save();
    }


    public function menu_status() {
        $this->model_admin_menu->menu_status();
    }


    public function menu_select_type() {
        $this->model_admin_menu->menu_select_type();
    }


    public function menu_add() {
        $this->model_admin_menu->menu_add();
    }


    public function menu_add_inbase() {
        $this->model_admin_menu->menu_add_inbase();
    }


    public function menu_edit() {
        $this->model_admin_menu->menu_edit();
    }


    public function menu_edit_inbase() {
        $this->model_admin_menu->menu_edit_inbase();
    }
}
?>