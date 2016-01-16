<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.4
 */
class controller_guestbook extends controller_base
{


    /**
     * @var guestbook
     */
    private $model_guestbook;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_guestbook = singleton::getinstance('guestbook', $registry);
    }


    private function guestbook_view($type, $titles, $cache_category = false, $cache = false, $template = false)
    {
        if (empty($cache_category)) {
            $cache_category = 'modules|guestbook|error';
        }
        if (empty($cache)) {
            $cache = $type;
        }
        if (empty($template)) {
            $template = 'guestbook_messages.html';
        }
        if ($template === 'guestbook_add.html') {
            $this->configLoad($this->registry->language . "/modules/guestbook/guestbook.conf");
            $this->assign("include_center", "modules/guestbook/" . $template);
            if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
                $this->registry->main_class->set_sitemeta($this->getConfigVars($titles['title']), $titles['description'], $titles['keywords']);
            }
        } elseif (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->configLoad($this->registry->language . "/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->getConfigVars($titles['title']), $titles['description'], $titles['keywords']);
            if ($template === 'guestbook_messages.html') {
                $this->assign("guestbook_message", $type);
            }
            $this->assign("include_center", "modules/guestbook/" . $template);
        }
        $this->display("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language);
        exit();
    }


    public function index()
    {
        if (isset($_GET['num_page']) && intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } else {
            $cache_user = "other";
        }
        $type = false;
        $titles = false;
        $cache_category = 'modules|guestbook|display|' . $cache_user;
        $cache = $num_page;
        $template = 'guestbook_show.html';
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_guestbook->index($num_page);
            $error = $this->model_guestbook->get_property_value('error');
            $result = $this->model_guestbook->get_property_value('result');
            if ($error === 'unknown_page') {
                if (SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /" . SITE_DIR . $this->registry->sitelang . "/guestbook/");
                } else {
                    header("Location: /" . SITE_DIR . "index.php?path=guestbook&lang=" . $this->registry->sitelang);
                }
                exit();
            }
            $this->assign_array($result);
            $titles['title'] = '_GUESTBOOK_MODULE_METATITLE';
            $titles['description'] = $result['systemdk_guestbook_metadescription'];
            $titles['keywords'] = $result['systemdk_guestbook_metakeywords'];
        }
        $this->guestbook_view($type, $titles, $cache_category, $cache, $template);
    }


    public function add()
    {
        $type = false;
        $titles = false;
        $cache_category = 'modules|guestbook';
        $cache = 'add';
        $template = 'guestbook_add.html';
        $this->model_guestbook->add();
        $result = $this->model_guestbook->get_property_value('result');
        $this->assign_array($result);
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $titles['title'] = '_GUESTBOOK_MODULE_METATITLE';
            $titles['description'] = $result['systemdk_guestbook_metadescription'];
            $titles['keywords'] = $result['systemdk_guestbook_metakeywords'];
        }
        $this->guestbook_view($type, $titles, $cache_category, $cache, $template);
    }


    public function insert()
    {
        $post_array = false;
        if (!empty($_POST)) {
            $keys = [
                'post_user_name',
                'post_user_country',
                'post_user_city',
                'post_user_telephone',
                'post_user_email',
                'post_text',
                'captcha',
            ];
            $keys2 = [
                'post_show_telephone',
                'post_show_email',
            ];
            foreach ($_POST as $key => $value) {
                if (in_array($key, $keys)) {
                    $post_array[$key] = trim($value);
                }
                if (in_array($key, $keys2)) {
                    $post_array[$key] = intval($value);
                }
            }
        }
        $this->model_guestbook->insert($post_array);
        $error = $this->model_guestbook->get_property_value('error');
        $error_array = $this->model_guestbook->get_property_value('error_array');
        $result = $this->model_guestbook->get_property_value('result');
        $titles['title'] = '_GUESTBOOK_MODULE_METATITLE';
        $titles['description'] = $result['systemdk_guestbook_metadescription'];
        $titles['keywords'] = $result['systemdk_guestbook_metakeywords'];
        if ($error === 'not_all_data' || $error === 'check_code' || $error === 'post_limit' || $error === 'user_email' || $error === 'post_length'
            || $error === 'not_insert'
        ) {
            if ($error === 'not_insert') {
                $this->assign("errortext", $error_array);
            }
            $this->guestbook_view($error, $titles);
        }
        $type = $result['guestbook_message'];
        $cache_category = 'modules|guestbook|insert';
        $this->guestbook_view($type, $titles, $cache_category);
    }
}