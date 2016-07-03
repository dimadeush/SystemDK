<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      block_model_account.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class block_account extends model_base
{


    public function index()
    {
        if ($this->registry->main_class->is_user()) {
            $result['block_content_array'] = $this->registry->main_class->get_user_name();
        } else {
            $result['block_content_array'] = "no";
        }

        return $result;
    }
}