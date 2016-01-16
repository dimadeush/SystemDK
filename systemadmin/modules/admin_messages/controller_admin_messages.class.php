<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_messages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class controller_admin_messages extends controller_base
{


    /**
     * @var admin_messages
     */
    private $model_admin_messages;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_messages = singleton::getinstance('admin_messages', $registry);
    }


    private function messages_view($type, $cache_category = false, $template = false, $title)
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|messages|error';
        }
        if (empty($template)) {
            $template = 'message_update.html';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if ($template === 'message_update.html') {
                $this->assign("message_update", $type);
            }
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/messages/" . $template);
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
        $title = '_MENUMESSAGES';
        $cache_category = 'modules|messages|show';
        if (!$this->isCached(
            "systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $num_page . "|" . $this->registry->language
        )
        ) {
            $this->model_admin_messages->index($num_page);
            $error = $this->model_admin_messages->get_property_value('error');
            $error_array = $this->model_admin_messages->get_property_value('error_array');
            $result = $this->model_admin_messages->get_property_value('result');
            if ($error === 'unknown_page' || $error === 'show_sql_error') {
                if ($error === 'unknown_page') {
                    header("Location: index.php?path=admin_messages&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $cache_category = false;
                $template = false;
                $this->messages_view($error, $cache_category, $template, $title);
            }
            $this->assign_array($result);
        }
        $template = 'messages.html';
        $this->messages_view($num_page, $cache_category, $template, $title);
    }


    public function message_add()
    {
        $title = '_ADMINISTRATIONADDMESSAGEPAGETITLE';
        $this->model_admin_messages->message_add();
        $result = $this->model_admin_messages->get_property_value('result');
        $this->assign_array($result);
        $type = 'add';
        $cache_category = 'modules|messages';
        $template = 'message_add.html';
        $this->messages_view($type, $cache_category, $template, $title);
    }


    public function message_add_inbase()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'message_title',
                'message_content',
                'message_notes',
                'message_termexpireaction',
            ];
            $keys2 = [
                'message_status',
                'message_termexpire',
                'message_valueview',
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
        $title = '_ADMINISTRATIONADDMESSAGEPAGETITLE';
        $template = false;
        $this->model_admin_messages->message_add_inbase($data_array);
        $error = $this->model_admin_messages->get_property_value('error');
        $error_array = $this->model_admin_messages->get_property_value('error_array');
        $result = $this->model_admin_messages->get_property_value('result');
        if ($error === 'add_not_all_data' || $error === 'add_sql_error') {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->messages_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|messages';
        $this->messages_view($result, $cache_category, $template, $title);
    }


    public function message_status()
    {
        $data_array = false;
        if (isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if (isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if (isset($_GET['message_id'])) {
            $data_array['get_message_id'] = intval($_GET['message_id']);
        }
        if (isset($_POST['message_id'])) {
            $data_array['post_message_id'] = $_POST['message_id'];
        }
        $title = '_MENUMESSAGES';
        $template = false;
        $this->model_admin_messages->message_status($data_array);
        $error = $this->model_admin_messages->get_property_value('error');
        $error_array = $this->model_admin_messages->get_property_value('error_array');
        $result = $this->model_admin_messages->get_property_value('result');
        if ($error === 'empty_data' || $error === 'unknown_action' || $error === 'status_sql_error' || $error === 'status_ok') {
            if ($error === 'empty_data' || $error === 'unknown_action') {
                header("Location: index.php?path=admin_messages&func=index&lang=" . $this->registry->sitelang);
                exit();
            }
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->messages_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|messages|status';
        $this->messages_view($result, $cache_category, $template, $title);
    }


    public function message_edit()
    {
        $message_id = 0;
        if (isset($_GET['message_id'])) {
            $message_id = intval($_GET['message_id']);
        }
        $title = '_ADMINISTRATIONEDITMESSAGEPAGETITLE';
        $cache_category = 'modules|messages|edit';
        $template = 'message_edit.html';
        if (!$this->isCached(
            "systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $message_id . "|" . $this->registry->language
        )
        ) {
            $this->model_admin_messages->message_edit($message_id);
            $error = $this->model_admin_messages->get_property_value('error');
            $error_array = $this->model_admin_messages->get_property_value('error_array');
            $result = $this->model_admin_messages->get_property_value('result');
            if ($error === 'empty_data' || $error === 'edit_sql_error' || $error === 'edit_not_found') {
                if ($error === 'empty_data') {
                    header("Location: index.php?path=admin_messages&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $cache_category = false;
                $template = false;
                $this->messages_view($error, $cache_category, $template, $title);
            }
            $this->assign_array($result);
        }
        $this->messages_view($message_id, $cache_category, $template, $title);
    }


    public function message_edit_inbase()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'message_author',
                'message_title',
                'message_content',
                'message_notes',
                'message_termexpireaction',
            ];
            $keys2 = [
                'message_id',
                'message_status',
                'message_termexpire',
                'message_valueview',
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
        $title = '_ADMINISTRATIONEDITMESSAGEPAGETITLE';
        $template = false;
        $this->model_admin_messages->message_edit_inbase($data_array);
        $error = $this->model_admin_messages->get_property_value('error');
        $error_array = $this->model_admin_messages->get_property_value('error_array');
        $result = $this->model_admin_messages->get_property_value('result');
        if ($error === 'edit_not_all_data' || $error === 'edit_sql_error' || $error === 'edit_ok') {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $template = false;
            $this->messages_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|messages|save';
        $this->messages_view($result, $cache_category, $template, $title);
    }
}