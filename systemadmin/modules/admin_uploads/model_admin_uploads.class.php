<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_uploads.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class admin_uploads extends model_base {


    private $error;
    private $error_array;
    private $result;


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','error_array','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index() {
    }
}