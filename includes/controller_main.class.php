<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_main.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2013 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.0
 */
class controller_main extends controller_base {


    private $model_theme;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_theme = singleton::getinstance('model_theme',$this->registry);
    }


    public function configLoad($config) {
        $this->template->configLoad($config);
    }


    public function getConfigVars($index) {
        return $this->template->getConfigVars($index);
    }


    public function assign($index,$value) {
        $this->template->$index = array("name" => $index,"value" => $value);
    }


    public function run($name) {
        $this->template->$name();
    }


    public function isCached($template,$group) {
        return $this->template->isCached($template,$group);
    }


    public function clearCache($template,$group) {
        $this->template->clearCache($template,$group);
    }


    public function clearAllCache() {
        return $this->template->clearAllCache();
    }


    public function fetch($template,$group = false) {
        return $this->template->fetch($template,$group);
    }


    public function display($name,$group) {
        $this->template->display($name,$group);
    }


    public function index() {
        $this->model->index();
    }


    public function gzip_out($level = 3,$debug = 0){
        $this->model->gzip_out($level,$debug);
    }


    public function systemdk_is_admin() {
        return $this->model->systemdk_is_admin();
    }


    public function get_admin_id() {
        return $this->model->get_admin_id();
    }


    public function get_admin_rights() {
        return $this->model->get_admin_rights();
    }


    public function get_admin_login() {
        return $this->model->get_admin_login();
    }


    public function displayadmininfo() {
        $this->model->displayadmininfo();
    }


    public function load_controller_error($error,$admin_part = false,$method = false) {
        $this->model->load_controller_error($error,$admin_part,$method);
    }


    public function path($controller) {
        return $this->model->path($controller);
    }


    public function blocks_autocheck() {
        $this->model->blocks_autocheck();
    }


    public function modules_account_autodelete() {
        $this->model->modules_account_autodelete();
    }


    public function systemdk_clearcache($clearcachepath) {
        $this->model->systemdk_clearcache($clearcachepath);
    }


    public function modules_feedback_autodelete() {
        $this->model->modules_feedback_autodelete();
    }


    public function modules_news_programm() {
        $this->model->modules_news_programm();
    }


    public function database_close() {
        $this->model->database_close();
    }


    public function check_db_need_lobs() {
        return $this->model->check_db_need_lobs();
    }


    public function check_db_need_from_clause() {
        return $this->model->check_db_need_from_clause();
    }


    public function check_email($email) {
        return $this->model->check_email($email);
    }


    public function encode_rss_text($text,$rss_encoding) {
        return $this->model->encode_rss_text($text,$rss_encoding);
    }


    public function processing_data($processing_content,$need = 'no') {
        return $this->model->processing_data($processing_content,$need);
    }


    public function extracting_data($extracting_content) {
        return $this->model->extracting_data($extracting_content);
    }


    public function extracting_data2($extracting_content) {
        return $this->model->extracting_data2($extracting_content);
    }


    public function checkneed_stripslashes($stripslashes_content) {
        return $this->model->checkneed_stripslashes($stripslashes_content);
    }


    public function format_htmlspecchars($format_content) {
        return $this->model->format_htmlspecchars($format_content);
    }


    public function format_striptags($format_content,$allowable_tags = 'no') {
        return $this->model->format_striptags($format_content,$allowable_tags);
    }


    public function format_htmlspecchars_stripslashes_striptags($format_content) {
        return $this->model->format_htmlspecchars_stripslashes_striptags($format_content);
    }


    public function search_player_entry($content) {
        return $this->model->search_player_entry($content);
    }


    public function check_player_entry($content) {
        return $this->model->check_player_entry($content);
    }


    public function formatsize($size) {
        return $this->model->formatsize($size);
    }


    public function set_sitemeta($title = 'no',$description = 'no',$keywords = 'no') {
        $this->model->set_sitemeta($title,$description,$keywords);
    }


    public function set_locale() {
        $this->model->set_locale();
    }


    public function float2db($float_value) {
        return $this->model->float2db($float_value);
    }


    public function shop_header() {
        $this->model->shop_header();
    }


    public function is_user_in_mainfunc() {
        return $this->model->is_user_in_mainfunc();
    }


    public function is_admin_in_mainfunc() {
        return $this->model->is_admin_in_mainfunc();
    }


    public function is_user($user) {
        return $this->model->is_user($user);
    }


    public function is_admin($admin) {
        return $this->model->is_admin($admin);
    }


    public function module_is_active($module) {
        return $this->model->module_is_active($module);
    }


    public function block_is_active($block) {
        return $this->model->block_is_active($block);
    }


    public function block($block_locate) {
        $this->model->block($block_locate);
    }


    public function block_rss($block_id,$block_locate) {
        return $this->model->block_rss($block_id,$block_locate);
    }


    public function messages() {
        $this->model->messages();
    }


    public function get_user_info($user) {
        return $this->model->get_user_info($user);
    }


    public function gettime() {
        return $this->model->gettime();
    }


    public function timetounix($string) {
        return $this->model->timetounix($string);
    }


    public function main_pages() {
        $this->model->main_pages();
    }


    public function messages_check() {
        $this->model->messages_check();
    }


    public function header() {
        $this->model->header();
    }


    public function footer() {
        $this->model->footer();
    }


    public function loadhome($status = 'ok') {
        $this->model->loadhome($status);
    }


    public function display_theme_langlist($path) {
        return $this->model_theme->display_theme_langlist($path);
    }


    public function display_theme_systemerror($kind,$text) {
        $this->model_theme->display_theme_systemerror($kind,$text);
    }


    public function display_theme_headerbox($blocks_alldata) {
        $this->model_theme->display_theme_headerbox($blocks_alldata);
    }


    public function display_theme_centerupbox($blocks_alldata) {
        $this->model_theme->display_theme_centerupbox($blocks_alldata);
    }


    public function display_theme_centerdownbox($blocks_alldata) {
        $this->model_theme->display_theme_centerdownbox($blocks_alldata);
    }


    public function display_theme_leftbox($blocks_alldata) {
        $this->model_theme->display_theme_leftbox($blocks_alldata);
    }


    public function display_theme_rightbox($blocks_alldata) {
        $this->model_theme->display_theme_rightbox($blocks_alldata);
    }


    public function display_theme_footerbox($blocks_alldata) {
        $this->model_theme->display_theme_footerbox($blocks_alldata);
    }


    public function display_theme_header() {
        $this->model_theme->display_theme_header();
    }


    public function display_theme_adminheader($error = false) {
        $this->model_theme->display_theme_adminheader($error);
    }


    public function display_theme_adminmain() {
        $this->model_theme->display_theme_adminmain();
    }


    public function display_theme_footer() {
        $this->model_theme->display_theme_footer();
    }


    public function mainenter() {
        $this->model->mainenter();
    }
}

?>