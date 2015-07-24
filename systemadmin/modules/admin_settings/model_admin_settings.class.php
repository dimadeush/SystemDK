<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      model_admin_settings.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class admin_settings extends model_base {


    private $error;
    private $result;


    public function get_property_value($property) {
        if(isset($this->$property) and in_array($property,array('error','result'))) {
            return $this->$property;
        }
        return false;
    }


    public function index($cached = false) {
        $this->result = false;
        if(!$cached) {
            $this->result['site_id'] = trim(SITE_ID);
            $this->result['mod_rewrite'] = trim(SYSTEMDK_MODREWRITE);
            $this->result['db_persistency'] = trim(DB_PERSISTENCY);
            $this->result['smarty_caching'] = trim(SMARTY_CACHING);
            $this->result['smarty_debugging'] = trim(SMARTY_DEBUGGING);
            $this->result['adodb_debugging'] = trim(ADODB_DEBUGGING);
            $this->result['cache_lifetime'] = intval(CACHE_LIFETIME);
            $this->result['site_charset'] = trim(SITE_CHARSET);
            $this->result['site_url'] = trim(SITE_URL);
            $this->result['admin_email'] = trim(ADMIN_EMAIL);
            $this->result['site_edit_name'] = trim(SITE_NAME);
            $this->result['site_logo'] = trim(SITE_LOGO);
            $this->result['site_startdate'] = trim(SITE_STARTDATE);
            $this->result['site_keywords'] = trim(SITE_KEYWORDS);
            $this->result['site_description'] = trim(SITE_DESCRIPTION);
            $this->result['site_status'] = trim(SITE_STATUS);
            $this->result['site_status_notes'] = trim(SITE_STATUS_NOTES);
            $this->result['gzip'] = trim(GZIP);
            $this->result['enter_check'] = trim(ENTER_CHECK);
            $this->result['session_cookie_live'] = intval(SESSION_COOKIE_LIVE);
            $this->result['display_admin_graphic'] = trim(DISPLAY_ADMIN_GRAPHIC);
            $this->result['systemdk_adminrows_perpage'] = intval(SYSTEMDK_ADMINROWS_PERPAGE);
            $this->result['systemdk_mail_method'] = trim(SYSTEMDK_MAIL_METHOD);
            $this->result['systemdk_smtp_host'] = trim(SYSTEMDK_SMTP_HOST);
            $this->result['systemdk_smtp_port'] = intval(SYSTEMDK_SMTP_PORT);
            $this->result['systemdk_smtp_user'] = trim(SYSTEMDK_SMTP_USER);
            $this->result['systemdk_smtp_pass'] = trim(SYSTEMDK_SMTP_PASS);
            $this->result['systemdk_account_registration'] = trim(SYSTEMDK_ACCOUNT_REGISTRATION);
            $this->result['systemdk_account_activation'] = trim(SYSTEMDK_ACCOUNT_ACTIVATION);
            $this->result['systemdk_account_rules'] = trim(SYSTEMDK_ACCOUNT_RULES);
            $this->result['systemdk_account_maxusers'] = intval(SYSTEMDK_ACCOUNT_MAXUSERS);
            $this->result['systemdk_account_maxactivationdays'] = intval(SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS);
            $this->result['systemdk_statistics'] = trim(SYSTEMDK_STATISTICS);
            $this->result['systemdk_player_autoplay'] = trim(SYSTEMDK_PLAYER_AUTOPLAY);
            $this->result['systemdk_player_autobuffer'] = trim(SYSTEMDK_PLAYER_AUTOBUFFER);
            $this->result['systemdk_player_controls'] = trim(SYSTEMDK_PLAYER_CONTROLS);
            $this->result['systemdk_player_audioautoplay'] = trim(SYSTEMDK_PLAYER_AUDIOAUTOPLAY);
            $this->result['systemdk_player_audioautobuffer'] = trim(SYSTEMDK_PLAYER_AUDIOAUTOBUFFER);
            $this->result['systemdk_player_audio_controls'] = trim(SYSTEMDK_PLAYER_AUDIOCONTROLS);
            $this->result['systemdk_player_ratio'] = $this->registry->main_class->float_to_db(trim(SYSTEMDK_PLAYER_RATIO));
            $this->result['systemdk_player_skin'] = trim(SYSTEMDK_PLAYER_SKIN);
            $this->result['systemdk_feedback_receiver'] = trim(SYSTEMDK_FEEDBACK_RECEIVER);
            $this->result['systemdk_ipantispam'] = trim(SYSTEMDK_IPANTISPAM);
            $this->result['systemdk_ipantispam_num'] = intval(SYSTEMDK_IPANTISPAM_NUM);
            $this->result['systemdk_site_lang'] = trim(SYSTEMDK_SITE_LANG);
            $this->result['systemdk_admin_site_lang'] = trim(SYSTEMDK_ADMIN_SITE_LANG);
            $this->result['systemdk_interface_lang'] = trim(SYSTEMDK_INTERFACE_LANG);
            $this->result['systemdk_admin_interface_lang'] = trim(SYSTEMDK_ADMIN_INTERFACE_LANG);
        }
        $this->result['default_language'] = trim(LANGUAGE);
        $this->result['site_theme'] = trim(SITE_THEME);
        $this->result['Home_Module'] = trim(HOME_MODULE);
        $dir = opendir('../themes');
        while(false !== ($file = readdir($dir))) {
            if((!preg_match("/[.]/i",$file))) {
                $themelist[] = "$file";
            }
        }
        closedir($dir);
        $this->result['themelist'] = $themelist;
        $languagelist = $this->registry->controller_theme->display_theme_langlist('../themes/'.SITE_THEME.'/languages');
        $this->result['languagelist'] = $languagelist;
        $moduledir = opendir('../modules');
        while(false !== ($file = readdir($moduledir))) {
            if(!preg_match("/[.]/i",$file)) {
                $module = $file;
                $modulelist[] = "$module";
            }
        }
        closedir($moduledir);
        $this->result['modulelist'] = $modulelist;
    }


    public function settingssave($post_array) {
        $this->result = false;
        $this->error = false;
        $keys = array(
            'site_id',
            'mod_rewrite',
            'db_persistency',
            'smarty_caching',
            'smarty_debugging',
            'adodb_debugging',
            'site_charset',
            'admin_email',
            'site_url',
            'site_name',
            'site_logo',
            'site_startdate',
            'site_keywords',
            'site_description',
            'site_status',
            'site_status_notes',
            'site_theme',
            'language',
            'Home_Module',
            'gzip',
            'enter_check',
            'display_admin_graphic',
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
            'systemdk_feedback_receiver',
            'systemdk_ipantispam',
            'systemdk_site_lang',
            'systemdk_admin_site_lang',
            'systemdk_interface_lang',
            'systemdk_admin_interface_lang'
        );
        if(!empty($post_array)) {
            foreach($post_array as $key => $value) {
                $keys2 = array(
                    'cache_lifetime',
                    'session_cookie_live',
                    'systemdk_adminrows_perpage',
                    'systemdk_smtp_port',
                    'systemdk_account_maxusers',
                    'systemdk_account_maxactivationdays',
                    'systemdk_ipantispam_num'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        if((!isset($post_array['site_id']) or !isset($post_array['mod_rewrite']) or !isset($post_array['db_persistency']) or !isset($post_array['smarty_caching']) or !isset($post_array['smarty_debugging']) or !isset($post_array['adodb_debugging']) or !isset($post_array['gzip']) or !isset($post_array['cache_lifetime']) or !isset($post_array['session_cookie_live']) or !isset($post_array['site_charset']) or !isset($post_array['admin_email']) or !isset($post_array['site_url']) or !isset($post_array['site_name']) or !isset($post_array['site_keywords']) or !isset($post_array['site_description']) or !isset($post_array['site_status']) or !isset($post_array['site_status_notes']) or !isset($post_array['site_theme']) or !isset($post_array['language']) or !isset($post_array['Home_Module']) or !isset($post_array['enter_check']) or !isset($post_array['display_admin_graphic']) or !isset($post_array['systemdk_adminrows_perpage']) or !isset($post_array['systemdk_mail_method']) or !isset($post_array['systemdk_smtp_host']) or !isset($post_array['systemdk_smtp_port']) or !isset($post_array['systemdk_smtp_user']) or !isset($post_array['systemdk_smtp_pass']) or !isset($post_array['systemdk_account_registration']) or !isset($post_array['systemdk_account_activation']) or !isset($post_array['systemdk_account_rules']) or !isset($post_array['systemdk_account_maxusers']) or !isset($post_array['systemdk_account_maxactivationdays']) or !isset($post_array['systemdk_statistics']) or !isset($post_array['systemdk_player_autoplay']) or !isset($post_array['systemdk_player_autobuffer']) or !isset($post_array['systemdk_player_controls']) or !isset($post_array['systemdk_player_audioautoplay']) or !isset($post_array['systemdk_player_audioautobuffer']) or !isset($post_array['systemdk_player_audio_controls']) or !isset($post_array['systemdk_player_ratio']) or !isset($post_array['systemdk_player_skin']) or !isset($post_array['systemdk_feedback_receiver']) or !isset($post_array['systemdk_ipantispam']) or !isset($post_array['systemdk_ipantispam_num']) or !isset($post_array['systemdk_site_lang']) or !isset($post_array['systemdk_admin_site_lang']) or !isset($post_array['systemdk_interface_lang']) or !isset($post_array['systemdk_admin_interface_lang'])) or ($data_array['site_id'] == "" or $data_array['mod_rewrite'] == "" or $data_array['db_persistency'] == "" or $data_array['smarty_caching'] == "" or $data_array['smarty_debugging'] == "" or $data_array['adodb_debugging'] == "" or $data_array['gzip'] == "" or $data_array['cache_lifetime'] === "" or $data_array['session_cookie_live'] === "" or $data_array['site_charset'] == "" or $data_array['admin_email'] == "" or $data_array['site_url'] == "" or $data_array['site_name'] == "" or $data_array['site_keywords'] == "" or $data_array['site_description'] == "" or $data_array['site_status'] == "" or $data_array['site_theme'] == "" or $data_array['language'] == "" or $data_array['enter_check'] == "" or $data_array['display_admin_graphic'] == "" or $data_array['systemdk_mail_method'] == "" or $data_array['systemdk_account_registration'] == "" or $data_array['systemdk_account_activation'] == "" or $data_array['systemdk_account_rules'] == "" or $data_array['systemdk_statistics'] == "" or $data_array['systemdk_player_autoplay'] == "" or $data_array['systemdk_player_autobuffer'] == "" or $data_array['systemdk_player_controls'] == "" or $data_array['systemdk_player_audioautoplay'] == "" or $data_array['systemdk_player_audioautobuffer'] == "" or $data_array['systemdk_player_audio_controls'] == "" or $data_array['systemdk_player_skin'] == "" or $data_array['systemdk_feedback_receiver'] == "" or $data_array['systemdk_ipantispam'] == "" or $data_array['systemdk_site_lang'] == "" or $data_array['systemdk_admin_site_lang'] == "" or $data_array['systemdk_interface_lang'] == "" or $data_array['systemdk_admin_interface_lang'] == "")) {
            $this->error = 'not_all_data';
            return;
        }
        foreach($data_array as $key => $value) {
            if(in_array($key,$keys)) {
                $data_array[$key] = $this->registry->main_class->format_htmlspecchars_stripslashes_striptags($value);
            }
        }
        if($data_array['systemdk_adminrows_perpage'] == 0) {
            $data_array['systemdk_adminrows_perpage'] = 5;
        }
        if(!$this->registry->main_class->check_email($data_array['admin_email'])) {
            $this->error = 'incorrect_email';
            return;
        }
        @chmod("../includes/data/config.inc",0777);
        $file = @fopen("../includes/data/config.inc","w");
        if(!$file) {
            $this->error = 'no_file';
            return;
        }
        $content = "<?php\n\n";
        $content .= 'define("SYSTEMDK_VERSION","'.SYSTEMDK_VERSION."\");\n";
        $content .= 'define("SYSTEMDK_MODREWRITE","'.$data_array['mod_rewrite']."\");\n";
        $content .= 'define("SYSTEMDK_DESCRIPTION","'.SYSTEMDK_DESCRIPTION."\");\n";
        $content .= 'define("SITE_ID","'.$data_array['site_id']."\");\n";
        $content .= 'define("SITE_DIR","'.SITE_DIR."\");\n";
        $content .= 'define("ADMIN_DIR","'.ADMIN_DIR."\");\n";
        $content .= 'define("ADMIN_EMAIL","'.$data_array['admin_email']."\");\n";
        $content .= 'define("DB_HOST","'.DB_HOST."\");\n";
        $content .= 'define("DB_PORT","'.DB_PORT."\");\n";
        $content .= 'define("DB_USER_NAME","'.DB_USER_NAME."\");\n";
        $content .= 'define("DB_PASSWORD","'.DB_PASSWORD."\");\n";
        $content .= 'define("DB_NAME","'.DB_NAME."\");\n";
        $content .= 'define("DB_PERSISTENCY","'.$data_array['db_persistency']."\");\n";
        $content .= 'define("PREFIX","'.PREFIX."\");\n";
        $content .= 'define("DBTYPE","'.DBTYPE."\");\n";
        $content .= 'define("DB_CHARACTER","'.DB_CHARACTER."\");\n";
        $content .= 'define("DB_COLLATE","'.DB_COLLATE."\");\n";
        $content .= 'define("SMARTY_CACHING","'.$data_array['smarty_caching']."\");\n";
        $content .= 'define("SMARTY_DEBUGGING","'.$data_array['smarty_debugging']."\");\n";
        $content .= 'define("ADODB_DEBUGGING","'.$data_array['adodb_debugging']."\");\n";
        $content .= 'define("CACHE_LIFETIME","'.$data_array['cache_lifetime']."\");\n";
        $content .= 'define("SITE_CHARSET","'.$data_array['site_charset']."\");\n";
        $content .= 'define("SITE_URL","'.$data_array['site_url']."\");\n";
        $content .= 'define("SITE_NAME","'.$data_array['site_name']."\");\n";
        $content .= 'define("SITE_LOGO","'.$data_array['site_logo']."\");\n";
        $content .= 'define("SITE_STARTDATE","'.$data_array['site_startdate']."\");\n";
        $content .= 'define("SITE_KEYWORDS","'.$data_array['site_keywords']."\");\n";
        $content .= 'define("SITE_DESCRIPTION","'.$data_array['site_description']."\");\n";
        $content .= 'define("SITE_STATUS","'.$data_array['site_status']."\");\n";
        $content .= 'define("SITE_STATUS_NOTES","'.$data_array['site_status_notes']."\");\n";
        $content .= 'define("SITE_THEME","'.$data_array['site_theme']."\");\n";
        $content .= 'define("LANGUAGE","'.$data_array['language']."\");\n";
        $content .= 'define("SYSTEMDK_SITE_LANG","'.$data_array['systemdk_site_lang']."\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_SITE_LANG","'.$data_array['systemdk_admin_site_lang']."\");\n";
        $content .= 'define("SYSTEMDK_INTERFACE_LANG","'.$data_array['systemdk_interface_lang']."\");\n";
        $content .= 'define("SYSTEMDK_ADMIN_INTERFACE_LANG","'.$data_array['systemdk_admin_interface_lang']."\");\n";
        $content .= 'define("HOME_MODULE","'.$data_array['Home_Module']."\");\n";
        $content .= 'define("GZIP","'.$data_array['gzip']."\");\n";
        $content .= 'define("ENTER_CHECK","'.$data_array['enter_check']."\");\n";
        $content .= 'define("SESSION_COOKIE_LIVE","'.$data_array['session_cookie_live']."\");\n";
        $content .= 'define("DISPLAY_ADMIN_GRAPHIC","'.$data_array['display_admin_graphic']."\");\n";
        $content .= 'define("SYSTEMDK_ADMINROWS_PERPAGE","'.$data_array['systemdk_adminrows_perpage']."\");\n";
        $content .= 'define("SYSTEMDK_MAIL_METHOD","'.$data_array['systemdk_mail_method']."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_HOST","'.$data_array['systemdk_smtp_host']."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PORT","'.$data_array['systemdk_smtp_port']."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_USER","'.$data_array['systemdk_smtp_user']."\");\n";
        $content .= 'define("SYSTEMDK_SMTP_PASS","'.$data_array['systemdk_smtp_pass']."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_REGISTRATION","'.$data_array['systemdk_account_registration']."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_ACTIVATION","'.$data_array['systemdk_account_activation']."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_RULES","'.$data_array['systemdk_account_rules']."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXUSERS","'.$data_array['systemdk_account_maxusers']."\");\n";
        $content .= 'define("SYSTEMDK_ACCOUNT_MAXACTIVATIONDAYS","'.$data_array['systemdk_account_maxactivationdays']."\");\n";
        $content .= 'define("SYSTEMDK_STATISTICS","'.$data_array['systemdk_statistics']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOPLAY","'.$data_array['systemdk_player_autoplay']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUTOBUFFER","'.$data_array['systemdk_player_autobuffer']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_CONTROLS","'.$data_array['systemdk_player_controls']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOPLAY","'.$data_array['systemdk_player_audioautoplay']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOAUTOBUFFER","'.$data_array['systemdk_player_audioautobuffer']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_AUDIOCONTROLS","'.$data_array['systemdk_player_audio_controls']."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_RATIO","'.$this->registry->main_class->float_to_db($data_array['systemdk_player_ratio'])."\");\n";
        $content .= 'define("SYSTEMDK_PLAYER_SKIN","'.$data_array['systemdk_player_skin']."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM","'.$data_array['systemdk_ipantispam']."\");\n";
        $content .= 'define("SYSTEMDK_IPANTISPAM_NUM","'.$data_array['systemdk_ipantispam_num']."\");\n";
        $content .= 'define("SYSTEMDK_FEEDBACK_RECEIVER","'.$data_array['systemdk_feedback_receiver']."\");\n";
        $write_file = @fwrite($file,$content);
        if(!$write_file) {
            $this->error = 'not_write';
            return;
        } else {
            $this->registry->main_class->clearAllCache();
            $this->result = 'ok';
        }
        @fclose($file);
        @chmod("../includes/data/config.inc",0604);
    }


    public function clearallcache() {
        $this->result = false;
        $this->error = false;
        $done = $this->registry->main_class->clearAllCache();
        if($done) {
            $this->result = 'ok';
        } else {
            $this->error = 'clear_no';
        }
    }
}