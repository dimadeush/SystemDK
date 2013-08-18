<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_banip.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_admin_banip extends controller_base {


    private $model_admin_banip;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_banip = new admin_banip($registry);
    }


    public function index() {
        $this->model_admin_banip->index();
    }


    public function banip_save() {
        $this->model_admin_banip->banip_save();
    }
}

?>