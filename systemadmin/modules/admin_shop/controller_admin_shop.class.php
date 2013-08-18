<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_shop extends controller_base {


    private $model_admin_shop;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_shop = new admin_shop($registry);
    }


    public function index() {
        $this->model_admin_shop->index();
    }


    public function shop_edit() {
        $this->model_admin_shop->shop_edit();
    }


    public function shop_edit_inbase() {
        $this->model_admin_shop->shop_edit_inbase();
    }


    public function shop_add() {
        $this->model_admin_shop->shop_add();
    }


    public function shop_add_inbase() {
        $this->model_admin_shop->shop_add_inbase();
    }


    public function shop_status() {
        $this->model_admin_shop->shop_status();
    }


    public function shop_items() {
        $this->model_admin_shop->shop_items();
    }


    public function shop_edit_item() {
        $this->model_admin_shop->shop_edit_item();
    }


    public function shop_editi_inbase() {
        $this->model_admin_shop->shop_editi_inbase();
    }


    public function shop_add_item() {
        $this->model_admin_shop->shop_add_item();
    }


    public function shop_addi_inbase() {
        $this->model_admin_shop->shop_addi_inbase();
    }


    public function shop_sort() {
        $this->model_admin_shop->shop_sort();
    }


    public function shop_sort_save() {
        $this->model_admin_shop->shop_sort_save();
    }


    public function shop_orders() {
        $this->model_admin_shop->shop_orders();
    }


    public function shop_orderproc() {
        $this->model_admin_shop->shop_orderproc();
    }


    public function shop_order_save() {
        $this->model_admin_shop->shop_order_save();
    }


    public function shop_order_status() {
        $this->model_admin_shop->shop_order_status();
    }


    public function shop_orderi_edit() {
        $this->model_admin_shop->shop_orderi_edit();
    }


    public function shop_orderi_save() {
        $this->model_admin_shop->shop_orderi_save();
    }


    public function shop_methods() {
        $this->model_admin_shop->shop_methods();
    }


    public function shop_edit_method() {
        $this->model_admin_shop->shop_edit_method();
    }


    public function shop_method_save() {
        $this->model_admin_shop->shop_method_save();
    }


    public function shop_method_add() {
        $this->model_admin_shop->shop_method_add();
    }


    public function shop_method_save2() {
        $this->model_admin_shop->shop_method_save2();
    }


    public function shop_config() {
        $this->model_admin_shop->shop_config();
    }


    public function shop_config_save() {
        $this->model_admin_shop->shop_config_save();
    }
}

?>