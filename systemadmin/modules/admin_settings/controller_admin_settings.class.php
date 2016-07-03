<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_settings.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_admin_settings extends controller_base
{


    /**
     * @var admin_settings
     */
    private $model_admin_settings;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_settings = singleton::getinstance('admin_settings', $registry);
    }


    private function settings_view($type, $cache_category = false, $template = false)
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|settings|error';
        }
        if (empty($template)) {
            $template = 'config.html';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MENUCONFIGS'));
            if ($template === 'config2.html' || $template === 'config3.html') {
                $this->assign("done", $type);
            }
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/settings/" . $template);
        }
        $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        $type = 'show';
        $cache_category = 'modules|settings';
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->model_admin_settings->index();
        } else {
            $cached = true;
            $this->model_admin_settings->index($cached);
        }
        $result = $this->model_admin_settings->get_property_value('result');
        $this->assign_array($result);
        $this->settings_view($type, $cache_category);
    }


    public function settingssave()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
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
                'systemdk_admin_interface_lang',
            ];
            $keys2 = [
                'cache_lifetime',
                'session_cookie_live',
                'systemdk_adminrows_perpage',
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
        $this->model_admin_settings->settingssave($data_array);
        $error = $this->model_admin_settings->get_property_value('error');
        $result = $this->model_admin_settings->get_property_value('result');
        $template = 'config2.html';
        if ($error === 'not_all_data' || $error === 'incorrect_email' || $error === 'no_file') {
            $cache_category = false;
            $this->settings_view($error, $cache_category, $template);
        }
        $cache_category = 'modules|settings|save';
        $this->settings_view($result, $cache_category, $template);
    }


    public function clearallcache()
    {
        $this->model_admin_settings->clearallcache();
        $error = $this->model_admin_settings->get_property_value('error');
        $result = $this->model_admin_settings->get_property_value('result');
        $template = 'config3.html';
        if ($error === 'clear_no') {
            $cache_category = false;
            $this->settings_view($error, $cache_category, $template);
        }
        $cache_category = 'modules|settings|clear';
        $this->settings_view($result, $cache_category, $template);
    }
}