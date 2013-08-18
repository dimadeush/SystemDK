<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_messages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_messages extends controller_base {


    private $model_admin_messages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_messages = new admin_messages($registry);
    }


    public function index() {
        $this->model_admin_messages->index();
    }


    public function message_add() {
        $this->model_admin_messages->message_add();
    }


    public function message_add_inbase() {
        $this->model_admin_messages->message_add_inbase();
    }


    public function message_status() {
        $this->model_admin_messages->message_status();
    }


    public function message_edit() {
        $this->model_admin_messages->message_edit();
    }


    public function message_edit_inbase() {
        $this->model_admin_messages->message_edit_inbase();
    }
}
?>