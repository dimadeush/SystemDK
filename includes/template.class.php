<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      template.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
include_once(__SITE_PATH."/smarty/Smarty.class.php");
class template {


    /*The template registry
    @access private */
    private $registry;
    /*Array of template variables
    @access private */
    //private $vars = array();
    //The smarty template engine engine
    private $smarty;


    /*Default constructor
    @access public
    @return void */
    public function __construct($registry) {
        //Store the registry locally
        $this->registry = $registry;
        //Instantiate template engine
        $this->smarty = new Smarty();
        //Set template engine properties
        $this->smarty->setConfigDir(array(
                                         "1" => __SITE_PATH.'/themes/'.SITE_THEME.'/configs/',
                                         "2" => __SITE_PATH.'/themes/'.SITE_THEME.'/languages/'
                                    ));
        $this->smarty->setTemplateDir(__SITE_PATH.'/themes/'.SITE_THEME.'/templates/');
        $this->smarty->setCompileDir(__SITE_PATH.'/themes/'.SITE_THEME.'/templates_c/');
        $this->smarty->setCacheDir(__SITE_PATH.'/themes/'.SITE_THEME.'/cache/');
        if(SMARTY_DEBUGGING == "yes") {
            $this->smarty->debugging = "true";
        }
        if(SMARTY_CACHING == "yes") {
            $this->smarty->caching = Smarty::CACHING_LIFETIME_SAVED;
        }
        if(defined('CACHE_LIFETIME') and CACHE_LIFETIME != "") {
            $this->smarty->cache_lifetime = CACHE_LIFETIME;
        }
    }


    /*Set template variables
    @param string $index
    @param mixed $value
    @return void */
    public function __set($index,$value) {
        //$this->vars[$index] = $value;
        $this->smarty->assign($value['name'],$value['value']);
    }


    public function register_plugins() {
        $this->smarty->registerPlugin('function','gentimewatch','funcgentimewatch');
    }


    public function configLoad($config) {
        $this->smarty->configLoad($config);
    }


    public function display($name,$group = null) {
        $templatename = $name;
        //Assign all the control variables to the engine
        /*foreach($this->vars as $templatevariable)
         {
           $this->smarty->assign($templatevariable["name"], $templatevariable["value"]);
         }*/
        //Display the page using template
        if(isset($group) and $group != "") {
            $this->smarty->display($templatename,$group);
        } else {
            $this->smarty->display($templatename);
        }
        $this->registry->main_class->gzip_out();
    }


    public function fetch($name,$group = false) {
        $templatename = $name;
        if(isset($group) and $group != "") {
            return $this->smarty->fetch($templatename,$group);
        } else {
            return $this->smarty->fetch($templatename);
        }
    }


    public function isCached($template,$group) {
        return $this->smarty->isCached($template,$group);
    }


    public function clearCache($template,$group) {
        $this->smarty->clearCache($template,$group);
    }


    public function clearAllCache() {
        return $this->smarty->clearAllCache();
    }


    public function getConfigVars($index) {
        return $this->smarty->getConfigVars($index);
    }
}

?>