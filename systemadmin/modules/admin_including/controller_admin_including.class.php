<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_including.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_admin_including extends controller_base
{


    /**
     * @var admin_including
     */
    private $model_admin_including;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_including = singleton::getinstance('admin_including', $registry);
    }


    private function including_view($type, $cache_category = false)
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|including|error';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MENUENTERS'));
            $this->assign("including_save", $type);
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/including/including.html");
        }
        $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        $type = 'show';
        $cache_category = 'modules|including';
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->model_admin_including->index();
            $error = $this->model_admin_including->get_property_value('error');
            $result = $this->model_admin_including->get_property_value('result');
            if ($error === 'no_file') {
                $this->including_view($error);
            }
            $this->assign_array($result);
        }
        $this->including_view($type, $cache_category);
    }


    public function including_save()
    {
        $post_array = false;
        if (isset($_POST['headerincluding'])) {
            $post_array['headerincluding'] = trim($_POST['headerincluding']);
        }
        if (isset($_POST['footerincluding'])) {
            $post_array['footerincluding'] = trim($_POST['footerincluding']);
        }
        $this->model_admin_including->including_save($post_array);
        $error = $this->model_admin_including->get_property_value('error');
        $result = $this->model_admin_including->get_property_value('result');
        if ($error === 'not_all_data' || $error === 'no_file' || $error === 'not_write') {
            $this->including_view($error);
        }
        $cache_category = 'modules|including|save';
        $this->including_view($result, $cache_category);
    }
}