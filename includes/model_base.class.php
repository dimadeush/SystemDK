<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_base.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
abstract class model_base
{


    /**
     * @var registry
     */
    protected $registry;
    /**
     * @var db
     */
    protected $db;


    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->db = singleton::getinstance('db', $this->registry);
    }
}