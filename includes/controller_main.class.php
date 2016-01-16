<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_main.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class controller_main extends controller_base
{


    private $content;


    public function __construct($registry)
    {
        parent::__construct($registry);
    }


    public function index()
    {
        if (isset($_GET['lang']) and trim($_GET['lang']) != "") {
            $this->registry->GET_lang = substr($this->format_striptags(trim($_GET['lang'])), 0, 2);
        }
        if (isset($_GET['ilang']) and trim($_GET['ilang']) != "") {
            $this->registry->GET_ilang = substr($this->format_striptags(trim($_GET['ilang'])), 0, 2);
        }
        if (isset($_GET['func']) and trim($_GET['func']) != "") {
            $this->registry->GET_func = $this->format_striptags(trim($_GET['func']));
        }
        if (isset($_POST['func']) and trim($_POST['func']) != "") {
            $this->registry->POST_func = $this->format_striptags(trim($_POST['func']));
        }
        if (isset($_POST['user_login']) and trim($_POST['user_login']) != "") {
            $this->registry->POST_user_login = $this->format_striptags(trim($_POST['user_login']));
        }
        if (isset($_POST['user_password']) and trim($_POST['user_password']) != "") {
            $this->registry->POST_user_password = trim($_POST['user_password']);
        }
        if (isset($_POST['captcha']) and trim($_POST['captcha']) != "") {
            $this->registry->POST_captcha = $this->format_striptags(trim($_POST['captcha']));
        }
        $this->assign("display_site_lang", SYSTEMDK_SITE_LANG);
        if (defined('SYSTEMDK_SITE_LANG') and SYSTEMDK_SITE_LANG === "yes") {
            $this->assign("display_adminsite_lang", "yes");
        } else {
            $this->assign("display_adminsite_lang", SYSTEMDK_ADMIN_SITE_LANG);
        }
        $this->assign("display_int_lang", SYSTEMDK_INTERFACE_LANG);
        if (defined('SYSTEMDK_INTERFACE_LANG') and SYSTEMDK_INTERFACE_LANG === "yes") {
            $this->assign("display_adminint_lang", "yes");
        } else {
            $this->assign("display_adminint_lang", SYSTEMDK_ADMIN_INTERFACE_LANG);
        }
        $this->run("register_plugins");
        $result = $this->model->index();
        if ($result === 'lang_file') {
            $this->assign_data();
            if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->registry->controller_theme->display_theme_systemerror(
                    "Fatal Error",
                    "SystemDK can't find language files: " . __SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $this->registry->language . "/" . ADMIN_DIR
                );
                $this->display("error2.html", $this->registry->sitelang . "|fatalerror|admlangfile|" . $this->registry->language);
            } else {
                $this->registry->controller_theme->display_theme_systemerror(
                    "Fatal Error", "SystemDK can't find language files: " . __SITE_PATH . "/themes/" . SITE_THEME . "/languages/" . $this->registry->language
                );
                $this->display("error2.html", $this->registry->sitelang . "|fatalerror|langfile|" . $this->registry->language);
            }
            exit();
        } elseif ($result === 'ban_ip' || $result === 'find_install' || $result === 'version_php') {
            if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                if (SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /" . SITE_DIR . "lang/" . $this->registry->sitelang . "/");
                } else {
                    header("Location: /" . SITE_DIR . "index.php?lang=" . $this->registry->sitelang);
                }
                exit();
            }
            $this->assign_data();
            $this->assign("error_kind", "no");
            $this->assign("error_text", "no");
            $this->assign("error", "error_" . $result);
            $this->display("error2.html", $this->registry->sitelang . "|error|" . $result . "|" . $this->registry->language);
            exit();
        } elseif ($result === 'site_off') {
            $this->assign_data();
            $this->assign("error_kind", "no");
            $this->assign("error_text", "no");
            $this->assign("error", "messege_siteoff");
            if (defined('SITE_STATUS_NOTES') and SITE_STATUS_NOTES != "") {
                $this->assign("site_status_notes", SITE_STATUS_NOTES);
                $this->display("error2.html", $this->registry->sitelang . "|siteoff|withnotes|" . $this->registry->language);
            } else {
                $this->assign("site_status_notes", "no");
                $this->display("error2.html", $this->registry->sitelang . "|siteoff|" . $this->registry->language);
            }
            exit();
        } elseif ($result === 'find_tags') {
            $this->assign_data();
            $this->assign("error_kind", "no");
            $this->assign("error_text", "no");
            $this->assign("error", "error_tagerror");
            $this->assign("error_addr", $this->registry->my_cache_id);
            if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->display("error2.html", $this->registry->sitelang . "|error|admtag|" . $this->registry->language);
            } else {
                $this->display("error2.html", $this->registry->sitelang . "|error|tag|" . $this->registry->language);
            }
            exit();
        } elseif ($result === 'no_db_connect') {
            $this->assign_data();
            $this->assign("error", "error_noconnect");
            if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes') {
                $this->display("systemadmin/error.html", $this->registry->sitelang . "|error|errorconnect|" . $this->registry->language);
            } else {
                $this->assign("error_kind", "no");
                $this->assign("error_text", "no");
                $this->display("error2.html", $this->registry->sitelang . "|error|noconnect|" . $this->registry->language);
            }
            exit();
        }
        if ($this->is_admin()) {
            $this->assign("admin", "yes");
        } else {
            $this->assign("admin", "no");
        }
        $this->assign("admin_folder", ADMIN_DIR);
        if (!defined('__SITE_ADMIN_PART')) {
            if ($this->module_is_active('shop')) {
                $controller_shop = singleton::getinstance('controller_shop', $this->registry);
                $controller_shop->shop_header();
            }
        }
        if (defined('__SITE_ADMIN_PART') and __SITE_ADMIN_PART === 'yes' and !empty($this->registry->module_account_error)) {
            $controller_admin_administrator = singleton::getinstance('controller_admin_administrator', $this->registry);
            $controller_admin_administrator->enter_error();
        }
    }


    private function assign_data()
    {
        $this->assign("site_charset", SITE_CHARSET);
        $this->assign("site_name", SITE_NAME);
        $this->assign("site_keywords", SITE_KEYWORDS);
        $this->assign("site_description", SITE_DESCRIPTION);
        $this->assign("site_language", $this->registry->sitelang);
        $this->assign("language", $this->registry->language);
        $this->assign("site_logo", SITE_LOGO);
        $this->assign("system_mod_rewrite", SYSTEMDK_MODREWRITE);
        $this->assign("system_dir", SITE_DIR);
        $langlist = $this->registry->controller_theme->display_theme_langlist(__SITE_PATH . '/themes/' . SITE_THEME . '/languages');
        $this->assign("langlist", $langlist);
        $include_header_data = file_get_contents(__SITE_PATH . "/includes/data/header.inc", FILE_USE_INCLUDE_PATH);
        $this->assign("include_header_data_tpl", $include_header_data);
    }


    public function processing_data($processing_content, $need = 'no')
    {
        return $this->model->processing_data($processing_content, $need);
    }


    public function extracting_data($extracting_content)
    {
        return $this->model->extracting_data($extracting_content);
    }


    public function extracting_data2($extracting_content)
    {
        return $this->model->extracting_data2($extracting_content);
    }


    public function checkneed_stripslashes($stripslashes_content)
    {
        return $this->model->checkneed_stripslashes($stripslashes_content);
    }


    public function format_htmlspecchars($format_content)
    {
        return $this->model->format_htmlspecchars($format_content);
    }


    public function format_striptags($format_content, $allowable_tags = 'no')
    {
        return $this->model->format_striptags($format_content, $allowable_tags);
    }


    public function format_htmlspecchars_stripslashes_striptags($format_content)
    {
        return $this->model->format_htmlspecchars_stripslashes_striptags($format_content);
    }


    public function database_close()
    {
        $this->model->database_close();
    }


    public function db_process_sequence($sequence, $for_field)
    {
        return $this->model->db_process_sequence($sequence, $for_field);
    }


    public function db_get_last_insert_id()
    {
        return $this->model->db_get_last_insert_id();
    }


    public function check_db_need_lobs()
    {
        return $this->model->check_db_need_lobs();
    }


    public function check_db_need_from_clause()
    {
        return $this->model->check_db_need_from_clause();
    }


    public function salt($cost = 13)
    {
        return $this->model->salt($cost);
    }


    public function logout()
    {
        $this->model->logout();
    }


    public function is_user()
    {
        return $this->model->is_user();
    }


    public function is_admin()
    {
        return $this->model->is_admin();
    }


    public function get_user_id()
    {
        return $this->model->get_user_id();
    }


    public function get_user_rights()
    {
        return $this->model->get_user_rights();
    }


    public function get_user_login()
    {
        return trim($this->model->get_user_login());
    }


    public function get_user_name()
    {
        return $this->model->get_user_name();
    }


    public function gzip_out($level = 3, $debug = 0)
    {
        $encoding = $this->model->check_can_gzip();
        if ($encoding) {
            $contents = ob_get_contents();
            ob_end_clean();
            if ($debug) {
                echo("\n<!-- Not compress length: " . strlen($contents) . " -->\n");
                echo("\n<!-- Compressed length: " . strlen(gzcompress($contents, $level)) . " -->\n");
            }
            header("Content-Encoding: $encoding");
            print "\x1f\x8b\x08\x00\x00\x00\x00\x00";
            $contents = gzcompress($contents, $level);
            $contents = substr($contents, 0, strlen($contents) - 4);
            print $contents;
        } else {
            ob_end_flush();
        }
        //exit();
    }


    public function path($controller)
    {
        return $this->model->path($controller);
    }


    public function process_file_name($file)
    {
        return $this->model->process_file_name($file);
    }


    public function check_email($email)
    {
        return $this->model->check_email($email);
    }


    public function set_sitemeta($title = 'no', $description = 'no', $keywords = 'no')
    {
        $result = $this->model->set_sitemeta($title, $description, $keywords);
        if ($result['site_name']) {
            $this->assign("site_name", $result['site_name'] . " &raquo; " . SITE_NAME);
        }
        if ($result['site_description']) {
            $this->assign("site_description", $result['site_description']);
        }
        if ($result['site_keywords']) {
            $this->assign("site_keywords", $result['site_keywords']);
        }
    }


    public function set_page_title($title)
    {
        $this->assign("page_title", $title);
    }


    public function search_player_entry($content)
    {
        if ($this->check_player_entry($content)) {
            $this->assign("systemdk_display_player", "yes");
        }

        return $this->content;
    }


    public function check_player_entry($content)
    {
        $result = $this->model->check_player_entry($content);
        $this->content = $result['content'];
        if ($result['systemdk_html5_exist']) {
            $this->assign("systemdk_html5_exist", "yes");
        }

        return $result['search_flowplayer'];
    }


    public function split_content_by_pages($content)
    {
        return $this->model->split_content_by_pages($content);
    }


    public function encode_rss_text($text, $rss_encoding)
    {
        return $this->model->encode_rss_text($text, $rss_encoding);
    }


    public function format_size($size)
    {
        return $this->model->format_size($size);
    }


    public function set_locale()
    {
        $this->model->set_locale();
    }


    public function float_to_db($float_value)
    {
        return $this->model->float_to_db($float_value);
    }


    public function get_time()
    {
        return $this->model->get_time();
    }


    public function time_to_unix($string)
    {
        return $this->model->time_to_unix($string);
    }


    public function module_is_active($module)
    {
        return $this->model->module_is_active($module);
    }


    public function block_is_active($block)
    {
        return $this->model->block_is_active($block);
    }


    public function systemdk_clearcache($clearcachepath)
    {
        $clear_langlist = $this->registry->controller_theme->display_theme_langlist(__SITE_PATH . '/themes/' . SITE_THEME . '/languages');
        for ($i = 0, $size = sizeof($clear_langlist); $i < $size; ++$i) {
            $this->clearCache(null, $clear_langlist[$i]['name'] . "|" . $clearcachepath);
        }
    }


    public function block($block_locate)
    {
        $blocks_alldata = $this->model->block($block_locate);
        if (!empty($blocks_alldata)) {
            foreach ($blocks_alldata as &$content) {
                if (!empty($content['block_content']) and ($content['block_content'] === 'error_file' || $content['block_content'] === 'error_content')) {
                    $content['block_content'] = $this->block_error($content['block_content']);
                }
            }
            unset($content);
        }
        if ($block_locate == "header") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_headerbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_headerbox("false");
            }
        } elseif ($block_locate == "center-up") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_centerupbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_centerupbox("false");
            }
        } elseif ($block_locate == "center-down") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_centerdownbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_centerdownbox("false");
            }
        } elseif ($block_locate == "left") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_leftbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_leftbox("false");
            }
        } elseif ($block_locate == "right") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_rightbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_rightbox("false");
            }
        } elseif ($block_locate == "footer") {
            if (!empty($blocks_alldata)) {
                $this->registry->controller_theme->display_theme_footerbox($blocks_alldata);
            } else {
                $this->registry->controller_theme->display_theme_footerbox("false");
            }
        }
    }


    private function block_error($error)
    {
        $param = 'block_' . $error;
        if (!empty($this->registry->$param)) {
            return $this->registry->$param;
        }
        $this->assign("block_error", $error);
        $this->registry->$param = $this->fetch("block_error.html", $this->registry->sitelang . "|error|block_" . $error . "|" . $this->registry->language);

        return $this->registry->$param;
    }


    public function blocks_check_process()
    {
        $this->model->blocks_check_process();
    }


    public function messages_check_process()
    {
        $this->model->messages_check_process();
    }


    public function header()
    {
        $this->model->header();
        $this->model->blocks_check_process();
        $this->registry->controller_theme->display_theme_header();
        if (isset($this->registry->home_page) and $this->registry->home_page == "1") {
            $this->model->messages_check_process();
            $this->messages();
            $this->block("center-up");
        }
    }


    public function footer()
    {
        if (isset($this->registry->home_page) and $this->registry->home_page == "1") {
            $this->block("center-down");
        }
        $this->registry->controller_theme->display_theme_footer();
    }


    public function load_home($status = 'ok')
    {
        $this->set_sitemeta($this->getConfigVars('_MAINPAGELINK'));
        if ($status === 'ok') {
            $this->assign("include_center", "no");
            $this->display("index.html", $this->registry->sitelang . "|homepage|" . $this->registry->language);
        } else {
            $this->assign("error", "error_hommodule");
            $this->assign("include_center", "error.html");
            $this->display("index.html", $this->registry->sitelang . "|error|homepage|" . $this->registry->language);
            exit();
        }
    }


    public function messages()
    {
        $message_alldata = $this->model->messages();
        if (!empty($message_alldata)) {
            $this->assign("message_alldata", $message_alldata);
        } else {
            $this->assign("message_alldata", "no");
        }
    }


    public function block_rss($block_customname, $block_content, $block_url, $block_refresh, $block_lastrefresh, $block_id, $admin_rss_action = false)
    {
        $result = $this->model->block_rss($block_url, $block_refresh, $block_lastrefresh, $admin_rss_action);
        if (!empty($result['rss_link']) and $result['rss_link'] === 'no') {
            if ($admin_rss_action) {
                return $result;
            }
            $this->assign("rss_data", "no");
            $this->assign("url", $block_url);
            $this->assign("language", $this->registry->language);
            $cont = $this->fetch("systemadmin/modules/blocks/blockrss.html");
            $result = $this->model->update_block_rss(
                [
                    'case'      => 'case1',
                    'cont'      => $cont,
                    'block_id'  => $block_id,
                    'block_url' => $block_url,
                ]
            );
            $this->assign("rss_data", "addline");
            $this->assign("rssurl", $result['rss_url']);
            $block_content = $result['block_content'];
            $block_content .= $this->fetch("systemadmin/modules/blocks/blockrss.html");
            $block_alldata = ["block_customname" => $block_customname, "block_content" => $block_content];

            return $block_alldata;
        } elseif (!empty($result['rss_link']) and $result['rss_link'] === 'no2') {
            if ($admin_rss_action) {
                return $result;
            }
            $block_content = "";
            $this->model->update_block_rss(
                [
                    'case'          => 'case2',
                    'block_content' => $block_content,
                    'block_id'      => $block_id,
                ]
            );
            $block_alldata = [
                "block_customname" => $block_customname,
                "block_content"    => $block_content,
            ];

            return $block_alldata;
        } elseif ($result) {
            $block_content = "";
            if (!empty($result['rss_link'])) {
                $this->assign("language", $this->registry->language);
                $this->assign("rss_data", $result['rss_link']);
                $cont = $this->fetch("systemadmin/modules/blocks/blockrss.html");
                $cont = $this->processing_data($cont, 'need');
                $cont = substr(substr($cont, 0, -1), 1);
                $block_content = $cont;
            }
            if ($admin_rss_action) {
                return $block_content;
            }
            $result = $this->model->update_block_rss(
                [
                    'case'          => 'case3',
                    'block_id'      => $block_id,
                    'block_content' => $block_content,
                ]
            );
            $block_content = $this->extracting_data2($block_content);
        }
        $rssurl = preg_replace("/http:\/\//i", "", $block_url);
        $rssurl = explode("/", $rssurl);
        if ((isset($result['context']) and ($result['context'] == 1)) || (!empty($block_content))) {
            $this->assign("rss_data", "addline");
            $this->assign("rssurl", $rssurl[0]);
            $this->assign("language", $this->registry->language);
            $block_content .= $this->fetch("systemadmin/modules/blocks/blockrss.html");
        }
        if ((isset($result['context']) and ($result['context'] == 0)) || (empty($block_content))) {
            $this->assign("rss_data", "error");
            $this->assign("language", $this->registry->language);
            $block_content = $this->fetch("systemadmin/modules/blocks/blockrss.html");
        }
        $block_alldata = ["block_customname" => $block_customname, "block_content" => $block_content];

        return $block_alldata;
    }


    public function assign_admin_info()
    {
        $this->assign("admin_name", $this->get_user_name());
        $this->assign("admin_id", $this->get_user_id());
    }


    public function load_controller_error($error, $admin_part = false, $method = false)
    {
        if ($admin_part) {
            $this->registry->controller_theme->display_theme_adminheader();
            $this->registry->controller_theme->display_theme_adminmain();
            $this->assign_admin_info();
            $this->assign("admin_class", $error);
            if ($method) {
                $this->assign("admin_method", $method);
                $cache = 'nomethod';
            } else {
                $cache = 'nomodule';
            }
            if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|error|" . $cache . "|" . $this->registry->language)) {
                $this->assign("admin_edit", $cache);
                $this->assign("include_center_up", "systemadmin/adminup.html");
                $this->assign("include_center", "systemadmin/admin_update.html");
            }
            $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|error|" . $cache . "|" . $this->registry->language);
        } else {
            $this->assign("error", "error_" . $error);
            $this->assign("include_center", "error.html");
            $this->display("index.html", $this->registry->sitelang . "|error|" . $error . "|" . $this->registry->language);
        }
        exit();
    }


    public function get_user_info($user_id = false, $only_active = true)
    {
        $model_account = singleton::getinstance('account', $this->registry);
        if (is_object($model_account)) { //and $this->module_is_active('account')
            if (!$user_id) {
                $user_id = $this->get_user_id();
            }

            return $model_account->get_user_info($user_id, $only_active);
        }

        return false;
    }
}