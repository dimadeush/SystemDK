<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_including.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_including extends controller_base {


    private $model_admin_including;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_including = new admin_including($registry);
    }


    public function index() {
        $this->model_admin_including->index();
    }


    public function including_save() {
        $this->model_admin_including->including_save();
    }
}

?>