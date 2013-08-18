<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_shop.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_shop extends controller_base {


    private $model_shop;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_shop = new shop($registry);
    }


    public function index() {
        $this->model_shop->index();
    }


    public function categs() {
        $this->model_shop->categs();
    }


    public function subcategs() {
        $this->model_shop->subcategs();
    }


    public function items() {
        $this->model_shop->items();
    }


    public function item() {
        $this->model_shop->item();
    }


    public function cart() {
        $this->model_shop->cart();
    }


    public function checkout() {
        $this->model_shop->checkout();
    }


    public function checkout2() {
        $this->model_shop->checkout2();
    }


    public function insert() {
        $this->model_shop->insert();
    }
}

?>