<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_base.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
abstract class model_base {


    protected $registry;
    protected $db;


    public function __construct($registry) {
        $this->registry = $registry;
        $this->db = singleton::getinstance('db',$this->registry);
    }
}