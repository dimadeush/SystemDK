<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_feedback extends controller_base {


    private $model_feedback;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_feedback = new feedback($registry);
    }


    public function index() {
        $this->model_feedback->index();
    }


    public function feedback_send() {
        $this->model_feedback->feedback_send();
    }
}

?>