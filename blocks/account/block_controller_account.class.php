<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      block_controller_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class block_controller_account extends controller_base
{


    private $model_block_account;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_block_account = singleton::getinstance('block_account', $registry);
    }


    public function index()
    {
        $block_content_array = $this->model_block_account->index();
        $this->assign_array($block_content_array);

        return $block_content = $this->fetch("blocks/account/account.html");
    }
}