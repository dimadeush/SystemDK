<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_uploads.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_admin_uploads extends controller_base
{


    /**
     * @var admin_uploads
     */
    private $model_admin_uploads;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_uploads = singleton::getinstance('admin_uploads', $registry);
    }


    public function index()
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|modules|uploads|show|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars('_MENUUPLOADS'));
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/uploads/uploads.html");
        }
        $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|modules|uploads|show|" . $this->registry->language);
    }
}