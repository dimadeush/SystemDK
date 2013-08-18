<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_uploads.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_uploads extends controller_base {


    private $model_admin_uploads;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_uploads = new admin_uploads($registry);
    }


    public function index() {
        $this->model_admin_uploads->index();
    }
}

?>