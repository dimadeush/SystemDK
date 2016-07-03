<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_modules.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_admin_modules extends controller_base
{


    /**
     * @var admin_modules
     */
    private $model_admin_modules;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_modules = singleton::getinstance('admin_modules', $registry);
    }


    private function modules_view($type, $cache_category = false, $template = false, $title)
    {
        $this->configLoad($this->registry->language . "/systemadmin/modules/modules/modules.conf");
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|modules|error';
        }
        if (empty($template)) {
            $template = 'edit_module.html';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if ($template === 'edit_module.html') {
                $this->assign("module_edit_save", $type);
            }
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/modules/" . $template);
        }
        $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        $title = '_MENUMODULES';
        $type = 'show';
        $cache_category = 'modules|modules';
        $cached = true;
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $cached = false;
        }
        $this->model_admin_modules->index($cached);
        $this->model_admin_modules->display_menu();
        $error = $this->model_admin_modules->get_property_value('error');
        $error_array = $this->model_admin_modules->get_property_value('error_array');
        $result = $this->model_admin_modules->get_property_value('result');
        if ($error === 'show_sql_error') {
            $type = $error;
            $cache_category = false;
        }
        $this->assign_array($result);
        if (!empty($error_array)) {
            $this->assign("error", $error_array);
        }
        $template = 'modules.html';
        $this->modules_view($type, $cache_category, $template, $title);
    }


    public function edit_module()
    {
        $module_id = 0;
        if (isset($_GET['module_id'])) {
            $module_id = intval($_GET['module_id']);
        }
        $title = '_ADMINISTRATIONEDITMODULEPAGETITLE';
        $cache_category = 'modules|modules|edit';
        $template = false;
        if (!$this->isCached(
            "systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $module_id . "|" . $this->registry->language
        )
        ) {
            $this->model_admin_modules->edit_module($module_id);
        }
        $this->model_admin_modules->display_menu();
        $error = $this->model_admin_modules->get_property_value('error');
        $error_array = $this->model_admin_modules->get_property_value('error_array');
        $result = $this->model_admin_modules->get_property_value('result');
        $this->assign_array($result);
        if ($error === 'empty_data' || $error === 'edit_sql_error' || $error === 'edit_not_found' || $error === 'show_sql_error') {
            if ($error === 'empty_data') {
                header("Location: index.php?path=admin_modules&func=index&lang=" . $this->registry->sitelang);
                exit();
            }
            if ($error === 'show_sql_error') {
                $error = 'edit_sql_error';
            }
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->modules_view($error, $cache_category, $template, $title);
        }
        $this->modules_view($module_id, $cache_category, $template, $title);
    }


    public function edit_module_save()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'module_customname',
            ];
            $keys2 = [
                'module_id',
                'module_status',
                'module_valueview',
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
        $title = '_ADMINISTRATIONEDITMODULEPAGETITLE';
        $template = false;
        $this->model_admin_modules->edit_module_save($data_array);
        $this->model_admin_modules->display_menu();
        $error = $this->model_admin_modules->get_property_value('error');
        $error_array = $this->model_admin_modules->get_property_value('error_array');
        $result = $this->model_admin_modules->get_property_value('result');
        $this->assign_array($result);
        if ($error === 'edit_not_all_data' || $error === 'edit_found' || $error === 'edit_sql_error' || $error === 'edit_ok' || $error === 'show_sql_error') {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            if ($error === 'show_sql_error') {
                $error = 'edit_sql_error';
            }
            $cache_category = false;
            $this->modules_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|modules';
        $this->modules_view($result['module_edit_save'], $cache_category, $template, $title);
    }


    public function status_module()
    {
        $data_array = false;
        if (isset($_GET['status'])) {
            $data_array['status'] = trim($_GET['status']);
        }
        if (isset($_GET['module_id'])) {
            $data_array['module_id'] = intval($_GET['module_id']);
        }
        $title = '_MENUMODULES';
        $template = false;
        $this->model_admin_modules->status_module($data_array);
        $this->model_admin_modules->display_menu();
        $error = $this->model_admin_modules->get_property_value('error');
        $error_array = $this->model_admin_modules->get_property_value('error_array');
        $result = $this->model_admin_modules->get_property_value('result');
        $this->assign_array($result);
        if ($error === 'empty_data' || $error === 'unknown_action' || $error === 'status_sql_error' || $error === 'status_ok' || $error === 'show_sql_error') {
            if ($error === 'empty_data' || $error === 'unknown_action') {
                header("Location: index.php?path=admin_modules&func=index&lang=" . $this->registry->sitelang);
                exit();
            }
            if ($error === 'show_sql_error') {
                $error = 'status_sql_error';
            }
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->modules_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|modules|status';
        $this->modules_view($result['module_edit_save'], $cache_category, $template, $title);
    }
}