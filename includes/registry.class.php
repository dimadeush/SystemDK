<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      registry.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class registry
{


    /*Array of variables
      @access private*/
    private $vars = [];


    /*Set undefined variables.
      @param string $index The index of the registry variable
      @param mixed $value The value to assign to the variable
      @return void*/
    public function __set($index, $value)
    {
        $this->vars[$index] = $value;
    }


    /*Get a variable from the registry
      @param mixed $index The index of the registry variable
      @return mixed*/
    public function &__get($index)
    {
        if (isset($this->vars[$index])) {
            return $this->vars[$index];
        } else {
            return null;
        }
    }


    public function __isset($index)
    {
        if (isset($this->vars[$index])) {
            return (false === empty($this->vars[$index]));
        } else {
            return null;
        }
    }
}