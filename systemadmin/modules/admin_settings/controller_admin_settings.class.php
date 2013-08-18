<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_settings.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_settings extends controller_base {


    private $model_admin_settings;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_settings = new admin_settings($registry);
    }


    public function index() {
        $this->model_admin_settings->index();
    }


    public function settingssave() {
        $this->model_admin_settings->settingssave();
    }


    public function clearallcache() {
        $this->model_admin_settings->clearallcache();
    }
}

?>