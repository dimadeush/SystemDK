<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_feedback extends controller_base {


    private $model_admin_feedback;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_feedback = new admin_feedback($registry);
    }


    public function index() {
        $this->model_admin_feedback->index();
    }


    public function feedback_add() {
        $this->model_admin_feedback->feedback_add();
    }


    public function feedback_add_inbase() {
        $this->model_admin_feedback->feedback_add_inbase();
    }


    public function feedback_edit() {
        $this->model_admin_feedback->feedback_edit();
    }


    public function feedback_edit_inbase() {
        $this->model_admin_feedback->feedback_edit_inbase();
    }


    public function feedback_status() {
        $this->model_admin_feedback->feedback_status();
    }
}