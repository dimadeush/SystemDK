<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      template.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
include_once(__SITE_PATH . "/smarty/Smarty.class.php");

class template
{


    /**
     * @var registry
     */
    private $registry;
    // Array of template variables
    //private $vars = array();
    /**
     * @var \Smarty
     */
    private $smarty;


    public function __construct($registry)
    {
        $this->registry = $registry;
        //Instantiate template engine
        $this->smarty = new Smarty();
        //Set template engine properties
        $this->smarty->setConfigDir(
            [
                "1" => __SITE_PATH . '/themes/' . SITE_THEME . '/configs/',
                "2" => __SITE_PATH . '/themes/' . SITE_THEME . '/languages/',
            ]
        );
        $this->smarty->setTemplateDir(__SITE_PATH . '/themes/' . SITE_THEME . '/templates/');
        $this->smarty->setCompileDir(__SITE_PATH . '/themes/' . SITE_THEME . '/templates_c/');
        $this->smarty->setCacheDir(__SITE_PATH . '/themes/' . SITE_THEME . '/cache/');
        if (SMARTY_DEBUGGING == "yes") {
            $this->smarty->debugging = "true";
        }
        if (SMARTY_CACHING == "yes") {
            $this->smarty->caching = Smarty::CACHING_LIFETIME_SAVED;
        }
        if (defined('CACHE_LIFETIME') && CACHE_LIFETIME != "") {
            $this->smarty->cache_lifetime = CACHE_LIFETIME;
        }
    }


    public function __set($index, $value)
    {
        //$this->vars[$index] = $value;
        $this->smarty->assign($value['name'], $value['value']);
    }


    public function register_plugins()
    {
        $this->smarty->registerPlugin('function', 'gentimewatch', 'funcgentimewatch');
    }


    public function configLoad($config)
    {
        $this->smarty->configLoad($config);
    }


    public function display($name, $group = null)
    {
        $template_name = $name;
        // Assign all the control variables to the engine
        /*foreach($this->vars as $templatevariable)
         {
           $this->smarty->assign($templatevariable["name"], $templatevariable["value"]);
         }*/
        if (isset($GLOBALS['EXECS'])) {
            $execs = $GLOBALS['EXECS'];
        } else {
            $execs = 0;
        }
        if (isset($GLOBALS['CACHED'])) {
            $cached = $GLOBALS['CACHED'];
        } else {
            $cached = 0;
        }
        $sql_stat = $execs + $cached;
        $this->smarty->assign("sqlstat", $sql_stat);
        $this->smarty->assign("sqlstatcached", $cached);
        //Display the page using template
        if (isset($group) && $group != "") {
            $this->smarty->display($template_name, $group);
        } else {
            $this->smarty->display($template_name);
        }
        $this->registry->main_class->gzip_out();
    }


    public function fetch($name, $group = false)
    {
        $templatename = $name;
        if (isset($group) && $group != "") {
            return $this->smarty->fetch($templatename, $group);
        } else {
            return $this->smarty->fetch($templatename);
        }
    }


    public function isCached($template, $group)
    {
        return $this->smarty->isCached($template, $group);
    }


    public function clearCache($template, $group)
    {
        $this->smarty->clearCache($template, $group);
    }


    public function clearAllCache()
    {
        return $this->smarty->clearAllCache();
    }


    public function getConfigVars($index)
    {
        return $this->smarty->getConfigVars($index);
    }
}