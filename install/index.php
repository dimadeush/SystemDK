<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      install/index.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class install {


    private $smarty;
    private $func;


    public function __construct() {
        if(function_exists("date_default_timezone_set") and function_exists("date_default_timezone_get")) {
            @date_default_timezone_set(@date_default_timezone_get());
        }
        require_once("../smarty/Smarty.class.php");
        $this->smarty = new Smarty;
        $this->smarty->setConfigDir(array("1" => '../themes/systemdk/configs/',"2" => '../themes/systemdk/languages/'));
        $this->smarty->setTemplateDir('../themes/systemdk/templates/');
        $this->smarty->setCompileDir('../themes/systemdk/templates_c/');
        $this->smarty->setCacheDir('../themes/systemdk/cache/');
        if(!isset($_POST['func']) or strip_tags(trim($_POST['func'])) == "") {
            $func = "step1";
        } else {
            $func = strip_tags(trim($_POST['func']));
        }
        if(is_callable(array('install',$func)) == true) {
            $this->func = $func;
            $this->$func();
        } else {
            $this->func = 'step1';
            $this->step1();
        }
    }


    public function step1() {
        $this->smarty->display("install/install1.html");
    }


    public function funcexit() {
        $this->smarty->display("install/installexit.html");
    }


    public function step2() {
        $this->check_php_version();
        $this->smarty->display("install/install2.html");
    }


    public function step2a() {
        $this->check_php_version();
        $this->smarty->display("install/install2a.html");
    }


    public function step3() {
        if(isset($_POST['select_db_type'])) {
            $select_db_type = trim($_POST['select_db_type']);
        }
        if(!isset($_POST['select_db_type']) or trim($select_db_type) == "") {
            $this->smarty->assign("error","notalldata");
            $this->smarty->display("install/action.html","|notalldata");
            exit();
        }
        $this->check_php_version();
        $select_db_type = strip_tags(trim($select_db_type));
        $this->smarty->assign("select_db_type",$select_db_type);
        $dir = opendir('../themes');
        while($file = readdir($dir)) {
            if((!preg_match("/[.]/",$file))) {
                $themelist[] = "$file";
            }
        }
        closedir($dir);
        $this->smarty->assign("themelist",$themelist);
        $moduledir = opendir('../modules');
        while($file = readdir($moduledir)) {
            if(!preg_match("/[.]/",$file)) {
                $module = $file;
                $modulelist[] = "$module";
            }
        }
        closedir($moduledir);
        $this->smarty->assign("modulelist",$modulelist);
        $langlist = $this->get_langlist();
        $this->smarty->assign("languagelist",$langlist);
        $this->smarty->display("install/install3.html");
    }


    public function step4() {
        if(isset($_POST['site_id'])) {
            $site_id = trim($_POST['site_id']);
        }
        if(isset($_POST['site_name'])) {
            $site_name = trim($_POST['site_name']);
        }
        if(isset($_POST['site_url'])) {
            $site_url = trim($_POST['site_url']);
        }
        if(isset($_POST['mod_rewrite'])) {
            $mod_rewrite = trim($_POST['mod_rewrite']);
        }
        if(isset($_POST['admin_email'])) {
            $admin_email = trim($_POST['admin_email']);
        }
        if(isset($_POST['site_dir'])) {
            $site_dir = trim($_POST['site_dir']);
        }
        if(isset($_POST['admin_dir'])) {
            $admin_dir = trim($_POST['admin_dir']);
        }
        if(isset($_POST['smarty_debugging'])) {
            $smarty_debugging = trim($_POST['smarty_debugging']);
        }
        if(isset($_POST['adodb_debugging'])) {
            $adodb_debugging = trim($_POST['adodb_debugging']);
        }
        if(isset($_POST['gzip'])) {
            $gzip = trim($_POST['gzip']);
        }
        if(isset($_POST['smarty_caching'])) {
            $smarty_caching = trim($_POST['smarty_caching']);
        }
        if(isset($_POST['cache_lifetime'])) {
            $cache_lifetime = intval($_POST['cache_lifetime']);
        }
        if(isset($_POST['cookie_live'])) {
            $cookie_live = intval($_POST['cookie_live']);
        }
        if(isset($_POST['site_logo'])) {
            $site_logo = trim($_POST['site_logo']);
        }
        if(isset($_POST['site_charset'])) {
            $site_charset = trim($_POST['site_charset']);
        }
        if(isset($_POST['site_keywords'])) {
            $site_keywords = trim($_POST['site_keywords']);
        }
        if(isset($_POST['site_description'])) {
            $site_description = trim($_POST['site_description']);
        }
        if(isset($_POST['site_status'])) {
            $site_status = trim($_POST['site_status']);
        }
        if(isset($_POST['site_status_notes'])) {
            $site_status_notes = trim($_POST['site_status_notes']);
        }
        if(isset($_POST['site_theme'])) {
            $site_theme = trim($_POST['site_theme']);
        }
        if(isset($_POST['language'])) {
            $language = trim($_POST['language']);
        }
        if(isset($_POST['Home_Module'])) {
            $Home_Module = trim($_POST['Home_Module']);
        }
        if(isset($_POST['enter_check'])) {
            $enter_check = trim($_POST['enter_check']);
        }
        if(isset($_POST['display_admin_graphic'])) {
            $display_admin_graphic = trim($_POST['display_admin_graphic']);
        }
        if(isset($_POST['systemdk_adminrows_perpage'])) {
            $systemdk_adminrows_perpage = intval($_POST['systemdk_adminrows_perpage']);
        }
        if(isset($_POST['db_host'])) {
            $db_host = trim($_POST['db_host']);
        }
        if(isset($_POST['db_type'])) {
            $db_type = trim($_POST['db_type']);
        }
        if(isset($_POST['db_name'])) {
            $db_name = trim($_POST['db_name']);
        }
        if(isset($_POST['db_user_name'])) {
            $db_user_name = trim($_POST['db_user_name']);
        }
        if(isset($_POST['db_password'])) {
            $db_password = trim($_POST['db_password']);
        }
        if(isset($_POST['prefix'])) {
            $prefix = trim($_POST['prefix']);
        }
        if(isset($_POST['db_character'])) {
            $db_character = trim($_POST['db_character']);
        }
        if(isset($_POST['db_persistency'])) {
            $db_persistency = trim($_POST['db_persistency']);
        }
        if(isset($_POST['db_port'])) {
            $db_port = intval($_POST['db_port']);
        }
        if(isset($_POST['systemdk_mail_method'])) {
            $systemdk_mail_method = trim($_POST['systemdk_mail_method']);
        }
        if(isset($_POST['systemdk_smtp_host'])) {
            $systemdk_smtp_host = trim($_POST['systemdk_smtp_host']);
        }
        if(isset($_POST['systemdk_smtp_port'])) {
            $systemdk_smtp_port = intval($_POST['systemdk_smtp_port']);
        }
        if(isset($_POST['systemdk_smtp_user'])) {
            $systemdk_smtp_user = trim($_POST['systemdk_smtp_user']);
        }
        if(isset($_POST['systemdk_smtp_pass'])) {
            $systemdk_smtp_pass = trim($_POST['systemdk_smtp_pass']);
        }
        if(isset($_POST['systemdk_account_registration'])) {
            $systemdk_account_registration = trim($_POST['systemdk_account_registration']);
        }
        if(isset($_POST['systemdk_account_activation'])) {
            $systemdk_account_activation = trim($_POST['systemdk_account_activation']);
        }
        if(isset($_POST['systemdk_account_rules'])) {
            $systemdk_account_rules = trim($_POST['systemdk_account_rules']);
        }
        if(isset($_POST['systemdk_account_maxusers'])) {
            $systemdk_account_maxusers = intval($_POST['systemdk_account_maxusers']);
        }
        if(isset($_POST['systemdk_account_maxactivationdays'])) {
            $systemdk_account_maxactivationdays = intval($_POST['systemdk_account_maxactivationdays']);
        }
        if(isset($_POST['systemdk_statistics'])) {
            $systemdk_statistics = trim($_POST['systemdk_statistics']);
        }
        if(isset($_POST['systemdk_player_autoplay'])) {
            $systemdk_player_autoplay = trim($_POST['systemdk_player_autoplay']);
        }
        if(isset($_POST['systemdk_player_autobuffer'])) {
            $systemdk_player_autobuffer = trim($_POST['systemdk_player_autobuffer']);
        }
        if(isset($_POST['systemdk_player_controls'])) {
            $systemdk_player_controls = trim($_POST['systemdk_player_controls']);
        }
        if(isset($_POST['systemdk_player_audioautoplay'])) {
            $systemdk_player_audioautoplay = trim($_POST['systemdk_player_audioautoplay']);
        }
        if(isset($_POST['systemdk_player_audioautobuffer'])) {
            $systemdk_player_audioautobuffer = trim($_POST['systemdk_player_audioautobuffer']);
        }
        if(isset($_POST['systemdk_player_audio_controls'])) {
            $systemdk_player_audio_controls = trim($_POST['systemdk_player_audio_controls']);
        }
        if(isset($_POST['systemdk_player_ratio'])) {
            $systemdk_player_ratio = trim($_POST['systemdk_player_ratio']);
        }
        if(isset($_POST['systemdk_player_skin'])) {
            $systemdk_player_skin = trim($_POST['systemdk_player_skin']);
        }
        if(isset($_POST['systemdk_ipantispam'])) {
            $systemdk_ipantispam = trim($_POST['systemdk_ipantispam']);
        }
        if(isset($_POST['systemdk_ipantispam_num'])) {
            $systemdk_ipantispam_num = intval($_POST['systemdk_ipantispam_num']);
        }
        if(isset($_POST['systemdk_site_lang'])) {
            $systemdk_site_lang = trim($_POST['systemdk_site_lang']);
        }
        if(isset($_POST['systemdk_admin_site_lang'])) {
            $systemdk_admin_site_lang = trim($_POST['systemdk_admin_site_lang']);
        }
        if(isset($_POST['systemdk_interface_lang'])) {
            $systemdk_interface_lang = trim($_POST['systemdk_interface_lang']);
        }
        if(isset($_POST['systemdk_admin_interface_lang'])) {
            $systemdk_admin_interface_lang = trim($_POST['systemdk_admin_interface_lang']);
        }
        if((!isset($_POST['site_id']) or !isset($_POST['site_name']) or !isset($_POST['site_url']) or !isset($_POST['mod_rewrite']) or !isset($_POST['admin_email']) or !isset($_POST['site_dir']) or !isset($_POST['admin_dir']) or !isset($_POST['smarty_debugging']) or !isset($_POST['adodb_debugging']) or !isset($_POST['gzip']) or !isset($_POST['smarty_caching']) or !isset($_POST['cache_lifetime']) or !isset($_POST['cookie_live']) or !isset($_POST['site_logo']) or !isset($_POST['site_charset']) or !isset($_POST['site_keywords']) or !isset($_POST['site_description']) or !isset($_POST['site_status']) or !isset($_POST['site_status_notes']) or !isset($_POST['site_theme']) or !isset($_POST['language']) or !isset($_POST['Home_Module']) or !isset($_POST['enter_check']) or !isset($_POST['display_admin_graphic']) or !isset($_POST['systemdk_adminrows_perpage']) or !isset($_POST['db_host']) or !isset($_POST['db_type']) or !isset($_POST['db_name']) or !isset($_POST['db_user_name']) or !isset($_POST['db_password']) or !isset($_POST['prefix']) or !isset($_POST['db_character']) or !isset($_POST['db_persistency']) or !isset($_POST['db_port']) or !isset($_POST['systemdk_mail_method']) or !isset($_POST['systemdk_smtp_host']) or !isset($_POST['systemdk_smtp_port']) or !isset($_POST['systemdk_smtp_user']) or !isset($_POST['systemdk_smtp_pass']) or !isset($_POST['systemdk_account_registration']) or !isset($_POST['systemdk_account_activation']) or !isset($_POST['systemdk_account_rules']) or !isset($_POST['systemdk_account_maxusers']) or !isset($_POST['systemdk_account_maxactivationdays']) or !isset($_POST['systemdk_statistics']) or !isset($_POST['systemdk_player_autoplay']) or !isset($_POST['systemdk_player_autobuffer']) or !isset($_POST['systemdk_player_controls']) or !isset($_POST['systemdk_player_audioautoplay']) or !isset($_POST['systemdk_player_audioautobuffer']) or !isset($_POST['systemdk_player_audio_controls']) or !isset($_POST['systemdk_player_ratio']) or !isset($_POST['systemdk_player_skin']) or !isset($_POST['systemdk_ipantispam']) or !isset($_POST['systemdk_ipantispam_num']) or !isset($_POST['systemdk_site_lang']) or !isset($_POST['systemdk_admin_site_lang']) or !isset($_POST['systemdk_interface_lang']) or !isset($_POST['systemdk_admin_interface_lang']) or !isset($_POST['systemdk_install_lang'])) or ($site_id == "" or $site_name == "" or $site_url == "" or $mod_rewrite == "" or $admin_email == "" or $admin_dir == "" or $smarty_debugging == "" or $adodb_debugging == "" or $gzip == "" or $smarty_caching == "" or $site_charset == "" or $site_keywords == "" or $site_description == "" or $site_status == "" or $site_theme == "" or $language == "" or $enter_check == "" or $display_admin_graphic == "" or $db_host == "" or $db_type == "" or $db_name == "" or $db_user_name == "" or $db_password == "" or $prefix == "" or $db_persistency == "" or ($db_character == "" and $db_type == 'oci8') or ($db_character == "" and $db_type == 'mysql') or $systemdk_mail_method == "" or $systemdk_account_registration == "" or $systemdk_account_activation == "" or $systemdk_account_rules == "" or $systemdk_statistics == "" or $systemdk_player_autoplay == "" or $systemdk_player_autobuffer == "" or $systemdk_player_controls == "" or $systemdk_player_audioautoplay == "" or $systemdk_player_audioautobuffer == "" or $systemdk_player_audio_controls == "" or $systemdk_player_skin == "" or $systemdk_ipantispam == "" or $systemdk_site_lang == "" or $systemdk_admin_site_lang == "" or $systemdk_interface_lang == "" or $systemdk_admin_interface_lang == "" or count($_POST['systemdk_install_lang']) < 1)) {
            $this->smarty->assign("error","notalldata");
            $this->smarty->display("install/action.html","|notalldata");
            exit();
        }
        $this->check_php_version();
        $site_id = strip_tags($site_id);
        $site_name = strip_tags($site_name);
        $site_url = strip_tags($site_url);
        $mod_rewrite = strip_tags($mod_rewrite);
        $admin_email = strip_tags($admin_email);
        $site_dir = strip_tags($site_dir);
        $admin_dir = strip_tags($admin_dir);
        $smarty_debugging = strip_tags($smarty_debugging);
        $adodb_debugging = strip_tags($adodb_debugging);
        $gzip = strip_tags($gzip);
        $smarty_caching = strip_tags($smarty_caching);
        $site_logo = strip_tags($site_logo);
        $site_charset = strip_tags($site_charset);
        $site_keywords = strip_tags($site_keywords);
        $site_description = strip_tags($site_description);
        $site_status = strip_tags($site_status);
        $site_status_notes = strip_tags($site_status_notes);
        $site_theme = strip_tags($site_theme);
        $language = strip_tags($language);
        $Home_Module = strip_tags($Home_Module);
        $enter_check = strip_tags($enter_check);
        $display_admin_graphic = strip_tags($display_admin_graphic);
        $db_host = strip_tags($db_host);
        $db_type = strip_tags($db_type);
        $db_name = strip_tags($db_name);
        $db_user_name = strip_tags($db_user_name);
        $db_password = strip_tags($db_password);
        $prefix = strip_tags($prefix);
        $db_character = strip_tags($db_character);
        $db_persistency = strip_tags($db_persistency);
        $display_admin_graphic = strip_tags($display_admin_graphic);
        $systemdk_mail_method = strip_tags($systemdk_mail_method);
        $systemdk_smtp_host = strip_tags($systemdk_smtp_host);
        $systemdk_smtp_user = strip_tags($systemdk_smtp_user);
        $systemdk_smtp_pass = strip_tags($systemdk_smtp_pass);
        $systemdk_account_registration = strip_tags($systemdk_account_registration);
        $systemdk_account_activation = strip_tags($systemdk_account_activation);
        $systemdk_account_rules = strip_tags($systemdk_account_rules);
        $systemdk_statistics = strip_tags($systemdk_statistics);
        $systemdk_player_autoplay = strip_tags($systemdk_player_autoplay);
        $systemdk_player_autobuffer = strip_tags($systemdk_player_autobuffer);
        $systemdk_player_controls = strip_tags($systemdk_player_controls);
        $systemdk_player_audioautoplay = strip_tags($systemdk_player_audioautoplay);
        $systemdk_player_audioautobuffer = strip_tags($systemdk_player_audioautobuffer);
        $systemdk_player_audio_controls = strip_tags($systemdk_player_audio_controls);
        $systemdk_player_ratio = strip_tags($systemdk_player_ratio);
        $systemdk_player_skin = strip_tags($systemdk_player_skin);
        $systemdk_ipantispam = strip_tags($systemdk_ipantispam);
        $systemdk_site_lang = strip_tags($systemdk_site_lang);
        $systemdk_admin_site_lang = strip_tags($systemdk_admin_site_lang);
        $systemdk_interface_lang = strip_tags($systemdk_interface_lang);
        $systemdk_admin_interface_lang = strip_tags($systemdk_admin_interface_lang);
        @chmod("../includes/data",0777);
        @chmod("../includes/data/ban_ip.inc",0777);
        @chmod("../includes/data/header.inc",0777);
        @chmod("../includes/data/footer.inc",0777);
        @chmod("../themes/".$site_theme."/cache",0777);
        @chmod("../themes/".$site_theme."/templates_c",0777);
        @chmod("../themes/systemdk/cache",0777);
        @chmod("../themes/systemdk/templates_c",0777);
        @chmod("../themes/systemdk2/cache",0777);
        @chmod("../themes/systemdk2/templates_c",0777);
        @chmod("../modules/news/news_config.inc",0777);
        @chmod("../userfiles",0777);
        @chmod("../userfiles/image",0777);
        @chmod("../userfiles/news",0777);
        @chmod("../userfiles/file",0777);
        @chmod("../userfiles/flash",0777);
        @chmod("../userfiles/media",0777);
        @chmod("../userfiles/_thumbs",0777);
        @chmod("../ckeditor/Djenx.Explorer/connector/php/cache",0777);
        include_once("../adodb/adodb.inc.php");
        $adodb = ADONewConnection($db_type);
        if($adodb_debugging === 'yes') {
            $adodb->debug = true;
        }
        if($db_type === 'oci8') {
            $adodb->charSet = $db_character;
            $cnt = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=".$db_host.")(PORT=".$db_port."))(CONNECT_DATA=(SID=".$db_name.")))";
        }
        if($db_persistency === 'yes') {
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
            if($db_type === 'oci8' and $db_port != 0) {
                $db2 = $adodb->PConnect($cnt,$db_user_name,$db_password);
            } elseif($db_type === 'pdo') {
                $db2 = $adodb->PConnect('mysql:host='.$db_host.';dbname='.$db_name,$db_user_name,$db_password);
            } else {
                $db2 = $adodb->PConnect($db_host,$db_user_name,$db_password,$db_name);
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
            if($db_type === 'oci8' and $db_port != 0) {
                $db2 = $adodb->Connect($cnt,$db_user_name,$db_password);
            } elseif($db_type === 'pdo') {
                $db2 = $adodb->Connect('mysql:host='.$db_host.';dbname='.$db_name,$db_user_name,$db_password);
            } else {
                $db2 = $adodb->Connect($db_host,$db_user_name,$db_password,$db_name);
            }
        }
        if(!$db2) {
            $this->smarty->assign("error","notconnect");
            $this->smarty->display("install/action.html","|notconnect");
            exit();
        }
        if($db_type === 'mysql' or $db_type === 'pdo' or $db_type === 'mysqli' or $db_type === 'mysqlt' or $db_type === 'maxsql') {
            $adodb->Execute("set names '".$db_character."'");
        }
        if(!get_magic_quotes_gpc()) {
            $get_magic_quotes_gpc = 'off';
        } else {
            $get_magic_quotes_gpc = 'on';
        }
        if($get_magic_quotes_gpc == 'on') {
            $site_id = stripslashes($site_id);
            $smarty_caching = stripslashes($smarty_caching);
            $smarty_debugging = stripslashes($smarty_debugging);
            $adodb_debugging = stripslashes($adodb_debugging);
            $site_name = stripslashes($site_name);
            $site_url = stripslashes($site_url);
            $mod_rewrite = stripslashes($mod_rewrite);
            $admin_email = stripslashes($admin_email);
            $site_dir = stripslashes($site_dir);
            $admin_dir = stripslashes($admin_dir);
            $site_logo = stripslashes($site_logo);
            $site_charset = stripslashes($site_charset);
            $site_keywords = stripslashes($site_keywords);
            $site_description = stripslashes($site_description);
            $site_status = stripslashes($site_status);
            $site_status_notes = stripslashes($site_status_notes);
            $site_theme = stripslashes($site_theme);
            $language = stripslashes($language);
            $Home_Module = stripslashes($Home_Module);
            $gzip = stripslashes($gzip);
            $enter_check = stripslashes($enter_check);
            $db_host = stripslashes($db_host);
            $db_type = stripslashes($db_type);
            $db_name = stripslashes($db_name);
            $db_user_name = stripslashes($db_user_name);
            $db_password = stripslashes($db_password);
            $prefix = stripslashes($prefix);
            $db_character = stripslashes($db_character);
            $db_persistency = stripslashes($db_persistency);
            $systemdk_mail_method = stripslashes($systemdk_mail_method);
            $systemdk_smtp_host = stripslashes($systemdk_smtp_host);
            $systemdk_smtp_user = stripslashes($systemdk_smtp_user);
            $systemdk_smtp_pass = stripslashes($systemdk_smtp_pass);
            $systemdk_account_registration = stripslashes($systemdk_account_registration);
            $systemdk_account_activation = stripslashes($systemdk_account_activation);
            $systemdk_account_rules = stripslashes($systemdk_account_rules);
            $systemdk_statistics = stripslashes($systemdk_statistics);
            $systemdk_player_autoplay = stripslashes($systemdk_player_autoplay);
            $systemdk_player_autobuffer = stripslashes($systemdk_player_autobuffer);
            $systemdk_player_controls = stripslashes($systemdk_player_controls);
            $systemdk_player_audioautoplay = stripslashes($systemdk_player_audioautoplay);
            $systemdk_player_audioautobuffer = stripslashes($systemdk_player_audioautobuffer);
            $systemdk_player_audio_controls = stripslashes($systemdk_player_audio_controls);
            $systemdk_player_ratio = stripslashes($systemdk_player_ratio);
            $systemdk_player_skin = stripslashes($systemdk_player_skin);
            $systemdk_ipantispam = stripslashes($systemdk_ipantispam);
            $systemdk_site_lang = stripslashes($systemdk_site_lang);
            $systemdk_admin_site_lang = stripslashes($systemdk_admin_site_lang);
            $systemdk_interface_lang = stripslashes($systemdk_interface_lang);
            $systemdk_admin_interface_lang = stripslashes($systemdk_admin_interface_lang);
        }
        if($systemdk_adminrows_perpage == 0) {
            $systemdk_adminrows_perpage = 5;
        }
        if(!filter_var($admin_email,FILTER_VALIDATE_EMAIL)) {
            if($db_persistency === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","notcorrectemail");
            $this->smarty->display("install/action.html","|notcorrectemail");
            exit();
        }
        $site_charset = htmlspecialchars($site_charset,ENT_QUOTES);
        $site_id = htmlspecialchars($site_id,ENT_QUOTES);
        $smarty_caching = htmlspecialchars($smarty_caching,ENT_QUOTES);
        $smarty_debugging = htmlspecialchars($smarty_debugging,ENT_QUOTES);
        $adodb_debugging = htmlspecialchars($adodb_debugging,ENT_QUOTES);
        $site_name = htmlspecialchars($site_name,ENT_QUOTES,$site_charset);
        $site_url = htmlspecialchars($site_url,ENT_QUOTES);
        $mod_rewrite = htmlspecialchars($mod_rewrite,ENT_QUOTES);
        $admin_email = htmlspecialchars($admin_email,ENT_QUOTES);
        $site_dir = htmlspecialchars($site_dir,ENT_QUOTES);
        $admin_dir = htmlspecialchars($admin_dir,ENT_QUOTES);
        $site_logo = htmlspecialchars($site_logo,ENT_QUOTES);
        $site_keywords = htmlspecialchars($site_keywords,ENT_QUOTES,$site_charset);
        $site_description = htmlspecialchars($site_description,ENT_QUOTES,$site_charset);
        $site_status = htmlspecialchars($site_status,ENT_QUOTES);
        $site_status_notes = htmlspecialchars($site_status_notes,ENT_QUOTES,$site_charset);
        $site_theme = htmlspecialchars($site_theme,ENT_QUOTES);
        $language = htmlspecialchars($language,ENT_QUOTES);
        $Home_Module = htmlspecialchars($Home_Module,ENT_QUOTES);
        $gzip = htmlspecialchars($gzip,ENT_QUOTES);
        $enter_check = htmlspecialchars($enter_check,ENT_QUOTES);
        $db_host = htmlspecialchars($db_host,ENT_QUOTES);
        $db_type = htmlspecialchars($db_type,ENT_QUOTES);
        $db_name = htmlspecialchars($db_name,ENT_QUOTES);
        $db_user_name = htmlspecialchars($db_user_name,ENT_QUOTES);
        $db_password = htmlspecialchars($db_password,ENT_QUOTES);
        $prefix = htmlspecialchars($prefix,ENT_QUOTES);
        $db_character = htmlspecialchars($db_character,ENT_QUOTES);
        $db_persistency = htmlspecialchars($db_persistency,ENT_QUOTES);
        $systemdk_mail_method = htmlspecialchars($systemdk_mail_method,ENT_QUOTES);
        $systemdk_smtp_host = htmlspecialchars($systemdk_smtp_host,ENT_QUOTES);
        $systemdk_smtp_user = htmlspecialchars($systemdk_smtp_user,ENT_QUOTES);
        $systemdk_smtp_pass = htmlspecialchars($systemdk_smtp_pass,ENT_QUOTES);
        $systemdk_account_registration = htmlspecialchars($systemdk_account_registration,ENT_QUOTES);
        $systemdk_account_activation = htmlspecialchars($systemdk_account_activation,ENT_QUOTES);
        $systemdk_account_rules = htmlspecialchars($systemdk_account_rules,ENT_QUOTES);
        $systemdk_statistics = htmlspecialchars($systemdk_statistics,ENT_QUOTES);
        $systemdk_player_autoplay = htmlspecialchars($systemdk_player_autoplay,ENT_QUOTES);
        $systemdk_player_autobuffer = htmlspecialchars($systemdk_player_autobuffer,ENT_QUOTES);
        $systemdk_player_controls = htmlspecialchars($systemdk_player_controls,ENT_QUOTES);
        $systemdk_player_audioautoplay = htmlspecialchars($systemdk_player_audioautoplay,ENT_QUOTES);
        $systemdk_player_audioautobuffer = htmlspecialchars($systemdk_player_audioautobuffer,ENT_QUOTES);
        $systemdk_player_audio_controls = htmlspecialchars($systemdk_player_audio_controls,ENT_QUOTES);
        $systemdk_player_ratio = htmlspecialchars($systemdk_player_ratio,ENT_QUOTES);
        $systemdk_player_skin = htmlspecialchars($systemdk_player_skin,ENT_QUOTES);
        $systemdk_ipantispam = htmlspecialchars($systemdk_ipantispam,ENT_QUOTES);
        $systemdk_site_lang = htmlspecialchars($systemdk_site_lang,ENT_QUOTES);
        $systemdk_admin_site_lang = htmlspecialchars($systemdk_admin_site_lang,ENT_QUOTES);
        $systemdk_interface_lang = htmlspecialchars($systemdk_interface_lang,ENT_QUOTES);
        $systemdk_admin_interface_lang = htmlspecialchars($systemdk_admin_interface_lang,ENT_QUOTES);
        clearstatcache();
        for($i = 0,$size = count($_POST['systemdk_install_lang']);$i < $size;++$i) {
            $systemdk_install_lang = strip_tags(trim($_POST['systemdk_install_lang'][$i]));
            if($get_magic_quotes_gpc == 'on') {
                $systemdk_install_lang = stripslashes($systemdk_install_lang);
            }
            $systemdk_install_lang = substr(htmlspecialchars($systemdk_install_lang,ENT_QUOTES),0,2);
            if(file_exists($_SERVER['DOCUMENT_ROOT']."/".$site_dir."themes/".$site_theme."/languages/".$systemdk_install_lang)) {
                $install_lang[$i] = $systemdk_install_lang;
                if($install_lang[$i] == $language) {
                    $language_check = 1;
                }
            } else {
                $this->smarty->assign("notfindlangvalue",$site_dir."themes/".$site_theme."/languages/".$systemdk_install_lang);
                $this->smarty->assign("error","nolang");
                $this->smarty->display("install/action.html","|nolang");
                exit();
            }
        }
        if(!isset($language_check)) {
            $install_lang[] = $language;
        }
        for($i = 0,$size = count($install_lang);$i < $size;++$i) {
            if($db_type === 'mysqli' or $db_type === 'pdo' or $db_type === 'mysql' or $db_type === 'mysqlt' or $db_type === 'maxsql') {
                if($i == 0) {
                    $insert = $adodb->Execute("SET storage_engine=INNODB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_admins (
                        admin_id int(10) not null auto_increment,
                        admin_name varchar(25),
                        admin_surname varchar(25),
                        admin_website varchar(60),
                        admin_email varchar(60) not null,
                        admin_login varchar(25) not null,
                        admin_password varchar(40) not null,
                        admin_rights varchar(255),
                        admin_country varchar(100),
                        admin_city varchar(100),
                        admin_address varchar(255),
                        admin_telephone varchar(25),
                        admin_icq varchar(25),
                        admin_gmt varchar(3) default '+00' not null,
                        admin_notes varchar(255),
                        admin_active enum('0','1') default '1' not null,
                        admin_activenotes varchar(255),
                        admin_regdate bigint(19) not null,
                        admin_viewemail enum('0','1') default '1' not null,
                        PRIMARY KEY (admin_id),
                        UNIQUE admin_email (admin_email),
                        UNIQUE admin_login (admin_login)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_admins_id",0);
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users (
                        user_id int(10) not null auto_increment,
                        user_name varchar(25),
                        user_surname varchar(25),
                        user_website varchar(60),
                        user_email varchar(60) not null,
                        user_login varchar(25) not null,
                        user_password varchar(40) not null,
                        user_rights varchar(255),
                        user_country varchar(100),
                        user_city varchar(100),
                        user_address varchar(255),
                        user_telephone varchar(25),
                        user_icq varchar(25),
                        user_gmt varchar(3) default '+00' not null,
                        user_notes varchar(255),
                        user_active enum('0','1') default '1' not null,
                        user_activenotes varchar(255),
                        user_regdate bigint(19) not null,
                        user_viewemail enum('0','1') default '1' not null,
                        user_activatekey varchar(32),
                        user_newpassword varchar(32),
                        PRIMARY KEY (user_id),
                        UNIQUE user_login (user_login),
                        UNIQUE user_email (user_email)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_users_id",0);
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users_temp (
                        user_id int(10) not null auto_increment,
                        user_name varchar(25),
                        user_surname varchar(25),
                        user_website varchar(60),
                        user_email varchar(60) not null,
                        user_login varchar(25) not null,
                        user_password varchar(40) not null,
                        user_rights varchar(255),
                        user_country varchar(100),
                        user_city varchar(100),
                        user_address varchar(255),
                        user_telephone varchar(25),
                        user_icq varchar(25),
                        user_gmt varchar(3) default '+00' not null,
                        user_notes varchar(255),
                        user_regdate bigint(19) not null,
                        user_viewemail enum('0','1') default '1' not null,
                        user_checknum varchar(50) not null,
                        PRIMARY KEY (user_id),
                        UNIQUE user_login (user_login),
                        UNIQUE user_email (user_email)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_users_temp_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_modules_".$install_lang[$i]." (
                    module_id int(10) not null auto_increment,
                    module_name varchar(25) not null,
                    module_customname varchar(25) not null,
                    module_version varchar(255),
                    module_status enum('0','1') default '0' not null,
                    module_valueview enum('0','1','2','3') default '0' not null,
                    PRIMARY KEY (module_id),
                    UNIQUE module_name (module_name),
                    UNIQUE module_customname (module_customname)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_modules_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_modules_".$install_lang[$i]." VALUES
                    (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'news', 'News', NULL, '1', '0'),
                    (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'main_pages', 'Main Pages', NULL, '1', '0');
                    ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_blocks_".$install_lang[$i]." (
                    block_id int(10) not null auto_increment,
                    block_name varchar(25),
                    block_customname varchar(25) not null,
                    block_version varchar(255),
                    block_status enum('0','1') default '0' not null,
                    block_valueview enum('0','1','2','3') default '0' not null,
                    block_content mediumtext,
                    block_url varchar(255),
                    block_arrangements varchar(11) not null,
                    block_priority int(3) default '1' not null,
                    block_refresh bigint(19),
                    block_lastrefresh bigint(19),
                    block_termexpire bigint(19),
                    block_termexpireaction varchar(10),
                    PRIMARY KEY (block_id),
                    UNIQUE block_customname (block_customname)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_blocks_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_blocks_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_blocks_id_".$install_lang[$i]).", 'menu', 'Navigation', NULL, '1', '0', NULL, NULL, 'left', 1, NULL, NULL, NULL, NULL);");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                //shop start
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_catalogs_".$install_lang[$i]." (
                    catalog_id int(10) not null auto_increment,
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
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_catalogs_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_categories_".$install_lang[$i]." (
                    category_id int(10) not null auto_increment,
                    category_img varchar(255),
                    category_name varchar(255) not null,
                    cat_catalog_id int(10) not null,
                    category_description varchar(255),
                    category_position int(4) not null default '0',
                    category_show enum('0','1') not null default '1',
                    category_date bigint(19) not null,
                    category_meta_title varchar(255),
                    category_meta_keywords varchar(255),
                    category_meta_description varchar(255),
                    PRIMARY KEY (category_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_categories_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_subcategories_".$install_lang[$i]." (
                    subcategory_id int(10) not null auto_increment,
                    subcategory_img varchar(255),
                    subcategory_name varchar(255) not null,
                    subcategory_cat_id int(10) not null,
                    subcategory_description varchar(255),
                    subcategory_position int(4) not null default '0',
                    subcategory_show enum('0','1') not null default '1',
                    subcategory_date bigint(19) not null,
                    subcategory_meta_title varchar(255),
                    subcategory_meta_keywords varchar(255),
                    subcategory_meta_description varchar(255),
                    PRIMARY KEY (subcategory_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_subcategories_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_items_".$install_lang[$i]." (
                    item_id int(10) not null auto_increment,
                    item_img varchar(255),
                    item_name varchar(255) not null,
                    item_price float(13,2) not null default '0.00',
                    item_price_discounted float(13,2),
                    item_catalog_id int(10),
                    item_category_id int(10),
                    item_subcategory_id int(10),
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
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_items_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_delivery_".$install_lang[$i]." (
                    delivery_id int(10) not null auto_increment,
                    delivery_name varchar(255) not null,
                    delivery_price float(13,2) not null default '0.00',
                    delivery_status enum('0','1') not null default '1',
                    PRIMARY KEY (delivery_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_delivery_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_delivery_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_delivery_id_".$install_lang[$i]).", 'personal pickup', '0.00', '1');");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_pay_".$install_lang[$i]." (
                    pay_id int(10) not null auto_increment,
                    pay_name varchar(255) not null,
                    pay_price float(13,2) not null default '0.00',
                    pay_status enum('0','1') not null default '1',
                    PRIMARY KEY (pay_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_pay_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_pay_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_pay_id_".$install_lang[$i]).", 'cash', '0.00', '1');");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_orders_".$install_lang[$i]." (
                    order_id int(10) not null auto_increment,
                    order_customer_id int(10),
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
                    ship_postalcode varchar(255),
                    ship_country varchar(255),
                    ship_telephone varchar(255) not null,
                    ship_email varchar(255) not null,
                    ship_addinfo varchar(255),
                    order_delivery int(10) not null,
                    order_pay int(10) not null,
                    order_comments varchar(255),
                    order_ip varchar(39) not null,
                    PRIMARY KEY (order_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_orders_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_order_items_".$install_lang[$i]." (
                    order_item_id int(10) not null auto_increment,
                    order_id int(10) not null,
                    item_id int(10) not null,
                    order_item_name varchar(255) not null,
                    order_item_price float(13,2) not null default '0.00',
                    order_item_quantity int(3) not null,
                    PRIMARY KEY (order_item_id),
                    UNIQUE KEY `unique` (order_id,item_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_order_items_id_".$install_lang[$i],0);
                //shop end
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_guestbook (
                        post_id int(10) not null auto_increment,
                        post_user_name varchar(255) not null,
                        post_user_country varchar(100) default null,
                        post_user_city varchar(100) default null,
                        post_user_telephone varchar(25) default null,
                        post_show_telephone enum('0','1') not null default '1',
                        post_user_email varchar(60) not null,
                        post_show_email enum('0','1') not null default '1',
                        post_user_id int(10) default null,
                        post_user_ip varchar(20) not null,
                        post_date bigint(19) not null,
                        post_text mediumtext not null,
                        post_status enum('0','1','2') not null default '0',
                        post_unswer mediumtext,
                        post_unswer_date bigint(19) default null,
                        PRIMARY KEY (post_id)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_guestbook_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_receivers_".$install_lang[$i]." (
                    receiver_id int(10) not null auto_increment,
                    receiver_name varchar(25) not null,
                    receiver_email varchar(60) not null,
                    receiver_active enum('0','1') default'1' not null,
                    PRIMARY KEY (receiver_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_mail_receivers_id_".$install_lang[$i],0);
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_sendlog (
                        mail_id int(10) not null auto_increment,
                        mail_user varchar(25),
                        mail_date bigint(19) not null,
                        mail_module varchar(25) not null,
                        mail_ip varchar(20) not null,
                        PRIMARY KEY (mail_id),
                        KEY mail_date (mail_date),
                        KEY mail_module (mail_module),
                        KEY mail_ip (mail_ip)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_mail_sendlog_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_menu_".$install_lang[$i]." (
                    mainmenu_id int(10) not null auto_increment,
                    mainmenu_type varchar(10) not null,
                    mainmenu_name varchar(25) not null,
                    mainmenu_module varchar(255),
                    mainmenu_submodule varchar(255),
                    mainmenu_target varchar(20) default '_parent' not null,
                    mainmenu_priority int(3) not null,
                    mainmenu_inmenu enum('0','1') default '1' not null,
                    PRIMARY KEY (mainmenu_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_main_menu_id_".$install_lang[$i],0);
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_session (
                        session_login varchar(25),
                        session_time bigint(19) not null,
                        session_hostaddress varchar(20) not null,
                        session_whoonline varchar(20) not null,
                        KEY session_login (session_login)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_system_pages (
                        systempage_id int(10) not null auto_increment,
                        systempage_author varchar(25) not null,
                        systempage_name varchar(255) not null,
                        systempage_title varchar(255) not null,
                        systempage_date bigint(19) not null,
                        systempage_content mediumtext not null,
                        systempage_lang varchar(2),
                        PRIMARY KEY (systempage_id)
                      ) ENGINE=InnoDB");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_system_pages_id",0);
                    $insert = $adodb->Execute("INSERT INTO ".$prefix."_system_pages VALUES (".$adodb->GenID($prefix."_system_pages_id").", 'admin', 'account_rules', 'Общие правила', '".time()."', 'Заходя на сайт, Вы подтверждаете своё согласие со следующими условиями. Если Вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь нашим сайтом. Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить Вас об этом, однако с Вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование нашего сайта после обновления/исправления условий означает Ваше согласие с ними.<br><br>Наши сайты работают под управлением программного обеспечения SystemDK для создания веб-сайтов. Скачать его можно по адресу www.systemsdk.com. За дополнительной информацией о SystemsDK обращайтесь по адресу http://www.systemsdk.com.<br><br>Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы Вашей страны, страны, которая предоставляет услуги хостинга для этого сайта, или международное право. Попытки размещения таких сообщений могут привести к Вашему немедленному отключению от сайта, при этом Ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы сайта имеют право удалить, отредактировать, перенести или закрыть Вам доступ в любое время по своему усмотрению. Как пользователь Вы согласны с тем, что введённая Вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без Вашего разрешения, ни администрация сайта, ни авторы программного обеспечения SystemDK не могут быть ответственными за действия хакеров, которые могут привести к несанкционированному доступу к ней.', 'ru'), (".$adodb->GenID($prefix."_system_pages_id").", 'admin', 'account_rules', 'General rules', '".time()."', 'By accessing our web-site, you agree to be legally bound by the following terms. If you do not agree to be legally bound by all of the following terms then please do not access and/or use our web-site. We may change these at any time and we’ll do our utmost in informing you, though it would be prudent to review this regularly yourself as your continued usage of our web-site after changes mean you agree to be legally bound by these terms as they are updated and/or amended.<br><br> Our web-sites are powered by SystemDK which can be downloaded from www.systemsdk.com. For further information about SystemDK, please see: http://www.systemsdk.com.<br><br>You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any laws be it of your country, the country where this web-site is hosted or International Law. Doing so may lead to you being immediately and permanently banned, with notification of your Internet Service Provider if deemed required by us. The IP address are recorded to aid in enforcing these conditions. You agree that web-site administrators have the right to remove, edit, move or close access for You at any time should we see fit. As a user you agree to any information you have entered to being stored in a database. While this information will not be disclosed to any third party without your consent, neither web-site administration nor SystemDK developers shall be held responsible for any hacking attempt that may lead to the data being compromised.', 'en');");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_messages_".$install_lang[$i]." (
                    message_id int(10) not null auto_increment,
                    message_author varchar(25) not null,
                    message_title varchar(100) not null,
                    message_content mediumtext not null,
                    message_notes mediumtext,
                    message_date bigint(19) not null,
                    message_termexpire bigint(19),
                    message_termexpireaction varchar(10),
                    message_status enum('0','1') default '1' not null,
                    message_valueview enum('0','1','2','3') default '0' not null,
                    PRIMARY KEY (message_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_messages_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_rss_".$install_lang[$i]." (
                    rss_id int(10) not null auto_increment,
                    rss_sitename varchar(30) not null,
                    rss_siteurl varchar(255) not null,
                    PRIMARY KEY (rss_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_rss_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_pages_".$install_lang[$i]." (
                    mainpage_id int(10) not null auto_increment,
                    mainpage_author varchar(25) not null,
                    mainpage_title varchar(255) not null,
                    mainpage_date bigint(19) not null,
                    mainpage_content mediumtext not null,
                    mainpage_readcounter int(10) default '0' not null,
                    mainpage_notes mediumtext,
                    mainpage_valueview enum('0','1','2','3') default '0' not null,
                    mainpage_status enum('0','1') default '0' not null,
                    mainpage_termexpire bigint(19),
                    mainpage_termexpireaction varchar(10),
                    PRIMARY KEY (mainpage_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_main_pages_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_".$install_lang[$i]." (
                    news_id int(10) not null auto_increment,
                    news_author varchar(25) not null,
                    news_category_id int(10) not null,
                    news_title varchar(255) not null,
                    news_date bigint(19) not null,
                    news_images varchar(255),
                    news_short_text mediumtext not null,
                    news_content mediumtext not null,
                    news_readcounter int(10) default '0' not null,
                    news_notes mediumtext,
                    news_valueview enum('0','1','2','3') default '0' not null,
                    news_status enum('0','1') default '0' not null,
                    news_termexpire bigint(19),
                    news_termexpireaction varchar(10),
                    news_onhome enum('0','1') default '0' not null,
                    news_link varchar(255),
                    PRIMARY KEY (news_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_auto_".$install_lang[$i]." (
                    news_id int(10) not null auto_increment,
                    news_author varchar(25) not null,
                    news_category_id int(10) not null,
                    news_title varchar(255) not null,
                    news_date bigint(19) not null,
                    news_images varchar(255),
                    news_short_text mediumtext not null,
                    news_content mediumtext not null,
                    news_readcounter int(10) default '0' not null,
                    news_notes mediumtext,
                    news_valueview enum('0','1','2','3') default '0' not null,
                    news_status enum('0','1') default '0' not null,
                    news_termexpire bigint(19),
                    news_termexpireaction varchar(10),
                    news_onhome enum('0','1') default '0' not null,
                    news_link varchar(255),
                    PRIMARY KEY (news_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_auto_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_categories_".$install_lang[$i]." (
                    news_category_id int(10) not null auto_increment,
                    news_category_name varchar(255) not null,
                    PRIMARY KEY (news_category_id)
                  ) ENGINE=InnoDB");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_categories_id_".$install_lang[$i],0);
            } elseif($db_type === 'oci8') {
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_admins (
                        admin_id NUMBER(10,0) NOT NULL,
                        admin_name VARCHAR2(25 CHAR),
                        admin_surname VARCHAR2(25 CHAR),
                        admin_website VARCHAR2(60 CHAR),
                        admin_email VARCHAR2(60 CHAR) NOT NULL,
                        admin_login VARCHAR2(25 CHAR) NOT NULL,
                        admin_password VARCHAR2(40 CHAR) NOT NULL,
                        admin_rights VARCHAR2(255 CHAR),
                        admin_country VARCHAR2(100 CHAR),
                        admin_city VARCHAR2(100 CHAR),
                        admin_address VARCHAR2(255 CHAR),
                        admin_telephone VARCHAR2(25 CHAR),
                        admin_icq VARCHAR2(25 CHAR),
                        admin_gmt VARCHAR2(3 CHAR) DEFAULT +00 NOT NULL,
                        admin_notes VARCHAR2(255 CHAR),
                        admin_active NUMBER(1,0) DEFAULT 1 NOT NULL,
                        admin_activenotes VARCHAR2(255 CHAR),
                        admin_regdate NUMBER(19,0) NOT NULL,
                        admin_viewemail NUMBER(1,0) DEFAULT 1 NOT NULL
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_admins ADD (
                        PRIMARY KEY(admin_id),
                        UNIQUE (admin_login, admin_email)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_admins_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users (
                        user_id NUMBER(10,0) NOT NULL,
                        user_name VARCHAR2(25 CHAR),
                        user_surname VARCHAR2(25 CHAR),
                        user_website VARCHAR2(60 CHAR),
                        user_email VARCHAR2(60 CHAR) NOT NULL,
                        user_login VARCHAR2(25 CHAR) NOT NULL,
                        user_password VARCHAR2(40 CHAR) NOT NULL,
                        user_rights VARCHAR2(255 CHAR),
                        user_country VARCHAR2(100 CHAR),
                        user_city VARCHAR2(100 CHAR),
                        user_address VARCHAR2(255 CHAR),
                        user_telephone VARCHAR2(25 CHAR),
                        user_icq VARCHAR2(25 CHAR),
                        user_gmt VARCHAR2(3 CHAR) DEFAULT +00 NOT NULL,
                        user_notes VARCHAR2(255 CHAR),
                        user_active NUMBER(1,0) DEFAULT 1 NOT NULL,
                        user_activenotes VARCHAR2(255 CHAR),
                        user_regdate NUMBER(19,0) NOT NULL,
                        user_viewemail NUMBER(1,0) DEFAULT 1 NOT NULL,
                        user_activatekey  VARCHAR2(32 CHAR),
                        user_newpassword  VARCHAR2(32 CHAR)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_users ADD (
                        PRIMARY KEY(user_id),
                        UNIQUE (user_login, user_email)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_users_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users_temp (
                        user_id NUMBER(10,0) NOT NULL,
                        user_name VARCHAR2(25 CHAR),
                        user_surname VARCHAR2(25 CHAR),
                        user_website VARCHAR2(60 CHAR),
                        user_email VARCHAR2(60 CHAR) NOT NULL,
                        user_login VARCHAR2(25 CHAR) NOT NULL,
                        user_password VARCHAR2(40 CHAR) NOT NULL,
                        user_rights VARCHAR2(255 CHAR),
                        user_country VARCHAR2(100 CHAR),
                        user_city VARCHAR2(100 CHAR),
                        user_address VARCHAR2(255 CHAR),
                        user_telephone VARCHAR2(25 CHAR),
                        user_icq VARCHAR2(25 CHAR),
                        user_gmt VARCHAR2(3 CHAR) DEFAULT +00 NOT NULL,
                        user_notes VARCHAR2(255 CHAR),
                        user_regdate NUMBER(19,0) NOT NULL,
                        user_viewemail NUMBER(1,0) DEFAULT 1 NOT NULL,
                        user_checknum  VARCHAR2(50 CHAR) NOT NULL
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_users_temp ADD (
                        PRIMARY KEY(user_id),
                        UNIQUE (user_login, user_email)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_users_temp_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_modules_".$install_lang[$i]." (
                    module_id NUMBER(10,0) NOT NULL,
                    module_name VARCHAR2(25 CHAR) NOT NULL,
                    module_customname VARCHAR2(25 CHAR) NOT NULL,
                    module_version VARCHAR2(255 CHAR),
                    module_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    module_valueview NUMBER(1,0) DEFAULT 0 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_modules_".$install_lang[$i]." ADD (
                    PRIMARY KEY(module_id),
                    UNIQUE (module_name, module_customname)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_modules_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_modules_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'news', 'News', NULL, '1', '0')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_modules_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'main_pages', 'Main Pages', NULL, '1', '0')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_blocks_".$install_lang[$i]." (
                    block_id NUMBER(10,0) NOT NULL,
                    block_name VARCHAR2(25 CHAR),
                    block_customname VARCHAR2(25 CHAR) NOT NULL,
                    block_version VARCHAR2(255 CHAR),
                    block_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    block_valueview NUMBER(1,0) DEFAULT 0 NOT NULL,
                    block_content CLOB,
                    block_url VARCHAR2(255 CHAR),
                    block_arrangements VARCHAR2(11 CHAR) NOT NULL,
                    block_priority NUMBER(3,0) DEFAULT 1 NOT NULL,
                    block_refresh NUMBER(19,0),
                    block_lastrefresh NUMBER(19,0),
                    block_termexpire NUMBER(19,0),
                    block_termexpireaction VARCHAR2(10 CHAR)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_blocks_".$install_lang[$i]." ADD (
                    PRIMARY KEY(block_id),
                    UNIQUE (block_customname)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_blocks_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_blocks_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_blocks_id_".$install_lang[$i]).", 'menu', 'Navigation', NULL, '1', '0', NULL, NULL, 'left', 1, NULL, NULL, NULL, NULL)");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                //shop start
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_catalogs_".$install_lang[$i]." (
                    catalog_id NUMBER(10,0) NOT NULL,
                    catalog_img VARCHAR2(255),
                    catalog_name VARCHAR2(255) NOT NULL,
                    catalog_description VARCHAR2(255),
                    catalog_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    catalog_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    catalog_date NUMBER(19,0) NOT NULL,
                    catalog_meta_title VARCHAR2(255),
                    catalog_meta_keywords VARCHAR2(255),
                    catalog_meta_description VARCHAR2(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_catalogs_".$install_lang[$i]." ADD (PRIMARY KEY(catalog_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_catalogs_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_categories_".$install_lang[$i]." (
                    category_id NUMBER(10,0) NOT NULL,
                    category_img VARCHAR2(255),
                    category_name VARCHAR2(255) NOT NULL,
                    cat_catalog_id NUMBER(10,0) NOT NULL,
                    category_description VARCHAR2(255),
                    category_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    category_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    category_date NUMBER(19,0) NOT NULL,
                    category_meta_title VARCHAR2(255),
                    category_meta_keywords VARCHAR2(255),
                    category_meta_description VARCHAR2(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_categories_".$install_lang[$i]." ADD (PRIMARY KEY(category_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_categories_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_subcategories_".$install_lang[$i]." (
                    subcategory_id NUMBER(10,0) NOT NULL,
                    subcategory_img VARCHAR2(255),
                    subcategory_name VARCHAR2(255) NOT NULL,
                    subcategory_cat_id NUMBER(10,0) NOT NULL,
                    subcategory_description VARCHAR2(255),
                    subcategory_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    subcategory_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    subcategory_date NUMBER(19,0) NOT NULL,
                    subcategory_meta_title VARCHAR2(255),
                    subcategory_meta_keywords VARCHAR2(255),
                    subcategory_meta_description VARCHAR2(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_subcategories_".$install_lang[$i]." ADD (PRIMARY KEY(subcategory_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_subcategories_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_items_".$install_lang[$i]." (
                    item_id NUMBER(10,0) NOT NULL,
                    item_img VARCHAR2(255),
                    item_name VARCHAR2(255) NOT NULL,
                    item_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    item_price_discounted NUMBER(15,2),
                    item_catalog_id NUMBER(10,0),
                    item_category_id NUMBER(10,0),
                    item_subcategory_id NUMBER(10,0),
                    item_short_description CLOB NOT NULL,
                    item_description CLOB NOT NULL,
                    item_position NUMBER(4,0) DEFAULT 0 NOT NULL,
                    item_show NUMBER(1,0) DEFAULT 1 NOT NULL,
                    item_quantity NUMBER(10,0),
                    item_quantity_unlim NUMBER(1,0) DEFAULT 0 NOT NULL,
                    item_quantity_param NUMBER(1,0),
                    item_display NUMBER(1,0),
                    item_date NUMBER(19,0) NOT NULL,
                    item_meta_title VARCHAR2(255),
                    item_meta_keywords VARCHAR2(255),
                    item_meta_description VARCHAR2(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_items_".$install_lang[$i]." ADD (PRIMARY KEY(item_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_items_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_delivery_".$install_lang[$i]." (
                    delivery_id NUMBER(10,0) NOT NULL,
                    delivery_name VARCHAR2(255) NOT NULL,
                    delivery_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    delivery_status NUMBER(1,0) DEFAULT 1 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_delivery_".$install_lang[$i]." ADD (PRIMARY KEY(delivery_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_delivery_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_delivery_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_delivery_id_".$install_lang[$i]).", 'personal pickup', '0.00', '1')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_pay_".$install_lang[$i]." (
                    pay_id NUMBER(10,0) NOT NULL,
                    pay_name VARCHAR2(255) NOT NULL,
                    pay_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    pay_status NUMBER(1,0) DEFAULT 1 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_pay_".$install_lang[$i]." ADD (PRIMARY KEY(pay_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_pay_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_pay_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_pay_id_".$install_lang[$i]).", 'cash', '0.00', '1')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_orders_".$install_lang[$i]." (
                    order_id NUMBER(10,0) NOT NULL,
                    order_customer_id NUMBER(10,0),
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
                    ship_postalcode VARCHAR2(255),
                    ship_country VARCHAR2(255),
                    ship_telephone VARCHAR2(255) NOT NULL,
                    ship_email VARCHAR2(255) NOT NULL,
                    ship_addinfo VARCHAR2(255),
                    order_delivery NUMBER(10,0) NOT NULL,
                    order_pay NUMBER(10,0) NOT NULL,
                    order_comments VARCHAR2(255),
                    order_ip VARCHAR2(39) NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_orders_".$install_lang[$i]." ADD (PRIMARY KEY(order_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_orders_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_order_items_".$install_lang[$i]." (
                    order_item_id NUMBER(10,0) NOT NULL,
                    order_id NUMBER(10,0) NOT NULL,
                    item_id NUMBER(10,0) NOT NULL,
                    order_item_name VARCHAR2(255) NOT NULL,
                    order_item_price NUMBER(15,2) DEFAULT 0.00 NOT NULL,
                    order_item_quantity NUMBER(3,0) NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_order_items_".$install_lang[$i]." ADD (
                    PRIMARY KEY(order_item_id),
                    UNIQUE (order_id,item_id)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_order_items_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                //shop end
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_guestbook (
                        post_id NUMBER(10,0) NOT NULL,
                        post_user_name VARCHAR2(255 CHAR) NOT NULL,
                        post_user_country VARCHAR2(100 CHAR),
                        post_user_city VARCHAR2(100 CHAR),
                        post_user_telephone VARCHAR2(25 CHAR),
                        post_show_telephone NUMBER(1,0) DEFAULT 1 NOT NULL,
                        post_user_email VARCHAR2(60 CHAR) NOT NULL,
                        post_show_email NUMBER(1,0) DEFAULT 1 NOT NULL,
                        post_user_id NUMBER(10,0),
                        post_user_ip VARCHAR2(20 CHAR) NOT NULL,
                        post_date NUMBER(19,0) NOT NULL,
                        post_text CLOB NOT NULL,
                        post_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                        post_unswer CLOB,
                        post_unswer_date NUMBER(19,0)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_guestbook ADD (PRIMARY KEY(post_id))");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_guestbook_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_receivers_".$install_lang[$i]." (
                    receiver_id NUMBER(10,0) NOT NULL,
                    receiver_name VARCHAR2(25 CHAR) NOT NULL,
                    receiver_email VARCHAR2(60 CHAR) NOT NULL,
                    receiver_active NUMBER(1,0) DEFAULT 1 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_mail_receivers_".$install_lang[$i]." ADD (PRIMARY KEY(receiver_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_mail_receivers_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_sendlog (
                        mail_id NUMBER(10,0) NOT NULL,
                        mail_user VARCHAR2(25 CHAR),
                        mail_date NUMBER(19,0) NOT NULL,
                        mail_module VARCHAR2(25 CHAR) NOT NULL,
                        mail_ip VARCHAR2(20 CHAR) NOT NULL
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_mail_sendlog ADD (PRIMARY KEY(mail_id))");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog1 ON ".$prefix."_mail_sendlog (mail_date)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog2 ON ".$prefix."_mail_sendlog (mail_module)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog3 ON ".$prefix."_mail_sendlog (mail_ip)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_mail_sendlog_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_menu_".$install_lang[$i]." (
                    mainmenu_id NUMBER(10,0) NOT NULL,
                    mainmenu_type VARCHAR2(10 CHAR) NOT NULL,
                    mainmenu_name VARCHAR2(25 CHAR) NOT NULL,
                    mainmenu_module VARCHAR2(255 CHAR),
                    mainmenu_submodule VARCHAR2(255 CHAR),
                    mainmenu_target VARCHAR2(20 CHAR) DEFAULT '_parent' NOT NULL,
                    mainmenu_priority NUMBER(3,0) NOT NULL,
                    mainmenu_inmenu NUMBER(1,0) DEFAULT 1 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_main_menu_".$install_lang[$i]." ADD (PRIMARY KEY(mainmenu_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_main_menu_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_session (
                        session_login VARCHAR2(25 CHAR),
                        session_time  NUMBER(19,0) NOT NULL,
                        session_hostaddress VARCHAR2(20 CHAR) NOT NULL,
                        session_whoonline VARCHAR2(20 CHAR) NOT NULL,
                        mainmenu_submodule VARCHAR2(255 CHAR)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_session_login ON ".$prefix."_session (session_login)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_system_pages (
                        systempage_id NUMBER(10,0) NOT NULL,
                        systempage_author VARCHAR2(25 CHAR) NOT NULL,
                        systempage_name VARCHAR2(255 CHAR) NOT NULL,
                        systempage_title VARCHAR2(255 CHAR) NOT NULL,
                        systempage_date NUMBER(19,0) NOT NULL,
                        systempage_content CLOB NOT NULL,
                        systempage_lang VARCHAR2(2 CHAR)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("ALTER TABLE ".$prefix."_system_pages ADD (PRIMARY KEY(systempage_id))");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_system_pages_id
                        START WITH 1
                        INCREMENT BY 1
                        MINVALUE 1
                        NOCACHE
                        NOCYCLE
                        NOORDER
                      ");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->StartTrans();
                    $systempage_id = $adodb->GenID($prefix."_system_pages_id");
                    $insert = $adodb->Execute("INSERT INTO ".$prefix."_system_pages VALUES (".$systempage_id.", 'admin', 'account_rules', 'Общие правила', '".time()."', empty_clob(), 'ru')");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->UpdateClob($prefix.'_system_pages','systempage_content','Заходя на сайт, Вы подтверждаете своё согласие со следующими условиями. Если Вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь нашим сайтом. Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить Вас об этом, однако с Вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование нашего сайта после обновления/исправления условий означает Ваше согласие с ними.<br><br>Наши сайты работают под управлением программного обеспечения SystemDK для создания веб-сайтов. Скачать его можно по адресу www.systemsdk.com. За дополнительной информацией о SystemsDK обращайтесь по адресу http://www.systemsdk.com.<br><br>Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы Вашей страны, страны, которая предоставляет услуги хостинга для этого сайта, или международное право. Попытки размещения таких сообщений могут привести к Вашему немедленному отключению от сайта, при этом Ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы сайта имеют право удалить, отредактировать, перенести или закрыть Вам доступ в любое время по своему усмотрению. Как пользователь Вы согласны с тем, что введённая Вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без Вашего разрешения, ни администрация сайта, ни авторы программного обеспечения SystemDK не могут быть ответственными за действия хакеров, которые могут привести к несанкционированному доступу к ней.','systempage_id='.$systempage_id);
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $systempage_id = $adodb->GenID($prefix."_system_pages_id");
                    $insert = $adodb->Execute("INSERT INTO ".$prefix."_system_pages VALUES (".$systempage_id.", 'admin', 'account_rules', 'General rules', '".time()."', empty_clob(), 'en')");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->UpdateClob($prefix.'_system_pages','systempage_content','By accessing our web-site, you agree to be legally bound by the following terms. If you do not agree to be legally bound by all of the following terms then please do not access and/or use our web-site. We may change these at any time and we’ll do our utmost in informing you, though it would be prudent to review this regularly yourself as your continued usage of our web-site after changes mean you agree to be legally bound by these terms as they are updated and/or amended.<br><br> Our web-sites are powered by SystemDK which can be downloaded from www.systemsdk.com. For further information about SystemDK, please see: http://www.systemsdk.com.<br><br>You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any laws be it of your country, the country where this web-site is hosted or International Law. Doing so may lead to you being immediately and permanently banned, with notification of your Internet Service Provider if deemed required by us. The IP address are recorded to aid in enforcing these conditions. You agree that web-site administrators have the right to remove, edit, move or close access for You at any time should we see fit. As a user you agree to any information you have entered to being stored in a database. While this information will not be disclosed to any third party without your consent, neither web-site administration nor SystemDK developers shall be held responsible for any hacking attempt that may lead to the data being compromised.','systempage_id='.$systempage_id);
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->CompleteTrans();
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_messages_".$install_lang[$i]." (
                    message_id NUMBER(10,0) NOT NULL,
                    message_author VARCHAR2(25 CHAR) NOT NULL,
                    message_title VARCHAR2(100 CHAR) NOT NULL,
                    message_content CLOB NOT NULL,
                    message_notes CLOB,
                    message_date NUMBER(19,0) NOT NULL,
                    message_termexpire NUMBER(19,0),
                    message_termexpireaction VARCHAR2(10 CHAR),
                    message_status NUMBER(1,0) DEFAULT 1 NOT NULL,
                    message_valueview NUMBER(1,0) DEFAULT 0 NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_messages_".$install_lang[$i]." ADD (PRIMARY KEY(message_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_messages_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_rss_".$install_lang[$i]." (
                    rss_id NUMBER(10,0) NOT NULL,
                    rss_sitename VARCHAR2(30 CHAR) NOT NULL,
                    rss_siteurl VARCHAR2(255 CHAR) NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_rss_".$install_lang[$i]." ADD (PRIMARY KEY(rss_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_rss_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_pages_".$install_lang[$i]." (
                    mainpage_id NUMBER(10,0) NOT NULL,
                    mainpage_author VARCHAR2(25 CHAR) NOT NULL,
                    mainpage_title VARCHAR2(255 CHAR) NOT NULL,
                    mainpage_date NUMBER(19,0) NOT NULL,
                    mainpage_content CLOB NOT NULL,
                    mainpage_readcounter NUMBER(10,0) DEFAULT 0 NOT NULL,
                    mainpage_notes CLOB,
                    mainpage_valueview NUMBER(1,0) DEFAULT 0 NOT NULL,
                    mainpage_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    mainpage_termexpire NUMBER(19,0),
                    mainpage_termexpireaction VARCHAR2(10 CHAR)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_main_pages_".$install_lang[$i]." ADD (PRIMARY KEY(mainpage_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_main_pages_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_".$install_lang[$i]." (
                    news_id NUMBER(10,0) NOT NULL,
                    news_author VARCHAR2(25 CHAR) NOT NULL,
                    news_category_id NUMBER(10,0) NOT NULL,
                    news_title VARCHAR2(255 CHAR) NOT NULL,
                    news_date NUMBER(19,0) NOT NULL,
                    news_images VARCHAR2(255 CHAR),
                    news_short_text CLOB NOT NULL,
                    news_content CLOB NOT NULL,
                    news_readcounter NUMBER(10,0) DEFAULT 0 NOT NULL,
                    news_notes CLOB,
                    news_valueview NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_termexpire NUMBER(19,0),
                    news_termexpireaction VARCHAR2(10 CHAR),
                    news_onhome NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_link VARCHAR2(255 CHAR)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_news_".$install_lang[$i]." ADD (PRIMARY KEY(news_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_news_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_auto_".$install_lang[$i]." (
                    news_id NUMBER(10,0) NOT NULL,
                    news_author VARCHAR2(25 CHAR) NOT NULL,
                    news_category_id NUMBER(10,0) NOT NULL,
                    news_title VARCHAR2(255 CHAR) NOT NULL,
                    news_date NUMBER(19,0) NOT NULL,
                    news_images VARCHAR2(255 CHAR),
                    news_short_text CLOB NOT NULL,
                    news_content CLOB NOT NULL,
                    news_readcounter NUMBER(10,0) DEFAULT 0 NOT NULL,
                    news_notes CLOB,
                    news_valueview NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_status NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_termexpire NUMBER(19,0),
                    news_termexpireaction VARCHAR2(10 CHAR),
                    news_onhome NUMBER(1,0) DEFAULT 0 NOT NULL,
                    news_link VARCHAR2(255 CHAR)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_news_auto_".$install_lang[$i]." ADD (PRIMARY KEY(news_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_news_auto_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_categories_".$install_lang[$i]." (
                    news_category_id NUMBER(10,0) NOT NULL,
                    news_category_name VARCHAR2(255 CHAR) NOT NULL
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_news_categories_".$install_lang[$i]." ADD (PRIMARY KEY(news_category_id))");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE SEQUENCE ".$prefix."_news_categories_id_".$install_lang[$i]."
                    START WITH 1
                    INCREMENT BY 1
                    MINVALUE 1
                    NOCACHE
                    NOCYCLE
                    NOORDER
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
            } /* elseif($db_type === 'mssql' or $db_type === 'ado_mssql' or $db_type === 'odbc_mssql') {
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_admins (
                        admin_id int(10) not null PRIMARY KEY IDENTITY,
                        admin_name varchar(25),
                        admin_surname varchar(25),
                        admin_website varchar(60),
                        admin_email varchar(60) not null UNIQUE,
                        admin_login varchar(25) not null UNIQUE,
                        admin_password varchar(40) not null,
                        admin_rights varchar(255),
                        admin_country varchar(100),
                        admin_city varchar(100),
                        admin_address varchar(255),
                        admin_telephone varchar(25),
                        admin_icq varchar(25),
                        admin_gmt varchar(3) default '+00' not null,
                        admin_notes varchar(255),
                        admin_active int(1) default '1' not null,
                        admin_activenotes varchar(255),
                        admin_regdate bigint(19) not null,
                        admin_viewemail int(1) default '1' not null
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_admins_id",0);
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users (
                        user_id int(10) not null PRIMARY KEY IDENTITY,
                        user_name varchar(25),
                        user_surname varchar(25),
                        user_website varchar(60),
                        user_email varchar(60) not null UNIQUE,
                        user_login varchar(25) not null UNIQUE,
                        user_password varchar(40) not null,
                        user_rights varchar(255),
                        user_country varchar(100),
                        user_city varchar(100),
                        user_address varchar(255),
                        user_telephone varchar(25),
                        user_icq varchar(25),
                        user_gmt varchar(3) default '+00' not null,
                        user_notes varchar(255),
                        user_active int(1) default '1' not null,
                        user_activenotes varchar(255),
                        user_regdate bigint(19) not null,
                        user_viewemail int(1) default '1' not null,
                        user_activatekey varchar(32),
                        user_newpassword varchar(32)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_users_id",0);
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_users_temp (
                        user_id int(10) not null PRIMARY KEY IDENTITY,
                        user_name varchar(25),
                        user_surname varchar(25),
                        user_website varchar(60),
                        user_email varchar(60) not null UNIQUE,
                        user_login varchar(25) not null UNIQUE,
                        user_password varchar(40) not null,
                        user_rights varchar(255),
                        user_country varchar(100),
                        user_city varchar(100),
                        user_address varchar(255),
                        user_telephone varchar(25),
                        user_icq varchar(25),
                        user_gmt int(3) default '+00' not null,
                        user_notes varchar(255),
                        user_regdate bigint(19) not null,
                        user_viewemail int(1) default '1' not null,
                        user_checknum varchar(50) not null
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_users_temp_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_modules_".$install_lang[$i]." (
                    module_id int(5) not null PRIMARY KEY IDENTITY,
                    module_name varchar(25) not null UNIQUE,
                    module_customname varchar(25) not null UNIQUE,
                    module_version varchar(255),
                    module_status int(1) default '0' not null,
                    module_valueview int(1) default '0' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_modules_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_modules_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'news', 'News', NULL, '1', '0')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_modules_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_modules_id_".$install_lang[$i]).", 'main_pages', 'Main Pages', NULL, '1', '0')");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_blocks_".$install_lang[$i]." (
                    block_id int(10) not null PRIMARY KEY IDENTITY,
                    block_name varchar(25),
                    block_customname varchar(25) not null UNIQUE,
                    block_version varchar(255),
                    block_status int(1) default '0' not null,
                    block_valueview int(1) default '0' not null,
                    block_content text,
                    block_url varchar(255),
                    block_arrangements varchar(11) not null,
                    block_priority int(3) default '1' not null,
                    block_refresh bigint(19),
                    block_lastrefresh bigint(19),
                    block_termexpire bigint(19),
                    block_termexpireaction varchar(10)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_blocks_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_blocks_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_blocks_id_".$install_lang[$i]).", 'menu', 'Navigation', NULL, '1', '0', NULL, NULL, 'left', 1, NULL, NULL, NULL, NULL);");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                //shop start
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_catalogs_".$install_lang[$i]." (
                    catalog_id int(10) not null PRIMARY KEY IDENTITY,
                    catalog_img varchar(255),
                    catalog_name varchar(255) not null,
                    catalog_description varchar(255),
                    catalog_position int(4) default '0' not null,
                    catalog_show int(1) default '1' not null,
                    catalog_date bigint(19) not null,
                    catalog_meta_title varchar(255),
                    catalog_meta_keywords varchar(255),
                    catalog_meta_description varchar(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_catalogs_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_categories_".$install_lang[$i]." (
                    category_id int(10) not null PRIMARY KEY IDENTITY,
                    category_img varchar(255),
                    category_name varchar(255) not null,
                    cat_catalog_id int(10) not null,
                    category_description varchar(255),
                    category_position int(4) default '0' not null,
                    category_show int(1) default '1' not null,
                    category_date bigint(19) not null,
                    category_meta_title varchar(255),
                    category_meta_keywords varchar(255),
                    category_meta_description varchar(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_categories_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_subcategories_".$install_lang[$i]." (
                    subcategory_id int(10) not null PRIMARY KEY IDENTITY,
                    subcategory_img varchar(255),
                    subcategory_name varchar(255) not null,
                    subcategory_cat_id int(10) not null,
                    subcategory_description varchar(255),
                    subcategory_position int(4) not null default '0',
                    subcategory_show int(1) default '1' not null,
                    subcategory_date bigint(19) not null,
                    subcategory_meta_title varchar(255),
                    subcategory_meta_keywords varchar(255),
                    subcategory_meta_description varchar(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_subcategories_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_items_".$install_lang[$i]." (
                    item_id int(10) not null PRIMARY KEY IDENTITY,
                    item_img varchar(255),
                    item_name varchar(255) not null,
                    item_price decimal(13,2) not null default '0.00',
                    item_price_discounted decimal(13,2),
                    item_catalog_id int(10),
                    item_category_id int(10),
                    item_subcategory_id int(10),
                    item_short_description text not null,
                    item_description text not null,
                    item_position int(4) not null default '0',
                    item_show int(1) default '1' not null,
                    item_quantity int(10),
                    item_quantity_unlim int(1) default '0' not null,
                    item_quantity_param int(1),
                    item_display int(1),
                    item_date bigint(19) not null,
                    item_meta_title varchar(255),
                    item_meta_keywords varchar(255),
                    item_meta_description varchar(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_items_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_delivery_".$install_lang[$i]." (
                    delivery_id int(10) not null PRIMARY KEY IDENTITY,
                    delivery_name varchar(255) not null,
                    delivery_price decimal(13,2) not null default '0.00',
                    delivery_status int(1) default '1' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_delivery_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_delivery_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_delivery_id_".$install_lang[$i]).", 'personal pickup', '0.00', '1');");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_pay_".$install_lang[$i]." (
                    pay_id int(10) not null PRIMARY KEY IDENTITY,
                    pay_name varchar(255) not null,
                    pay_price decimal(13,2) not null default '0.00',
                    pay_status int(1) default '1' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_pay_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("INSERT INTO ".$prefix."_pay_".$install_lang[$i]." VALUES (".$adodb->GenID($prefix."_pay_id_".$install_lang[$i]).", 'cash', '0.00', '1');");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_orders_".$install_lang[$i]." (
                    order_id int(10) not null PRIMARY KEY IDENTITY,
                    order_customer_id int(10),
                    order_amount decimal(13,2) not null default '0.00',
                    order_date bigint(19) not null,
                    ship_date bigint(19),
                    order_status int(1) default '1' not null,
                    ship_name varchar(255) not null,
                    ship_street varchar(255),
                    ship_house varchar(255),
                    ship_flat varchar(255),
                    ship_city varchar(255),
                    ship_state varchar(255),
                    ship_postalcode varchar(255),
                    ship_country varchar(255),
                    ship_telephone varchar(255) not null,
                    ship_email varchar(255) not null,
                    ship_addinfo varchar(255),
                    order_delivery int(10) not null,
                    order_pay int(10) not null,
                    order_comments varchar(255),
                    order_ip varchar(39) not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_orders_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_order_items_".$install_lang[$i]." (
                    order_item_id int(10) not null PRIMARY KEY IDENTITY,
                    order_id int(10) not null,
                    item_id int(10) not null,
                    order_item_name varchar(255) not null,
                    order_item_price decimal(13,2) not null default '0.00',
                    order_item_quantity int(3) not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $insert = $adodb->Execute("ALTER TABLE ".$prefix."_order_items_".$install_lang[$i]." ADD
                    CONSTRAINT order_item
                    UNIQUE (order_id,item_id)
                  ");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_order_items_id_".$install_lang[$i],0);
                //shop end
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_guestbook (
                        post_id int(10) not null PRIMARY KEY IDENTITY,
                        post_user_name varchar(255) not null,
                        post_user_country varchar(100),
                        post_user_city varchar(100),
                        post_user_telephone varchar(25),
                        post_show_telephone int(1) default '1' not null,
                        post_user_email varchar(60) not null,
                        post_show_email int(1) default '1' not null,
                        post_user_id int(10),
                        post_user_ip varchar(20) not null,
                        post_date bigint(19) not null,
                        post_text text not null,
                        post_status int(1) default '0' not null,
                        post_unswer text,
                        post_unswer_date bigint(19)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_guestbook_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_receivers_".$install_lang[$i]." (
                    receiver_id int(10) not null PRIMARY KEY IDENTITY,
                    receiver_name varchar(25) not null,
                    receiver_email varchar(60) not null,
                    receiver_active int(1) default '1' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_mail_receivers_id_".$install_lang[$i],0);
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_mail_sendlog (
                        mail_id int(10) not null PRIMARY KEY IDENTITY,
                        mail_user varchar(25),
                        mail_date bigint(19) not null,
                        mail_module varchar(25) not null,
                        mail_ip varchar(20) not null
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog1 ON ".$prefix."_mail_sendlog (mail_date)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog2 ON ".$prefix."_mail_sendlog (mail_module)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_mail_sendlog3 ON ".$prefix."_mail_sendlog (mail_ip)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_mail_sendlog_id",0);
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_menu_".$install_lang[$i]." (
                    mainmenu_id int(10) not null PRIMARY KEY IDENTITY,
                    mainmenu_type varchar(10) not null,
                    mainmenu_name varchar(25) not null,
                    mainmenu_module varchar(255),
                    mainmenu_submodule varchar(255),
                    mainmenu_target varchar(20) default '_parent' not null,
                    mainmenu_priority int(3) not null,
                    mainmenu_inmenu int(1) default '1' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_main_menu_id_".$install_lang[$i],0);
                if($i == 0) {
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_session (
                        session_login varchar(25),
                        session_time bigint(19) not null,
                        session_hostaddress varchar(20) not null,
                        session_whoonline varchar(20) not null
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE INDEX ".$prefix."_session_login ON ".$prefix."_session (session_login)");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("CREATE TABLE ".$prefix."_system_pages (
                        systempage_id int(10) not null PRIMARY KEY IDENTITY,
                        systempage_author varchar(25) not null,
                        systempage_name varchar(255) not null,
                        systempage_title varchar(255) not null,
                        systempage_date bigint(19) not null,
                        systempage_content text not null,
                        systempage_lang varchar(2)
                      )");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $adodb->GenID($prefix."_system_pages_id",0);
                    $insert = $adodb->Execute("INSERT INTO ".$prefix."_system_pages VALUES (".$adodb->GenID($prefix."_system_pages_id").", 'admin', 'account_rules', 'Общие правила', '".time()."', 'Заходя на сайт, Вы подтверждаете своё согласие со следующими условиями. Если Вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь нашим сайтом. Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить Вас об этом, однако с Вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование нашего сайта после обновления/исправления условий означает Ваше согласие с ними.<br><br>Наши сайты работают под управлением программного обеспечения SystemDK для создания веб-сайтов. Скачать его можно по адресу www.systemsdk.com. За дополнительной информацией о SystemsDK обращайтесь по адресу http://www.systemsdk.com.<br><br>Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы Вашей страны, страны, которая предоставляет услуги хостинга для этого сайта, или международное право. Попытки размещения таких сообщений могут привести к Вашему немедленному отключению от сайта, при этом Ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы сайта имеют право удалить, отредактировать, перенести или закрыть Вам доступ в любое время по своему усмотрению. Как пользователь Вы согласны с тем, что введённая Вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без Вашего разрешения, ни администрация сайта, ни авторы программного обеспечения SystemDK не могут быть ответственными за действия хакеров, которые могут привести к несанкционированному доступу к ней.', 'ru');");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                    $insert = $adodb->Execute("INSERT INTO ".$prefix."_system_pages VALUES (".$adodb->GenID($prefix."_system_pages_id").", 'admin', 'account_rules', 'General rules', '".time()."', 'By accessing our web-site, you agree to be legally bound by the following terms. If you do not agree to be legally bound by all of the following terms then please do not access and/or use our web-site. We may change these at any time and we’ll do our utmost in informing you, though it would be prudent to review this regularly yourself as your continued usage of our web-site after changes mean you agree to be legally bound by these terms as they are updated and/or amended.<br><br> Our web-sites are powered by SystemDK which can be downloaded from www.systemsdk.com. For further information about SystemDK, please see: http://www.systemsdk.com.<br><br>You agree not to post any abusive, obscene, vulgar, slanderous, hateful, threatening, sexually-orientated or any other material that may violate any laws be it of your country, the country where this web-site is hosted or International Law. Doing so may lead to you being immediately and permanently banned, with notification of your Internet Service Provider if deemed required by us. The IP address are recorded to aid in enforcing these conditions. You agree that web-site administrators have the right to remove, edit, move or close access for You at any time should we see fit. As a user you agree to any information you have entered to being stored in a database. While this information will not be disclosed to any third party without your consent, neither web-site administration nor SystemDK developers shall be held responsible for any hacking attempt that may lead to the data being compromised.', 'en');");
                    if(!$insert) {
                        $error_message = $adodb->ErrorMsg();
                        $error_code = $adodb->ErrorNo();
                        $error[] = array("code" => $error_code,"message" => $error_message);
                    }
                }
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_messages_".$install_lang[$i]." (
                    message_id int(10) not null PRIMARY KEY IDENTITY,
                    message_author varchar(25) not null,
                    message_title varchar(100) not null,
                    message_content text not null,
                    message_notes text,
                    message_date bigint(19) not null,
                    message_termexpire bigint(19),
                    message_termexpireaction varchar(10),
                    message_status int(1) default '1' not null,
                    message_valueview int(1) default '0' not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_messages_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_rss_".$install_lang[$i]." (
                    rss_id int(10) not null PRIMARY KEY IDENTITY,
                    rss_sitename varchar(30) not null,
                    rss_siteurl varchar(255) not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_rss_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_main_pages_".$install_lang[$i]." (
                    mainpage_id int(10) not null PRIMARY KEY IDENTITY,
                    mainpage_author varchar(25) not null,
                    mainpage_title varchar(255) not null,
                    mainpage_date bigint(19) not null,
                    mainpage_content text not null,
                    mainpage_readcounter int(10) default '0' not null,
                    mainpage_notes text,
                    mainpage_valueview int(1) default '0' not null,
                    mainpage_status int(1) default '0' not null,
                    mainpage_termexpire bigint(19),
                    mainpage_termexpireaction varchar(10)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_main_pages_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_".$install_lang[$i]." (
                    news_id int(10) not null PRIMARY KEY IDENTITY,
                    news_author varchar(25) not null,
                    news_category_id int(10) not null,
                    news_title varchar(255) not null,
                    news_date bigint(19) not null,
                    news_images varchar(255),
                    news_short_text text not null,
                    news_content text not null,
                    news_readcounter int(10) default '0' not null,
                    news_notes text,
                    news_valueview int(1) default '0' not null,
                    news_status int(1) default '0' not null,
                    news_termexpire bigint(19),
                    news_termexpireaction varchar(10),
                    news_onhome int(1) default '0' not null,
                    news_link varchar(255)
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_auto_".$install_lang[$i]." (
                    news_id int(10) not null PRIMARY KEY IDENTITY,
                    news_author varchar(25) not null,
                    news_category_id int(10) not null,
                    news_title varchar(255) not null,
                    news_date bigint(19) not null,
                    news_images varchar(255),
                    news_short_text text not null,
                    news_content text not null,
                    news_readcounter int(10) default '0' not null,
                    news_notes text,
                    news_valueview int(1) default '0' not null,
                    news_status int(1) default '0' not null,
                    news_termexpire bigint(19),
                    news_termexpireaction varchar(10),
                    news_onhome int(1) default '0' not null,
                    news_link varchar(255)
                 )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_auto_id_".$install_lang[$i],0);
                $insert = $adodb->Execute("CREATE TABLE ".$prefix."_news_categories_".$install_lang[$i]." (
                    news_category_id int(10) not null PRIMARY KEY IDENTITY,
                    news_category_name varchar(255) not null
                  )");
                if(!$insert) {
                    $error_message = $adodb->ErrorMsg();
                    $error_code = $adodb->ErrorNo();
                    $error[] = array("code" => $error_code,"message" => $error_message);
                }
                $adodb->GenID($prefix."_news_categories_id_".$install_lang[$i],0);
            }*/ else {
                if($db_persistency === 'no') {
                    $adodb->Close();
                }
                header("Location: ../index.php");
                exit();
            }
            if(isset($error)) {
                if($db_persistency === 'no') {
                    $adodb->Close();
                }
                $this->smarty->assign("error","inserterror");
                $this->smarty->assign("error_mess",$error);
                $this->smarty->display("install/action.html","|errorinsert");
                exit();
            } else {
                $file = @fopen("../includes/data/".$install_lang[$i]."_installed.dat","w");
                @fclose($file);
            }
        }
        $site_startdate = date("d/m/Y");
        $file = @fopen("../includes/data/config.inc","w");
        $content = "<?php\n\n";
        $content .= 'define("SYSTEMDK_VERSION","3.0.0");'."\n";
        $content .= 'define("SYSTEMDK_MODREWRITE","'.$mod_rewrite."\");\n";
        $content .= 'define("SYSTEMDK_DESCRIPTION","");'."\n";
        $content .= 'define("SITE_ID","'.$site_id."\");\n";
        $content .= 'define("SITE_DIR","'.$site_dir."\");\n";
        $content .= 'define("ADMIN_DIR","'.$admin_dir."\");\n";
        $content .= 'define("ADMIN_EMAIL","'.$admin_email."\");\n";
        $content .= 'define("DB_HOST","'.$db_host."\");\n";
        $content .= 'define("DB_PORT","'.$db_port."\");\n";
        $content .= 'define("DB_USER_NAME","'.$db_user_name."\");\n";
        $content .= 'define("DB_PASSWORD","'.$db_password."\");\n";
        $content .= 'define("DB_NAME","'.$db_name."\");\n";
        $content .= 'define("DB_PERSISTENCY","'.$db_persistency."\");\n";
        $content .= 'define("PREFIX","'.$prefix."\");\n";
        $content .= 'define("DBTYPE","'.$db_type."\");\n";
        $content .= 'define("DB_CHARACTER","'.$db_character."\");\n";
        $content .= 'define("SMARTY_CACHING","'.$smarty_caching."\");\n";
        $content .= 'define("SMARTY_DEBUGGING","'.$smarty_debugging."\");\n";
        $content .= 'define("ADODB_DEBUGGING","'.$adodb_debugging."\");\n";
        $content .= 'define("CACHE_LIFETIME","'.$cache_lifetime."\");\n";
        $content .= 'define("SITE_CHARSET","'.$site_charset."\");\n";
        $content .= 'define("SITE_URL","'.$site_url."\");\n";
        $content .= 'define("SITE_NAME","'.$site_name."\");\n";
        $content .= 'define("SITE_LOGO","'.$site_logo."\");\n";
        $content .= 'define("SITE_STARTDATE","'.$site_startdate."\");\n";
        $content .= 'define("SITE_KEYWORDS","'.$site_keywords."\");\n";
        $content .= 'define("SITE_DESCRIPTION","'.$site_description."\");\n";
        $content .= 'define("SITE_STATUS","'.$site_status."\");\n";
        $content .= 'define("SITE_STATUS_NOTES","'.$site_status_notes."\");\n";
        $content .= 'define("SITE_THEME","'.$site_theme."\");\n";
        $content .= 'define("LANGUAGE","'.$language."\");\n";
        $content .= 'define("SYSTEMDK_SITE_LANG","'.$systemdk_site_lang."\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_SITE_LANG","'.$systemdk_admin_site_lang."\");\n";
        $content .= 'define("SYSTEMDK_INTERFACE_LANG","'.$systemdk_interface_lang."\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_INTERFACE_LANG","'.$systemdk_admin_interface_lang."\");\n";
        $content .= 'define("HOME_MODULE","'.$Home_Module."\");\n";
        $content .= 'define("GZIP","'.$gzip."\");\n";
        $content .= 'define("ENTER_CHECK","'.$enter_check."\");\n";
        $content .= 'define("COOKIE_LIVE","'.$cookie_live."\");\n";
        $content .= 'define("DISPLAY_ADMIN_GRAPHIC","'.$display_admin_graphic."\");\n";
        $content .= 'define("SYSTEMDK_ADMINROWS_PERPAGE","'.$systemdk_adminrows_perpage."\");\n";
        $content .= 'define("SYSTEMDK_MAIL_METHOD","'.$systemdk_mail_method."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_HOST","'.$systemdk_smtp_host."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PORT","'.$systemdk_smtp_port."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_USER","'.$systemdk_smtp_user."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PASS","'.$systemdk_smtp_pass."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_REGISTRATION","'.$systemdk_account_registration."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_ACTIVATION","'.$systemdk_account_activation."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_RULES","'.$systemdk_account_rules."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXUSERS","'.$systemdk_account_maxusers."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS","'.$systemdk_account_maxactivationdays."\");\n";
        $content .= 'define("SYSTEMDK_STATISTICS","'.$systemdk_statistics."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOPLAY","'.$systemdk_player_autoplay."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOBUFFER","'.$systemdk_player_autobuffer."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_CONTROLS","'.$systemdk_player_controls."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOPLAY","'.$systemdk_player_audioautoplay."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOBUFFER","'.$systemdk_player_audioautobuffer."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOCONTROLS","'.$systemdk_player_audio_controls."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_RATIO","'.$systemdk_player_ratio."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_SKIN","'.$systemdk_player_skin."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM","'.$systemdk_ipantispam."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM_NUM","'.$systemdk_ipantispam_num."\");\n";
        $content .= 'define("SYSTEMDK_FEEDBACK_RECEIVER","default");'."\n";
        $content .= "?>\n";
        $writefile = @fwrite($file,$content);
        if(!$writefile) {
            if($db_persistency === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","nowrite");
            $this->smarty->display("install/action.html","|nowrite");
            exit();
        }
        @fclose($file);
        @chmod("../includes/data/config.inc",0604);
        if($db_persistency === 'no') {
            $adodb->Close();
        }
        session_start();
        $_SESSION["install"] = $site_id;
        $this->smarty->display("install/install4.html");
    }


    public function step5() {
        $this->check_php_version();
        $this->smarty->display("install/install5.html");
    }


    public function step6() {
        if(isset($_POST['admin_add_login'])) {
            $admin_add_login = trim($_POST['admin_add_login']);
        }
        if(isset($_POST['admin_add_password'])) {
            $admin_add_password = trim($_POST['admin_add_password']);
        }
        if(isset($_POST['admin_add_password2'])) {
            $admin_add_password2 = trim($_POST['admin_add_password2']);
        }
        if(isset($_POST['admin_add_email'])) {
            $admin_add_email = trim($_POST['admin_add_email']);
        }
        if(isset($_POST['admin_add_viewemail'])) {
            $admin_add_viewemail = trim($_POST['admin_add_viewemail']);
        }
        if((!isset($_POST['admin_add_email']) or !isset($_POST['admin_add_login']) or !isset($_POST['admin_add_password']) or !isset($_POST['admin_add_password2']) or !isset($_POST['admin_add_viewemail'])) or ($admin_add_email == "" or $admin_add_login == "" or $admin_add_password == "" or $admin_add_password2 == "" or $admin_add_viewemail == "")) {
            $this->smarty->assign("error","notalldata");
            $this->smarty->display("install/action.html","|notalldata");
            exit();
        }
        $this->check_php_version();
        include_once("../includes/data/config.inc");
        include_once("../adodb/adodb.inc.php");
        if(session_id() == "") {
            session_start();
        }
        if(!isset($_SESSION["install"]) or (isset($_SESSION["install"]) and $_SESSION["install"] != SITE_ID)) {
            header("Location: ../index.php");
            if(isset($_SESSION["install"])) {
                unset($_SESSION["install"]);
            }
            session_unset();
            setcookie(session_name(),'',time() - 3600,'/');
            session_destroy();
            exit();
        }
        $adodb = ADONewConnection(DBTYPE);
        if(ADODB_DEBUGGING === 'yes') {
            $adodb->debug = true;
        }
        if(DBTYPE === 'oci8') {
            $adodb->charSet = DB_CHARACTER;
            $cnt = "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=".DB_HOST.")(PORT=".DB_PORT."))(CONNECT_DATA=(SID=".DB_NAME.")))";
        }
        if(DB_PERSISTENCY === 'yes') {
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
            if(DBTYPE === 'oci8' and DB_PORT != 0) {
                $db2 = $adodb->PConnect($cnt,DB_USER_NAME,DB_PASSWORD);
            } elseif(DBTYPE === 'pdo') {
                $db2 = $adodb->PConnect('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER_NAME,DB_PASSWORD);
            } else {
                $db2 = $adodb->PConnect(DB_HOST,DB_USER_NAME,DB_PASSWORD,DB_NAME);
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
            if(DBTYPE === 'oci8' and DB_PORT != 0) {
                $db2 = $adodb->Connect($cnt,DB_USER_NAME,DB_PASSWORD);
            } elseif(DBTYPE === 'pdo') {
                $db2 = $adodb->Connect('mysql:host='.DB_HOST.';dbname='.DB_NAME,DB_USER_NAME,DB_PASSWORD);
            } else {
                $db2 = $adodb->Connect(DB_HOST,DB_USER_NAME,DB_PASSWORD,DB_NAME);
            }
        }
        if(!$db2) {
            $this->smarty->assign("error","notconnect");
            $this->smarty->display("install/action.html","|notconnect");
            exit();
        }
        if(DBTYPE === 'mysql' or DBTYPE === 'pdo' or DBTYPE === 'mysqli' or DBTYPE === 'mysqlt' or DBTYPE === 'maxsql') {
            $adodb->Execute("set names '".DB_CHARACTER."'");
        }
        $admin_add_login = strip_tags($admin_add_login);
        $admin_add_email = strip_tags($admin_add_email);
        $admin_add_password = strip_tags($admin_add_password);
        $admin_add_password2 = strip_tags($admin_add_password2);
        $admin_add_viewemail = strip_tags($admin_add_viewemail);
        if(preg_match("/[^a-zA-Zа-яА-Я0-9_-]/i",$admin_add_login)) {
            if(DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","errorlogin");
            $this->smarty->display("install/action.html","|errorlogin");
            exit();
        }
        if($admin_add_password !== $admin_add_password2) {
            if(DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","noteqpass");
            $this->smarty->display("install/action.html","|noteqpass");
            exit();
        }
        if(!filter_var($admin_add_email,FILTER_VALIDATE_EMAIL)) {
            if(DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","notcorrectemail");
            $this->smarty->display("install/action.html","|notcorrectemail");
            exit();
        }
        if(isset($_SESSION["install"])) {
            unset($_SESSION["install"]);
        }
        session_unset();
        setcookie(session_name(),'',time() - 3600,'/');
        session_destroy();
        $admin_add_login = $adodb->qstr($admin_add_login,get_magic_quotes_gpc());
        $admin_add_email = $adodb->qstr($admin_add_email,get_magic_quotes_gpc());
        $admin_add_password = $adodb->qstr($admin_add_password,get_magic_quotes_gpc());
        $admin_add_viewemail = $adodb->qstr($admin_add_viewemail,get_magic_quotes_gpc());
        $admin_add_password = substr(substr($admin_add_password,0,-1),1);
        $admin_add_password = md5($admin_add_password);
        $now = time();
        $adodb->Execute("UPDATE ".PREFIX."_system_pages SET systempage_author=$admin_add_login WHERE systempage_author='admin'");
        $admin_add_id = $adodb->GenID(PREFIX."_admins_id");
        $sql = "INSERT INTO ".PREFIX."_admins (admin_id,admin_email,admin_login,admin_password,admin_rights,admin_gmt,admin_active,admin_regdate,admin_viewemail)  VALUES ('$admin_add_id',$admin_add_email,$admin_add_login,'$admin_add_password','main_admin','+00','1','$now',$admin_add_viewemail)";
        $result = $adodb->Execute($sql);
        $num_result = $adodb->Affected_Rows();
        if($num_result === false or $num_result == 0) {
            $error_message = $adodb->ErrorMsg();
            $error_code = $adodb->ErrorNo();
            $error[] = array("code" => $error_code,"message" => $error_message);
            if(DB_PERSISTENCY === 'no') {
                $adodb->Close();
            }
            $this->smarty->assign("error","inserterror2");
            $this->smarty->assign("error_mess",$error);
            $this->smarty->display("install/action.html","|errorinsert2");
            exit();
        }
        if(DB_PERSISTENCY === 'no') {
            $adodb->Close();
        }
        $this->smarty->assign("error","addok");
        $this->smarty->display("install/action.html");
    }


    private function check_php_version() {
        $phpversion = phpversion();
        $phpversion = explode(".",$phpversion);
        $phpversion = "$phpversion[0]$phpversion[1]";
        if($phpversion < '52' and $this->func !== 'step2') {
            if($this->func == 'step5' or $this->func == 'step6') {
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION["install"])) {
                    unset($_SESSION["install"]);
                }
                session_unset();
                setcookie(session_name(),'',time() - 3600,'/');
                session_destroy();
                header("Location: ../index.php");
                exit();
            } else {
                header("Location: ../index.php");
                exit();
            }
        } elseif($phpversion < '52' and $this->func == 'step2') {
            $this->smarty->assign("servconf","no");
        } elseif($this->func == 'step2') {
            $this->smarty->assign("servconf","yes");
        }
    }


    private function get_langlist() {
        if(is_dir('../themes/systemdk/languages')) {
            if($fileread = opendir('../themes/systemdk/languages')) {
                $i = 0;
                while(false !== ($file = readdir($fileread))) {
                    if(!preg_match("/[.]/",$file)) {
                        $langlist[] = array("item" => $i,"name" => $file);
                        $i++;
                    }
                }
                closedir($fileread);
            }
        }
        if(!isset($langlist)) {
            $langlist = "no";
        }
        return $langlist;
    }
}

$load = new install();
?>