<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      systemdk.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class model_theme_systemdk extends model_base {


    private $langlist;


    public function display_theme_header() {
        $this->registry->main_class->assign("site_charset",SITE_CHARSET);
        $this->registry->main_class->assign("site_name",SITE_NAME);
        $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
        $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
        $this->registry->main_class->assign("site_language",$this->registry->sitelang);
        $this->registry->main_class->assign("language",$this->registry->language);
        $this->registry->main_class->assign("site_logo",SITE_LOGO);
        $this->registry->main_class->assign("systemdk_account_registration",SYSTEMDK_ACCOUNT_REGISTRATION);
        $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
        $this->registry->main_class->assign("system_dir",SITE_DIR);
        $this->registry->main_class->assign("system_url",SITE_URL."/".SITE_DIR);
        $this->langlist = $this->display_theme_langlist('themes/'.SITE_THEME.'/languages');
        $this->registry->main_class->assign("langlist",$this->langlist);
        $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
        $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
        $this->registry->main_class->configLoad('theme.conf');
        $this->registry->main_class->configLoad($this->registry->language."/index.conf");
        $this->registry->main_class->assign("include_header","header.html");
        $this->registry->main_class->block("header");
        $this->registry->main_class->block("left");
        $this->registry->main_class->assign("include_center_up","mainup.html");
    }


    public function display_theme_footer() {
        $this->registry->main_class->assign("include_center_down","maindown.html");
        $this->registry->main_class->block("right");
        $this->registry->main_class->block("footer");
        $include_footer_data = file_get_contents(__SITE_PATH."/includes/data/footer.inc",FILE_USE_INCLUDE_PATH);
        $this->registry->main_class->assign("include_footer_data_tpl",$include_footer_data);
        $this->registry->main_class->assign("systemdk_statistics",SYSTEMDK_STATISTICS);
        $this->registry->main_class->assign("systemdk_player_autoplay",SYSTEMDK_PLAYER_AUTOPLAY);
        $this->registry->main_class->assign("systemdk_player_autobuffer",trim(SYSTEMDK_PLAYER_AUTOBUFFER));
        $this->registry->main_class->assign("systemdk_player_audioautoplay",trim(SYSTEMDK_PLAYER_AUDIOAUTOPLAY));
        $this->registry->main_class->assign("systemdk_player_skin",SYSTEMDK_PLAYER_SKIN);
        $this->registry->main_class->assign("include_footer","footer.html");
    }


    public function display_theme_headerbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_headerdata",$blocks_alldata);
    }


    public function display_theme_centerupbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_centerupdata",$blocks_alldata);
    }


    public function display_theme_centerdownbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_centerdowndata",$blocks_alldata);
    }


    public function display_theme_leftbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_leftdata",$blocks_alldata);
        if(!isset($this->registry->home_page)) {
            $this->registry->main_class->assign("homepage","false");
        } else {
            $this->registry->main_class->assign("homepage","true");
        }
    }


    public function display_theme_rightbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_rightdata",$blocks_alldata);
    }


    public function display_theme_footerbox($blocks_alldata) {
        $this->registry->main_class->assign("blocks_all_footerdata",$blocks_alldata);
    }


    public function display_theme_adminheader($error = false) {
        $this->registry->main_class->assign("site_charset",SITE_CHARSET);
        $this->registry->main_class->assign("site_keywords",SITE_KEYWORDS);
        $this->registry->main_class->assign("site_description",SITE_DESCRIPTION);
        $this->registry->main_class->assign("site_language",$this->registry->sitelang);
        $this->registry->main_class->assign("language",$this->registry->language);
        $this->registry->main_class->assign("site_logo",SITE_LOGO);
        $this->registry->main_class->assign("system_mod_rewrite",SYSTEMDK_MODREWRITE);
        $this->registry->main_class->assign("system_dir",SITE_DIR);
        $this->langlist = $this->display_theme_langlist(__SITE_PATH.'/themes/'.SITE_THEME.'/languages');
        $this->registry->main_class->assign("langlist",$this->langlist);
        $include_header_data = file_get_contents(__SITE_PATH."/includes/data/header.inc",FILE_USE_INCLUDE_PATH);
        $this->registry->main_class->assign("include_header_data_tpl",$include_header_data);
        if($error === false) {
            $this->registry->main_class->assign("site_name",SITE_NAME);
            $this->registry->main_class->configLoad('theme.conf');
            $this->registry->main_class->configLoad($this->registry->language."/systemadmin/main.conf");
            $this->registry->main_class->assign("session_id",session_id());
        }
    }


    public function display_theme_adminmain() {
        $this->registry->main_class->assign("include_header","systemadmin/adminheader.html");
        $include_footer_data = file_get_contents(__SITE_PATH."/includes/data/footer.inc",FILE_USE_INCLUDE_PATH);
        $this->registry->main_class->assign("include_footer_data_tpl",$include_footer_data);
        $this->registry->main_class->assign("systemdk_statistics",SYSTEMDK_STATISTICS);
        $this->registry->main_class->assign("include_footer","systemadmin/adminfooter.html");
    }


    public function display_theme_langlist($path) {
        if(!isset($this->langlist)) {
            if(is_dir($path)) {
                if($fileread = opendir($path)) {
                    $i = 0;
                    $k = 0;
                    while(false !== ($file = readdir($fileread))) {
                        if(!preg_match("/[.]/",$file)) {
                            if(file_exists(__SITE_PATH."/includes/data/".$file."_installed.dat")) {
                                $this->langlist[] = array(
                                    "item" => $i,
                                    "name" => $file,
                                    "installed" => "yes",
                                    "count_installed" => $k
                                );
                                $k++;
                            } else {
                                $this->langlist[] = array(
                                    "item" => $i,
                                    "name" => $file,
                                    "installed" => "no",
                                    "count_installed" => 0
                                );
                            }
                            $i++;
                        }
                    }
                    closedir($fileread);
                }
            }
            if(!isset($this->langlist)) {
                $this->langlist = "no";
            }
        }
        return $this->langlist;
    }


    public function display_theme_systemerror($kind,$text) {
        $this->registry->main_class->assign("error_kind",$kind);
        $this->registry->main_class->assign("error_text",$text);
    }
}

?>