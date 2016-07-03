<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      index.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
// Define the site path constant
define('__SITE_PATH', realpath(dirname(__FILE__)));
clearstatcache();
if (!file_exists(__SITE_PATH . "/includes/data/config.inc")) {
    if (file_exists(__SITE_PATH . "/install")) {
        header("Location: /install/index.php");
        exit();
    }
}
// Include the initialization
include_once(__SITE_PATH . '/includes/init.php');
// Load the router class
$registry->router = new router($registry);
// Set the path to the controllers directory
if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
    $registry->router->setpath(__SITE_ADMIN_PATH . '/modules');
} else {
    $registry->router->setpath(__SITE_PATH . '/modules');
}
// Use the router to handle the request.
$registry->router->loader();