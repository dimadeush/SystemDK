<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_guestbook extends controller_base {


    private $model_admin_guestbook;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_guestbook = new admin_guestbook($registry);
    }


    public function index() {
        $this->model_admin_guestbook->index();
    }


    public function guestbook_status() {
        $this->model_admin_guestbook->guestbook_status();
    }


    public function guestbook_edit() {
        $this->model_admin_guestbook->guestbook_edit();
    }


    public function guestbook_edit_inbase() {
        $this->model_admin_guestbook->guestbook_edit_inbase();
    }


    public function guestbook_config() {
        $this->model_admin_guestbook->guestbook_config();
    }


    public function guestbook_config_save() {
        $this->model_admin_guestbook->guestbook_config_save();
    }
}
?>