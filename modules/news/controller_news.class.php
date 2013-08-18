<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_news extends controller_base {


    private $model_news;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_news = new news($registry);
    }


    public function index() {
        $this->model_news->index();
    }


    public function newsread() {
        $this->model_news->newsread();
    }
}

?>