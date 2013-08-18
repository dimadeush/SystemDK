<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      init.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
header("Expires: Mon, 30 Nov 2009 00:39:00 GMT");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0");
header("Cache-Control: max-age=0");
header("Pragma: no-cache");
if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")) {
    @date_default_timezone_set(@date_default_timezone_get());
}
if(file_exists(__SITE_PATH."/includes/data/config.inc")) {
    include_once(__SITE_PATH.'/includes/data/config.inc');
} else {
    echo "<html><header><title>SystemDK - Fatal Error</title></header><body><br><br><center>Fatal error<br><br>SystemDK can't find config file</center></body></html>";
    exit();
}
if(GZIP === "yes") {
    ob_start();
    ob_implicit_flush(0);
}
if(SYSTEMDK_STATISTICS == "yes") {
    funcgentimewatch(0,0);
}
include_once(__SITE_PATH.'/includes/singleton.class.php');
//Include the controller classes
include_once(__SITE_PATH.'/includes/controller_base.class.php');
include_once(__SITE_PATH.'/includes/controller_main.class.php');
//Include the model classes
include_once(__SITE_PATH.'/includes/model_base.class.php');
include_once(__SITE_PATH.'/includes/model_main.class.php');
//Include the registry class
include_once(__SITE_PATH.'/includes/registry.class.php');
//Include the router class
include_once(__SITE_PATH.'/includes/router.class.php');
//Include the template class
include_once(__SITE_PATH.'/includes/template.class.php');
//Include the db class
include_once(__SITE_PATH.'/includes/db.class.php');
//Include the mail class
include_once(__SITE_PATH.'/includes/mail.class.php');
//Load model classes
function my_autoloader($class) {
    //Assume that the class is defined within the model directory
    if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
        $filename = __SITE_ADMIN_PATH.'/modules/'.strtolower($class).'/model_'.strtolower($class).'.class.php';
    } else {
        $filename = __SITE_PATH.'/modules/'.strtolower($class).'/model_'.strtolower($class).'.class.php';
    }
    //Return false if the file does not exist
    if(file_exists($filename) == false) {
        return false;
    }
    // The file exists. Include it
    include_once($filename);
}

spl_autoload_register('my_autoloader');
//Instantiate a new registry
$registry = new registry;
if(file_exists(__SITE_PATH."/themes/".SITE_THEME."/".SITE_THEME.".php")) {
    include_once(__SITE_PATH."/themes/".SITE_THEME."/".SITE_THEME.".php");
} else {
    echo "<html><header><title>SystemDK - Fatal Error</title></header><body><br><br><center>Fatal error<br><br>SystemDK can't find theme file: ".__SITE_PATH."/themes/".SITE_THEME."/".SITE_THEME.".php</center></body></html>";
    exit();
}
$registry->main_class = new controller_main($registry);
$registry->main_class->index();
$registry->mail = new mail();
function funcgentimewatch($params,$smarty) {
    static $mt_previous = 0;
    $mt_current = (float)array_sum(explode(' ',microtime()));
    if(!$mt_previous) {
        $mt_previous = $mt_current;
        return "";
    } else {
        $mt_diff = ($mt_current - $mt_previous);
        $mt_previous = $mt_current;
        return sprintf('%.5f',$mt_diff);
    }
}

function CountExecs($adodb,$sql,$inputarray) {
    global $EXECS;
    if(!is_array($inputarray)) {
        $EXECS++;
    } elseif(is_array(reset($inputarray))) {
        $EXECS += sizeof($inputarray);
    } else {
        $EXECS++;
    }
}

function CountCachedExecs($adodb,$secs2cache,$sql,$inputarray) {
    global $CACHED;
    $CACHED++;
}
?>