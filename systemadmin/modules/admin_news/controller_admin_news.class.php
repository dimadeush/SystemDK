<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_news extends controller_base {


    private $model_admin_news;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_news = new admin_news($registry);
    }


    public function index() {
        $this->model_admin_news->index();
    }


    public function news_current() {
        $this->model_admin_news->news_current();
    }


    public function news_edit() {
        $this->model_admin_news->news_edit();
    }


    public function news_edit_inbase() {
        $this->model_admin_news->news_edit_inbase();
    }


    public function news_add() {
        $this->model_admin_news->news_add();
    }


    public function news_add_inbase() {
        $this->model_admin_news->news_add_inbase();
    }


    public function news_category() {
        $this->model_admin_news->news_category();
    }


    public function category_add() {
        $this->model_admin_news->category_add();
    }


    public function category_add_inbase() {
        $this->model_admin_news->category_add_inbase();
    }


    public function category_edit() {
        $this->model_admin_news->category_edit();
    }


    public function category_edit_inbase() {
        $this->model_admin_news->category_edit_inbase();
    }


    public function category_delete() {
        $this->model_admin_news->category_delete();
    }


    public function news_status() {
        $this->model_admin_news->news_status();
    }


    public function news_config() {
        $this->model_admin_news->news_config();
    }


    public function news_config_save() {
        $this->model_admin_news->news_config_save();
    }
}

?>