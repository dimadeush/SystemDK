<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_feedback.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class controller_admin_feedback extends controller_base
{


    /**
     * @var admin_feedback
     */
    private $model_admin_feedback;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_admin_feedback = singleton::getinstance('admin_feedback', $registry);
    }


    private function feedback_view($type, $cache_category = false, $template = false, $title)
    {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if (empty($cache_category)) {
            $cache_category = 'modules|feedback|error';
        }
        if (empty($template)) {
            $template = 'feedback_messages.html';
        }
        if (!$this->isCached("systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $type . "|" . $this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if ($template === 'feedback_messages.html') {
                $this->assign("feedback_message", $type);
            }
            $this->assign("include_center_up", "systemadmin/adminup.html");
            $this->assign("include_center", "systemadmin/modules/feedback/" . $template);
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
        $title = '_MENUFEEDBACK';
        $cache_category = 'modules|feedback|show';
        if (!$this->isCached(
            "systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $num_page . "|" . $this->registry->language
        )
        ) {
            $this->model_admin_feedback->index($num_page);
            $error = $this->model_admin_feedback->get_property_value('error');
            $error_array = $this->model_admin_feedback->get_property_value('error_array');
            $result = $this->model_admin_feedback->get_property_value('result');
            if ($error === 'unknown_page' || $error === 'show_sql_error') {
                if ($error === 'unknown_page') {
                    header("Location: index.php?path=admin_feedback&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $cache_category = false;
                $template = false;
                $this->feedback_view($error, $cache_category, $template, $title);
            }
            $this->assign_array($result);
        }
        $template = 'feedback_receivers.html';
        $this->feedback_view($num_page, $cache_category, $template, $title);
    }


    public function feedback_add()
    {
        $this->model_admin_feedback->feedback_add();
        $result = $this->model_admin_feedback->get_property_value('result');
        $title = '_ADMINISTRATIONADDFEEDBACKPAGETITLE';
        $cache_category = 'modules|feedback';
        $template = 'feedback_add.html';
        $this->feedback_view($result, $cache_category, $template, $title);
    }


    public function feedback_add_inbase()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'receiver_name',
                'receiver_email',
            ];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        if (isset($_POST['receiver_active'])) {
            $data_array['receiver_active'] = intval($_POST['receiver_active']);
        }
        $title = '_ADMINISTRATIONADDFEEDBACKPAGETITLE';
        $template = false;
        $this->model_admin_feedback->feedback_add_inbase($data_array);
        $error = $this->model_admin_feedback->get_property_value('error');
        $error_array = $this->model_admin_feedback->get_property_value('error_array');
        $result = $this->model_admin_feedback->get_property_value('result');
        if ($error === 'add_not_all_data' || $error === 'add_incorrect_email' || $error === 'add_sql_error') {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->feedback_view($error, $cache_category, $template, $title);
        }
        $cache_category = 'modules|feedback';
        $this->feedback_view($result, $cache_category, $template, $title);
    }


    public function feedback_edit()
    {
        $receiver_id = 0;
        if (isset($_GET['receiver_id'])) {
            $receiver_id = intval($_GET['receiver_id']);
        }
        $title = '_ADMINISTRATIONEDITFEEDBACKPAGETITLE';
        $cache_category = 'modules|feedback|edit';
        $template = 'feedback_edit.html';
        if (!$this->isCached(
            "systemadmin/main.html", $this->registry->sitelang . "|systemadmin|" . $cache_category . "|" . $receiver_id . "|" . $this->registry->language
        )
        ) {
            $this->model_admin_feedback->feedback_edit($receiver_id);
            $error = $this->model_admin_feedback->get_property_value('error');
            $error_array = $this->model_admin_feedback->get_property_value('error_array');
            $result = $this->model_admin_feedback->get_property_value('result');
            if ($error === 'empty_receiver_id' || $error === 'edit_sql_error' || $error === 'edit_not_found') {
                if ($error === 'empty_receiver_id') {
                    header("Location: index.php?path=admin_feedback&func=index&lang=" . $this->registry->sitelang);
                    exit();
                }
                if (!empty($error_array)) {
                    $this->assign("error", $error_array);
                }
                $cache_category = false;
                $template = false;
                $this->feedback_view($error, $cache_category, $template, $title);
            }
            $this->assign_array($result);
        }
        $this->feedback_view($receiver_id, $cache_category, $template, $title);
    }


    public function feedback_edit_inbase()
    {
        $data_array = false;
        if (!empty($_POST)) {
            $keys = [
                'receiver_name',
                'receiver_email',
            ];
            $keys2 = [
                'receiver_id',
                'receiver_active',
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
        $title = '_ADMINISTRATIONEDITFEEDBACKPAGETITLE';
        $cache_category = 'modules|feedback';
        $template = false;
        $this->model_admin_feedback->feedback_edit_inbase($data_array);
        $error = $this->model_admin_feedback->get_property_value('error');
        $error_array = $this->model_admin_feedback->get_property_value('error_array');
        $result = $this->model_admin_feedback->get_property_value('result');
        if ($error === 'edit_not_all_data' || $error === 'edit_sql_error' || $error === 'edit_ok') {
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->feedback_view($error, $cache_category, $template, $title);
        }
        $this->feedback_view($result, $cache_category, $template, $title);
    }


    public function feedback_status()
    {
        if (isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        }
        if (isset($_GET['receiver_id'])) {
            $data_array['receiver_id'] = intval($_GET['receiver_id']);
        } else {
            $data_array['receiver_id'] = 0;
        }
        $title = '_MENUFEEDBACK';
        $cache_category = 'modules|feedback|status';
        $template = false;
        $this->model_admin_feedback->feedback_status($data_array);
        $error = $this->model_admin_feedback->get_property_value('error');
        $error_array = $this->model_admin_feedback->get_property_value('error_array');
        $result = $this->model_admin_feedback->get_property_value('result');
        if ($error === 'empty_data' || $error === 'unknown_action' || $error === 'status_sql_error' || $error === 'status_ok') {
            if ($error === 'empty_data' || $error === 'unknown_action') {
                header("Location: index.php?path=admin_feedback&func=index&lang=" . $this->registry->sitelang);
                exit();
            }
            if (!empty($error_array)) {
                $this->assign("error", $error_array);
            }
            $cache_category = false;
            $this->feedback_view($error, $cache_category, $template, $title);
        }
        $this->feedback_view($result['feedback_message'], $cache_category, $template, $title);
    }
}