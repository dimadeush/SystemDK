<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_main.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class model_main extends model_base {


    private $systemdk_is_admin_now = 0;
    private $systemdk_is_user_now = 0;
    private $content;
    // for admin site part
    private $adminnow = false;
    private $admin_login;
    private $admin_id;
    private $admin_name;
    private $admin_rights;


    public function index() {
        $this->registry->my_cache_id = $_SERVER['REQUEST_URI'];
        $this->registry->ip_addr = getenv('REMOTE_ADDR');
        if(session_id() == "") {
            session_start();
        }
        $this->registry->language = LANGUAGE;
        if(isset($_GET['lang']) and trim($_GET['lang']) != "" and ((defined('SYSTEMDK_SITE_LANG') and SYSTEMDK_SITE_LANG === "yes") or (defined('SYSTEMDK_ADMIN_SITE_LANG') and SYSTEMDK_ADMIN_SITE_LANG === "yes" and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes'))) {
            $lang = substr(strip_tags(trim($_GET['lang'])),0,2);
            if(file_exists(__SITE_PATH."/themes/".SITE_THEME."/languages/".$lang) and trim($lang) != "" and file_exists(__SITE_PATH."/includes/data/".$lang."_installed.dat")) {
                $sitelang = $lang;
                if((!isset($_GET['ilang']) or trim(substr(strip_tags(trim($_GET['ilang'])),0,2)) == "") and (!isset($_SESSION["ilanguage"]) or trim(substr(strip_tags(trim($_SESSION["ilanguage"])),0,2)) == "") and ((defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "yes") or (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') and SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes'))) {
                    $_SESSION["ilanguage"] = $lang;
                    $this->registry->language = $lang;
                } elseif((defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "no" and !defined('__SITE_ADMIN_PART')) or (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') and SYSTEMDK_ADMIN_INTERFACE_LANG === "no" and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes' and defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "no")) {
                    $this->registry->language = $lang;
                }
            }
        }
        if(!isset($sitelang)) {
            $this->registry->sitelang = $this->registry->language;
        } else {
            $this->registry->sitelang = $sitelang;
        }
        if(isset($_GET['ilang']) and trim($_GET['ilang']) != "" and ((defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "yes") or (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') and SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes'))) {
            $ilang = substr(strip_tags(trim($_GET['ilang'])),0,2);
            if(file_exists(__SITE_PATH."/themes/".SITE_THEME."/languages/".$ilang) and trim($ilang) != "") {
                $_SESSION["ilanguage"] = $ilang;
                $this->registry->language = $ilang;
            }
        } elseif(isset($_SESSION["ilanguage"]) and trim($_SESSION["ilanguage"]) != "" and ((defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "yes") or (defined('SYSTEMDK_ADMIN_INTERFACE_LANG') and SYSTEMDK_ADMIN_INTERFACE_LANG === "yes" and defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes'))) {
            $ilang = substr(strip_tags(trim($_SESSION["ilanguage"])),0,2);
            if(file_exists(__SITE_PATH."/themes/".SITE_THEME."/languages/".$ilang) and trim($ilang) != "") {
                $this->registry->language = $ilang;
            }
        }
        $this->registry->main_class->assign("display_site_lang",SYSTEMDK_SITE_LANG);
        if(defined('SYSTEMDK_SITE_LANG') and SYSTEMDK_SITE_LANG === "yes") {
            $this->registry->main_class->assign("display_adminsite_lang","yes");
        } else {
            $this->registry->main_class->assign("display_adminsite_lang",SYSTEMDK_ADMIN_SITE_LANG);
        }
        $this->registry->main_class->assign("display_int_lang",SYSTEMDK_INTERFACE_LANG);
        if(defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "yes") {
            $this->registry->main_class->assign("display_adminint_lang","yes");
        } else {
            $this->registry->main_class->assign("display_adminint_lang",SYSTEMDK_ADMIN_INTERFACE_LANG);
        }
        $this->registry->main_class->run("register_plugins");
        if((defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes' and !file_exists(__SITE_PATH."/themes/".SITE_THEME."/languages/".$this->registry->language."/".ADMIN_DIR)) or !file_exists(__SITE_PATH."/themes/".SITE_THEME."/languages/".$this->registry->language)) {
            $this->registry->main_class->assign("site_charset",SITE_CHARSET);
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
            $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
            $this->registry->main_class->assign("site_language",$this->registry->sitelang);
            $this->registry->main_class->assign("language",$this->registry->language);
            $this->registry->main_class->assign("site_logo",SITE_LOGO);
            $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
            $this->registry->main_class->assign("system_dir",SITE_DIR);
            $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
            $this->registry->main_class->assign("langlist",$langlist);
            $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
            $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
            if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->registry->main_class->assign("error2locationpath","../");
                $this->registry->main_class->display_theme_systemerror("Fatal Error","SystemDK can't find language files: ".__SITE_PATH."/themes/".SITE_THEME."/languages/".$this->registry->language."/".ADMIN_DIR);
                $this->registry->main_class->display("error2.html",$this->registry->sitelang."|fatalerror|admlangfile|".$this->registry->language);
            } else {
                $this->registry->main_class->assign("error2locationpath","");
                $this->registry->main_class->display_theme_systemerror("Fatal Error","SystemDK can't find language files: ".__SITE_PATH."/themes/".SITE_THEME."/languages/".$this->registry->language);
                $this->registry->main_class->display("error2.html",$this->registry->sitelang."|fatalerror|langfile|".$this->registry->language);
            }
            exit();
        }
        if(file_exists(__SITE_PATH."/includes/data/ban_ip.inc")) {
            include_once(__SITE_PATH."/includes/data/ban_ip.inc");
            $ip_addr = getenv('REMOTE_ADDR');
            $ip_net = explode("|",BAN_IP);
            foreach($ip_net as $ban_client) {
                if($ip_addr == $ban_client) {
                    if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                        if(SYSTEMDK_MODREWRITE === 'yes') {
                            header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                        } else {
                            header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                        }
                        exit();
                    }
                    $this->registry->main_class->assign("site_charset",SITE_CHARSET);
                    $this->registry->main_class->assign("site_name",SITE_NAME);
                    $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
                    $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
                    $this->registry->main_class->assign("site_language",$this->registry->sitelang);
                    $this->registry->main_class->assign("language",$this->registry->language);
                    $this->registry->main_class->assign("site_logo",SITE_LOGO);
                    $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
                    $this->registry->main_class->assign("system_dir",SITE_DIR);
                    $this->registry->main_class->assign("error2locationpath","");
                    $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                    $this->registry->main_class->assign("langlist",$langlist);
                    $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
                    $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
                    $this->registry->main_class->assign("error_kind","no");
                    $this->registry->main_class->assign("error_text","no");
                    $this->registry->main_class->assign("error","error_banip");
                    $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|banip|".$this->registry->language);
                    exit();
                }
            }
        }
        if(file_exists(__SITE_PATH."/install")) {
            if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                if(SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                } else {
                    header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                }
                exit();
            }
            $this->registry->main_class->assign("site_charset",SITE_CHARSET);
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
            $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
            $this->registry->main_class->assign("site_language",$this->registry->sitelang);
            $this->registry->main_class->assign("language",$this->registry->language);
            $this->registry->main_class->assign("site_logo",SITE_LOGO);
            $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
            $this->registry->main_class->assign("system_dir",SITE_DIR);
            $this->registry->main_class->assign("error2locationpath","");
            $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
            $this->registry->main_class->assign("langlist",$langlist);
            $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
            $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
            $this->registry->main_class->assign("error_kind","no");
            $this->registry->main_class->assign("error_text","no");
            $this->registry->main_class->assign("error","error_findinstall");
            $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|findinstall|".$this->registry->language);
            exit();
        }
        if(SITE_STATUS == "off" and !defined('__SITE_ADMIN_PART')) {
            $this->registry->main_class->assign("site_charset",SITE_CHARSET);
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
            $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
            $this->registry->main_class->assign("site_language",$this->registry->sitelang);
            $this->registry->main_class->assign("language",$this->registry->language);
            $this->registry->main_class->assign("site_logo",SITE_LOGO);
            $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
            $this->registry->main_class->assign("system_dir",SITE_DIR);
            $this->registry->main_class->assign("error2locationpath","");
            $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
            $this->registry->main_class->assign("langlist",$langlist);
            $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
            $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
            $this->registry->main_class->assign("error_kind","no");
            $this->registry->main_class->assign("error_text","no");
            $this->registry->main_class->assign("error","messege_siteoff");
            if(defined('SITE_STATUS_NOTES') and SITE_STATUS_NOTES != "") {
                $this->registry->main_class->assign("site_status_notes",SITE_STATUS_NOTES);
                $this->registry->main_class->display("error2.html",$this->registry->sitelang."|siteoff|withnotes|".$this->registry->language);
            } else {
                $this->registry->main_class->assign("site_status_notes","no");
                $this->registry->main_class->display("error2.html",$this->registry->sitelang."|siteoff|".$this->registry->language);
            }
            exit();
        }
        $phpversion = phpversion();
        $phpversion = explode(".",$phpversion);
        $phpversion = "$phpversion[0]$phpversion[1]";
        if($phpversion < '52') {
            if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                if(SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                } else {
                    header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                }
                exit();
            }
            $this->registry->main_class->assign("site_charset",SITE_CHARSET);
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
            $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
            $this->registry->main_class->assign("site_language",$this->registry->sitelang);
            $this->registry->main_class->assign("language",$this->registry->language);
            $this->registry->main_class->assign("site_logo",SITE_LOGO);
            $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
            $this->registry->main_class->assign("system_dir",SITE_DIR);
            $this->registry->main_class->assign("error2locationpath","");
            $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
            $this->registry->main_class->assign("langlist",$langlist);
            $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
            $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
            $this->registry->main_class->assign("error_kind","no");
            $this->registry->main_class->assign("error_text","no");
            $this->registry->main_class->assign("error","error_notphp5");
            $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|notphp5|".$this->registry->language);
            exit();
        }
        foreach($_GET as $secvalue) {
            if((preg_match("/UNION/i",$secvalue)) or (preg_match("/OUTFILE/i",$secvalue)) or (preg_match("/FROM/i",$secvalue)) or (preg_match("/<[^>]*script*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*object*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*iframe*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*applet*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*meta*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*style*\"?[^>]*>/i",$secvalue)) or (preg_match("/<[^>]*form*\"?[^>]*>/i",$secvalue)) or (preg_match("/\([^>]*\"?[^)]*\)/i",$secvalue)) or (preg_match("/\"/i",$secvalue))) {
                $this->registry->main_class->assign("site_charset",SITE_CHARSET);
                $this->registry->main_class->assign("site_name",SITE_NAME);
                $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
                $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
                $this->registry->main_class->assign("site_language",$this->registry->sitelang);
                $this->registry->main_class->assign("language",$this->registry->language);
                $this->registry->main_class->assign("site_logo",SITE_LOGO);
                $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
                $this->registry->main_class->assign("system_dir",SITE_DIR);
                if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                    $this->registry->main_class->assign("error2locationpath","../");
                    $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                } else {
                    $this->registry->main_class->assign("error2locationpath","");
                    $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
                }
                $this->registry->main_class->assign("langlist",$langlist);
                $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
                $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
                $this->registry->main_class->assign("error_kind","no");
                $this->registry->main_class->assign("error_text","no");
                $this->registry->main_class->assign("error","error_tagerror");
                $this->registry->main_class->assign("error_addr",$this->registry->my_cache_id);
                if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                    $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|admtag|".$this->registry->language);
                } else {
                    $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|tag|".$this->registry->language);
                }
                exit();
            }
        }
        if(isset($_COOKIE['admin_cookie']) and $_COOKIE['admin_cookie'] != "") {
            $this->registry->admin = $_COOKIE['admin_cookie'];
        } else {
            $this->registry->admin = "";
        }
        if(!$this->db_connect()) {
            $this->registry->main_class->assign("site_charset",SITE_CHARSET);
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
            $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
            $this->registry->main_class->assign("site_language",$this->registry->sitelang);
            $this->registry->main_class->assign("language",$this->registry->language);
            $this->registry->main_class->assign("site_logo",SITE_LOGO);
            $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
            $this->registry->main_class->assign("system_dir",SITE_DIR);
            $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
            $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
            $langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
            $this->registry->main_class->assign("langlist",$langlist);
            $this->registry->main_class->assign("error","error_noconnect");
            if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->registry->main_class->assign("error2locationpath","../");
                $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|error|errorconnect|".$this->registry->language);
                exit();
            } else {
                $this->registry->main_class->assign("error2locationpath","");
                $this->registry->main_class->assign("error_kind","no");
                $this->registry->main_class->assign("error_text","no");
                $this->registry->main_class->display("error2.html",$this->registry->sitelang."|error|noconnect|".$this->registry->language);
                exit();
            }
        }
        if(!get_magic_quotes_gpc()) {
            $this->registry->get_magic_quotes_gpc = 'off';
        } else {
            $this->registry->get_magic_quotes_gpc = 'on';
        }
        $check_http_host = strpos($_SERVER['HTTP_HOST'],'.');
        if($check_http_host === false) {
            $this->registry->http_host = '';
        } else {
            $this->registry->http_host = $_SERVER['HTTP_HOST'];
        }
        if(isset($_POST['user_login']) and isset($_POST['user_password']) and isset($_POST['func']) and trim($_POST['user_login']) != "" and trim($_POST['user_password']) != "" and $_POST['func'] == "mainuser") {
            if(!preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/i",trim($_POST['user_login']))) {
                $user_login = substr(trim($_POST['user_login']),0,25);
                $user_password = substr(trim($_POST['user_password']),0,25);
                if(ENTER_CHECK == "yes") {
                    if(extension_loaded("gd") and session_id() == "") {
                        session_start();
                    }
                    if(extension_loaded("gd") and ((isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== strip_tags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                        if(isset($_SESSION["captcha"])) {
                            unset($_SESSION["captcha"]);
                        }
                        $this->registry->captcha_check = "noteq";
                        $this->registry->user = "";
                    } else {
                        if(isset($_SESSION["captcha"])) {
                            unset($_SESSION["captcha"]);
                        }
                        $user_password = strip_tags($user_password);
                        $user_password = $this->qstr($user_password,get_magic_quotes_gpc());
                        $user_password = substr(substr($user_password,0,-1),1);
                        $user_password = md5($user_password);
                        $user_cookie = base64_encode("$user_login:$user_password");
                        $this->registry->user = $user_cookie;
                        unset($user_password);
                        unset($user_cookie);
                    }
                } else {
                    $user_password = strip_tags($user_password);
                    $user_password = $this->qstr($user_password,get_magic_quotes_gpc());
                    $user_password = substr(substr($user_password,0,-1),1);
                    $user_password = md5($user_password);
                    $user_cookie = base64_encode("$user_login:$user_password");
                    $this->registry->user = $user_cookie;
                    unset($user_password);
                    unset($user_cookie);
                }
            } else {
                $this->registry->user = "";
            }
        } elseif(isset($_GET['func']) and $_GET['func'] == "account_logout") {
            if(isset($_COOKIE['user_cookie'])) {
                setcookie("user_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
            }
            $this->registry->user = "";
        } elseif(isset($_COOKIE['user_cookie']) and $_COOKIE['user_cookie'] != "") {
            $this->registry->user = $_COOKIE['user_cookie'];
        } else {
            $this->registry->user = "";
        }
        $this->systemdk_is_admin_now = $this->is_admin($this->registry->admin);
        $this->systemdk_is_user_now = $this->is_user($this->registry->user);
        $this->who_online();
        //$this->modules_account_autodelete();
        //$this->modules_feedback_autodelete();
        //$this->blocks_autocheck();
        //$this->main_pages();
        if($this->systemdk_is_admin_now) {
            $this->registry->main_class->assign("admin","yes");
        } else {
            $this->registry->main_class->assign("admin","no");
        }
        $this->registry->main_class->assign("admin_folder",ADMIN_DIR);
        if(!defined('__SITE_ADMIN_PART')) {
            $this->shop_header();
        }
        if(defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
            $this->index_admin();
        }
    }


    private function index_admin() {
        if((isset($_POST['admin_login'])) and (isset($_POST['admin_password'])) and trim($_POST['admin_login']) != "" and trim($_POST['admin_password']) != "" and $_POST['func'] == "mainadmin") {
            if(preg_match("/[^a-zA-Zà-ÿÀ-ß0-9_-]/",trim($_POST['admin_login']))) {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                $this->registry->main_class->assign("error","error_login");
                $this->registry->main_class->display_theme_adminheader(true);
                $this->database_close();
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
                if(isset($_SESSION['systemdk_if_admin'])) {
                    unset($_SESSION['systemdk_if_admin']);
                }
                //session_unset();
                //setcookie(session_name(), '', time()-3600, '/');
                //session_destroy();
                $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|login|".$this->registry->language);
                exit();
            }
            $this->admin_login = substr(trim($_POST['admin_login']),0,25);
            $this->admin_login = $this->format_striptags($this->admin_login);
            $this->admin_login = $this->processing_data($this->admin_login);
            $admin_password = substr(trim($_POST['admin_password']),0,25);
            if(ENTER_CHECK == "yes") {
                if(session_id() == "") {
                    session_start();
                }
                if(extension_loaded("gd") and ((isset($_SESSION["captcha"]) and $_SESSION["captcha"] !== $this->format_striptags($_POST["captcha"])) or (!isset($_SESSION["captcha"])))) {
                    if(isset($_SESSION["captcha"])) {
                        unset($_SESSION["captcha"]);
                    }
                    if(isset($_SESSION['systemdk_if_admin'])) {
                        unset($_SESSION['systemdk_if_admin']);
                    }
                    //session_unset();
                    //setcookie(session_name(), '', time()-3600, '/');
                    //session_destroy();
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                    $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                    $this->registry->main_class->assign("error","error_checkkode");
                    $this->registry->main_class->display_theme_adminheader(true);
                    $this->database_close();
                    $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|checkkode|".$this->registry->language);
                    exit();
                }
                if(isset($_SESSION["captcha"])) {
                    unset($_SESSION["captcha"]);
                }
            }
            $admin_password = $this->format_striptags($admin_password);
            $admin_password = $this->processing_data($admin_password);
            $admin_password = substr(substr($admin_password,0,-1),1);
            $admin_password = md5($admin_password);
            $sql = "SELECT admin_id, admin_name, admin_surname, admin_login, admin_password, admin_active, admin_rights FROM ".PREFIX."_admins WHERE admin_login=".$this->admin_login;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            }
            if(isset($numrows) and $numrows > 0) {
                if($this->extracting_data($result->fields['4']) == $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) {
                    $this->admin_login = substr(substr($this->admin_login,0,-1),1);
                    $this->admin_login = $this->extracting_data($this->admin_login);
                    $admin_cookie = base64_encode($this->admin_login.":".$admin_password);
                    setcookie("admin_cookie",$admin_cookie,time() + COOKIE_LIVE,"/".SITE_DIR,$this->registry->http_host);
                    if(session_id() == "") {
                        session_start();
                    }
                    $_SESSION['systemdk_if_admin'] = 'yes';
                    $this->admin_id = intval($result->fields['0']);
                    $this->admin_name = $this->format_htmlspecchars($this->extracting_data($result->fields['1']));
                    $admin_surname = $this->format_htmlspecchars($this->extracting_data($result->fields['2']));
                    if((!isset($this->admin_name) or $this->admin_name == "") and (isset($admin_surname) and $admin_surname != "")) {
                        $this->admin_name = $admin_surname;
                    } elseif((!isset($this->admin_name) or $this->admin_name == "") and (!isset($admin_surname) or $admin_surname == "")) {
                        $this->admin_name = $this->extracting_data($result->fields['3']);
                    } elseif((isset($this->admin_name) and $this->admin_name != "") and (isset($admin_surname) and $admin_surname != "")) {
                        $this->admin_name = $this->admin_name." ".$admin_surname;
                    }
                    $this->admin_rights = $this->extracting_data($result->fields['6']);
                    $this->adminnow = 1;
                } elseif($this->extracting_data($result->fields['4']) == $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0) {
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                    $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                    $this->registry->main_class->assign("error","error_adminnotactive");
                    $this->registry->main_class->display_theme_adminheader(true);
                    $this->database_close();
                    if(session_id() == "") {
                        session_start();
                    }
                    if(isset($_SESSION['systemdk_if_admin'])) {
                        unset($_SESSION['systemdk_if_admin']);
                    }
                    //session_unset();
                    //setcookie(session_name(), '', time()-3600, '/');
                    //session_destroy();
                    $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminnotactive|".$this->registry->language);
                    exit();
                } elseif(($this->extracting_data($result->fields['4']) != $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) or ($this->extracting_data($result->fields['4']) != $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0)) {
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                    $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                    $this->registry->main_class->assign("error","error_adminincorrectpass");
                    $this->registry->main_class->display_theme_adminheader(true);
                    $this->database_close();
                    if(session_id() == "") {
                        session_start();
                    }
                    if(isset($_SESSION['systemdk_if_admin'])) {
                        unset($_SESSION['systemdk_if_admin']);
                    }
                    //session_unset();
                    //setcookie(session_name(), '', time()-3600, '/');
                    //session_destroy();
                    $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminincorrectpass|".$this->registry->language);
                    exit();
                }
            } else {
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                $this->registry->main_class->assign("error","error_adminnotfind");
                $this->registry->main_class->display_theme_adminheader(true);
                $this->database_close();
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION['systemdk_if_admin'])) {
                    unset($_SESSION['systemdk_if_admin']);
                }
                //session_unset();
                //setcookie(session_name(), '', time()-3600, '/');
                //session_destroy();
                $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminnotfind|".$this->registry->language);
                exit();
            }
        } elseif((isset($_POST['admin_login'])) or (isset($_POST['admin_password'])) and $_POST['func'] == "mainadmin") {
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
            $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
            $this->registry->main_class->assign("error","error_adminnotalldata");
            $this->registry->main_class->display_theme_adminheader(true);
            $this->database_close();
            if(session_id() == "") {
                session_start();
            }
            if(isset($_SESSION["captcha"])) {
                unset($_SESSION["captcha"]);
            }
            if(isset($_SESSION['systemdk_if_admin'])) {
                unset($_SESSION['systemdk_if_admin']);
            }
            //session_unset();
            //setcookie(session_name(), '', time()-3600, '/');
            //session_destroy();
            $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminnotalldata|".$this->registry->language);
            exit();
        } elseif(isset($this->registry->admin) and $this->registry->admin != "" and !isset($_POST['admin_login']) and !isset($_POST['admin_password'])) {
            $admin_cookie = base64_decode($this->registry->admin);
            $admin_cookie = explode(":",$admin_cookie);
            $this->admin_login = "{$admin_cookie[0]}";
            $admin_password = "{$admin_cookie[1]}";
            if($this->admin_login == "" or $admin_password == "") {
                $this->database_close();
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION['systemdk_if_admin'])) {
                    unset($_SESSION['systemdk_if_admin']);
                }
                //session_unset();
                //setcookie(session_name(), '', time()-3600, '/');
                //session_destroy();
                if(SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                } else {
                    header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                }
                exit();
            }
            $this->admin_login = substr($this->admin_login,0,25);
            $this->admin_login = $this->format_striptags($this->admin_login);
            $this->admin_login = $this->processing_data($this->admin_login);
            $admin_password = substr($admin_password,0,40);
            $admin_password = $this->format_striptags($admin_password);
            $admin_password = $this->processing_data($admin_password);
            $admin_password = substr(substr($admin_password,0,-1),1);
            $sql = "SELECT admin_id, admin_name, admin_surname, admin_login, admin_password, admin_active, admin_rights FROM ".PREFIX."_admins WHERE admin_login=".$this->admin_login;
            $result = $this->db->Execute($sql);
            if($result) {
                if(isset($result->fields['0'])) {
                    $numrows = intval($result->fields['0']);
                } else {
                    $numrows = 0;
                }
            }
            if(isset($numrows) and $numrows > 0) {
                if($this->extracting_data($result->fields['4']) == $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) {
                    if(session_id() == "") {
                        session_start();
                    }
                    $_SESSION['systemdk_if_admin'] = 'yes';
                    $this->admin_login = $this->extracting_data($result->fields['3']);
                    $this->admin_id = intval($result->fields['0']);
                    $this->admin_name = $this->format_htmlspecchars($this->extracting_data($result->fields['1']));
                    $admin_surname = $this->format_htmlspecchars($this->extracting_data($result->fields['2']));
                    if((!isset($this->admin_name) or $this->admin_name == "") and (isset($admin_surname) and $admin_surname != "")) {
                        $this->admin_name = $admin_surname;
                    } elseif((!isset($this->admin_name) or $this->admin_name == "") and (!isset($admin_surname) or $admin_surname == "")) {
                        $this->admin_name = $this->extracting_data($result->fields['3']);
                    } elseif((isset($this->admin_name) and $this->admin_name != "") and (isset($admin_surname) and $admin_surname != "")) {
                        $this->admin_name = $this->admin_name." ".$admin_surname;
                    }
                    $this->admin_rights = $this->extracting_data($result->fields['6']);
                    $this->adminnow = 1;
                } elseif($this->extracting_data($result->fields['4']) == $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0) {
                    if(isset($_COOKIE['admin_cookie'])) {
                        setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                    }
                    if(session_id() == "") {
                        session_start();
                    }
                    if(isset($_SESSION['systemdk_if_admin'])) {
                        unset($_SESSION['systemdk_if_admin']);
                    }
                    //session_unset();
                    //setcookie(session_name(), '', time()-3600, '/');
                    //session_destroy();
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                    $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                    $this->registry->main_class->assign("error","error_adminnotactive");
                    $this->registry->main_class->display_theme_adminheader(true);
                    $this->database_close();
                    $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminnotactive|".$this->registry->language);
                    exit();
                } elseif(($this->extracting_data($result->fields['4']) != $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 1) or ($this->extracting_data($result->fields['4']) != $admin_password and $this->extracting_data($result->fields['4']) != "" and intval($result->fields['5']) == 0)) {
                    if(isset($_COOKIE['admin_cookie'])) {
                        setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                    }
                    if(session_id() == "") {
                        session_start();
                    }
                    if(isset($_SESSION['systemdk_if_admin'])) {
                        unset($_SESSION['systemdk_if_admin']);
                    }
                    //session_unset();
                    //setcookie(session_name(), '', time()-3600, '/');
                    //session_destroy();
                    $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                    $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                    $this->registry->main_class->assign("error","error_adminincorrectpass");
                    $this->registry->main_class->display_theme_adminheader(true);
                    $this->database_close();
                    $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminincorrectpass|".$this->registry->language);
                    exit();
                }
            } else {
                if(isset($_COOKIE['admin_cookie'])) {
                    setcookie("admin_cookie","",time(),"/".SITE_DIR,$this->registry->http_host);
                }
                if(session_id() == "") {
                    session_start();
                }
                if(isset($_SESSION['systemdk_if_admin'])) {
                    unset($_SESSION['systemdk_if_admin']);
                }
                //session_unset();
                //setcookie(session_name(), '', time()-3600, '/');
                //session_destroy();
                $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
                $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
                $this->registry->main_class->assign("error","error_adminnotfind");
                $this->registry->main_class->display_theme_adminheader(true);
                $this->database_close();
                $this->registry->main_class->display("systemadmin/error.html",$this->registry->sitelang."|systemadmin|error|adminnotfind|".$this->registry->language);
                exit();
            }
        }
    }


    public function db_connect() {
        return $this->db->check_connect();
    }


    public function qstr($string,$magic_quotes) {
        return $this->db->qstr($string,$magic_quotes);
    }


    private function check_can_gzip() {
        if(headers_sent() or connection_aborted() or !function_exists('ob_gzhandler') or ini_get('zlib.output_compression') or GZIP !== 'yes') {
            return 0;
        }
        if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'x-gzip') !== false)
            return "x-gzip";
        if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false)
            return "gzip";
        return 0;
    }


    public function gzip_out($level,$debug) {
        $encoding = $this->check_can_gzip();
        if($encoding) {
            $contents = ob_get_contents();
            ob_end_clean();
            if($debug) {
                echo("\n<!-- Not compress length: ".strlen($contents)." -->\n");
                echo("\n<!-- Compressed length: ".strlen(gzcompress($contents,$level))." -->\n");
            }
            header("Content-Encoding: $encoding");
            print "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $contents = gzcompress($contents,$level);
            $contents = substr($contents,0,strlen($contents) - 4);
            print $contents;
        } else {
            ob_end_flush();
        }
        //exit();
    }


    public function systemdk_is_admin() {
        return $this->adminnow;
    }


    public function get_admin_id() {
        return $this->admin_id;
    }


    public function get_admin_rights() {
        return $this->admin_rights;
    }


    public function get_admin_login() {
        return $this->admin_login;
    }


    public function displayadmininfo() {
        if(isset($this->admin_name) and isset($this->admin_id)) {
            $this->registry->main_class->assign("admin_name",$this->admin_name);
            $this->registry->main_class->assign("admin_id",$this->admin_id);
        }
    }


    public function load_controller_error($error,$admin_part,$method) {
        if($admin_part) {
            $this->registry->main_class->display_theme_adminheader();
            $this->registry->main_class->display_theme_adminmain();
            $this->displayadmininfo();
            $this->registry->main_class->assign("admin_class",$error);
            if($method) {
                $this->registry->main_class->assign("admin_method",$method);
                $cache = 'nomethod';
            } else {
                $cache = 'nomodule';
            }
            if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|".$cache."|".$this->registry->language)) {
                $this->registry->main_class->assign("admin_edit",$cache);
                $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
                $this->registry->main_class->assign("include_center","systemadmin/admin_update.html");
            }
            $this->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|error|".$cache."|".$this->registry->language);
            exit();
        } else {
            $this->database_close();
            $this->registry->main_class->assign("error","error_".$error);
            $this->registry->main_class->assign("include_center","error.html");
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|error|".$error."|".$this->registry->language);
            exit();
        }
    }


    public function path($controller) {
        $sql = "SELECT module_customname, module_status, module_valueview, (SELECT max(mainmenu_name) FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$controller."' OR mainmenu_submodule='".$controller."') as mainmenu_name,module_id FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_name='".$controller."'";
        $result = $this->db->Execute($sql);
        return $result;
    }


    public function blocks_autocheck() {
        $now = $this->gettime();
        $this->db->Execute("UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_status = '0', block_termexpire = NULL WHERE block_termexpire IS NOT NULL and block_termexpire <= ".$now." and block_termexpireaction = 'off'");
        $num_result1 = $this->db->Affected_Rows();
        $this->db->Execute("DELETE FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_termexpire IS NOT NULL and block_termexpire <= ".$now." and block_termexpireaction = 'delete'");
        $num_result2 = $this->db->Affected_Rows();
        if($num_result1 > 0 or $num_result2 > 0) {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|blocks|edit");
        }
    }


    public function modules_account_autodelete() {
        if(defined('SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS') and intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) != 0) {
            $systemdk_account_maxactivationdays = $this->gettime() - (intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS) * 86400);
            $result = $this->db->Execute("DELETE FROM ".PREFIX."_users_temp WHERE user_regdate < ".$systemdk_account_maxactivationdays);
            if($result) {
                $num_result = $this->db->Affected_Rows();
                if($num_result > 0) {
                    $this->systemdk_clearcache("systemadmin|modules|users|showtemp");
                }
            }
        }
    }


    public function systemdk_clearcache($clearcachepath) {
        $clear_langlist = $this->registry->main_class->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
        for($i = 0,$size = sizeof($clear_langlist);$i < $size;++$i) {
            $this->registry->main_class->clearCache(null,$clear_langlist[$i]['name']."|".$clearcachepath);
        }
    }


    public function modules_feedback_autodelete() {
        $systemdk_feedback_maxsavehistdays = $this->gettime() - 86400;
        $result = $this->db->Execute("DELETE FROM ".PREFIX."_mail_sendlog WHERE mail_module = 'feedback' and mail_date < ".$systemdk_feedback_maxsavehistdays);
    }


    public function modules_news_programm() {
        $sql = "SELECT news_id FROM ".PREFIX."_news_auto_".$this->registry->sitelang." WHERE news_date <= ".$this->gettime();
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                $this->db->StartTrans();
                while(!$result->EOF) {
                    $news_id = intval($result->fields['0']);
                    $news_add_id = $this->db->GenID(PREFIX."_news_id_".$this->registry->sitelang);
                    $sql2 = "INSERT INTO ".PREFIX."_news_".$this->registry->sitelang."(news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_readcounter,news_notes,news_valueview,news_status,news_termexpire,news_termexpireaction,news_onhome,news_link) SELECT "."'".$news_add_id."'"." as news_id,news_author,news_category_id,news_title,news_date,news_images,news_short_text,news_content,news_readcounter,news_notes,news_valueview,news_status,news_termexpire,news_termexpireaction,news_onhome,news_link FROM ".PREFIX."_news_auto_".$this->registry->sitelang." WHERE news_id = ".$news_id;
                    $result2 = $this->db->Execute($sql2);
                    $result->MoveNext();
                }
                $sql3 = "DELETE FROM ".PREFIX."_news_auto_".$this->registry->sitelang." WHERE news_date <= ".$this->gettime();
                $result3 = $this->db->Execute($sql3);
                $this->db->CompleteTrans();
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showprogramm");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|editprogramm");
                $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
            }
        }
        $sql2 = "UPDATE ".PREFIX."_news_".$this->registry->sitelang." SET news_status='0', news_termexpire=NULL, news_termexpireaction=NULL WHERE news_termexpire <= '".$this->gettime()."' AND news_termexpireaction = 'off'";
        $this->db->Execute($sql2);
        $num_result2 = $this->db->Affected_Rows();
        $sql3 = "DELETE FROM ".PREFIX."_news_".$this->registry->sitelang." WHERE news_termexpire <= '".$this->gettime()."' AND news_termexpireaction = 'delete'";
        $this->db->Execute($sql3);
        $num_result3 = $this->db->Affected_Rows();
        if($num_result2 > 0 or $num_result3 > 0) {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|news|newsread");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|showcurrent");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|news|edit");
        }
    }


    public function database_close() {
        if(DB_PERSISTENCY === 'no') {
            $this->db->Close();
        }
        if(isset($GLOBALS['EXECS'])) {
            $execs = $GLOBALS['EXECS'];
        } else {
            $execs = 0;
        }
        if(isset($GLOBALS['CACHED'])) {
            $cached = $GLOBALS['CACHED'];
        } else {
            $cached = 0;
        }
        $sqlstat = $execs + $cached;
        $this->registry->main_class->assign("sqlstat",$sqlstat);
        $this->registry->main_class->assign("sqlstatcached",$cached);
    }


    public function check_db_need_lobs() {
        if(DBTYPE == 'oci8' or DBTYPE == 'oci805' or DBTYPE == 'oci8po' or DBTYPE == 'odbc_oracle' or DBTYPE == 'oracle') {
            return 'yes';
        } else {
            return 'no';
        }
    }


    public function check_db_need_from_clause() {
        if(DBTYPE == 'mssql' or DBTYPE == 'ado_mssql' or DBTYPE == 'odbc_mssql') {
            return false;
        } else {
            return 'FROM DUAL';
        }
    }


    public function check_email($email) {
        return filter_var($email,FILTER_VALIDATE_EMAIL);
    }


    public function encode_rss_text($text,$rss_encoding) {
        if($rss_encoding == "none" or ($rss_encoding != "UTF-8" and $rss_encoding != "WINDOWS-1251" and $rss_encoding != strtoupper(SITE_CHARSET))) {
            $rss_encoding = @mb_detect_encoding($text,"UTF-8, WINDOWS-1251",true);
        }
        if(!isset($rss_encoding) or $rss_encoding == "" or $rss_encoding === false) {
            $rss_encoding = strtoupper(SITE_CHARSET);
        }
        $trans_t = get_html_translation_table(HTML_ENTITIES);
        $trans_t = array_flip($trans_t);
        return strtr(mb_convert_encoding($this->format_striptags($text),SITE_CHARSET,$rss_encoding),$trans_t);
    }


    public function processing_data($processing_content,$need = 'no') {
        if($need == 'need') {
            $processing_content = $this->db->qstr($processing_content,0);
        } else {
            $processing_content = $this->db->qstr($processing_content,get_magic_quotes_gpc());
        }
        return $processing_content;
    }


    public function extracting_data($extracting_content) {
        $extracting_content = stripslashes(trim($extracting_content));
        if(DBTYPE == 'oci8' or DBTYPE == 'oci805' or DBTYPE == 'oci8po' or DBTYPE == 'odbc_oracle' or DBTYPE == 'oracle' or DBTYPE == 'mssql' or DBTYPE == 'ado_mssql' or DBTYPE == 'odbc_mssql') {
            $extracting_content = str_replace("''","'",$extracting_content);
        }
        return $extracting_content;
    }


    public function extracting_data2($extracting_content) {
        $extracting_content = stripcslashes(trim($extracting_content));
        if(DBTYPE == 'oci8' or DBTYPE == 'oci805' or DBTYPE == 'oci8po' or DBTYPE == 'odbc_oracle' or DBTYPE == 'oracle' or DBTYPE == 'mssql' or DBTYPE == 'ado_mssql' or DBTYPE == 'odbc_mssql') {
            $extracting_content = str_replace("''","'",$extracting_content);
        }
        return $extracting_content;
    }


    public function checkneed_stripslashes($stripslashes_content) {
        if(!get_magic_quotes_gpc()) {
            $stripslashes_content = $stripslashes_content;
        } else {
            $stripslashes_content = stripslashes($stripslashes_content);
        }
        return $stripslashes_content;
    }


    public function format_htmlspecchars($format_content) {
        $format_content = htmlspecialchars($format_content,ENT_QUOTES,SITE_CHARSET);
        return $format_content;
    }


    public function format_striptags($format_content,$allowable_tags = 'no') {
        if($allowable_tags == 'no') {
            $format_content = strip_tags($format_content);
        } else {
            $format_content = strip_tags($format_content,$allowable_tags);
        }
        return $format_content;
    }


    public function format_htmlspecchars_stripslashes_striptags($format_content) {
        $format_content = $this->format_htmlspecchars($this->checkneed_stripslashes($this->format_striptags($format_content)));
        return $format_content;
    }


    public function search_player_entry($content) {
        $this->content = $content;
        if($this->check_player_entry($this->content)) {
            $this->registry->main_class->assign("systemdk_display_player","yes");
        }
        return $this->content;
    }


    public function check_player_entry($content) {
        $this->content = $content;
        if(defined('SYSTEMDK_PLAYER_RATIO') and SYSTEMDK_PLAYER_RATIO !== '') {
            $this->content = str_ireplace('class="flowplayer','data-ratio="'.$this->float2db(SYSTEMDK_PLAYER_RATIO).'" class="flowplayer',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_CONTROLS') and SYSTEMDK_PLAYER_CONTROLS === 'yes') {
            $this->content = str_ireplace('class="flowplayer','class="flowplayer fixed-controls',$this->content);
            $this->content = str_ireplace('<video','<video controls',$this->content);
        } else {
            $this->content = str_ireplace('="flowplayer','="flowplayer',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_AUTOPLAY') and SYSTEMDK_PLAYER_AUTOPLAY === 'yes') {
            $this->content = str_ireplace('<video','<video autoplay',$this->content);
        } else {
            $this->content = str_ireplace('<video','<video',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_AUTOBUFFER') and SYSTEMDK_PLAYER_AUTOBUFFER === 'yes') {
            $this->content = str_ireplace('<video','<video preload',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_AUDIOAUTOPLAY') and SYSTEMDK_PLAYER_AUDIOAUTOPLAY === 'yes') {
            $this->content = str_ireplace('<audio','<audio autoplay',$this->content);
        } else {
            $this->content = str_ireplace('<audio','<audio',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_AUDIOAUTOBUFFER') and SYSTEMDK_PLAYER_AUDIOAUTOBUFFER === 'yes') {
            $this->content = str_ireplace('<audio','<audio preload',$this->content);
        }
        if(defined('SYSTEMDK_PLAYER_AUDIOCONTROLS') and SYSTEMDK_PLAYER_AUDIOCONTROLS === 'yes') {
            $this->content = str_ireplace('<audio','<audio controls',$this->content);
        }
        $search_flowplayer = substr_count($this->content,'="flowplayer');
        $search_html5_video = substr_count($this->content,'<video');
        $search_html5_audio = substr_count($this->content,'<audio');
        if($search_flowplayer > 0 or $search_html5_video > 0 or $search_html5_audio > 0) {
            $this->registry->main_class->assign("systemdk_html5_exist","yes");
        }
        if($search_flowplayer > 0) {
            return 1;
        } else {
            return 0;
        }
    }


    public function formatsize($size) {
        if($size >= 1073741824) {
            $size = round($size / 1073741824 * 100) / 100;
            $size = $size." Gb";
        } elseif($size >= 1048576) {
            $size = round($size / 1048576 * 100) / 100;
            $size = $size." Mb";
        } elseif($size >= 1024) {
            $size = round($size / 1024 * 100) / 100;
            $size = $size." Kb";
        } else {
            $size = $size." b";
        }
        return $size;
    }


    public function set_sitemeta($title = 'no',$description = 'no',$keywords = 'no') {
        if($title != 'no' and trim($title) != '') {
            $title = $this->format_htmlspecchars($this->format_striptags($title));
            $this->registry->main_class->assign("site_name",$title." &raquo; ".SITE_NAME);
        }
        if($description != 'no' and trim($description) != '') {
            $description = $this->format_htmlspecchars($this->format_striptags($description));
            $this->registry->main_class->assign("site_description",$description);
        }
        if($keywords != 'no' and trim($keywords) != '') {
            $keywords = $this->format_htmlspecchars($this->format_striptags($keywords));
            $this->registry->main_class->assign("site_keywords",$keywords);
        }
    }


    public function set_locale() {
        setlocale(LC_ALL,'ru_RU.UTF-8');
    }


    public function float2db($float_value) {
        $float_value = (preg_replace("/,/",".",$float_value));
        $float_value = (preg_replace('/\s+/','',$float_value));
        $float_value = floatval($float_value);
        $locale_info = localeconv();
        $locale_search = array(
            $locale_info['decimal_point'],
            $locale_info['mon_decimal_point'],
            $locale_info['thousands_sep'],
            $locale_info['mon_thousands_sep'],
            $locale_info['currency_symbol'],
            $locale_info['int_curr_symbol']
        );
        $locale_replace = array('.','.','','','','');
        return str_replace($locale_search,$locale_replace,$float_value);
    }


    public function shop_header() {
        if($this->module_is_active('shop')) {
            if(session_id() == "") {
                session_start();
            }
            $systemdk_shop_cart = 'systemdk_shop_cart_'.$this->registry->sitelang;
            $systemdk_shop_items = 'systemdk_shop_items_'.$this->registry->sitelang;
            $systemdk_shop_total_price = 'systemdk_shop_total_price_'.$this->registry->sitelang;
            if((isset($_GET['path']) and isset($_GET['func']) and $this->format_striptags(trim($_GET['path'])) == "shop" and $this->format_striptags(trim($_GET['func'])) == "cart") or (isset($_POST['path']) and isset($_POST['func']) and $this->format_striptags(trim($_POST['path'])) == "shop" and $this->format_striptags(trim($_POST['func'])) == "cart")) {
                if(isset($_POST['shop_new']) and intval($_POST['shop_new']) != 0) {
                    $systemdk_shop_new_item = intval($_POST['shop_new']);
                    if(!isset($_SESSION[$systemdk_shop_cart])) {
                        $_SESSION[$systemdk_shop_cart] = array();
                        $_SESSION[$systemdk_shop_items] = 0;
                        $_SESSION[$systemdk_shop_total_price] = 0.00;
                    }
                    if(isset($_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item])) {
                        $_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item]++;
                    } else {
                        $_SESSION[$systemdk_shop_cart][$systemdk_shop_new_item] = 1;
                    }
                    $calculate_result = $this->shop_calculate_price($_SESSION[$systemdk_shop_cart]);
                    $_SESSION[$systemdk_shop_total_price] = $calculate_result['2'];
                    $_SESSION[$systemdk_shop_items] = $calculate_result['1'];
                }
                if(isset($_POST['shop_save'])) {
                    foreach($_SESSION[$systemdk_shop_cart] as $item_id => $qty) {
                        if((isset($_POST[$item_id]) and intval($_POST[$item_id]) == 0) or !isset($_POST[$item_id]) or intval($_POST[$item_id]) == 0) {
                            unset($_SESSION[$systemdk_shop_cart][$item_id]);
                        } else {
                            $_SESSION[$systemdk_shop_cart][$item_id] = $_POST[$item_id];
                        }
                    }
                    $calculate_result = $this->shop_calculate_price($_SESSION[$systemdk_shop_cart]);
                    $_SESSION[$systemdk_shop_total_price] = $calculate_result['2'];
                    $_SESSION[$systemdk_shop_items] = $calculate_result['1'];
                }
            }
            if(!isset($_SESSION[$systemdk_shop_items])) {
                $_SESSION[$systemdk_shop_items] = 0;
            }
            if(!isset($_SESSION[$systemdk_shop_total_price])) {
                $_SESSION[$systemdk_shop_total_price] = 0.00;
            }
            $this->registry->main_class->assign("shop_carttotal_items",$_SESSION[$systemdk_shop_items]);
            $this->registry->main_class->assign("shop_carttotal_price",number_format($_SESSION[$systemdk_shop_total_price],2,'.',' '));
            if($this->is_admin_in_mainfunc()) {
                $this->registry->main_class->assign("shop_carttotal_display","no");
            } else {
                $this->registry->main_class->assign("shop_carttotal_display","yes");
            }
        }
    }


    private function shop_calculate_price($cart = 0) {
        $systemdk_shop_items = 0;
        $systemdk_shop_price = 0.0;
        if($cart != 0 and is_array($cart) and count($cart) > 0) {
            $i = 0;
            $item_id = null;
            foreach($cart as $cart_item_id => $qty) {
                if(isset($cart_item_id) and intval($cart_item_id) != 0) {
                    if($i == 0) {
                        $item_id = "'".intval($cart_item_id)."'";
                    } else {
                        $item_id .= ",'".intval($cart_item_id)."'";
                    }
                    $i++;
                }
            }
            if(isset($item_id)) {
                $from = "FROM ".PREFIX."_items_".$this->registry->sitelang." a LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." c ON a.item_catalog_id = c.catalog_id and a.item_catalog_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." d ON a.item_category_id = d.category_id and a.item_category_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." f ON d.cat_catalog_id=f.catalog_id and a.item_category_id is NOT NULL "."LEFT OUTER JOIN ".PREFIX."_subcategories_".$this->registry->sitelang." e ON a.item_subcategory_id = e.subcategory_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_categories_".$this->registry->sitelang." g ON e.subcategory_cat_id=g.category_id and a.item_subcategory_id is NOT NULL LEFT OUTER JOIN ".PREFIX."_catalogs_".$this->registry->sitelang." h ON g.cat_catalog_id=h.catalog_id and a.item_subcategory_id is NOT NULL WHERE a.item_id IN (".$item_id.") and ((a.item_catalog_id is NULL and a.item_category_id is NULL and a.item_subcategory_id is NULL) or (a.item_catalog_id is NOT NULL) or (a.item_category_id is NOT NULL) or (a.item_subcategory_id is NOT NULL)) and (c.catalog_show is NULL or c.catalog_show = '1') and (d.category_show is NULL or d.category_show = '1') and (f.catalog_show is NULL or f.catalog_show = '1') and (e.subcategory_show is NULL or e.subcategory_show = '1') and (g.category_show is NULL or g.category_show = '1') and (h.catalog_show is NULL or h.catalog_show = '1')";
                $sql = "SELECT a.item_id,a.item_price,a.item_show,a.item_price_discounted ".$from;
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        while(!$result->EOF) {
                            $item_id = intval($result->fields['0']);
                            $systemdk_shop_item_price = floatval($result->fields['1']);
                            $systemdk_shop_item_price_discounted = floatval($result->fields['3']);
                            if(isset($cart[$item_id])) {
                                $item_qty = $cart[$item_id];
                            } else {
                                $item_qty = 0;
                            }
                            if($systemdk_shop_item_price_discounted > 0) {
                                $systemdk_shop_price += $systemdk_shop_item_price_discounted * $item_qty;
                            } else {
                                $systemdk_shop_price += $systemdk_shop_item_price * $item_qty;
                            }
                            $systemdk_shop_items += $item_qty;
                            $result->MoveNext();
                        }
                    }
                }
            }
        }
        return array(1 => $systemdk_shop_items,2 => $systemdk_shop_price);
    }


    public function is_user_in_mainfunc() {
        return $this->systemdk_is_user_now;
    }


    public function is_admin_in_mainfunc() {
        return $this->systemdk_is_admin_now;
    }


    public function is_user($user) {
        if(isset($user) and trim($user) != "" and !is_array($user)) {
            $user = base64_decode($user);
            $user = explode(":",$user);
            if(isset($user[0])) {
                $user_login = $user[0];
            } else {
                $user_login = null;
            }
            if(isset($user[1])) {
                $user_password = $user[1];
            } else {
                $user_password = null;
            }
            if(isset($user_login) and isset($user_password) and $user_login != "" and $user_password != "") {
                $user_login = substr($user_login,0,25);
                $user_login = $this->format_striptags($user_login);
                $user_login = $this->processing_data($user_login);
                $user_password = substr($user_password,0,40);
                $user_password = $this->format_striptags($user_password);
                $user_password = $this->processing_data($user_password);
                $user_password = substr(substr($user_password,0,-1),1);
                $sql = "SELECT user_password, user_active, user_id FROM ".PREFIX."_users WHERE user_login=".$user_login;
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['2'])) {
                        $numrows = intval($result->fields['2']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        $password = $this->extracting_data($result->fields['0']);
                        $active = intval($result->fields['1']);
                        if($password == $user_password and $password != "" and $active == 1) {
                            unset($password);
                            $this->systemdk_is_user_now = 1;
                            return 1;
                        }
                        unset($password);
                    }
                }
                unset($user_password);
            }
        }
        $this->systemdk_is_user_now = 0;
        return 0;
    }


    public function is_admin($admin) {
        if(isset($admin) and trim($admin) != "" and !is_array($admin)) {
            $admin = base64_decode($admin);
            $admin = explode(":",$admin);
            if(isset($admin[0])) {
                $admin_login = $admin[0];
            } else {
                $admin_login = null;
            }
            if(isset($admin[1])) {
                $admin_password = $admin[1];
            } else {
                $admin_password = null;
            }
            if(isset($admin_login) and isset($admin_password) and $admin_login != "" and $admin_password != "") {
                $admin_login = substr($admin_login,0,25);
                $admin_login = $this->format_striptags($admin_login);
                $admin_login = $this->processing_data($admin_login);
                $admin_password = substr($admin_password,0,40);
                $admin_password = $this->format_striptags($admin_password);
                $admin_password = $this->processing_data($admin_password);
                $admin_password = substr(substr($admin_password,0,-1),1);
                $sql = "SELECT admin_password, admin_active, admin_id FROM ".PREFIX."_admins WHERE admin_login=".$admin_login;
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['2'])) {
                        $numrows = intval($result->fields['2']);
                    } else {
                        $numrows = 0;
                    }
                    if($numrows > 0) {
                        $password = $this->extracting_data($result->fields['0']);
                        $active = intval($result->fields['1']);
                        if($password == $admin_password and $password != "" and $active == 1) {
                            unset($password);
                            $this->systemdk_is_admin_now = 1;
                            return 1;
                        }
                        unset($password);
                    }
                }
                unset($admin_password);
            }
        }
        $this->systemdk_is_admin_now = 0;
        return 0;
    }


    public function module_is_active($module) {
        $module = trim($module);
        $module = $this->format_striptags($module);
        $property = 'module_'.$module.'_status';
        if(isset($this->registry->$property)) {
            return $this->registry->$property;
        }
        $module = $this->processing_data($module);
        $sql = "SELECT module_status, module_id FROM ".PREFIX."_modules_".$this->registry->sitelang." WHERE module_name=".$module;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['1'])) {
                $numrows = intval($result->fields['1']);
            } else {
                $numrows = 0;
            }
        }
        if($result and isset($numrows) and $numrows > 0) {
            $status = intval($result->fields['0']);
            if($status == 1) {
                $this->registry->$property = 1;
                return 1;
            } else {
                $this->registry->$property = 0;
                return 0;
            }
        } else {
            $this->registry->$property = 0;
            return 0;
        }
    }


    public function block_is_active($block) {
        $block = trim($block);
        $block = $this->format_striptags($block);
        $block = $this->processing_data($block);
        $sql = "SELECT block_status, block_id FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_name=".$block;
        $result = $this->db->Execute($sql);
        if($result) {
            if(isset($result->fields['1'])) {
                $numrows = intval($result->fields['1']);
            } else {
                $numrows = 0;
            }
        }
        if($result and isset($numrows) and $numrows > 0) {
            $status = intval($result->fields['0']);
            if($status == 1) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }


    private function block_render($block_locate,$block_name,$block_customname,$block_content,$block_id,$block_url) {
        if($block_url == "") {
            if($block_name == "") {
                $block_alldata = array("block_customname" => $block_customname,"block_content" => $block_content);
                return ($block_alldata);
            } else {
                if($block_locate != "") {
                    $block_alldata = $this->block_fileinclude($block_customname,$block_name,$block_locate);
                    return ($block_alldata);
                }
            }
        } else {
            if($block_locate != "") {
                $block_alldata = $this->block_rss($block_id,$block_locate);
                return ($block_alldata);
            }
        }
    }


    public function block($block_locate) {
        $block_locate = trim($block_locate);
        $block_locate = $this->format_striptags($block_locate);
        $block_locate = $this->processing_data($block_locate);
        $block_locate = substr(substr($block_locate,0,-1),1);
        //$blocks_alldata = NULL;
        $sql = "SELECT block_id, block_name, block_customname, block_valueview, block_content, block_url FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_arrangements = '".$block_locate."' AND block_status = '1' ORDER BY block_priority ASC";
        $result_blocks = $this->db->Execute($sql);
        if($result_blocks) {
            if(isset($result_blocks->fields['0'])) {
                $numrows = intval($result_blocks->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if($result_blocks and isset($numrows) and $numrows > 0) {
            while(!$result_blocks->EOF) {
                $block_id = $result_blocks->fields['0'];
                $block_id = intval($block_id);
                $block_name = $this->extracting_data($result_blocks->fields['1']);
                $block_customname = $this->format_htmlspecchars($this->extracting_data($result_blocks->fields['2']));
                $block_valueview = $result_blocks->fields['3'];
                $block_valueview = intval($block_valueview);
                $block_content = $this->extracting_data($result_blocks->fields['4']);
                $block_content = $this->search_player_entry($block_content);
                $block_url = $this->extracting_data($result_blocks->fields['5']);
                if($block_valueview == "0") {
                    $blocks_alldata[] = $this->block_render($block_locate,$block_name,$block_customname,$block_content,$block_id,$block_url);
                } elseif($block_valueview == "1" and ($this->systemdk_is_user_now or $this->systemdk_is_admin_now)) {
                    $blocks_alldata[] = $this->block_render($block_locate,$block_name,$block_customname,$block_content,$block_id,$block_url);
                } elseif($block_valueview == "2" and $this->systemdk_is_admin_now) {
                    $blocks_alldata[] = $this->block_render($block_locate,$block_name,$block_customname,$block_content,$block_id,$block_url);
                } elseif($block_valueview == "3" and (!$this->systemdk_is_user_now or $this->systemdk_is_admin_now)) {
                    $blocks_alldata[] = $this->block_render($block_locate,$block_name,$block_customname,$block_content,$block_id,$block_url);
                }
                $result_blocks->MoveNext();
            }
        }
        if($block_locate == "header") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_headerbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_headerbox("false");
            }
        } elseif($block_locate == "center-up") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_centerupbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_centerupbox("false");
            }
        } elseif($block_locate == "center-down") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_centerdownbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_centerdownbox("false");
            }
        } elseif($block_locate == "left") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_leftbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_leftbox("false");
            }
        } elseif($block_locate == "right") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_rightbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_rightbox("false");
            }
        } elseif($block_locate == "footer") {
            if(isset($blocks_alldata)) {
                $this->registry->main_class->display_theme_footerbox($blocks_alldata);
            } else {
                $this->registry->main_class->display_theme_footerbox("false");
            }
        }
    }


    private function block_fileinclude($block_customname,$block_name,$block_locate) {
        clearstatcache();
        if(!file_exists(__SITE_PATH."/blocks/".$block_name."/".$block_name.".class.php")) {
            $this->registry->main_class->assign("block_error","error_blockfile");
            $block_content = $this->registry->main_class->fetch("block_error.html",$this->registry->sitelang."|error|block_error1|".$this->registry->language);
        } else {
            include_once(__SITE_PATH."/blocks/".$block_name."/".$block_name.".class.php");
            $classname = 'block_'.$block_name.'_model';
            $block_model = new $classname($this->registry);
            $block_content = $block_model->index();
        }
        if(!isset($block_content) or $block_content == "") {
            $this->registry->main_class->assign("block_error","error_blockcontent");
            $block_content = $this->registry->main_class->fetch("block_error.html",$this->registry->sitelang."|error|block_error2|".$this->registry->language);
        }
        $block_alldata = array(
            "block_name" => $block_name,
            "block_customname" => $block_customname,
            "block_content" => $block_content
        );
        return ($block_alldata);
    }


    public function block_rss($block_id,$block_locate) {
        $block_id = intval($block_id);
        $sql = "SELECT block_customname, block_content, block_url, block_refresh, block_lastrefresh, block_id FROM ".PREFIX."_blocks_".$this->registry->sitelang." WHERE block_id='".$block_id."'";
        $result = $this->db->Execute($sql);
        $numrows = null;
        if($result) {
            if(isset($result->fields['5'])) {
                $numrows = intval($result->fields['5']);
            } else {
                $numrows = 0;
            }
        }
        if($result and isset($numrows) and $numrows > 0) {
            $block_customname = $this->format_htmlspecchars($this->extracting_data($result->fields['0']));
            $block_content = $this->extracting_data($result->fields['1']);
            $block_url = $this->extracting_data($result->fields['2']);
            $block_refresh = intval($result->fields['3']);
            $block_lastrefresh = intval($result->fields['4']);
            $past = $this->gettime() - $block_refresh;
            if($block_lastrefresh < $past) {
                $now = $this->gettime();
                $url = @parse_url($block_url);
                $fp = @fsockopen($url['host'],80,$errno,$errstr,20);
                if(!$fp) {
                    $this->registry->main_class->assign("rsslink","no");
                    $this->registry->main_class->assign("url",$block_url);
                    $this->registry->main_class->assign("language",$this->registry->language);
                    $cont = $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
                    $block_content = $this->processing_data($cont);
                    $block_content = substr(substr($block_content,0,-1),1);
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_content='".$block_content."', block_lastrefresh='".$now."' WHERE block_id='".$block_id."'";
                    $this->db->Execute($sql);
                    $context = 0;
                    $block_content = $this->extracting_data2($block_content);
                    //$block_customname = $this->format_htmlspecchars($this->extracting_data($block_customname));
                    $rssurl = preg_replace("/http:\/\//i","",$block_url);
                    $rssurl = explode("/",$rssurl);
                    $this->registry->main_class->assign("rsslink","addline");
                    $this->registry->main_class->assign("rssurl",$rssurl[0]);
                    $block_content .= $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
                    $block_alldata = array("block_customname" => $block_customname,"block_content" => $block_content);
                    return ($block_alldata);
                }
                if($fp) {
                    @fputs($fp,"GET ".$url['path']."?".$url['query']." HTTP/1.0\r\n");
                    @fputs($fp,"HOST: ".$url['host']."\r\n\r\n");
                    $line = "";
                    while(!feof($fp)) {
                        $text = @fgets($fp,300);
                        $line .= @chop($text);
                        if(!isset($rss_encoding) and preg_match("/encoding/i",$text)) {
                            $rss_encoding = explode("ENCODING=",strtoupper($text));
                            $rss_encoding = $rss_encoding[1];
                            $rss_encoding1 = explode('"',$rss_encoding);
                            if(!isset($rss_encoding1[1])) {
                                $rss_encoding = explode("'",$rss_encoding);
                            } else {
                                $rss_encoding = $rss_encoding1;
                            }
                            $rss_encoding = $this->format_striptags($rss_encoding[1]);
                        }
                    }
                    @fputs($fp,"Connection: close\r\n\r\n");
                    @fclose($fp);
                    if(!isset($rss_encoding) or $rss_encoding == "") {
                        $rss_encoding = "none";
                    }
                    $item = explode("</item>",$line);
                    $block_content = "";
                    for($i = 0;$i < 8;$i++) {
                        $item_link = @preg_replace('/.*<link>/i','',$item[$i]);
                        $item_link = @preg_replace('/<\/link>.*/i','',$item_link);
                        $item_link = @str_ireplace('<![CDATA[','',$item_link);
                        $item_link = @str_ireplace(']]>','',$item_link);
                        $item_link = @str_ireplace('&','&amp;',$item_link);
                        $item_title = @preg_replace('/.*<title>/i','',$item[$i]);
                        $item_title = @preg_replace('/<\/title>.*/i','',$item_title);
                        $item_title = @str_ireplace('<![CDATA[','',$item_title);
                        $item_title = @str_ireplace(']]>','',$item_title);
                        $item_description = @preg_replace('/.*<description>/i','',$item[$i]);
                        $item_description = @preg_replace('/<\/description>.*/i','',$item_description);
                        $item_description = @str_ireplace('<![CDATA[','',$item_description);
                        $item_description = @str_ireplace(']]>','',$item_description);
                        $item_image = @preg_replace('/\/>.*/i','>',$item_description);
                        if(strcasecmp($item_description,$item_image) == 0) {
                            $item_image = "";
                        }
                        $item_description = @preg_replace('/.*\/>/i','',$item_description);
                        $item_date = @preg_replace('/.*<pubDate>/i','',$item[$i]);
                        $item_date = @preg_replace('/<\/pubDate>.*/i','',$item_date);
                        $item_date = @strtotime($item_date);
                        if(@$item[$i] == "" and isset($context) and $context != 1) {
                            $block_content = "";
                            $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_content='".$block_content."', block_lastrefresh='".$now."' WHERE block_id='".$block_id."'";
                            $this->db->Execute($sql);
                            $context = 0;
                            $block_alldata = array(
                                "block_customname" => $block_customname,
                                "block_content" => $block_content
                            );
                            return ($block_alldata);
                        } else {
                            if(strcmp($item_link,$item_title) and $item[$i] != "") {
                                $context = 1;
                                $item_title = $this->encode_rss_text($item_title,$rss_encoding);
                                $item_description = $this->encode_rss_text($item_description,$rss_encoding);
                                $item_link = $this->format_striptags($item_link);
                                $item_image = $this->format_striptags($item_image,'<img>');
                                $item_date = $this->format_striptags($item_date);
                                $rsslink = array(
                                    "link" => $item_link,
                                    "title" => $item_title,
                                    "rssdate" => date("d.m.y H:i",$item_date),
                                    "description" => $item_description,
                                    "image" => $item_image
                                );
                                $this->registry->main_class->assign("rsslink",$rsslink);
                                $this->registry->main_class->assign("language",$this->registry->language);
                                $cont = $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
                                $cont = $this->processing_data($cont,'need');
                                $cont = substr(substr($cont,0,-1),1);
                                $block_content .= $cont;
                            }
                        }
                    }
                }
                $checkdb_needlobs = $this->check_db_need_lobs();
                if($checkdb_needlobs == 'yes') {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_content=empty_clob(), block_lastrefresh='".$now."' WHERE block_id='".$block_id."'";
                    $this->db->Execute($sql);
                    $this->db->UpdateClob(PREFIX.'_blocks_'.$this->registry->sitelang,'block_content',$block_content,'block_id='.$block_id);
                } else {
                    $sql = "UPDATE ".PREFIX."_blocks_".$this->registry->sitelang." SET block_content='".$block_content."', block_lastrefresh='".$now."' WHERE block_id='".$block_id."'";
                    $this->db->Execute($sql);
                }
                $block_content = $this->extracting_data2($block_content);
                $block_customname = $this->format_htmlspecchars($this->extracting_data($block_customname));
            }
            $rssurl = preg_replace("/http:\/\//i","",$block_url);
            $rssurl = explode("/",$rssurl);
        } else {
            $block_content = "";
            $block_customname = "rss";
        }
        if((isset($context) and ($context == 1)) or ($block_content != "")) {
            $this->registry->main_class->assign("rsslink","addline");
            $this->registry->main_class->assign("rssurl",$rssurl[0]);
            $this->registry->main_class->assign("language",$this->registry->language);
            $block_content .= $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
        }
        if((isset($context) and ($context == 0)) or ($block_content == "")) {
            $this->registry->main_class->assign("rsslink","error");
            $this->registry->main_class->assign("language",$this->registry->language);
            $block_content = $this->registry->main_class->fetch("systemadmin/modules/blocks/blockrss.html");
        }
        $block_alldata = array("block_customname" => $block_customname,"block_content" => $block_content);
        return ($block_alldata);
    }


    public function messages() {
        $sql = "SELECT message_id,message_title,message_content,message_notes,message_date,message_termexpire,message_valueview FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_status='1' order by message_date DESC";
        $result_messages = $this->db->Execute($sql);
        if($result_messages) {
            if(isset($result_messages->fields['0'])) {
                $numrows = intval($result_messages->fields['0']);
            } else {
                $numrows = 0;
            }
        }
        if($result_messages and isset($numrows) and $numrows > 0) {
            while(!$result_messages->EOF) {
                $message_id = intval($result_messages->fields['0']);
                $message_title = $this->format_htmlspecchars($this->extracting_data($result_messages->fields['1']));
                $message_content = $this->extracting_data($result_messages->fields['2']);
                $message_notes = $this->extracting_data($result_messages->fields['3']);
                if(!isset($message_notes) or trim($message_notes) == "") {
                    $message_notes = "no";
                }
                $message_date = intval($result_messages->fields['4']);
                $message_termexpire = intval($result_messages->fields['5']);
                $message_valueview = intval($result_messages->fields['6']);
                if($message_termexpire == 0) {
                    $message_termexpire = "unlim";
                    $message_termexpireday = "";
                } else {
                    $message_termexpire = date("d.m.y H:i",$message_termexpire);
                    $message_termexpireday = intval(($message_termexpire - $this->gettime()) / 3600) / 24;
                    $message_termexpireday = substr($message_termexpireday,0,1);
                }
                if($message_valueview == 0) {
                    $message_alldata[] = array(
                        "message_id" => $message_id,
                        "message_title" => $message_title,
                        "message_content" => $message_content,
                        "message_notes" => $message_notes,
                        "message_date" => $message_date,
                        "message_termexpire" => $message_termexpire,
                        "message_valueview" => $message_valueview,
                        "message_termexpireday" => $message_termexpireday
                    );
                } elseif($message_valueview == 1 and ($this->systemdk_is_user_now or $this->systemdk_is_admin_now)) {
                    $message_alldata[] = array(
                        "message_id" => $message_id,
                        "message_title" => $message_title,
                        "message_content" => $message_content,
                        "message_notes" => $message_notes,
                        "message_date" => $message_date,
                        "message_termexpire" => $message_termexpire,
                        "message_valueview" => $message_valueview,
                        "message_termexpireday" => $message_termexpireday
                    );
                } elseif($message_valueview == 2 and $this->systemdk_is_admin_now) {
                    $message_alldata[] = array(
                        "message_id" => $message_id,
                        "message_title" => $message_title,
                        "message_content" => $message_content,
                        "message_notes" => $message_notes,
                        "message_date" => $message_date,
                        "message_termexpire" => $message_termexpire,
                        "message_valueview" => $message_valueview,
                        "message_termexpireday" => $message_termexpireday
                    );
                } elseif($message_valueview == 3 and (!$this->systemdk_is_user_now or $this->systemdk_is_admin_now)) {
                    $message_alldata[] = array(
                        "message_id" => $message_id,
                        "message_title" => $message_title,
                        "message_content" => $message_content,
                        "message_notes" => $message_notes,
                        "message_date" => $message_date,
                        "message_termexpire" => $message_termexpire,
                        "message_valueview" => $message_valueview,
                        "message_termexpireday" => $message_termexpireday
                    );
                }
                $result_messages->MoveNext();
            }
            if(isset($message_alldata)) {
                $this->registry->main_class->assign("message_alldata",$message_alldata);
            } else {
                $this->registry->main_class->assign("message_alldata","no");
            }
        } else {
            $this->registry->main_class->assign("message_alldata","no");
        }
    }


    public function who_online() {
        if(isset($this->registry->user) and $this->registry->user != "") {
            $user_cookie = $this->decode_user_cookie($this->registry->user);
        }
        if(isset($this->registry->admin) and $this->registry->admin != "") {
            $admin_cookie = $this->decode_admin_cookie($this->registry->admin);
        }
        $ip_addr = $_SERVER["REMOTE_ADDR"];
        if(isset($user_cookie[0]) and $user_cookie[0] != "") {
            $user_login = $user_cookie[0];
        }
        if(isset($admin_cookie[0]) and $admin_cookie[0] != "") {
            $admin_login = $admin_cookie[0];
        }
        if(!isset($user_login) and !isset($admin_login)) {
            $user_login = "";
            $who = "guest";
        }
        if(isset($admin_login) and $admin_login != "") {
            $who = "admin";
        }
        if(isset($user_login) and $user_login != "" and !isset($admin_login)) // and $admin_login == ""
        {
            $who = "user";
        }
        $past = $this->gettime() - 300;
        $sql = "DELETE FROM ".PREFIX."_session WHERE session_time < '".$past."'";
        $this->db->Execute($sql);
        if((isset($user_login) and isset($admin_login) and $user_login != "" and $admin_login != "") or (isset($admin_login) and isset($user_login) == "" and $admin_login != "")) {
            $user_login = $admin_login;
        }
        $user_login = $this->format_striptags($user_login);
        $user_login = $this->processing_data($user_login);
        $sql = "SELECT count(session_time) FROM ".PREFIX."_session WHERE session_login = ".$user_login." and session_hostaddress = '".$ip_addr."'";
        $result = $this->db->Execute($sql);
        if($result) {
            $now = $this->gettime();
            $numrows = intval($result->fields['0']); //$result->PO_RecordCount(PREFIX."_session","session_login = ".$user_login." and session_hostaddress = '".$ip_addr."'");
            if($numrows > 0) {
                $sql = "UPDATE ".PREFIX."_session SET session_login=".$user_login.", session_time='".$now."', session_hostaddress = '".$ip_addr."', session_whoonline='".$who."' WHERE session_login = ".$user_login." and session_hostaddress = '".$ip_addr."'";
                $this->db->Execute($sql);
            } else {
                $sql = "INSERT INTO ".PREFIX."_session (session_login, session_time, session_hostaddress, session_whoonline) VALUES (".$user_login.", '".$now."', '".$ip_addr."', '".$who."')";
                $this->db->Execute($sql);
            }
        }
    }


    private function decode_user_cookie($user) {
        $user_info = base64_decode($user);
        $user_cookie = explode(":",$user_info);
        if($this->systemdk_is_user_now) {
            unset($user_info);
            return $user_cookie;
        } else {
            unset($user_cookie);
            unset($user_info);
        }
    }


    private function decode_admin_cookie($admin) {
        $admin_info = base64_decode($admin);
        $admin_cookie = explode(":",$admin_info);
        if($this->systemdk_is_admin_now) {
            unset($admin_info);
            return $admin_cookie;
        } else {
            unset($admin_cookie);
            unset($admin_info);
        }
    }


    public function get_user_info($user) {
        if(isset($user) and trim($user) != "" and !is_array($user)) {
            $user_get = base64_decode($user);
            $user_info = explode(":",$user_get);
            if(isset($user_info[0])) {
                $info1 = $user_info[0];
            } else {
                $info1 = null;
            }
            if(isset($user_info[1])) {
                $info2 = $user_info[1];
            } else {
                $info2 = null;
            }
            if(isset($info1) and isset($info2) and $info1 != "" and $info2 != "") {
                $info1 = substr($info1,0,25);
                $info1 = $this->format_striptags($info1);
                $info1 = $this->processing_data($info1);
                $info2 = substr($info2,0,40);
                $info2 = $this->format_striptags($info2);
                $info2 = $this->processing_data($info2);
                $sql = "SELECT user_id,user_name,user_surname,user_website,user_email,user_login,user_login,user_rights,user_country,user_city,user_address,user_telephone,user_icq,user_gmt,user_notes,user_active,user_activenotes,user_regdate,user_viewemail FROM ".PREFIX."_users WHERE user_login=".$info1." AND user_password=".$info2." AND user_active='1'";
                $result = $this->db->Execute($sql);
                if($result) {
                    if(isset($result->fields['0'])) {
                        $numrows = intval($result->fields['0']);
                    } else {
                        $numrows = 0;
                    }
                }
                if($result and isset($numrows) and $numrows > 0) {
                    $userinfo = $result->fields;
                    unset($info2);
                    unset($user_get);
                    return $userinfo;
                } else {
                    unset($info2);
                    unset($user_get);
                    return 0;
                }
            } else {
                unset($info2);
                unset($user_get);
                return 0;
            }
        } else {
            return 0;
        }
    }


    public function gettime() {
        $time = time();
        return $time;
    }


    public function timetounix($string) {
        $format_date = explode(".",$string);
        $format_day = intval($format_date[0]);
        $format_month = intval($format_date[1]);
        $format_year = intval($format_date[2]);
        if(isset($format_date[3])) {
            $format_hour = intval($format_date[3]);
        } else {
            $format_hour = 0;
        }
        if(isset($format_date[4])) {
            $format_minute = intval($format_date[4]);
        } else {
            $format_minute = 0;
        }
        $format_date = date('U',mktime($format_hour,$format_minute,0,$format_month,$format_day,$format_year));
        return $format_date;
    }


    public function main_pages() {
        $sql = "SELECT mainpage_id,mainpage_date,mainpage_valueview,mainpage_status,mainpage_termexpire,mainpage_termexpireaction FROM ".PREFIX."_main_pages_".$this->registry->sitelang." WHERE mainpage_termexpire is not NULL";
        $result = $this->db->Execute($sql);
        $clear = "no";
        if($result) {
            if(isset($result->fields['0'])) {
                $numrows = intval($result->fields['0']);
            } else {
                $numrows = 0;
            }
            if($numrows > 0) {
                while(!$result->EOF) {
                    $mainpage_id = $result->fields['0'];
                    $mainpage_id = intval($mainpage_id);
                    $mainpage_date = $result->fields['1'];
                    $mainpage_date = intval($mainpage_date);
                    $mainpage_termexpire = intval($result->fields['4']);
                    $mainpage_termexpireaction = $this->extracting_data($result->fields['5']);
                    $now = $this->gettime();
                    if($mainpage_termexpire != 0 and $mainpage_termexpire <= $now) {
                        if($mainpage_termexpireaction == "off") {
                            $this->db->Execute("UPDATE ".PREFIX."_main_pages_".$this->registry->sitelang." SET mainpage_status = '0', mainpage_termexpire = NULL, mainpage_termexpireaction = NULL WHERE mainpage_id = '".$mainpage_id."'");
                            $clear = "yes";
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|main_pages|show|".$mainpage_id);
                        } elseif($mainpage_termexpireaction == "delete") {
                            $this->db->StartTrans();
                            $this->db->Execute("DELETE FROM ".PREFIX."_main_pages_".$this->registry->sitelang." WHERE mainpage_id = '".$mainpage_id."'");
                            $num_result2 = $this->db->Affected_Rows();
                            $sql2 = "SELECT mainmenu_priority FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$mainpage_id."' or mainmenu_submodule='".$mainpage_id."'";
                            $result2 = $this->db->Execute($sql2);
                            if($result2) {
                                if(isset($result2->fields['0'])) {
                                    $mainmenu_priority = intval($result2->fields['0']);
                                } else {
                                    $mainmenu_priority = 0;
                                }
                                if($mainmenu_priority > 0) {
                                    $this->db->Execute("UPDATE ".PREFIX."_main_menu_".$this->registry->sitelang." SET mainmenu_priority=mainmenu_priority-1 WHERE mainmenu_priority>".$mainmenu_priority);
                                    $num_result3 = $this->db->Affected_Rows();
                                    $sql4 = "DELETE FROM ".PREFIX."_main_menu_".$this->registry->sitelang." WHERE mainmenu_module='".$mainpage_id."' or mainmenu_submodule='".$mainpage_id."'";
                                    $this->db->Execute($sql4);
                                    $num_result4 = $this->db->Affected_Rows();
                                    if($num_result4 > 0 or $num_result3 > 0) {
                                        $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|menu|show");
                                    }
                                }
                            }
                            $this->db->CompleteTrans();
                            $clear = "yes";
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|edit|".$mainpage_id);
                            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|modules|main_pages|show|".$mainpage_id);
                        }
                    }
                    $result->MoveNext();
                }
            }
        }
        if($clear == "yes") {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|main_pages|show");
        }
    }


    public function messages_check() {
        $now = $this->gettime();
        $this->db->Execute("UPDATE ".PREFIX."_messages_".$this->registry->sitelang." SET message_status = '0', message_termexpire = NULL, message_termexpireaction = NULL WHERE message_termexpire IS NOT NULL and message_termexpire <= ".$now." and message_termexpireaction = 'off'");
        $num_result = $this->db->Affected_Rows();
        $this->db->Execute("DELETE FROM ".PREFIX."_messages_".$this->registry->sitelang." WHERE message_termexpire IS NOT NULL and message_termexpire <= ".$now." and message_termexpireaction = 'delete'");
        $num_result2 = $this->db->Affected_Rows();
        if($num_result > 0 or $num_result2 > 0) {
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|show");
            $this->registry->main_class->clearCache(null,$this->registry->sitelang."|systemadmin|modules|messages|edit");
        }
    }


    public function header() {
        $this->main_pages();
        $this->registry->main_class->blocks_autocheck();
        $this->registry->main_class->display_theme_header();
        if(isset($this->registry->home_page) and $this->registry->home_page == "1") {
            $this->messages_check();
            $this->messages();
            $this->block("center-up");
        }
    }


    public function footer() {
        if(isset($this->registry->home_page) and $this->registry->home_page == "1") {
            $this->block("center-down");
        }
        $this->registry->main_class->display_theme_footer();
    }


    public function loadhome($status) {
        $this->set_sitemeta($this->registry->main_class->getConfigVars('_MAINPAGELINK'));
        $this->database_close();
        if($status === 'ok') {
            $this->registry->main_class->assign("include_center","no");
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|homepage|".$this->registry->language);
        } else {
            $this->registry->main_class->assign("error","error_hommodule");
            $this->registry->main_class->assign("include_center","error.html");
            $this->registry->main_class->display("index.html",$this->registry->sitelang."|error|homepage|".$this->registry->language);
            exit();
        }
    }


    public function mainenter() {
        $this->registry->main_class->configLoad($this->registry->language."/systemadmin/enter.conf");
        $this->registry->main_class->assign("site_name",$this->registry->main_class->getConfigVars('_ADMINENTERFORM')." &raquo; ".SITE_NAME);
        $this->registry->main_class->display_theme_adminheader();
        if(extension_loaded("gd") and (ENTER_CHECK == "yes")) {
            $this->registry->main_class->assign("enter_check","yes");
        } else {
            $this->registry->main_class->assign("enter_check","no");
        }
        if(session_id() == "") {
            session_start();
        }
        if(isset($_SESSION['systemdk_if_admin'])) {
            unset($_SESSION['systemdk_if_admin']);
        }
        $this->database_close();
        $this->registry->main_class->display("systemadmin/enter.html",$this->registry->sitelang."|systemadmin|enter|".$this->registry->language);
    }
}

?>