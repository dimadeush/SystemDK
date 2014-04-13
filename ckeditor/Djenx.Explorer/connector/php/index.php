<?php

define('DEV', false);
if (DEV) {
	error_reporting(E_ALL | E_NOTICE | E_STRICT);
	ini_set('display_errors', 'on');
	//header('Content-Type: text/html; charset=utf-8');
} else {
	error_reporting(0);
	ini_set('display_errors', 'off');
}


//	example: check access
function checkAllow_DjenxExplorer()
{
	//session_save_path();

	/*	in JavaScript
	DjenxExplorer.init({
		post: {
				'PHPSESSID': 'session_id()'
		}
	});
	*/
	if (isset($_POST['PHPSESSID'])) {
		session_id($_POST['PHPSESSID']);
	}

	//return true;	// !!! for example  (в рабочем проекте напишите свой обработчик)

	if (session_start()) {
		if (isset($_SESSION['systemdk_is_admin']) && $_SESSION['systemdk_is_admin'] == 'yes') {
			return true;
		}
	}
	return false;
}


require_once dirname(__FILE__)  . '/library/Djenx/Explorer.php';
$djenxExplorer = new Djenx_Explorer(array(
	'allowFunction'		=> 'checkAllow_DjenxExplorer',

	//	'dir'			=>	dirname(__FILE__),
	//	'iniFile'		=>	'path/to/config.ini',
	//	'cacheDir'		=>	'dir/to/cache',	// for: tree.php, statistic.php
	//	'cachePrefix'	=>	'',

	//	'_data'	=>	array()
));


/*

$options = array(
		'action'	=> '',
		'main'		=> [true, false],
		'json'		=> [true, false],
);

*/