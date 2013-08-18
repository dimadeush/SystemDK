<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_guestbook extends controller_base {


    private $model_guestbook;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_guestbook = new guestbook($registry);
    }


    public function index() {
        $this->model_guestbook->index();
    }


    public function add() {
        $this->model_guestbook->add();
    }


    public function insert() {
        $this->model_guestbook->insert();
    }
}

?>