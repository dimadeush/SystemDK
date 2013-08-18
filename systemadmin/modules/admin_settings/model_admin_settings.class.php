<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_settings.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class admin_settings extends model_base {


    public function index() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        if(!$this->registry->main_class->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|show|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUCONFIGS'));
            $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
            $this->registry->main_class->assign("include_center","systemadmin/modules/settings/config.html");
            $this->registry->main_class->assign("site_id",trim(SITE_ID));
            $this->registry->main_class->assign("mod_rewrite",trim(SYSTEMDK_MODREWRITE));
            $this->registry->main_class->assign("db_persistency",trim(DB_PERSISTENCY));
            $this->registry->main_class->assign("smarty_caching",trim(SMARTY_CACHING));
            $this->registry->main_class->assign("smarty_debugging",trim(SMARTY_DEBUGGING));
            $this->registry->main_class->assign("adodb_debugging",trim(ADODB_DEBUGGING));
            $this->registry->main_class->assign("cache_lifetime",intval(CACHE_LIFETIME));
            $this->registry->main_class->assign("site_charset",trim(SITE_CHARSET));
            $this->registry->main_class->assign("site_url",trim(SITE_URL));
            $this->registry->main_class->assign("admin_email",trim(ADMIN_EMAIL));
            $this->registry->main_class->assign("site_edit_name",trim(SITE_NAME));
            $this->registry->main_class->assign("site_logo",trim(SITE_LOGO));
            $this->registry->main_class->assign("site_startdate",trim(SITE_STARTDATE));
            $this->registry->main_class->assign("site_keywords",trim(SITE_KEYWORDS));
            $this->registry->main_class->assign("site_description",trim(SITE_DESCRIPTION));
            $this->registry->main_class->assign("site_status",trim(SITE_STATUS));
            $this->registry->main_class->assign("site_status_notes",trim(SITE_STATUS_NOTES));
            $this->registry->main_class->assign("gzip",trim(GZIP));
            $this->registry->main_class->assign("enter_check",trim(ENTER_CHECK));
            $this->registry->main_class->assign("cookie_live",intval(COOKIE_LIVE));
            $this->registry->main_class->assign("display_admin_graphic",trim(DISPLAY_ADMIN_GRAPHIC));
            $this->registry->main_class->assign("systemdk_adminrows_perpage",intval(SYSTEMDK_ADMINROWS_PERPAGE));
            $this->registry->main_class->assign("systemdk_mail_method",trim(SYSTEMDK_MAIL_METHOD));
            $this->registry->main_class->assign("systemdk_smtp_host",trim(SYSTEMDK_SMTP_HOST));
            $this->registry->main_class->assign("systemdk_smtp_port",intval(SYSTEMDK_SMTP_PORT));
            $this->registry->main_class->assign("systemdk_smtp_user",trim(SYSTEMDK_SMTP_USER));
            $this->registry->main_class->assign("systemdk_smtp_pass",trim(SYSTEMDK_SMTP_PASS));
            $this->registry->main_class->assign("systemdk_account_registration",trim(SYSTEMDK_ACCOUNT_REGISTRATION));
            $this->registry->main_class->assign("systemdk_account_activation",trim(SYSTEMDK_ACCOUNT_ACTIVATION));
            $this->registry->main_class->assign("systemdk_account_rules",trim(SYSTEMDK_ACCOUNT_RULES));
            $this->registry->main_class->assign("systemdk_account_maxusers",intval(SYSTEMDK_ACCOUNT_MAXUSERS));
            $this->registry->main_class->assign("systemdk_account_maxactivationdays",intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS));
            $this->registry->main_class->assign("systemdk_statistics",trim(SYSTEMDK_STATISTICS));
            $this->registry->main_class->assign("systemdk_player_autoplay",trim(SYSTEMDK_PLAYER_AUTOPLAY));
            $this->registry->main_class->assign("systemdk_player_autobuffer",trim(SYSTEMDK_PLAYER_AUTOBUFFER));
            $this->registry->main_class->assign("systemdk_player_controls",trim(SYSTEMDK_PLAYER_CONTROLS));
            $this->registry->main_class->assign("systemdk_player_audioautoplay",trim(SYSTEMDK_PLAYER_AUDIOAUTOPLAY));
            $this->registry->main_class->assign("systemdk_player_audioautobuffer",trim(SYSTEMDK_PLAYER_AUDIOAUTOBUFFER));
            $this->registry->main_class->assign("systemdk_player_audio_controls",trim(SYSTEMDK_PLAYER_AUDIOCONTROLS));
            $this->registry->main_class->assign("systemdk_player_ratio",$this->registry->main_class->float2db(trim(SYSTEMDK_PLAYER_RATIO)));
            $this->registry->main_class->assign("systemdk_player_skin",trim(SYSTEMDK_PLAYER_SKIN));
            $this->registry->main_class->assign("systemdk_feedback_receiver",trim(SYSTEMDK_FEEDBACK_RECEIVER));
            $this->registry->main_class->assign("systemdk_ipantispam",trim(SYSTEMDK_IPANTISPAM));
            $this->registry->main_class->assign("systemdk_ipantispam_num",intval(SYSTEMDK_IPANTISPAM_NUM));
            $this->registry->main_class->assign("systemdk_site_lang",trim(SYSTEMDK_SITE_LANG));
            $this->registry->main_class->assign("systemdk_admin_site_lang",trim(SYSTEMDK_ADMIN_SITE_LANG));
            $this->registry->main_class->assign("systemdk_interface_lang",trim(SYSTEMDK_INTERFACE_LANG));
            $this->registry->main_class->assign("systemdk_admin_interface_lang",trim(SYSTEMDK_ADMIN_INTERFACE_LANG));
        }
        $this->registry->main_class->assign("default_language",trim(LANGUAGE));
        $this->registry->main_class->assign("site_theme",trim(SITE_THEME));
        $this->registry->main_class->assign("Home_Module",trim(HOME_MODULE));
        $dir = opendir('../themes');
        while(false !== ($file = readdir($dir))) {
            if((!preg_match("/[.]/i",$file))) {
                $themelist[] = "$file";
            }
        }
        closedir($dir);
        $this->registry->main_class->assign("themelist",$themelist);
        $languagelist = $this->registry->main_class->display_theme_langlist('../themes/'.SITE_THEME.'/languages');
        $this->registry->main_class->assign("languagelist",$languagelist);
        $moduledir = opendir('../modules');
        while(false !== ($file = readdir($moduledir))) {
            if(!preg_match("/[.]/i",$file)) {
                $module = $file;
                $modulelist[] = "$module";
            }
        }
        closedir($moduledir);
        $this->registry->main_class->assign("modulelist",$modulelist);
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|show|".$this->registry->language);
    }


    public function settingssave() {
        if(isset($_POST['site_id'])) {
            $site_id = trim($_POST['site_id']);
        }
        if(isset($_POST['mod_rewrite'])) {
            $mod_rewrite = trim($_POST['mod_rewrite']);
        }
        if(isset($_POST['db_persistency'])) {
            $db_persistency = trim($_POST['db_persistency']);
        }
        if(isset($_POST['smarty_caching'])) {
            $smarty_caching = trim($_POST['smarty_caching']);
        }
        if(isset($_POST['smarty_debugging'])) {
            $smarty_debugging = trim($_POST['smarty_debugging']);
        }
        if(isset($_POST['adodb_debugging'])) {
            $adodb_debugging = trim($_POST['adodb_debugging']);
        }
        if(isset($_POST['cache_lifetime'])) {
            $cache_lifetime = intval($_POST['cache_lifetime']);
        }
        if(isset($_POST['site_charset'])) {
            $site_charset = trim($_POST['site_charset']);
        }
        if(isset($_POST['admin_email'])) {
            $admin_email = trim($_POST['admin_email']);
        }
        if(isset($_POST['site_url'])) {
            $site_url = trim($_POST['site_url']);
        }
        if(isset($_POST['site_name'])) {
            $site_name = trim($_POST['site_name']);
        }
        if(isset($_POST['site_logo'])) {
            $site_logo = trim($_POST['site_logo']);
        }
        if(isset($_POST['site_startdate'])) {
            $site_startdate = trim($_POST['site_startdate']);
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
        if(isset($_POST['gzip'])) {
            $gzip = trim($_POST['gzip']);
        }
        if(isset($_POST['enter_check'])) {
            $enter_check = trim($_POST['enter_check']);
        }
        if(isset($_POST['cookie_live'])) {
            $cookie_live = intval($_POST['cookie_live']);
        }
        if(isset($_POST['display_admin_graphic'])) {
            $display_admin_graphic = trim($_POST['display_admin_graphic']);
        }
        if(isset($_POST['systemdk_adminrows_perpage'])) {
            $systemdk_adminrows_perpage = intval($_POST['systemdk_adminrows_perpage']);
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
        if(isset($_POST['systemdk_feedback_receiver'])) {
            $systemdk_feedback_receiver = trim($_POST['systemdk_feedback_receiver']);
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
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUCONFIGS'));
        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
        $this->registry->main_class->assign("include_center","systemadmin/modules/settings/config2.html");
        if((!isset($_POST['site_id']) or !isset($_POST['mod_rewrite']) or !isset($_POST['db_persistency']) or !isset($_POST['smarty_caching']) or !isset($_POST['smarty_debugging']) or !isset($_POST['adodb_debugging']) or !isset($_POST['gzip']) or !isset($_POST['cache_lifetime']) or !isset($_POST['cookie_live']) or !isset($_POST['site_charset']) or !isset($_POST['admin_email']) or !isset($_POST['site_url']) or !isset($_POST['site_name']) or !isset($_POST['site_keywords']) or !isset($_POST['site_description']) or !isset($_POST['site_status']) or !isset($_POST['site_status_notes']) or !isset($_POST['site_theme']) or !isset($_POST['language']) or !isset($_POST['Home_Module']) or !isset($_POST['enter_check']) or !isset($_POST['display_admin_graphic']) or !isset($_POST['systemdk_adminrows_perpage']) or !isset($_POST['systemdk_mail_method']) or !isset($_POST['systemdk_smtp_host']) or !isset($_POST['systemdk_smtp_port']) or !isset($_POST['systemdk_smtp_user']) or !isset($_POST['systemdk_smtp_pass']) or !isset($_POST['systemdk_account_registration']) or !isset($_POST['systemdk_account_activation']) or !isset($_POST['systemdk_account_rules']) or !isset($_POST['systemdk_account_maxusers']) or !isset($_POST['systemdk_account_maxactivationdays']) or !isset($_POST['systemdk_statistics']) or !isset($_POST['systemdk_player_autoplay']) or !isset($_POST['systemdk_player_autobuffer']) or !isset($_POST['systemdk_player_controls']) or !isset($_POST['systemdk_player_audioautoplay']) or !isset($_POST['systemdk_player_audioautobuffer']) or !isset($_POST['systemdk_player_audio_controls']) or !isset($_POST['systemdk_player_ratio']) or !isset($_POST['systemdk_player_skin']) or !isset($_POST['systemdk_feedback_receiver']) or !isset($_POST['systemdk_ipantispam']) or !isset($_POST['systemdk_ipantispam_num']) or !isset($_POST['systemdk_site_lang']) or !isset($_POST['systemdk_admin_site_lang']) or !isset($_POST['systemdk_interface_lang']) or !isset($_POST['systemdk_admin_interface_lang'])) or ($site_id == "" or $mod_rewrite == "" or $db_persistency == "" or $smarty_caching == "" or $smarty_debugging == "" or $adodb_debugging == "" or $gzip == "" or $cache_lifetime === "" or $cookie_live === "" or $site_charset == "" or $admin_email == "" or $site_url == "" or $site_name == "" or $site_keywords == "" or $site_description == "" or $site_status == "" or  $site_theme == "" or $language == "" or $enter_check == "" or $display_admin_graphic == "" or $systemdk_mail_method == "" or $systemdk_account_registration == "" or $systemdk_account_activation == "" or $systemdk_account_rules == "" or $systemdk_statistics == "" or $systemdk_player_autoplay == "" or $systemdk_player_autobuffer == "" or $systemdk_player_controls == "" or $systemdk_player_audioautoplay == "" or $systemdk_player_audioautobuffer == "" or $systemdk_player_audio_controls == "" or $systemdk_player_skin == "" or $systemdk_feedback_receiver == "" or $systemdk_ipantispam == "" or $systemdk_site_lang == "" or $systemdk_admin_site_lang == "" or $systemdk_interface_lang == "" or $systemdk_admin_interface_lang == "")) {
            $this->registry->main_class->assign("done","noalldata");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|error|notalldata|".$this->registry->language);
            exit();
        }
        $site_id = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_id);
        $mod_rewrite = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($mod_rewrite);
        $db_persistency = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($db_persistency);
        $smarty_caching = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($smarty_caching);
        $smarty_debugging = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($smarty_debugging);
        $adodb_debugging = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($adodb_debugging);
        $site_charset = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_charset);
        $admin_email = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($admin_email);
        $site_url = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_url);
        $site_name = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_name);
        $site_logo = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_logo);
        $site_startdate = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_startdate);
        $site_keywords = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_keywords);
        $site_description = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_description);
        $site_status = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_status);
        $site_status_notes = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_status_notes);
        $site_theme = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($site_theme);
        $language = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($language);
        $Home_Module = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($Home_Module);
        $gzip = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($gzip);
        $enter_check = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($enter_check);
        $display_admin_graphic = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($display_admin_graphic);
        $systemdk_mail_method = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_mail_method);
        $systemdk_smtp_host = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_smtp_host);
        $systemdk_smtp_user = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_smtp_user);
        $systemdk_smtp_pass = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_smtp_pass);
        $systemdk_account_registration = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_account_registration);
        $systemdk_account_activation = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_account_activation);
        $systemdk_account_rules = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_account_rules);
        $systemdk_statistics = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_statistics);
        $systemdk_player_autoplay = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_autoplay);
        $systemdk_player_autobuffer = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_autobuffer);
        $systemdk_player_controls = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_controls);
        $systemdk_player_audioautoplay = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_audioautoplay);
        $systemdk_player_audioautobuffer = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_audioautobuffer);
        $systemdk_player_audio_controls = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_audio_controls);
        $systemdk_player_ratio = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_ratio);
        $systemdk_player_skin = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_player_skin);
        $systemdk_feedback_receiver = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_feedback_receiver);
        $systemdk_ipantispam = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_ipantispam);
        $systemdk_site_lang = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_site_lang);
        $systemdk_admin_site_lang = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_admin_site_lang);
        $systemdk_interface_lang = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_interface_lang);
        $systemdk_admin_interface_lang = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($systemdk_admin_interface_lang);
        if($systemdk_adminrows_perpage == 0) {
            $systemdk_adminrows_perpage = 5;
        }
        if(!$this->registry->main_class->check_email($admin_email)) {
            $this->registry->main_class->assign("done","incorrectemail");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|error|incorrectemail|".$this->registry->language);
            exit();
        }
        @chmod("../includes/data/config.inc",0777);
        $file = @fopen("../includes/data/config.inc","w");
        if(!$file) {
            $this->registry->main_class->assign("done","nofile");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|error|nofile|".$this->registry->language);
            exit();
        }
        $content = "<?php\n\n";
        $content .= 'define("SYSTEMDK_VERSION","'.SYSTEMDK_VERSION."\");\n";
        $content .= 'define("SYSTEMDK_MODREWRITE","'.$mod_rewrite."\");\n";
        $content .= 'define("SYSTEMDK_DESCRIPTION","'.SYSTEMDK_DESCRIPTION."\");\n";
        $content .= 'define("SITE_ID","'.$site_id."\");\n";
        $content .= 'define("SITE_DIR","'.SITE_DIR."\");\n";
        $content .= 'define("ADMIN_DIR","'.ADMIN_DIR."\");\n";
        $content .= 'define("ADMIN_EMAIL","'.$admin_email."\");\n";
        $content .= 'define("DB_HOST","'.DB_HOST."\");\n";
        $content .= 'define("DB_PORT","'.DB_PORT."\");\n";
        $content .= 'define("DB_USER_NAME","'.DB_USER_NAME."\");\n";
        $content .= 'define("DB_PASSWORD","'.DB_PASSWORD."\");\n";
        $content .= 'define("DB_NAME","'.DB_NAME."\");\n";
        $content .= 'define("DB_PERSISTENCY","'.$db_persistency."\");\n";
        $content .= 'define("PREFIX","'.PREFIX."\");\n";
        $content .= 'define("DBTYPE","'.DBTYPE."\");\n";
        $content .= 'define("DB_CHARACTER","'.DB_CHARACTER."\");\n";
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
        $content .= 'define("SYSTEMDK_PLAYER_RATIO","'.$this->registry->main_class->float2db($systemdk_player_ratio)."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_SKIN","'.$systemdk_player_skin."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM","'.$systemdk_ipantispam."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM_NUM","'.$systemdk_ipantispam_num."\");\n";
        $content .= 'define("SYSTEMDK_FEEDBACK_RECEIVER","'.$systemdk_feedback_receiver."\");\n";
        $content .= "?>\n";
        $writefile = @fwrite($file,$content);
        if(!$writefile) {
            $this->registry->main_class->assign("done","nowrite");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|error|nowrite|".$this->registry->language);
            exit();
        } else {
            $this->registry->main_class->clearAllCache();
            $this->registry->main_class->assign("done","ok");
        }
        @fclose($file);
        @chmod("../includes/data/config.inc",0604);
        $this->registry->main_class->database_close();
        $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|save|ok|".$this->registry->language);
    }


    public function clearallcache() {
        $this->registry->main_class->display_theme_adminheader();
        $this->registry->main_class->display_theme_adminmain();
        $this->registry->main_class->displayadmininfo();
        $this->registry->main_class->set_sitemeta($this->registry->main_class->getConfigVars('_MENUCONFIGS'));
        $this->registry->main_class->assign("include_center_up","systemadmin/adminup.html");
        $this->registry->main_class->assign("include_center","systemadmin/modules/settings/config3.html");
        $done = $this->registry->main_class->clearAllCache();
        if($done) {
            $this->registry->main_class->assign("done","ok");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|clear|ok|".$this->registry->language);
        } else {
            $this->registry->main_class->assign("done","no");
            $this->registry->main_class->database_close();
            $this->registry->main_class->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|settings|error|noclear|".$this->registry->language);
        }
    }
}

?>