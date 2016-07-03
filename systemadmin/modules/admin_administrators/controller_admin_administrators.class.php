<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_administrators.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_admin_administrators extends controller_base
{


    /**
     * @var admin_administrators
     */
    private $model_admin_administrators;
    /**
     * @var admin_users
     */
    private $model_admin_users;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_administrators = singleton::getinstance('admin_administrators', $registry);
        $this->model_admin_users = singleton::getinstance('admin_users', $registry);
    }


    private function administrators_view($type, $cache_category = false, $template = false, $title)
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|administrators|error';
        }
        if (empty($template)) {
            $template = 'adminupdate.html';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if ($template === 'adminupdate.html') {
                $this->assign("admin_update", $type);
            }
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/administrators/" . $template);
        }
        $this->display("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        if (isset($_GET['num_page']) && intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $type = 'show|' . $num_page;
        $cache_category = 'modules|administrators';
        $title = '_MENUADMINS';
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->model_admin_users->index($num_page, false, true);
            $error = $this->model_admin_users->get_property_value('error');
            $error_array = $this->model_admin_users->get_property_value('error_array');
            $result = $this->model_admin_users->get_property_value('result');
            if ($error === 'unknown_page' || $error === 'show_sql_error') {
                if ($error === 'unknown_page') {
                    header("Location: index.php?path=admin_administrators&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $this->administrators_view($error, false, false, $title);
            }
            $this->assign_array($result);
        }
        $template = 'admins.html';
        $this->administrators_view($type, $cache_category, $template, $title);
    }


    public function admin_add()
    {
        $admin = true;
        $this->model_admin_users->user_add($admin);
        $error = $this->model_admin_users->get_property_value('error');
        $title = '_ADMINISTRATIONADDADMINPAGETITLE';
        if ($error === 'add_rights') {
            $cache_category = false;
            $template = false;
            $this->administrators_view($error, $cache_category, $template, $title);
        }
        $type = 'add';
        $cache_category = 'modules|administrators';
        $template = 'adminadd.html';
        $this->administrators_view($type, $cache_category, $template, $title);
    }


    public function admin_add_inbase()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'admin_add_name',
                'admin_add_surname',
                'admin_add_website',
                'admin_add_email',
                'admin_add_login',
                'admin_add_password',
                'admin_add_password2',
                'admin_add_country',
                'admin_add_city',
                'admin_add_address',
                'admin_add_telephone',
                'admin_add_icq',
                'admin_add_skype',
                'admin_add_gmt',
                'admin_add_notes',
                'admin_add_activenotes',
            ];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($_POST['admin_add_active'])) {
            $data_array['admin_add_active'] = intval($_POST['admin_add_active']);
        }
        if (isset($_POST['admin_add_viewemail'])) {
            $data_array['admin_add_viewemail'] = intval($_POST['admin_add_viewemail']);
        }
        $data_array['admin'] = 1;
        $title = '_ADMINISTRATIONADDADMINPAGETITLE';
        $this->model_admin_users->user_add_inbase($data_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if ($error === 'add_rights' || $error === 'add_not_all_data' || $error === 'add_incorrect_email' || $error === 'add_login' || $error === 'add_pass'
            || $error === 'add_found'
            || $error === 'add_sql_error'
        ) {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $template = false;
            $this->administrators_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|administrators|add';
        $template = false;
        $this->administrators_view($result, $cache_category, $template, $title);
    }


    public function admin_status()
    {
        $data_array = false;
        if (isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if (isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if (isset($_GET['admin_id'])) {
            $data_array['get_user_id'] = intval($_GET['admin_id']);
        }
        if (isset($_POST['admin_id'])) {
            $data_array['post_user_id'] = $_POST['admin_id'];
        }
        $data_array['admin'] = 1;
        $title = '_MENUADMINS';
        $this->model_admin_users->user_status($data_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if ($error === 'status_rights' || $error === 'empty_data' || $error === 'status_sql_error' || $error === 'status_ok') {
            if ($error === 'empty_data') {
                header("Location: index.php?path=admin_administrators&func=index&lang=" . $this->registry->sitelang);
                exit();
            }
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $template = false;
            $this->administrators_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|administrators|status';
        $template = false;
        $this->administrators_view($result, $cache_category, $template, $title);
    }


    public function admin_edit()
    {
        $data_array = false;
        if (isset($_GET['admin_id'])) {
            $data_array['user_id'] = intval($_GET['admin_id']);
        }
        $data_array['admin'] = 1;
        if (!isset($data_array['user_id']) || $data_array['user_id'] == 0) {
            header("Location: index.php?path=admin_administrators&func=index&lang=" . $this->registry->sitelang);
            exit();
        }
        $title = '_ADMINISTRATIONEDITADMINPAGETITLE';
        if ($this->registry->main_class->get_user_rights() !== "main_admin" && isset($array['user_id'])
            && intval($this->registry->main_class->get_user_id()) != intval(
                $array['user_id']
            )
        ) {
            $error = 'edit_rights';
            $cache_category = false;
            $template = false;
            $this->administrators_view($error, $cache_category, $template, $title);
        }
        $type = $data_array['user_id'];
        $cache_category = 'modules|administrators|edit';
        $template = 'adminedit.html';
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->model_admin_users->user_edit($data_array);
            $error = $this->model_admin_users->get_property_value('error');
            $error_array = $this->model_admin_users->get_property_value('error_array');
            $result = $this->model_admin_users->get_property_value('result');
            if ($error === 'edit_rights' || $error === 'empty_data' || $error === 'edit_sql_error' || $error === 'not_found') {
                if ($error === 'empty_data') {
                    header("Location: index.php?path=admin_administrators&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $cache_category = false;
                $template = false;
                $this->administrators_view($error, $cache_category, $template, $title);
            }
            $this->assign_array($result);
        }
        $this->administrators_view($type, $cache_category, $template, $title);
    }


    public function admin_edit_inbase()
    {
        $post_array = false;
        if (isset($_POST['admin_edit_id'])) {
            $post_array['admin_edit_id'] = intval($_POST['admin_edit_id']);
        }
        if (!empty($_POST)) {
            $keys = [
                'admin_edit_name',
                'admin_edit_surname',
                'admin_edit_website',
                'admin_edit_email',
                'admin_edit_login',
                'admin_edit_newpassword',
                'admin_edit_newpassword2',
                'admin_edit_country',
                'admin_edit_city',
                'admin_edit_address',
                'admin_edit_telephone',
                'admin_edit_icq',
                'admin_edit_skype',
                'admin_edit_gmt',
                'admin_edit_notes',
                'admin_edit_activenotes',
            ];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $keys)) {
                    $post_array[$key] = trim($value);
                }
            }
        }
        if (isset($_POST['admin_edit_active'])) {
            $post_array['admin_edit_active'] = intval($_POST['admin_edit_active']);
        }
        if (isset($_POST['admin_edit_viewemail'])) {
            $post_array['admin_edit_viewemail'] = intval($_POST['admin_edit_viewemail']);
        }
        $post_array['admin'] = 1;
        $title = '_ADMINISTRATIONEDITADMINPAGETITLE';
        $this->model_admin_users->user_edit_inbase($post_array);
        $error = $this->model_admin_users->get_property_value('error');
        $error_array = $this->model_admin_users->get_property_value('error_array');
        $result = $this->model_admin_users->get_property_value('result');
        if ($error === 'edit_rights' || $error === 'edit_not_all_data' || $error === 'edit_incorrect_email' || $error === 'edit_login' || $error === 'edit_pass'
            || $error === 'edit_found'
            || $error === 'edit_sql_error'
            || $error === 'edit_ok'
        ) {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $template = false;
            $this->administrators_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|administrators|edit|' . $result;
        $template = false;
        $this->administrators_view($result, $cache_category, $template, $title);
    }
}