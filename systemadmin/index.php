<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      index.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
//Define the site admin path constant
define('__SITE_ADMIN_PART','yes');
//define('__SITE_ADMIN_DIR',basename(realpath(dirname(__FILE__))));
define('__SITE_ADMIN_PATH',realpath(dirname(__FILE__)));
include_once('../index.php');