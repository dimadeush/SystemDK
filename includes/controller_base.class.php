<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_base.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
abstract class controller_base
{


    /**
     * @var registry
     */
    protected $registry;
    /**
     * @var template
     */
    protected $template;
    /**
     * @var model_main
     */
    protected $model;


    public function __construct($registry)
    {
        $this->registry = $registry;
        $this->template = singleton::getinstance('template', $this->registry);
        $this->model = singleton::getinstance('model_main', $this->registry);
    }


    abstract function index();


    protected function configLoad($config)
    {
        $this->template->configLoad($config);
    }


    protected function getConfigVars($index)
    {
        return $this->template->getConfigVars($index);
    }


    protected function assign($index, $value)
    {
        $this->template->$index = ["name" => $index, "value" => $value];
    }


    protected function assign_array($array)
    {
        if (!empty($array) && is_array($array)) {
            foreach ($array as $key => $value) {
                $this->assign($key, $value);
            }
        }
    }


    protected function run($name)
    {
        $this->template->$name();
    }


    protected function isCached($template, $group)
    {
        return $this->template->isCached($template, $group);
    }


    public function clearCache($template, $group)
    {
        $this->template->clearCache($template, $group);
    }


    public function clearAllCache()
    {
        return $this->template->clearAllCache();
    }


    protected function fetch($template, $group = false)
    {
        return $this->template->fetch($template, $group);
    }


    protected function display($name, $group)
    {
        $this->template->display($name, $group);
    }
}