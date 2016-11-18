<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      install/index.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class install
{


    private $smarty;
    private $func;


    public function __construct()
    {
        header('Content-type: text/html; charset=utf-8');
        if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get")) {
            @date_default_timezone_set(@date_default_timezone_get());
        }
        require_once("../smarty/Smarty.class.php");
        $this->smarty = new Smarty;
        $this->smarty->setConfigDir(["1" => '../themes/systemdk/configs/', "2" => '../themes/systemdk/languages/']);
        $this->smarty->setTemplateDir('../themes/systemdk/templates/');
        $this->smarty->setCompileDir('../themes/systemdk/templates_c/');
        $this->smarty->setCacheDir('../themes/systemdk/cache/');
        $this->smarty->configLoad('theme.conf');
        if (!isset($_POST['func']) || strip_tags(trim($_POST['func'])) == "") {
            $func = "step1";
        } else {
            $func = strip_tags(trim($_POST['func']));
        }
        if (is_callable(['install', $func]) == true) {
            $this->func = $func;
            $this->$func();
        } else {
            $this->func = 'step1';
            $this->step1();
        }
    }


    public function step1()
    {
        $this->smarty->display("install/install1.html");
    }


    public function funcexit()
    {
        $this->smarty->display("install/installexit.html");
    }


    public function step2()
    {
        $this->check_php_version();
        $this->smarty->display("install/install2.html");
    }


    public function step2a()
    {
        $this->check_php_version();
        $this->smarty->display("install/install2a.html");
    }


    public function step3()
    {
        if (isset($_POST['select_db_type'])) {
            $select_db_type = trim($_POST['select_db_type']);
        }
        if (!isset($_POST['select_db_type']) || trim($select_db_type) == "") {
            $this->smarty->assign("error", "not_all_data");
            $this->smarty->display("install/action.html", "|not_all_data");
            exit();
        }
        $this->check_php_version();
        $select_db_type = strip_tags(trim($select_db_type));
        $this->smarty->assign("select_db_type", $select_db_type);
        $dir = opendir('../themes');
        while ($file = readdir($dir)) {
            if ((!preg_match("/[.]/", $file))) {
                $theme_list[] = $file;
            }
        }
        closedir($dir);
        $this->smarty->assign("themelist", $theme_list);
        $module_dir = opendir('../modules');
        while ($file = readdir($module_dir)) {
            if (!preg_match("/[.]/", $file)) {
                $module = $file;
                $module_list[] = $module;
            }
        }
        closedir($module_dir);
        $this->smarty->assign("modulelist", $module_list);
        $lang_list = $this->get_lang_list();
        $this->smarty->assign("languagelist", $lang_list);
        $this->smarty->display("install/install3.html");
    }


    public function step4()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'site_id',
                'site_name',
                'site_url',
                'mod_rewrite',
                'admin_email',
                'site_dir',
                'admin_dir',
                'smarty_debugging',
                'adodb_debugging',
                'gzip',
                'smarty_caching',
                'site_logo',
                'site_charset',
                'site_keywords',
                'site_description',
                'site_status',
                'site_status_notes',
                'site_theme',
                'language',
                'Home_Module',
                'enter_check',
                'display_admin_graphic',
                'db_host',
                'db_type',
                'db_name',
                'db_user_name',
                'db_password',
                'prefix',
                'db_character',
                'db_collate',
                'db_persistency',
                'systemdk_mail_method',
                'systemdk_smtp_host',
                'systemdk_smtp_user',
                'systemdk_smtp_pass',
                'systemdk_account_registration',
                'systemdk_account_activation',
                'systemdk_account_rules',
                'systemdk_statistics',
                'systemdk_player_autoplay',
                'systemdk_player_autobuffer',
                'systemdk_player_controls',
                'systemdk_player_audioautoplay',
                'systemdk_player_audioautobuffer',
                'systemdk_player_audio_controls',
                'systemdk_player_ratio',
                'systemdk_player_skin',
                'systemdk_ipantispam',
                'systemdk_site_lang',
                'systemdk_admin_site_lang',
                'systemdk_interface_lang',
                'systemdk_admin_interface_lang',
            ];
            $keys2 = [
                'cache_lifetime',
                'session_cookie_live',
                'systemdk_adminrows_perpage',
                'db_port',
                'systemdk_smtp_port',
                'systemdk_account_maxusers',
                'systemdk_account_maxactivationdays',
                'systemdk_ipantispam_num',
            ];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $data_array['systemdk_install_lang'] = $_POST['systemdk_install_lang'];
        if (!empty($data_array['db_type']) && !in_array($data_array['db_type'], ['mysql', 'pdo', 'mysqli', 'mysqlt', 'maxsql'])) {
            $data_array['db_collate'] = "";
        }
        if (
            (!isset($data_array['site_id']) || !isset($data_array['site_name']) || !isset($data_array['site_url']) || !isset($data_array['mod_rewrite'])
             || !isset($data_array['admin_email'])
             || !isset($data_array['site_dir'])
             || !isset($data_array['admin_dir'])
             || !isset($data_array['smarty_debugging'])
             || !isset($data_array['adodb_debugging'])
             || !isset($data_array['gzip'])
             || !isset($data_array['smarty_caching'])
             || !isset($data_array['cache_lifetime'])
             || !isset($data_array['session_cookie_live'])
             || !isset($data_array['site_logo'])
             || !isset($data_array['site_charset'])
             || !isset($data_array['site_keywords'])
             || !isset($data_array['site_description'])
             || !isset($data_array['site_status'])
             || !isset($data_array['site_status_notes'])
             || !isset($data_array['site_theme'])
             || !isset($data_array['language'])
             || !isset($data_array['Home_Module'])
             || !isset($data_array['enter_check'])
             || !isset($data_array['display_admin_graphic'])
             || !isset($data_array['systemdk_adminrows_perpage'])
             || !isset($data_array['db_host'])
             || !isset($data_array['db_type'])
             || !isset($data_array['db_name'])
             || !isset($data_array['db_user_name'])
             || !isset($data_array['db_password'])
             || !isset($data_array['prefix'])
             || !isset($data_array['db_character'])
             || !isset($data_array['db_collate'])
             || !isset($data_array['db_persistency'])
             || !isset($data_array['db_port'])
             || !isset($data_array['systemdk_mail_method'])
             || !isset($data_array['systemdk_smtp_host'])
             || !isset($data_array['systemdk_smtp_port'])
             || !isset($data_array['systemdk_smtp_user'])
             || !isset($data_array['systemdk_smtp_pass'])
             || !isset($data_array['systemdk_account_registration'])
             || !isset($data_array['systemdk_account_activation'])
             || !isset($data_array['systemdk_account_rules'])
             || !isset($data_array['systemdk_account_maxusers'])
             || !isset($data_array['systemdk_account_maxactivationdays'])
             || !isset($data_array['systemdk_statistics'])
             || !isset($data_array['systemdk_player_autoplay'])
             || !isset($data_array['systemdk_player_autobuffer'])
             || !isset($data_array['systemdk_player_controls'])
             || !isset($data_array['systemdk_player_audioautoplay'])
             || !isset($data_array['systemdk_player_audioautobuffer'])
             || !isset($data_array['systemdk_player_audio_controls'])
             || !isset($data_array['systemdk_player_ratio'])
             || !isset($data_array['systemdk_player_skin'])
             || !isset($data_array['systemdk_ipantispam'])
             || !isset($data_array['systemdk_ipantispam_num'])
             || !isset($data_array['systemdk_site_lang'])
             || !isset($data_array['systemdk_admin_site_lang'])
             || !isset($data_array['systemdk_interface_lang'])
             || !isset($data_array['systemdk_admin_interface_lang'])
             || !isset($data_array['systemdk_install_lang'])
            )
            || ($data_array['site_id'] == "" || $data_array['site_name'] == "" || $data_array['site_url'] == "" || $data_array['mod_rewrite'] == ""
                || $data_array['admin_email'] == ""
                || $data_array['admin_dir'] == ""
                || $data_array['smarty_debugging'] == ""
                || $data_array['adodb_debugging'] == ""
                || $data_array['gzip'] == ""
                || $data_array['smarty_caching'] == ""
                || $data_array['site_charset'] == ""
                || $data_array['site_keywords'] == ""
                || $data_array['site_description'] == ""
                || $data_array['site_status'] == ""
                || $data_array['site_theme'] == ""
                || $data_array['language'] == ""
                || $data_array['enter_check'] == ""
                || $data_array['display_admin_graphic'] == ""
                || $data_array['db_host'] == ""
                || $data_array['db_type'] == ""
                || $data_array['db_name'] == ""
                || $data_array['db_user_name'] == ""
                || $data_array['db_password'] == ""
                || $data_array['prefix'] == ""
                || $data_array['db_persistency'] == ""
                || ($data_array['db_character'] == "" && $data_array['db_type'] == 'oci8')
                || (($data_array['db_character'] == "" || $data_array['db_collate'] == "")
                    && in_array($data_array['db_type'], ['mysql', 'pdo', 'mysqli', 'mysqlt', 'maxsql']))
                || $data_array['systemdk_mail_method'] == ""
                || $data_array['systemdk_account_registration'] == ""
                || $data_array['systemdk_account_activation'] == ""
                || $data_array['systemdk_account_rules'] == ""
                || $data_array['systemdk_statistics'] == ""
                || $data_array['systemdk_player_autoplay'] == ""
                || $data_array['systemdk_player_autobuffer'] == ""
                || $data_array['systemdk_player_controls'] == ""
                || $data_array['systemdk_player_audioautoplay'] == ""
                || $data_array['systemdk_player_audioautobuffer']
                   == ""
                || $data_array['systemdk_player_audio_controls'] == ""
                || $data_array['systemdk_player_skin'] == ""
                || $data_array['systemdk_ipantispam'] == ""
                || $data_array['systemdk_site_lang'] == ""
                || $data_array['systemdk_admin_site_lang'] == ""
                || $data_array['systemdk_interface_lang'] == ""
                || $data_array['systemdk_admin_interface_lang'] == ""
                || count($data_array['systemdk_install_lang']) < 1
            )
        ) {
            $this->smarty->assign("error", "not_all_data");
            $this->smarty->display("install/action.html", "|not_all_data");
            exit();
        }
        $this->check_php_version();
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $data_array[$key] = strip_tags($value);
            }
        }
        @chmod("../includes/data", 0777);
        @chmod("../includes/data/ban_ip.inc", 0777);
        @chmod("../includes/data/header.inc", 0777);
        @chmod("../includes/data/footer.inc", 0777);
        @chmod("../themes/" . $data_array['site_theme'] . "/cache", 0777);
        @chmod("../themes/" . $data_array['site_theme'] . "/templates_c", 0777);
        @chmod("../themes/systemdk/cache", 0777);
        @chmod("../themes/systemdk/templates_c", 0777);
        @chmod("../themes/systemdk2/cache", 0777);
        @chmod("../themes/systemdk2/templates_c", 0777);
        @chmod("../modules/news/news_config.inc", 0777);
        @chmod("../userfiles", 0777);
        @chmod("../userfiles/image", 0777);
        @chmod("../userfiles/news", 0777);
        @chmod("../userfiles/file", 0777);
        @chmod("../userfiles/flash", 0777);
        @chmod("../userfiles/media", 0777);
        @chmod("../userfiles/_thumbs", 0777);
        @chmod("../ckeditor/Djenx.Explorer/connector/php/cache", 0777);
        include_once("../adodb/adodb.inc.php");
        $adodb = ADONewConnection($data_array['db_type']);
        if ($data_array['adodb_debugging'] === 'yes') {
            $adodb->debug = true;
        }
        if ($data_array['db_type'] === 'oci8') {
            $adodb->charSet = $data_array['db_character'];
            $cnt = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=" . $data_array['db_host'] . ")(PORT=" . $data_array['db_port'] . "))(CONNECT_DATA=(SID="
                   . $data_array['db_name'] . ")))";
        }
        if ($data_array['db_persistency'] === 'yes') {
            /*if($db_type === 'sqlite') {
                $db2 = $adodb->PConnect($_SERVER['DOCUMENT_ROOT']."/../".$site_dir."includes/data/".$db_name.".db");
            } elseif($db_type === 'access') {
                $db2 = $adodb->PConnect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$_SERVER['DOCUMENT_ROOT']."/../".$site_dir."includes/data/".$db_name.".mdb;Uid=".$db_user_name.";Pwd=".$db_password.";");
            }
            if($db_type === 'ado_mssql') {
                $db2 = $adodb->PConnect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".$db_host.";DATABASE=".$db_name.";UID=".$db_user_name.";PWD=".$db_password.";");
            } elseif($db_type === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".$db_host.";Database=".$db_name.";";
                $db2 = $adodb->PConnect($dsn,$db_user_name,$db_password);
            }*/
            if ($data_array['db_type'] === 'oci8' && $data_array['db_port'] != 0) {
                $db2 = $adodb->PConnect($cnt, $data_array['db_user_name'], $data_array['db_password']);
            } elseif ($data_array['db_type'] === 'pdo') {
                $db2 = $adodb->PConnect(
                    'mysql:host=' . $data_array['db_host'] . ';dbname=' . $data_array['db_name'], $data_array['db_user_name'], $data_array['db_password']
                );
            } else {
                $db2 = $adodb->PConnect($data_array['db_host'], $data_array['db_user_name'], $data_array['db_password'], $data_array['db_name']);
            }
        } else {
            /*if($db_type === 'sqlite') {
                $db2 = $adodb->Connect($_SERVER['DOCUMENT_ROOT']."/../".$site_dir."includes/data/".$db_name.".db");
            } elseif($db_type === 'access') {
                $db2 = $adodb->Connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$_SERVER['DOCUMENT_ROOT']."/../".$site_dir."includes/data/".$db_name.".mdb;Uid=".$db_user_name.";Pwd=".$db_password.";");
            }
            if($db_type === 'ado_mssql') {
                $db2 = $adodb->Connect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".$db_host.";DATABASE=".$db_name.";UID=".$db_user_name.";PWD=".$db_password.";");
            } elseif($db_type === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".$db_host.";Database=".$db_name.";";
                $db2 = $adodb->Connect($dsn,$db_user_name,$db_password);
            }*/
            if ($data_array['db_type'] === 'oci8' && $data_array['db_port'] != 0) {
                $db2 = $adodb->Connect($cnt, $data_array['db_user_name'], $data_array['db_password']);
            } elseif ($data_array['db_type'] === 'pdo') {
                $db2 = $adodb->Connect(
                    'mysql:host=' . $data_array['db_host'] . ';dbname=' . $data_array['db_name'], $data_array['db_user_name'], $data_array['db_password']
                );
            } else {
                $db2 = $adodb->Connect($data_array['db_host'], $data_array['db_user_name'], $data_array['db_password'], $data_array['db_name']);
            }
        }
        if (!$db2) {
            $this->smarty->assign("error", "not_connect");
            $this->smarty->display("install/action.html", "|not_connect");
            exit();
        }
        if ($data_array['db_type'] === 'mysql' || $data_array['db_type'] === 'pdo' || $data_array['db_type'] === 'mysqli' || $data_array['db_type'] === 'mysqlt'
            || $data_array['db_type'] === 'maxsql'
        ) {
            $adodb->Execute("SET NAMES '" . $data_array['db_character'] . "' COLLATE " . $data_array['db_collate']);
        }
        if (!get_magic_quotes_gpc()) {
            $get_magic_quotes_gpc = 'off';
        } else {
            $get_magic_quotes_gpc = 'on';
        }
        if ($get_magic_quotes_gpc == 'on') {
            foreach ($data_array as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = stripslashes($value);
                }
            }
        }
        if ($data_array['systemdk_adminrows_perpage'] == 0) {
            $data_array['systemdk_adminrows_perpage'] = 5;
        }
        if (!filter_var($data_array['admin_email'], FILTER_VALIDATE_EMAIL)) {
            if ($data_array['db_persistency'] === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "not_correct_email");
            $this->smarty->display("install/action.html", "|not_correct_email");
            exit();
        }
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $data_array[$key] = htmlspecialchars($value, ENT_QUOTES, $data_array['site_charset']);
            }
        }
        clearstatcache();
        for ($i = 0, $size = count($data_array['systemdk_install_lang']); $i < $size; ++$i) {
            $systemdk_install_lang = strip_tags(trim($data_array['systemdk_install_lang'][$i]));
            if ($get_magic_quotes_gpc == 'on') {
                $systemdk_install_lang = stripslashes($systemdk_install_lang);
            }
            $systemdk_install_lang = substr(htmlspecialchars($systemdk_install_lang, ENT_QUOTES), 0, 2);
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/" . $data_array['site_dir'] . "themes/" . $data_array['site_theme'] . "/languages/" . $systemdk_install_lang)) {
                $install_lang[$i] = $systemdk_install_lang;
                if ($install_lang[$i] == $data_array['language']) {
                    $language_check = 1;
                }
            } else {
                $this->smarty->assign("notfindlangvalue", $data_array['site_dir'] . "themes/" . $data_array['site_theme'] . "/languages/" . $systemdk_install_lang);
                $this->smarty->assign("error", "no_lang");
                $this->smarty->display("install/action.html", "|no_lang");
                exit();
            }
        }
        if (!isset($language_check)) {
            $install_lang[] = $data_array['language'];
        }
        for ($i = 0, $size = count($install_lang); $i < $size; ++$i) {
            if ($data_array['db_type'] === 'mysqli' || $data_array['db_type'] === 'pdo' || $data_array['db_type'] === 'mysql' || $data_array['db_type'] === 'mysqlt'
                || $data_array['db_type'] === 'maxsql'
            ) {
                if ($i == 0) {
                    $insert = $adodb->Execute("SET default_storage_engine=INNODB");
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_users (
                        user_id int(11) not null auto_increment,
                        user_temp enum('0','1') default '0' not null,
                        user_name varchar(25),
                        user_surname varchar(25),
                        user_website varchar(255),
                        user_email varchar(60) not null,
                        user_login varchar(25) not null,
                        user_password varchar(255) not null,
                        user_rights varchar(255),
                        user_session_code varchar(15),
                        user_session_agent varchar(255),
                        user_country varchar(100),
                        user_city varchar(100),
                        user_address varchar(255),
                        user_telephone varchar(100),
                        user_icq varchar(50),
                        user_skype varchar(50),
                        user_gmt varchar(3) default '+00' not null,
                        user_notes varchar(255),
                        user_active enum('0','1') default '1' not null,
                        user_active_notes varchar(255),
                        user_reg_date bigint(19) not null,
                        user_last_login_date bigint(19),
                        user_view_email enum('0','1') default '1' not null,
                        user_activate_key varchar(32),
                        user_new_password varchar(255),
                        user_reg_key varchar(50),
                        PRIMARY KEY (user_id),
                        UNIQUE KEY user_login (user_login),
                        UNIQUE KEY user_email (user_email)
                      ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " (
                    module_id int(11) not null auto_increment,
                    module_name varchar(25) not null,
                    module_custom_name varchar(25) not null,
                    module_version varchar(255),
                    module_status enum('0','1') default '0' not null,
                    module_value_view enum('0','1','2','3') default '0' not null,
                    PRIMARY KEY (module_id),
                    UNIQUE KEY module_name (module_name),
                    UNIQUE KEY module_custom_name (module_custom_name)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " (module_name,module_custom_name,module_version,module_status,module_value_view) VALUES
                    ('news', 'News', NULL, '1', '0'),
                    ('main_pages', 'Main Pages', NULL, '1', '0');
                    "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_blocks_" . $install_lang[$i] . " (
                    block_id int(11) not null auto_increment,
                    block_name varchar(25),
                    block_custom_name varchar(25) not null,
                    block_version varchar(255),
                    block_status enum('0','1') default '0' not null,
                    block_value_view enum('0','1','2','3') default '0' not null,
                    block_content mediumtext,
                    block_url varchar(255),
                    block_arrangements varchar(11) not null,
                    block_priority int(3) default '1' not null,
                    block_refresh bigint(19),
                    block_last_refresh bigint(19),
                    block_term_expire bigint(19),
                    block_term_expire_action varchar(10),
                    PRIMARY KEY (block_id),
                    UNIQUE KEY block_name (block_name),
                    UNIQUE KEY block_custom_name (block_custom_name)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_blocks_" . $install_lang[$i]
                    . " (block_name,block_custom_name,block_version,block_status,block_value_view,block_content,block_url,block_arrangements,block_priority,block_refresh,block_last_refresh,block_term_expire,block_term_expire_action) VALUES ('menu', 'Navigation', NULL, '1', '0', NULL, NULL, 'left', 1, NULL, NULL, NULL, NULL);"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                //shop start
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_catalogs_" . $install_lang[$i] . " (
                    catalog_id int(11) not null auto_increment,
                    catalog_img varchar(255),
                    catalog_name varchar(255) not null,
                    catalog_description varchar(255),
                    catalog_position int(4) not null default '0',
                    catalog_show enum('0','1') not null default '1',
                    catalog_date bigint(19) not null,
                    catalog_meta_title varchar(255),
                    catalog_meta_keywords varchar(255),
                    catalog_meta_description varchar(255),
                    PRIMARY KEY (catalog_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_categories_" . $install_lang[$i] . " (
                    category_id int(11) not null auto_increment,
                    category_img varchar(255),
                    category_name varchar(255) not null,
                    cat_catalog_id int(11) not null,
                    category_description varchar(255),
                    category_position int(4) not null default '0',
                    category_show enum('0','1') not null default '1',
                    category_date bigint(19) not null,
                    category_meta_title varchar(255),
                    category_meta_keywords varchar(255),
                    category_meta_description varchar(255),
                    PRIMARY KEY (category_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_subcategories_" . $install_lang[$i] . " (
                    subcategory_id int(11) not null auto_increment,
                    subcategory_img varchar(255),
                    subcategory_name varchar(255) not null,
                    subcategory_cat_id int(11) not null,
                    subcategory_description varchar(255),
                    subcategory_position int(4) not null default '0',
                    subcategory_show enum('0','1') not null default '1',
                    subcategory_date bigint(19) not null,
                    subcategory_meta_title varchar(255),
                    subcategory_meta_keywords varchar(255),
                    subcategory_meta_description varchar(255),
                    PRIMARY KEY (subcategory_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_items_" . $install_lang[$i] . " (
                    item_id int(11) not null auto_increment,
                    item_img varchar(255),
                    item_name varchar(255) not null,
                    item_price float(13,2) not null default '0.00',
                    item_price_discounted float(13,2),
                    item_catalog_id int(11),
                    item_category_id int(11),
                    item_subcategory_id int(11),
                    item_short_description mediumtext not null,
                    item_description mediumtext not null,
                    item_position int(4) not null default '0',
                    item_show enum('0','1') not null default '1',
                    item_quantity int(10),
                    item_quantity_unlim enum('0','1') not null default '0',
                    item_quantity_param enum('1','2','3'),
                    item_display enum('1','2','3'),
                    item_date bigint(19) not null,
                    item_meta_title varchar(255),
                    item_meta_keywords varchar(255),
                    item_meta_description varchar(255),
                    PRIMARY KEY (item_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_delivery_" . $install_lang[$i] . " (
                    delivery_id int(11) not null auto_increment,
                    delivery_name varchar(255) not null,
                    delivery_price float(13,2) not null default '0.00',
                    delivery_status enum('0','1') not null default '1',
                    PRIMARY KEY (delivery_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_delivery_" . $install_lang[$i]
                    . " (delivery_name,delivery_price,delivery_status) VALUES ('personal pickup', '0.00', '1');"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_payment_methods_" . $install_lang[$i] . " (
                    id int(11) not null auto_increment,
                    payment_name varchar(255) not null,
                    payment_commission float(13,2) not null default '0.00',
                    payment_system_id int(11),
                    payment_status enum('0','1') not null default '1',
                    PRIMARY KEY (id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_s_payment_methods_" . $install_lang[$i]
                    . " (payment_name,payment_commission,payment_system_id,payment_status) VALUES ('cash', '0.00', NULL, '1');"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_orders_" . $install_lang[$i] . " (
                    order_id int(11) not null auto_increment,
                    order_customer_id int(11),
                    order_amount float(13,2) not null default '0.00',
                    order_date bigint(19) not null,
                    ship_date bigint(19),
                    order_status enum('1','2','3','4','5','6','7','8','9') not null default '1',
                    ship_name varchar(255) not null,
                    ship_street varchar(255),
                    ship_house varchar(255),
                    ship_flat varchar(255),
                    ship_city varchar(255),
                    ship_state varchar(255),
                    ship_postal_code varchar(255),
                    ship_country varchar(255),
                    ship_telephone varchar(255) not null,
                    ship_email varchar(255) not null,
                    ship_add_info varchar(255),
                    order_delivery int(11) not null,
                    order_pay int(11) not null,
                    order_comments varchar(255),
                    order_ip varchar(45) not null,
                    order_hash varchar(150) not null,
                    invoice_date bigint(19),
                    invoice_amount float(13,2),
                    invoice_currency_code varchar(3),
                    invoice_exch_rate float(13,2),
                    invoice_paid enum('0','1') default '0' not null,
                    invoice_paid_date bigint(19),
                    PRIMARY KEY (order_id),
                    UNIQUE KEY order_hash (order_hash)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_order_items_" . $install_lang[$i] . " (
                    order_item_id int(11) not null auto_increment,
                    order_id int(11) not null,
                    item_id int(11) not null,
                    order_item_name varchar(255) not null,
                    order_item_price float(13,2) not null default '0.00',
                    order_item_quantity int(3) not null,
                    PRIMARY KEY (order_item_id),
                    UNIQUE KEY unique_key (order_id,item_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }


                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_add_par_g_types (
                        id int(11) not null auto_increment,
                        type_name varchar(255) not null,
                        type_description varchar(255) not null,
                        PRIMARY KEY (id)
                        ) ENGINE=InnoDB"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_add_par_g_types (id,type_name,type_description) VALUES
                        (1,'check_more', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_CHECK_MORE'),
                        (2,'check_single', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_CHECK_SINGLE'),
                        (3,'select_list', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_SELECT_LIST');"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_add_par_groups_" . $install_lang[$i] . " (
                    id int(11) not null auto_increment,
                    group_name varchar(255) not null,
                    group_type_id int(11) not null,
                    group_status enum('0','1') not null default '1',
                    PRIMARY KEY (id)
                  ) ENGINE=InnoDB"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_add_params_" . $install_lang[$i] . " (
                    id int(11) not null auto_increment,
                    param_group_id int(11) not null,
                    param_value varchar(255) not null,
                    param_status enum('0','1') not null default '1',
                    PRIMARY KEY (id)
                  ) ENGINE=InnoDB"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_item_add_params_" . $install_lang[$i] . " (
                    item_id int(11) not null,
                    item_param_id int(11) not null,
                    UNIQUE KEY item_id_param_id (item_id,item_param_id)
                  ) ENGINE=InnoDB"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_order_i_params_" . $install_lang[$i] . " (
                    id int(11) not null auto_increment,
                    order_id int(11) not null,
                    order_item_id int(11) not null,
                    order_suborder_id int(11) not null,
                    order_item_param_group_id int(11) not null,
                    order_item_param_id int(11) not null,
                    PRIMARY KEY (id)
                  ) ENGINE=InnoDB"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_payment_systems (
                        id int(11) not null,
                        system_name varchar(255) not null,
                        system_class varchar(255) not null,
                        system_integ_type varchar(255) not null,
                        system_integ_description varchar(255) not null,
                        system_version varchar(10) not null,
                        system_status enum('0','1') not null default '0',
                        PRIMARY KEY (id)
                        ) ENGINE=InnoDB"
                    );
                    
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_payment_systems"
                        . " (id,system_name,system_class,system_integ_type,system_integ_description,system_version,system_status) "
                        . "VALUES (1,'Portmone(acquiring)', 'shop_portmone', 'acquiring_agreement', 'Only when you have acquiring agreement with Portmone', '1.0', '0');"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_portmone_ac_conf (
                        id int(11) not null auto_increment,
                        payee_id varchar(255) not null,
                        login varchar(255) not null,
                        password varchar(255) not null,
                        payment_description varchar(255) not null,
                        log_mode varchar(10) not null,
                        log_life_days int(5),
                        error_notify enum('0','1') not null default '0',
                        error_notify_type varchar(20) not null,
                        display_error_mode varchar(20) not null,
                        PRIMARY KEY (id)
                        ) ENGINE=InnoDB"
                    );
                    
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_currency_exch (
                        id bigint(19) not null auto_increment,
                        from_currency_code varchar(3) not null,
                        to_currency_code varchar(3) not null,
                        rate float(13,2) not null,
                        date_from bigint(19) not null,
                        date_till bigint(19),
                        updated_at bigint(19) not null,
                        PRIMARY KEY (id)
                        ) ENGINE=InnoDB"
                    );
                    
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_currencies (
                        id int(11) not null auto_increment,
                        currency_code varchar(3) not null,
                        currency_name varchar(255) not null,
                        currency_order int(11) not null,
                        PRIMARY KEY (id)
                        ) ENGINE=InnoDB"
                    );
                    
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_currencies (id,currency_code,currency_name,currency_order) VALUES
                        (1,'rub', 'ruble', '4'),
                        (2,'uah', 'hryvnia', '3'),
                        (3,'usd', 'dollar', '2'),
                        (4,'eur', 'euro', '1');"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_payment_logs_" . $install_lang[$i] . " (
                    id bigint(19) not null auto_increment,
                    system_id int(11),
                    request_url varchar(255),
                    method varchar(255),
                    order_number int(11),
                    request_data mediumtext,
                    response_data mediumtext,
                    process_status varchar(255) not null,
                    error_data mediumtext,
                    log_date bigint(19) not null,
                    PRIMARY KEY (id),
                    KEY log_date(log_date)
                  ) ENGINE=InnoDB"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                //shop end
                // questionnaire start
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_groups_" . $install_lang[$i] . " (
                    group_id int(11) not null auto_increment,
                    questionnaire_id int(11) not null,
                    group_name varchar(255) not null,
                    group_status enum('0','1') not null default '0',
                    PRIMARY KEY (group_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_questions_" . $install_lang[$i] . " (
                    question_id int(11) not null auto_increment,
                    question_title varchar(255) not null,
                    questionnaire_id int(11) not null,
                    type_id int(11) not null,
                    question_priority int(11) not null,
                    question_subtext varchar(255),
                    question_obligatory enum('0','1') not null default '1',
                    PRIMARY KEY (question_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_question_answers_" . $install_lang[$i] . " (
                    question_answer_id int(11) not null auto_increment,
                    question_id int(11) not null,
                    question_answer varchar(255) not null,
                    question_answer_priority int(11) not null,
                    PRIMARY KEY (question_answer_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_question_items_" . $install_lang[$i] . " (
                    question_item_id int(11) not null auto_increment,
                    question_id int(11) not null,
                    question_item_title varchar(255) not null,
                    question_item_priority int(11) not null,
                    PRIMARY KEY (question_item_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_q_question_types (
                    type_id int(11) not null auto_increment,
                    type_name varchar(255) not null,
                    type_description varchar(255) not null,
                    PRIMARY KEY (type_id)
                  ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_q_question_types (type_id,type_name,type_description) VALUES
                    (1,'check_more', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_MORE'),
                    (2,'check_single', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_SINGLE'),
                    (3,'select_list', '_QUESTIONNAIRES_QUESTION_TYPE_SELECT_LIST'),
                    (4,'check_more_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_MORE_WITH_TEXT'),
                    (5,'check_single_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_SINGLE_WITH_TEXT'),
                    (6,'select_list_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_SELECT_LIST_WITH_TEXT'),
                    (7,'only_text', '_QUESTIONNAIRES_QUESTION_TYPE_ONLY_TEXT');
                    "
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_questionnaires_" . $install_lang[$i] . " (
                    questionnaire_id int(11) not null auto_increment,
                    questionnaire_name varchar(255) not null,
                    questionnaire_date bigint(19) not null,
                    questionnaire_status enum('0','1') not null default '0',
                    questionnaire_meta_title varchar(255),
                    questionnaire_meta_keywords varchar(255),
                    questionnaire_meta_description varchar(255),
                    PRIMARY KEY (questionnaire_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_answers_" . $install_lang[$i] . " (
                    answer_id int(11) not null auto_increment,
                    person_id int(11) not null,
                    question_id int(11) not null,
                    question_item_id int(11),
                    questionnaire_id int(11) not null,
                    answer_text mediumtext,
                    group_id int(11) not null,
                    question_answer_id int(11),
                    PRIMARY KEY (answer_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_groups_" . $install_lang[$i] . " (
                    voted_group_id int(11) not null auto_increment,
                    group_id int(11) not null,
                    questionnaire_id int(11) not null,
                    PRIMARY KEY (voted_group_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_persons_" . $install_lang[$i] . " (
                    person_id int(11) not null auto_increment,
                    user_id int(11),
                    voted_group_id int(11) not null,
                    person_ip varchar(45) not null,
                    voted_date bigint(19) not null,
                    PRIMARY KEY (person_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                // questionnaire end
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_guestbook (
                        post_id int(11) not null auto_increment,
                        post_user_name varchar(255) not null,
                        post_user_country varchar(100),
                        post_user_city varchar(100),
                        post_user_telephone varchar(100),
                        post_show_telephone enum('0','1') not null default '1',
                        post_user_email varchar(60) not null,
                        post_show_email enum('0','1') not null default '1',
                        post_user_id int(11),
                        post_user_ip varchar(45) not null,
                        post_date bigint(19) not null,
                        post_text mediumtext not null,
                        post_status enum('0','1','2') not null default '0',
                        post_answer mediumtext,
                        post_answer_date bigint(19),
                        PRIMARY KEY (post_id)
                      ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_mail_receivers_" . $install_lang[$i] . " (
                    receiver_id int(11) not null auto_increment,
                    receiver_name varchar(25) not null,
                    receiver_email varchar(60) not null,
                    receiver_active enum('0','1') default'1' not null,
                    PRIMARY KEY (receiver_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_mail_sendlog (
                        mail_id int(11) not null auto_increment,
                        mail_user varchar(25),
                        mail_date bigint(19) not null,
                        mail_module varchar(25) not null,
                        mail_ip varchar(45) not null,
                        PRIMARY KEY (mail_id),
                        KEY mail_date (mail_date),
                        KEY mail_module (mail_module),
                        KEY mail_ip (mail_ip)
                      ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_main_menu_" . $install_lang[$i] . " (
                    main_menu_id int(11) not null auto_increment,
                    main_menu_type varchar(10) not null,
                    main_menu_name varchar(25) not null,
                    main_menu_module varchar(255),
                    main_menu_submodule varchar(255),
                    main_menu_add_param varchar(255),
                    main_menu_target varchar(20) default '_self' not null,
                    main_menu_priority int(3) not null,
                    main_menu_in_menu enum('0','1') default '1' not null,
                    PRIMARY KEY (main_menu_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_session (
                        session_login varchar(25),
                        session_time bigint(19) not null,
                        session_host_address varchar(45) not null,
                        session_who_online varchar(20) not null,
                        KEY session_login (session_login)
                      ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_system_pages (
                        system_page_id int(11) not null auto_increment,
                        system_page_author varchar(25) not null,
                        system_page_name varchar(255) not null,
                        system_page_title varchar(255) not null,
                        system_page_date bigint(19) not null,
                        system_page_content mediumtext not null,
                        system_page_lang varchar(2),
                        system_page_meta_title varchar(255),
                        system_page_meta_keywords varchar(255),
                        system_page_meta_description varchar(255),
                        PRIMARY KEY (system_page_id)
                      ) ENGINE=InnoDB"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix']
                        . "_system_pages (system_page_author,system_page_name,system_page_title,system_page_date,system_page_content,system_page_lang,system_page_meta_title,system_page_meta_keywords,system_page_meta_description) VALUES ('admin', 'account_rules', 'Общие правила', '"
                        . time()
                        . "', 'Заходя на сайт, Вы подтверждаете своё согласие со следующими условиями. Если Вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь нашим сайтом. Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить Вас об этом, однако с Вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование нашего сайта после обновления/исправления условий означает Ваше согласие с ними.<br><br>Наши сайты работают под управлением программного обеспечения SystemDK для создания веб-сайтов. Скачать его можно по адресу www.systemsdk.com. За дополнительной информацией о SystemsDK обращайтесь по адресу http://www.systemsdk.com.<br><br>Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы Вашей страны, страны, которая предоставляет услуги хостинга для этого сайта, или международное право. Попытки размещения таких сообщений могут привести к Вашему немедленному отключению от сайта, при этом Ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы сайта имеют право удалить, отредактировать, перенести или закрыть Вам доступ в любое время по своему усмотрению. Как пользователь Вы согласны с тем, что введённая Вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без Вашего разрешения, ни администрация сайта, ни авторы программного обеспечения SystemDK не могут быть ответственными за действия хакеров, которые могут привести к несанкционированному доступу к ней.', 'ru',null,null,null), ('admin', 'account_rules', 'General rules', '"
                        . time()
                        . "', 'By accessing our web-site, you agree to be legally bound by the following terms. If you do not agree to be legally bound by all of the following terms then please do not access and/or use our web-site. We may change these at any time and we’ll do our utmost in informing you, though it would be prudent to review this regularly yourself as your continued usage of our web-site after changes mean you agree to be legally bound by these terms as they are updated and/or amended.<br><br> Our web-sites are powered by SystemDK which can be downloaded from www.systemsdk.com. For further information about SystemDK, please see: http://www.systemsdk.com.<br><br>You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any laws be it of your country, the country where this web-site is hosted or International Law. Doing so may lead to you being immediately and permanently banned, with notification of your Internet Service Provider if deemed required by us. The IP address are recorded to aid in enforcing these conditions. You agree that web-site administrators have the right to remove, edit, move or close access for You at any time should we see fit. As a user you agree to any information you have entered to being stored in a database. While this information will not be disclosed to any third party without your consent, neither web-site administration nor SystemDK developers shall be held responsible for any hacking attempt that may lead to the data being compromised.', 'en',null,null,null);"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_messages_" . $install_lang[$i] . " (
                    message_id int(11) not null auto_increment,
                    message_author varchar(25) not null,
                    message_title varchar(100) not null,
                    message_content mediumtext not null,
                    message_notes mediumtext,
                    message_date bigint(19) not null,
                    message_term_expire bigint(19),
                    message_term_expire_action varchar(10),
                    message_status enum('0','1') default '1' not null,
                    message_value_view enum('0','1','2','3') default '0' not null,
                    PRIMARY KEY (message_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_rss_" . $install_lang[$i] . " (
                    rss_id int(11) not null auto_increment,
                    rss_site_name varchar(30) not null,
                    rss_site_url varchar(255) not null,
                    PRIMARY KEY (rss_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_main_pages_" . $install_lang[$i] . " (
                    main_page_id int(11) not null auto_increment,
                    main_page_name_id varchar(191),
                    main_page_author varchar(25) not null,
                    main_page_title varchar(255) not null,
                    main_page_date bigint(19) not null,
                    main_page_content mediumtext not null,
                    main_page_notes mediumtext,
                    main_page_value_view enum('0','1','2','3') default '0' not null,
                    main_page_status enum('0','1') default '0' not null,
                    main_page_term_expire bigint(19),
                    main_page_term_expire_action varchar(10),
                    main_page_meta_title varchar(255),
                    main_page_meta_keywords varchar(255),
                    main_page_meta_description varchar(255),
                    PRIMARY KEY (main_page_id),
                    UNIQUE KEY main_page_name_id (main_page_name_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_news_" . $install_lang[$i] . " (
                    news_id int(11) not null auto_increment,
                    news_auto enum('0','1') default '0' not null,
                    news_author varchar(25) not null,
                    news_category_id int(11) not null,
                    news_title varchar(255) not null,
                    news_date bigint(19) not null,
                    news_images varchar(255),
                    news_short_text mediumtext not null,
                    news_content mediumtext not null,
                    news_read_counter int(11) default '0' not null,
                    news_notes mediumtext,
                    news_value_view enum('0','1','2','3') default '0' not null,
                    news_status enum('0','1') default '0' not null,
                    news_term_expire bigint(19),
                    news_term_expire_action varchar(10),
                    news_on_home enum('0','1') default '0' not null,
                    news_link varchar(255),
                    news_meta_title varchar(255),
                    news_meta_keywords varchar(255),
                    news_meta_description varchar(255),
                    PRIMARY KEY (news_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_news_categories_" . $install_lang[$i] . " (
                    news_category_id int(11) not null auto_increment,
                    news_category_name varchar(255) not null,
                    news_category_meta_title varchar(255),
                    news_category_meta_keywords varchar(255),
                    news_category_meta_description varchar(255),
                    PRIMARY KEY (news_category_id)
                  ) ENGINE=InnoDB"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } elseif ($data_array['db_type'] === 'oci8') {
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_users (
                        user_id NUMBER(11,0) NOT NULL,
                        user_temp NUMBER(1,0) DEFAULT 0 NOT NULL,
                        user_name VARCHAR2(25 CHAR),
                        user_surname VARCHAR2(25 CHAR),
                        user_website VARCHAR2(255 CHAR),
                        user_email VARCHAR2(60 CHAR) NOT NULL,
                        user_login VARCHAR2(25 CHAR) NOT NULL,
                        user_password VARCHAR2(255 CHAR) NOT NULL,
                        user_session_code VARCHAR2(15),
                        user_session_agent VARCHAR2(255),
                        user_rights VARCHAR2(255 CHAR),
                        user_country VARCHAR2(100 CHAR),
                        user_city VARCHAR2(100 CHAR),
                        user_address VARCHAR2(255 CHAR),
                        user_telephone VARCHAR2(100 CHAR),
                        user_icq VARCHAR2(50 CHAR),
                        user_skype VARCHAR2(50 CHAR),
                        user_gmt VARCHAR2(3 CHAR) DEFAULT +00 NOT NULL,
                        user_notes VARCHAR2(255 CHAR),
                        user_active NUMBER(1,0) DEFAULT 1 NOT NULL,
                        user_active_notes VARCHAR2(255 CHAR),
                        user_reg_date NUMBER(19,0) NOT NULL,
                        user_last_login_date NUMBER(19,0),
                        user_view_email NUMBER(1,0) DEFAULT 1 NOT NULL,
                        user_activate_key VARCHAR2(32 CHAR),
                        user_new_password VARCHAR2(255 CHAR),
                        user_reg_key VARCHAR2(50 CHAR)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_users ADD (
                        PRIMARY KEY(user_id),
                        UNIQUE (user_login, user_email)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_users_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " (
                    module_id NUMBER(11,0) NOT NULL,
                    module_name VARCHAR2(25 CHAR) NOT NULL,
                    module_custom_name VARCHAR2(25 CHAR) NOT NULL,
                    module_version VARCHAR2(255 CHAR),
                    module_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    module_value_view NUMBER(1,0) DEFAULT 0 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(module_id),
                    UNIQUE (module_name, module_custom_name)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_modules_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " VALUES (" . $adodb->GenID(
                        $data_array['prefix'] . "_modules_id_" . $install_lang[$i]
                    ) . ", 'news', 'News', NULL, '1', '0')"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_modules_" . $install_lang[$i] . " VALUES (" . $adodb->GenID(
                        $data_array['prefix'] . "_modules_id_" . $install_lang[$i]
                    ) . ", 'main_pages', 'Main Pages', NULL, '1', '0')"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_blocks_" . $install_lang[$i] . " (
                    block_id NUMBER(11,0) NOT NULL,
                    block_name VARCHAR2(25 CHAR),
                    block_custom_name VARCHAR2(25 CHAR) NOT NULL,
                    block_version VARCHAR2(255 CHAR),
                    block_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    block_value_view NUMBER(1,0) DEFAULT 0 NOT NULL,
                    block_content CLOB,
                    block_url VARCHAR2(255 CHAR),
                    block_arrangements VARCHAR2(11 CHAR) NOT NULL,
                    block_priority NUMBER(3,0) DEFAULT 1 NOT NULL,
                    block_refresh NUMBER(19,0),
                    block_last_refresh NUMBER(19,0),
                    block_term_expire NUMBER(19,0),
                    block_term_expire_action VARCHAR2(10 CHAR)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_blocks_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(block_id),
                    UNIQUE (block_name, block_custom_name)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_blocks_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_blocks_" . $install_lang[$i] . " VALUES (" . $adodb->GenID(
                        $data_array['prefix'] . "_blocks_id_" . $install_lang[$i]
                    ) . ", 'menu', 'Navigation', NULL, '1', '0', NULL, NULL, 'left', 1, NULL, NULL, NULL, NULL)"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                //shop start
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_catalogs_" . $install_lang[$i] . " (
                    catalog_id NUMBER(11,0) NOT NULL,
                    catalog_img VARCHAR2(255),
                    catalog_name VARCHAR2(255) NOT NULL,
                    catalog_description VARCHAR2(255),
                    catalog_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    catalog_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    catalog_date NUMBER(19,0) NOT NULL,
                    catalog_meta_title VARCHAR2(255),
                    catalog_meta_keywords VARCHAR2(255),
                    catalog_meta_description VARCHAR2(255)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_catalogs_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(catalog_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_catalogs_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_categories_" . $install_lang[$i] . " (
                    category_id NUMBER(11,0) NOT NULL,
                    category_img VARCHAR2(255),
                    category_name VARCHAR2(255) NOT NULL,
                    cat_catalog_id NUMBER(11,0) NOT NULL,
                    category_description VARCHAR2(255),
                    category_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    category_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    category_date NUMBER(19,0) NOT NULL,
                    category_meta_title VARCHAR2(255),
                    category_meta_keywords VARCHAR2(255),
                    category_meta_description VARCHAR2(255)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_categories_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(category_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_categories_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_subcategories_" . $install_lang[$i] . " (
                    subcategory_id NUMBER(11,0) NOT NULL,
                    subcategory_img VARCHAR2(255),
                    subcategory_name VARCHAR2(255) NOT NULL,
                    subcategory_cat_id NUMBER(11,0) NOT NULL,
                    subcategory_description VARCHAR2(255),
                    subcategory_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    subcategory_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    subcategory_date NUMBER(19,0) NOT NULL,
                    subcategory_meta_title VARCHAR2(255),
                    subcategory_meta_keywords VARCHAR2(255),
                    subcategory_meta_description VARCHAR2(255)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_subcategories_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(subcategory_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_subcategories_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_items_" . $install_lang[$i] . " (
                    item_id NUMBER(11,0) NOT NULL,
                    item_img VARCHAR2(255),
                    item_name VARCHAR2(255) NOT NULL,
                    item_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    item_price_discounted NUMBER(15,2),
                    item_catalog_id NUMBER(11,0),
                    item_category_id NUMBER(11,0),
                    item_subcategory_id NUMBER(11,0),
                    item_short_description CLOB NOT NULL,
                    item_description CLOB NOT NULL,
                    item_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    item_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    item_quantity NUMBER(11,0),
                    item_quantity_unlim NUMBER(1,0) DEFAULT 0 NOT NULL,
                    item_quantity_param NUMBER(1,0),
                    item_display NUMBER(1,0),
                    item_date NUMBER(19,0) NOT NULL,
                    item_meta_title VARCHAR2(255),
                    item_meta_keywords VARCHAR2(255),
                    item_meta_description VARCHAR2(255)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_items_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(item_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_items_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_delivery_" . $install_lang[$i] . " (
                    delivery_id NUMBER(11,0) NOT NULL,
                    delivery_name VARCHAR2(255) NOT NULL,
                    delivery_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    delivery_status NUMBER(1,0) DEFAULT 1 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_delivery_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(delivery_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_delivery_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_delivery_" . $install_lang[$i] . " VALUES (" . $adodb->GenID(
                        $data_array['prefix'] . "_delivery_id_" . $install_lang[$i]
                    ) . ", 'personal pickup', '0.00', '1')"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_payment_methods_" . $install_lang[$i] . " (
                    id NUMBER(11,0) NOT NULL,
                    payment_name VARCHAR2(255) NOT NULL,
                    payment_commission NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    payment_system_id NUMBER(11,0),
                    payment_status NUMBER(1,0) DEFAULT 1 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_payment_methods_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                /**
                 * we use ..._s_payment_method_... because in oracle sequence name should be no more then 30 symbols and differ from table name
                 */
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_s_payment_method_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "INSERT INTO " . $data_array['prefix'] . "_s_payment_methods_" . $install_lang[$i] . " VALUES (" . $adodb->GenID(
                        $data_array['prefix'] . "_s_payment_method_" . $install_lang[$i]
                    ) . ", 'cash', '0.00', NULL, '1')"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_orders_" . $install_lang[$i] . " (
                    order_id NUMBER(11,0) NOT NULL,
                    order_customer_id NUMBER(11,0),
                    order_amount NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    order_date  NUMBER(19,0) NOT NULL,
                    ship_date NUMBER(19,0),
                    order_status NUMBER(1,0) DEFAULT 1 NOT NULL,
                    ship_name VARCHAR2(255) NOT NULL,
                    ship_street VARCHAR2(255),
                    ship_house VARCHAR2(255),
                    ship_flat VARCHAR2(255),
                    ship_city VARCHAR2(255),
                    ship_state VARCHAR2(255),
                    ship_postal_code VARCHAR2(255),
                    ship_country VARCHAR2(255),
                    ship_telephone VARCHAR2(255) NOT NULL,
                    ship_email VARCHAR2(255) NOT NULL,
                    ship_add_info VARCHAR2(255),
                    order_delivery NUMBER(11,0) NOT NULL,
                    order_pay NUMBER(11,0) NOT NULL,
                    order_comments VARCHAR2(255),
                    order_ip VARCHAR2(45) NOT NULL,
                    order_hash VARCHAR2(150) NOT NULL,
                    invoice_date NUMBER(19,0),
                    invoice_amount NUMBER(15,2),
                    invoice_currency_code VARCHAR2(3),
                    invoice_exch_rate NUMBER(15,2),
                    invoice_paid NUMBER(1,0) DEFAULT 0 NOT NULL,
                    invoice_paid_date NUMBER(19,0)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_orders_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(order_id),
                    UNIQUE (order_hash)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_orders_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_order_items_" . $install_lang[$i] . " (
                    order_item_id NUMBER(11,0) NOT NULL,
                    order_id NUMBER(11,0) NOT NULL,
                    item_id NUMBER(11,0) NOT NULL,
                    order_item_name VARCHAR2(255) NOT NULL,
                    order_item_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    order_item_quantity NUMBER(3,0) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_order_items_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(order_item_id),
                    UNIQUE (order_id,item_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_order_items_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_add_par_g_types (
                        id NUMBER(11,0) NOT NULL,
                        type_name VARCHAR2(255 CHAR) NOT NULL,
                        type_description VARCHAR2(255 CHAR) NOT NULL
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_s_add_par_g_types ADD (
                        PRIMARY KEY(id)
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    // do not need sequence because type_id using in code to determine question type
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_add_par_g_types VALUES (1, 'check_more', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_CHECK_MORE')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_add_par_g_types VALUES (2, 'check_single', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_CHECK_SINGLE')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_add_par_g_types VALUES (3, 'select_list', '_SHOP_ITEM_ADDITIONAL_PARAMS_GROUP_TYPE_SELECT_LIST')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_add_par_groups_" . $install_lang[$i] . " (
                    id NUMBER(11,0) NOT NULL,
                    group_name VARCHAR2(255 CHAR) NOT NULL,
                    group_type_id NUMBER(11,0) NOT NULL,
                    group_status NUMBER(1,0) DEFAULT 1 NOT NULL
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_add_par_groups_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(id)
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                /**
                 * we use ..._s_add_par_group_id_... because in oracle sequence name should be no more then 30 symbols and differ from table name
                 */
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_s_add_par_group_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_add_params_" . $install_lang[$i] . " (
                    id NUMBER(11,0) NOT NULL,
                    param_group_id NUMBER(11,0) NOT NULL,
                    param_value VARCHAR2(255 CHAR) NOT NULL,
                    param_status NUMBER(1,0) DEFAULT 1 NOT NULL
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_add_params_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(id)
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_s_add_params_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_item_add_params_" . $install_lang[$i] . " (
                    item_id NUMBER(11,0) NOT NULL,
                    item_param_id NUMBER(11,0) NOT NULL
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_item_add_params_" . $install_lang[$i] . " ADD (
                    UNIQUE (item_id,item_param_id)
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_order_i_params_" . $install_lang[$i] . " (
                    id NUMBER(11,0) NOT NULL,
                    order_id NUMBER(11,0) NOT NULL,
                    order_item_id NUMBER(11,0) NOT NULL,
                    order_suborder_id NUMBER(11,0) NOT NULL,
                    order_item_param_group_id NUMBER(11,0) NOT NULL,
                    order_item_param_id NUMBER(11,0) NOT NULL
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_order_i_params_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(id)
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                /**
                 * we use .._s_order_i_param_id_.. and not {tablename}_id.. because in oracle sequence name should be no more then 30 symbols and differ from table name
                 */
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_s_order_i_param_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_payment_systems (
                        id NUMBER(11,0) NOT NULL,
                        system_name VARCHAR2(255 CHAR) NOT NULL,
                        system_class VARCHAR2(255 CHAR) NOT NULL,
                        system_integ_type VARCHAR2(255 CHAR) NOT NULL,
                        system_integ_description VARCHAR2(255 CHAR) NOT NULL,
                        system_version VARCHAR2(10 CHAR) NOT NULL,
                        system_status NUMBER(1,0) DEFAULT 0 NOT NULL
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_s_payment_systems ADD (
                        PRIMARY KEY(id)
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_payment_systems VALUES ('1',"
                        . " 'Portmone(acquiring)', 'shop_portmone', 'acquiring_agreement', 'Only when you have acquiring agreement with Portmone', '1.0', '0')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_portmone_ac_conf (
                        id NUMBER(11,0) NOT NULL,
                        payee_id VARCHAR2(255 CHAR) NOT NULL,
                        login VARCHAR2(255 CHAR) NOT NULL,
                        password VARCHAR2(255 CHAR) NOT NULL,
                        payment_description VARCHAR2(255 CHAR) NOT NULL,
                        log_mode VARCHAR2(10 CHAR) NOT NULL,
                        log_life_days NUMBER(5,0),
                        error_notify NUMBER(1,0) DEFAULT 0 NOT NULL,
                        error_notify_type VARCHAR2(20 CHAR) NOT NULL,
                        display_error_mode VARCHAR2(20 CHAR) NOT NULL
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_s_portmone_ac_conf ADD (
                        PRIMARY KEY(id)
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    
                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_s_portmone_ac_conf_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_currency_exch (
                        id NUMBER(19,0) NOT NULL,
                        from_currency_code VARCHAR2(3 CHAR) NOT NULL,
                        to_currency_code VARCHAR2(3 CHAR) NOT NULL,
                        rate NUMBER(15,2) NOT NULL,
                        date_from NUMBER(19,0) NOT NULL,
                        date_till NUMBER(19,0),
                        updated_at NUMBER(19,0) NOT NULL
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_s_currency_exch ADD (
                        PRIMARY KEY(id)
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_s_currency_exch_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_s_currencies (
                        id NUMBER(11,0) NOT NULL,
                        currency_code VARCHAR2(3 CHAR) NOT NULL,
                        currency_name VARCHAR2(255 CHAR) NOT NULL,
                        currency_order NUMBER(11,0) NOT NULL
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_s_currencies ADD (
                        PRIMARY KEY(id)
                        )"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_s_currencies_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_currencies VALUES (" . $adodb->GenID($data_array['prefix'] . "_s_currencies_id") . ","
                        . " 'rub', 'ruble', '4')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_currencies VALUES (" . $adodb->GenID($data_array['prefix'] . "_s_currencies_id") . ","
                        . " 'uah', 'hryvnia', '3')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_currencies VALUES (" . $adodb->GenID($data_array['prefix'] . "_s_currencies_id") . ","
                        . " 'usd', 'dollar', '2')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }

                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_s_currencies VALUES (" . $adodb->GenID($data_array['prefix'] . "_s_currencies_id") . ","
                        . " 'eur', 'euro', '1')"
                    );

                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_s_payment_logs_" . $install_lang[$i] . " (
                    id NUMBER(19,0) NOT NULL,
                    system_id NUMBER(11,0),
                    request_url VARCHAR2(255 CHAR),
                    method VARCHAR2(255 CHAR),
                    order_number NUMBER(11,0),
                    request_data CLOB,
                    response_data CLOB,
                    process_status VARCHAR2(255 CHAR) NOT NULL,
                    error_data CLOB,
                    log_date NUMBER(19,0) NOT NULL
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_s_payment_logs_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(id)
                    )"
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                $insert = $adodb->Execute("CREATE INDEX " . $data_array['prefix'] . "_s_payment_logs_" . $install_lang[$i] . "_dt ON " . $data_array['prefix'] . "_s_payment_logs_" . $install_lang[$i] . " (log_date)");
                
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }

                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_s_payment_logs_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );

                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                
                //shop end
                // questionnaire start
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_groups_" . $install_lang[$i] . " (
                    group_id NUMBER(11,0) NOT NULL,
                    questionnaire_id NUMBER(11,0) NOT NULL,
                    group_name VARCHAR2(255) NOT NULL,
                    group_status NUMBER(1,0) DEFAULT 0 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_groups_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(group_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_groups_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_questions_" . $install_lang[$i] . " (
                    question_id NUMBER(11,0) NOT NULL,
                    question_title VARCHAR2(255) NOT NULL,
                    questionnaire_id NUMBER(11,0) NOT NULL,
                    type_id NUMBER(11,0) NOT NULL,
                    question_priority NUMBER(11,0) NOT NULL,
                    question_subtext VARCHAR2(255),
                    question_obligatory NUMBER(1,0) DEFAULT 1 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_questions_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(question_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_questions_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_question_answers_" . $install_lang[$i] . " (
                    question_answer_id NUMBER(11,0) NOT NULL,
                    question_id NUMBER(11,0) NOT NULL,
                    question_answer VARCHAR2(255) NOT NULL,
                    question_answer_priority NUMBER(11,0) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_question_answers_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(question_answer_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_question_answers_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_question_items_" . $install_lang[$i] . " (
                    question_item_id NUMBER(11,0) NOT NULL,
                    question_id NUMBER(11,0) NOT NULL,
                    question_item_title VARCHAR2(255) NOT NULL,
                    question_item_priority NUMBER(11,0) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_question_items_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(question_item_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_question_items_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_q_question_types (
                        type_id NUMBER(11,0) NOT NULL,
                        type_name VARCHAR2(255) NOT NULL,
                        type_description VARCHAR2(255) NOT NULL
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_q_question_types ADD (
                        PRIMARY KEY(type_id)
                    )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    // do not need sequence because type_id using in code to determine question type
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_q_question_types VALUES (1, 'check_more', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_MORE')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_q_question_types VALUES (2, 'check_single', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_SINGLE')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_q_question_types VALUES (3, 'select_list', '_QUESTIONNAIRES_QUESTION_TYPE_SELECT_LIST')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix']
                        . "_q_question_types VALUES (4, 'check_more_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_MORE_WITH_TEXT')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix']
                        . "_q_question_types VALUES (5, 'check_single_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_CHECK_SINGLE_WITH_TEXT')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix']
                        . "_q_question_types VALUES (6, 'select_list_with_text', '_QUESTIONNAIRES_QUESTION_TYPE_SELECT_LIST_WITH_TEXT')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_q_question_types VALUES (7, 'only_text', '_QUESTIONNAIRES_QUESTION_TYPE_ONLY_TEXT')"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_questionnaires_" . $install_lang[$i] . " (
                    questionnaire_id NUMBER(11,0) NOT NULL,
                    questionnaire_name VARCHAR2(255) NOT NULL,
                    questionnaire_date NUMBER(19,0) NOT NULL,
                    questionnaire_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    questionnaire_meta_title VARCHAR2(255),
                    questionnaire_meta_keywords VARCHAR2(255),
                    questionnaire_meta_description VARCHAR2(255)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_questionnaires_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(questionnaire_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_questionnaires_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_answers_" . $install_lang[$i] . " (
                    answer_id NUMBER(11,0) NOT NULL,
                    person_id NUMBER(11,0) NOT NULL,
                    question_id NUMBER(11,0) NOT NULL,
                    question_item_id NUMBER(11,0),
                    questionnaire_id NUMBER(11,0) NOT NULL,
                    answer_text CLOB,
                    group_id NUMBER(11,0) NOT NULL,
                    question_answer_id NUMBER(11,0)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_voted_answers_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(answer_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_voted_answers_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_groups_" . $install_lang[$i] . " (
                    voted_group_id NUMBER(11,0) NOT NULL,
                    group_id NUMBER(11,0) NOT NULL,
                    questionnaire_id NUMBER(11,0) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_voted_groups_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(voted_group_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_voted_groups_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_q_voted_persons_" . $install_lang[$i] . " (
                    person_id NUMBER(11,0) NOT NULL,
                    user_id NUMBER(11,0),
                    voted_group_id NUMBER(11,0) NOT NULL,
                    person_ip VARCHAR2(45 CHAR) NOT NULL,
                    voted_date NUMBER(19,0) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_q_voted_persons_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(person_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_voted_persons_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                // questionnaire end
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_guestbook (
                        post_id NUMBER(11,0) NOT NULL,
                        post_user_name VARCHAR2(255 CHAR) NOT NULL,
                        post_user_country VARCHAR2(100 CHAR),
                        post_user_city VARCHAR2(100 CHAR),
                        post_user_telephone VARCHAR2(100 CHAR),
                        post_show_telephone NUMBER(1,0) DEFAULT 1 NOT NULL,
                        post_user_email VARCHAR2(60 CHAR) NOT NULL,
                        post_show_email NUMBER(1,0) DEFAULT 1 NOT NULL,
                        post_user_id NUMBER(11,0),
                        post_user_ip VARCHAR2(45 CHAR) NOT NULL,
                        post_date NUMBER(19,0) NOT NULL,
                        post_text CLOB NOT NULL,
                        post_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                        post_answer CLOB,
                        post_answer_date NUMBER(19,0)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_guestbook ADD (
                        PRIMARY KEY(post_id)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_guestbook_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_mail_receivers_" . $install_lang[$i] . " (
                    receiver_id NUMBER(11,0) NOT NULL,
                    receiver_name VARCHAR2(25 CHAR) NOT NULL,
                    receiver_email VARCHAR2(60 CHAR) NOT NULL,
                    receiver_active NUMBER(1,0) DEFAULT 1 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_mail_receivers_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(receiver_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_mail_receivers_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_mail_sendlog (
                        mail_id NUMBER(11,0) NOT NULL,
                        mail_user VARCHAR2(25 CHAR),
                        mail_date NUMBER(19,0) NOT NULL,
                        mail_module VARCHAR2(25 CHAR) NOT NULL,
                        mail_ip VARCHAR2(45 CHAR) NOT NULL
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_mail_sendlog ADD (
                        PRIMARY KEY(mail_id)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute("CREATE INDEX " . $data_array['prefix'] . "_mail_sendlog1 ON " . $data_array['prefix'] . "_mail_sendlog (mail_date)");
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute("CREATE INDEX " . $data_array['prefix'] . "_mail_sendlog2 ON " . $data_array['prefix'] . "_mail_sendlog (mail_module)");
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute("CREATE INDEX " . $data_array['prefix'] . "_mail_sendlog3 ON " . $data_array['prefix'] . "_mail_sendlog (mail_ip)");
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_mail_sendlog_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_main_menu_" . $install_lang[$i] . " (
                    main_menu_id NUMBER(11,0) NOT NULL,
                    main_menu_type VARCHAR2(10 CHAR) NOT NULL,
                    main_menu_name VARCHAR2(25 CHAR) NOT NULL,
                    main_menu_module VARCHAR2(255 CHAR),
                    main_menu_submodule VARCHAR2(255 CHAR),
                    main_menu_add_param VARCHAR2(255 CHAR),
                    main_menu_target VARCHAR2(20 CHAR) DEFAULT '_self' NOT NULL,
                    main_menu_priority NUMBER(3,0) NOT NULL,
                    main_menu_in_menu NUMBER(1,0) DEFAULT 1 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_main_menu_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(main_menu_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_main_menu_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                if ($i == 0) {
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_session (
                        session_login VARCHAR2(25 CHAR),
                        session_time  NUMBER(19,0) NOT NULL,
                        session_host_address VARCHAR2(45 CHAR) NOT NULL,
                        session_who_online VARCHAR2(20 CHAR) NOT NULL
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute("CREATE INDEX " . $data_array['prefix'] . "_session_login ON " . $data_array['prefix'] . "_session (session_login)");
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE TABLE " . $data_array['prefix'] . "_system_pages (
                        system_page_id NUMBER(11,0) NOT NULL,
                        system_page_author VARCHAR2(25 CHAR) NOT NULL,
                        system_page_name VARCHAR2(255 CHAR) NOT NULL,
                        system_page_title VARCHAR2(255 CHAR) NOT NULL,
                        system_page_date NUMBER(19,0) NOT NULL,
                        system_page_content CLOB NOT NULL,
                        system_page_lang VARCHAR2(2 CHAR),
                        system_page_meta_title VARCHAR2(255 CHAR),
                        system_page_meta_keywords VARCHAR2(255 CHAR),
                        system_page_meta_description VARCHAR2(255 CHAR)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "ALTER TABLE " . $data_array['prefix'] . "_system_pages ADD (
                        PRIMARY KEY(system_page_id)
                      )"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->Execute(
                        "CREATE SEQUENCE " . $data_array['prefix'] . "_system_pages_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      "
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $adodb->StartTrans();
                    $systempage_id = $adodb->GenID($data_array['prefix'] . "_system_pages_id");
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_system_pages VALUES (" . $systempage_id . ", 'admin', 'account_rules', 'Общие правила', '" . time()
                        . "', empty_clob(), 'ru',null,null,null)"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->UpdateClob(
                        $data_array['prefix'] . '_system_pages', 'system_page_content',
                        'Заходя на сайт, Вы подтверждаете своё согласие со следующими условиями. Если Вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь нашим сайтом. Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить Вас об этом, однако с Вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование нашего сайта после обновления/исправления условий означает Ваше согласие с ними.<br><br>Наши сайты работают под управлением программного обеспечения SystemDK для создания веб-сайтов. Скачать его можно по адресу www.systemsdk.com. За дополнительной информацией о SystemsDK обращайтесь по адресу http://www.systemsdk.com.<br><br>Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы Вашей страны, страны, которая предоставляет услуги хостинга для этого сайта, или международное право. Попытки размещения таких сообщений могут привести к Вашему немедленному отключению от сайта, при этом Ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы сайта имеют право удалить, отредактировать, перенести или закрыть Вам доступ в любое время по своему усмотрению. Как пользователь Вы согласны с тем, что введённая Вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без Вашего разрешения, ни администрация сайта, ни авторы программного обеспечения SystemDK не могут быть ответственными за действия хакеров, которые могут привести к несанкционированному доступу к ней.',
                        'system_page_id = ' . $systempage_id
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $systempage_id = $adodb->GenID($data_array['prefix'] . "_system_pages_id");
                    $insert = $adodb->Execute(
                        "INSERT INTO " . $data_array['prefix'] . "_system_pages VALUES (" . $systempage_id . ", 'admin', 'account_rules', 'General rules', '" . time()
                        . "', empty_clob(), 'en',null,null,null)"
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $insert = $adodb->UpdateClob(
                        $data_array['prefix'] . '_system_pages', 'system_page_content',
                        'By accessing our web-site, you agree to be legally bound by the following terms. If you do not agree to be legally bound by all of the following terms then please do not access and/or use our web-site. We may change these at any time and we’ll do our utmost in informing you, though it would be prudent to review this regularly yourself as your continued usage of our web-site after changes mean you agree to be legally bound by these terms as they are updated and/or amended.<br><br> Our web-sites are powered by SystemDK which can be downloaded from www.systemsdk.com. For further information about SystemDK, please see: http://www.systemsdk.com.<br><br>You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any laws be it of your country, the country where this web-site is hosted or International Law. Doing so may lead to you being immediately and permanently banned, with notification of your Internet Service Provider if deemed required by us. The IP address are recorded to aid in enforcing these conditions. You agree that web-site administrators have the right to remove, edit, move or close access for You at any time should we see fit. As a user you agree to any information you have entered to being stored in a database. While this information will not be disclosed to any third party without your consent, neither web-site administration nor SystemDK developers shall be held responsible for any hacking attempt that may lead to the data being compromised.',
                        'system_page_id = ' . $systempage_id
                    );
                    if (!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = ["code" => $error_code, "message" => $error_message];
                    }
                    $adodb->CompleteTrans();
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_messages_" . $install_lang[$i] . " (
                    message_id NUMBER(11,0) NOT NULL,
                    message_author VARCHAR2(25 CHAR) NOT NULL,
                    message_title VARCHAR2(100 CHAR) NOT NULL,
                    message_content CLOB NOT NULL,
                    message_notes CLOB,
                    message_date NUMBER(19,0) NOT NULL,
                    message_term_expire NUMBER(19,0),
                    message_term_expire_action VARCHAR2(10 CHAR),
                    message_status NUMBER(1,0) DEFAULT 1 NOT NULL,
                    message_value_view NUMBER(1,0) DEFAULT 0 NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_messages_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(message_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_messages_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_rss_" . $install_lang[$i] . " (
                    rss_id NUMBER(10,0) NOT NULL,
                    rss_site_name VARCHAR2(30 CHAR) NOT NULL,
                    rss_site_url VARCHAR2(255 CHAR) NOT NULL
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_rss_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(rss_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_rss_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_main_pages_" . $install_lang[$i] . " (
                    main_page_id NUMBER(11,0) NOT NULL,
                    main_page_name_id VARCHAR2(191 CHAR),
                    main_page_author VARCHAR2(25 CHAR) NOT NULL,
                    main_page_title VARCHAR2(255 CHAR) NOT NULL,
                    main_page_date NUMBER(19,0) NOT NULL,
                    main_page_content CLOB NOT NULL,
                    main_page_notes CLOB,
                    main_page_value_view NUMBER(1,0) DEFAULT 0 NOT NULL,
                    main_page_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    main_page_term_expire NUMBER(19,0),
                    main_page_term_expire_action VARCHAR2(10 CHAR),
                    main_page_meta_title VARCHAR2(255 CHAR),
                    main_page_meta_keywords VARCHAR2(255 CHAR),
                    main_page_meta_description VARCHAR2(255 CHAR)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_main_pages_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(main_page_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_main_pages_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_news_" . $install_lang[$i] . " (
                    news_id NUMBER(11,0) NOT NULL,
                    news_auto NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_author VARCHAR2(25 CHAR) NOT NULL,
                    news_category_id NUMBER(11,0) NOT NULL,
                    news_title VARCHAR2(255 CHAR) NOT NULL,
                    news_date NUMBER(19,0) NOT NULL,
                    news_images VARCHAR2(255 CHAR),
                    news_short_text CLOB NOT NULL,
                    news_content CLOB NOT NULL,
                    news_read_counter NUMBER(10,0) DEFAULT 0 NOT NULL,
                    news_notes CLOB,
                    news_value_view NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_term_expire NUMBER(19,0),
                    news_term_expire_action VARCHAR2(10 CHAR),
                    news_on_home NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_link VARCHAR2(255 CHAR),
                    news_meta_title VARCHAR2(255 CHAR),
                    news_meta_keywords VARCHAR2(255 CHAR),
                    news_meta_description VARCHAR2(255 CHAR)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_news_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(news_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_news_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE TABLE " . $data_array['prefix'] . "_news_categories_" . $install_lang[$i] . " (
                    news_category_id NUMBER(11,0) NOT NULL,
                    news_category_name VARCHAR2(255 CHAR) NOT NULL,
                    news_category_meta_title VARCHAR2(255 CHAR),
                    news_category_meta_keywords VARCHAR2(255 CHAR),
                    news_category_meta_description VARCHAR2(255 CHAR)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "ALTER TABLE " . $data_array['prefix'] . "_news_categories_" . $install_lang[$i] . " ADD (
                    PRIMARY KEY(news_category_id)
                  )"
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
                $insert = $adodb->Execute(
                    "CREATE SEQUENCE " . $data_array['prefix'] . "_news_categories_id_" . $install_lang[$i] . "
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  "
                );
                if (!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = ["code" => $error_code, "message" => $error_message];
                }
            } else {
                if ($data_array['db_persistency'] === 'no') {
                    $adodb->Close();
                }
                header("Location: ../index.php");
                exit();
            }
            if (isset($error)) {
                if ($data_array['db_persistency'] === 'no') {
                    $adodb->Close();
                }
                $this->smarty->assign("error", "insert_error");
                $this->smarty->assign("error_mess", $error);
                $this->smarty->display("install/action.html", "|error_insert");
                exit();
            } else {
                $file = @fopen("../includes/data/" . $install_lang[$i] . "_installed.dat", "w");
                @fclose($file);
            }
        }
        $site_startdate = date("d/m/Y");
        $file = @fopen("../includes/data/config.inc", "w");
        $content = "<?php\n\n";
        $content .= 'define("SYSTEMDK_VERSION","3.5.1");' . "\n";
        $content .= 'define("SYSTEMDK_MODREWRITE","' . $data_array['mod_rewrite'] . "\");\n";
        $content .= 'define("SYSTEMDK_DESCRIPTION","");' . "\n";
        $content .= 'define("SITE_ID","' . $data_array['site_id'] . "\");\n";
        $content .= 'define("SITE_DIR","' . $data_array['site_dir'] . "\");\n";
        $content .= 'define("ADMIN_DIR","' . $data_array['admin_dir'] . "\");\n";
        $content .= 'define("ADMIN_EMAIL","' . $data_array['admin_email'] . "\");\n";
        $content .= 'define("DB_HOST","' . $data_array['db_host'] . "\");\n";
        $content .= 'define("DB_PORT","' . $data_array['db_port'] . "\");\n";
        $content .= 'define("DB_USER_NAME","' . $data_array['db_user_name'] . "\");\n";
        $content .= 'define("DB_PASSWORD","' . $data_array['db_password'] . "\");\n";
        $content .= 'define("DB_NAME","' . $data_array['db_name'] . "\");\n";
        $content .= 'define("DB_PERSISTENCY","' . $data_array['db_persistency'] . "\");\n";
        $content .= 'define("PREFIX","' . $data_array['prefix'] . "\");\n";
        $content .= 'define("DBTYPE","' . $data_array['db_type'] . "\");\n";
        $content .= 'define("DB_CHARACTER","' . $data_array['db_character'] . "\");\n";
        $content .= 'define("DB_COLLATE","' . $data_array['db_collate'] . "\");\n";
        $content .= 'define("SMARTY_CACHING","' . $data_array['smarty_caching'] . "\");\n";
        $content .= 'define("SMARTY_DEBUGGING","' . $data_array['smarty_debugging'] . "\");\n";
        $content .= 'define("ADODB_DEBUGGING","' . $data_array['adodb_debugging'] . "\");\n";
        $content .= 'define("CACHE_LIFETIME","' . $data_array['cache_lifetime'] . "\");\n";
        $content .= 'define("SITE_CHARSET","' . $data_array['site_charset'] . "\");\n";
        $content .= 'define("SITE_URL","' . $data_array['site_url'] . "\");\n";
        $content .= 'define("SITE_NAME","' . $data_array['site_name'] . "\");\n";
        $content .= 'define("SITE_LOGO","' . $data_array['site_logo'] . "\");\n";
        $content .= 'define("SITE_STARTDATE","' . $site_startdate . "\");\n";
        $content .= 'define("SITE_KEYWORDS","' . $data_array['site_keywords'] . "\");\n";
        $content .= 'define("SITE_DESCRIPTION","' . $data_array['site_description'] . "\");\n";
        $content .= 'define("SITE_STATUS","' . $data_array['site_status'] . "\");\n";
        $content .= 'define("SITE_STATUS_NOTES","' . $data_array['site_status_notes'] . "\");\n";
        $content .= 'define("SITE_THEME","' . $data_array['site_theme'] . "\");\n";
        $content .= 'define("LANGUAGE","' . $data_array['language'] . "\");\n";
        $content .= 'define("SYSTEMDK_SITE_LANG","' . $data_array['systemdk_site_lang'] . "\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_SITE_LANG","' . $data_array['systemdk_admin_site_lang'] . "\");\n";
        $content .= 'define("SYSTEMDK_INTERFACE_LANG","' . $data_array['systemdk_interface_lang'] . "\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_INTERFACE_LANG","' . $data_array['systemdk_admin_interface_lang'] . "\");\n";
        $content .= 'define("HOME_MODULE","' . $data_array['Home_Module'] . "\");\n";
        $content .= 'define("GZIP","' . $data_array['gzip'] . "\");\n";
        $content .= 'define("ENTER_CHECK","' . $data_array['enter_check'] . "\");\n";
        $content .= 'define("SESSION_COOKIE_LIVE","' . $data_array['session_cookie_live'] . "\");\n";
        $content .= 'define("DISPLAY_ADMIN_GRAPHIC","' . $data_array['display_admin_graphic'] . "\");\n";
        $content .= 'define("SYSTEMDK_ADMINROWS_PERPAGE","' . $data_array['systemdk_adminrows_perpage'] . "\");\n";
        $content .= 'define("SYSTEMDK_MAIL_METHOD","' . $data_array['systemdk_mail_method'] . "\");\n";
        $content .= 'define("SYSTEMDK_SMTP_HOST","' . $data_array['systemdk_smtp_host'] . "\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PORT","' . $data_array['systemdk_smtp_port'] . "\");\n";
        $content .= 'define("SYSTEMDK_SMTP_USER","' . $data_array['systemdk_smtp_user'] . "\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PASS","' . $data_array['systemdk_smtp_pass'] . "\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_REGISTRATION","' . $data_array['systemdk_account_registration'] . "\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_ACTIVATION","' . $data_array['systemdk_account_activation'] . "\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_RULES","' . $data_array['systemdk_account_rules'] . "\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXUSERS","' . $data_array['systemdk_account_maxusers'] . "\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS","' . $data_array['systemdk_account_maxactivationdays'] . "\");\n";
        $content .= 'define("SYSTEMDK_STATISTICS","' . $data_array['systemdk_statistics'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOPLAY","' . $data_array['systemdk_player_autoplay'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOBUFFER","' . $data_array['systemdk_player_autobuffer'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_CONTROLS","' . $data_array['systemdk_player_controls'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOPLAY","' . $data_array['systemdk_player_audioautoplay'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOBUFFER","' . $data_array['systemdk_player_audioautobuffer'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOCONTROLS","' . $data_array['systemdk_player_audio_controls'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_RATIO","' . $data_array['systemdk_player_ratio'] . "\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_SKIN","' . $data_array['systemdk_player_skin'] . "\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM","' . $data_array['systemdk_ipantispam'] . "\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM_NUM","' . $data_array['systemdk_ipantispam_num'] . "\");\n";
        $content .= 'define("SYSTEMDK_FEEDBACK_RECEIVER","default");' . "\n";
        $write_file = @fwrite($file, $content);
        if (!$write_file) {
            if ($data_array['db_persistency'] === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "no_write");
            $this->smarty->display("install/action.html", "|no_write");
            exit();
        }
        @fclose($file);
        @chmod("../includes/data/config.inc", 0604);
        if ($data_array['db_persistency'] === 'no') {
            $adodb->Close();
        }
        session_start();
        $_SESSION["install"] = $data_array['site_id'];
        $this->smarty->display("install/install4.html");
    }


    public function step5()
    {
        $this->check_php_version();
        $this->smarty->display("install/install5.html");
    }


    public function step6()
    {
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                $keys = [
                    'admin_add_login',
                    'admin_add_password',
                    'admin_add_password2',
                    'admin_add_email',
                    'admin_add_viewemail',
                ];
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if ((!isset($data_array['admin_add_email']) || !isset($data_array['admin_add_login']) || !isset($data_array['admin_add_password'])
             || !isset($data_array['admin_add_password2'])
             || !isset($data_array['admin_add_viewemail']))
            || ($data_array['admin_add_email'] == ""
                || $data_array['admin_add_login'] == ""
                || $data_array['admin_add_password'] == ""
                || $data_array['admin_add_password2'] == ""
                || $data_array['admin_add_viewemail'] == "")
        ) {
            $this->smarty->assign("error", "not_all_data");
            $this->smarty->display("install/action.html", "|not_all_data");
            exit();
        }
        $this->check_php_version();
        include_once("../includes/data/config.inc");
        include_once("../adodb/adodb.inc.php");
        if (session_id() == "") {
            session_start();
        }
        if (!isset($_SESSION["install"]) || (isset($_SESSION["install"]) && $_SESSION["install"] != SITE_ID)) {
            header("Location: ../index.php");
            if (isset($_SESSION["install"])) {
                unset($_SESSION["install"]);
            }
            session_unset();
            setcookie(session_name(), '', time() - 3600, '/');
            session_destroy();
            exit();
        }
        $adodb = ADONewConnection(DBTYPE);
        if (ADODB_DEBUGGING === 'yes') {
            $adodb->debug = true;
        }
        if (DBTYPE === 'oci8') {
            $adodb->charSet = DB_CHARACTER;
            $cnt = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=" . DB_HOST . ")(PORT=" . DB_PORT . "))(CONNECT_DATA=(SID=" . DB_NAME . ")))";
        }
        if (DB_PERSISTENCY === 'yes') {
            /*if(DBTYPE === 'sqlite') {
                $db2 = $adodb->PConnect($_SERVER['DOCUMENT_ROOT']."/../".SITE_DIR."includes/data/".DB_NAME.".db");
            } elseif(DBTYPE === 'access') {
                $db2 = $adodb->PConnect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$_SERVER['DOCUMENT_ROOT']."/../".SITE_DIR."includes/data/".DB_NAME.".mdb;Uid=".DB_USER_NAME.";Pwd=".DB_PASSWORD.";");
            }
            if(DBTYPE === 'ado_mssql') {
                $db2 = $adodb->PConnect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".DB_HOST.";DATABASE=".DB_NAME.";UID=".DB_USER_NAME.";PWD=".DB_PASSWORD.";");
            } elseif(DBTYPE === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".DB_HOST.";Database=".DB_NAME.";";
                $db2 = $adodb->PConnect($dsn,DB_USER_NAME,DB_PASSWORD);
            }*/
            if (DBTYPE === 'oci8' && DB_PORT != 0) {
                $db2 = $adodb->PConnect($cnt, DB_USER_NAME, DB_PASSWORD);
            } elseif (DBTYPE === 'pdo') {
                $db2 = $adodb->PConnect('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER_NAME, DB_PASSWORD);
            } else {
                $db2 = $adodb->PConnect(DB_HOST, DB_USER_NAME, DB_PASSWORD, DB_NAME);
            }
        } else {
            /*if(DBTYPE === 'sqlite') {
                $db2 = $adodb->Connect($_SERVER['DOCUMENT_ROOT']."/../".SITE_DIR."includes/data/".DB_NAME.".db");
            } elseif(DBTYPE === 'access') {
                $db2 = $adodb->Connect("Driver={Microsoft Access Driver (*.mdb)};Dbq=".$_SERVER['DOCUMENT_ROOT']."/../".SITE_DIR."includes/data/".DB_NAME.".mdb;Uid=".DB_USER_NAME.";Pwd=".DB_PASSWORD.";");
            }
            if(DBTYPE === 'ado_mssql') {
                $db2 = $adodb->Connect("PROVIDER=MSDASQL;DRIVER={SQL Server};SERVER=".DB_HOST.";DATABASE=".DB_NAME.";UID=".DB_USER_NAME.";PWD=".DB_PASSWORD.";");
            } elseif(DBTYPE === 'odbc_mssql') {
                $dsn = "Driver={SQL Server};Server=".DB_HOST.";Database=".DB_NAME.";";
                $db2 = $adodb->Connect($dsn,DB_USER_NAME,DB_PASSWORD);
            }*/
            if (DBTYPE === 'oci8' && DB_PORT != 0) {
                $db2 = $adodb->Connect($cnt, DB_USER_NAME, DB_PASSWORD);
            } elseif (DBTYPE === 'pdo') {
                $db2 = $adodb->Connect('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER_NAME, DB_PASSWORD);
            } else {
                $db2 = $adodb->Connect(DB_HOST, DB_USER_NAME, DB_PASSWORD, DB_NAME);
            }
        }
        if (!$db2) {
            $this->smarty->assign("error", "not_connect");
            $this->smarty->display("install/action.html", "|not_connect");
            exit();
        }
        if (DBTYPE === 'mysql' || DBTYPE === 'pdo' || DBTYPE === 'mysqli' || DBTYPE === 'mysqlt' || DBTYPE === 'maxsql') {
            $adodb->Execute("SET NAMES '" . DB_CHARACTER . "' COLLATE " . DB_COLLATE);
        }
        foreach ($data_array as $key => $value) {
            if (in_array($key, $keys)) {
                $data_array[$key] = strip_tags($value);
            }
        }
        if (preg_match("/[^a-zA-Zа-яА-Я0-9_-]/i", $data_array['admin_add_login'])) {
            if (DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "error_login");
            $this->smarty->display("install/action.html", "|error_login");
            exit();
        }
        if ($data_array['admin_add_password'] !== $data_array['admin_add_password2']) {
            if (DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "not_eq_pass");
            $this->smarty->display("install/action.html", "|not_eq_pass");
            exit();
        }
        if (!filter_var($data_array['admin_add_email'], FILTER_VALIDATE_EMAIL)) {
            if (DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "not_correct_email");
            $this->smarty->display("install/action.html", "|not_correct_email");
            exit();
        }
        if (isset($_SESSION["install"])) {
            unset($_SESSION["install"]);
        }
        session_unset();
        setcookie(session_name(), '', time() - 3600, '/');
        session_destroy();
        $data_array['admin_add_login'] = $adodb->qstr($data_array['admin_add_login'], get_magic_quotes_gpc());
        $data_array['admin_add_email'] = $adodb->qstr($data_array['admin_add_email'], get_magic_quotes_gpc());
        $data_array['admin_add_password'] = $adodb->qstr($data_array['admin_add_password'], get_magic_quotes_gpc());
        $data_array['admin_add_viewemail'] = $adodb->qstr($data_array['admin_add_viewemail'], get_magic_quotes_gpc());
        $data_array['admin_add_password'] = substr(substr($data_array['admin_add_password'], 0, -1), 1);
        $data_array['admin_add_password'] = crypt($data_array['admin_add_password'], $this->salt());
        $now = time();
        $adodb->Execute("UPDATE " . PREFIX . "_system_pages SET system_page_author = " . $data_array['admin_add_login'] . " WHERE system_page_author = 'admin'");
        $admin_add_id = false;
        $field_user_id = false;
        if (DBTYPE === 'oci8') {
            $admin_add_id = "'" . $adodb->GenID(PREFIX . "_users_id") . "',";
            $field_user_id = 'user_id,';
        }
        $sql = "INSERT INTO " . PREFIX . "_users (" . $field_user_id
               . "user_temp,user_email,user_login,user_password,user_rights,user_gmt,user_active,user_reg_date,user_view_email)  VALUES (" . $admin_add_id . "'0',"
               . $data_array['admin_add_email'] . "," . $data_array['admin_add_login'] . ",'" . $data_array['admin_add_password'] . "','main_admin','+00','1','" . $now
               . "'," . $data_array['admin_add_viewemail'] . ")";
        $result = $adodb->Execute($sql);
        if ($result === false || $adodb->Affected_Rows() == 0) {
            $error_message = $adodb->ErrorMsg();
            $error_code = $adodb->ErrorNo();
            $error[] = ["code" => $error_code, "message" => $error_message];
            if (DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error", "insert_error2");
            $this->smarty->assign("error_mess", $error);
            $this->smarty->display("install/action.html", "|error_insert2");
            exit();
        }
        if (DB_PERSISTENCY === 'no') {
            $adodb->Close();
        }
        $this->smarty->assign("error", "add_ok");
        $this->smarty->display("install/action.html");
    }


    private function check_php_version()
    {
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        if (PHP_VERSION_ID < 50400 && $this->func !== 'step2') {
            if ($this->func == 'step5' || $this->func == 'step6') {
                if (session_id() == "") {
                    session_start();
                }
                if (isset($_SESSION["install"])) {
                    unset($_SESSION["install"]);
                }
                session_unset();
                setcookie(session_name(), '', time() - 3600, '/');
                session_destroy();
            }
            header("Location: ../index.php");
            exit();
        } elseif (PHP_VERSION_ID < 50400 && $this->func == 'step2') {
            $this->smarty->assign("servconf", "no");
        } elseif ($this->func == 'step2') {
            $this->smarty->assign("servconf", "yes");
        }
    }


    private function get_lang_list()
    {
        if (is_dir('../themes/systemdk/languages')) {
            if ($file_read = opendir('../themes/systemdk/languages')) {
                $i = 0;
                while (false !== ($file = readdir($file_read))) {
                    if (!preg_match("/[.]/", $file)) {
                        $lang_list[] = ["item" => $i, "name" => $file];
                        $i++;
                    }
                }
                closedir($file_read);
            }
        }
        if (!isset($lang_list)) {
            $lang_list = "no";
        }

        return $lang_list;
    }


    private function salt()
    {
        $cost = 13;
        $rand = [];
        for ($i = 0; $i < 8; $i += 1) {
            $rand[] = pack('S', mt_rand(0, 0xffff));
        }
        $rand[] = substr(microtime(), 2, 6);
        $rand = sha1(implode('', $rand), true);
        $salt_prefix = '$2a$';
        if (PHP_VERSION_ID >= 50307) {
            $salt_prefix = '$2y$';
        }
        $salt = $salt_prefix . sprintf('%02d', $cost) . '$';
        $salt .= strtr(substr(base64_encode($rand), 0, 22), ['+' => '.']);

        return $salt;
    }
}


$load = new install();