<?php

/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_news.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2016 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.5
 */
class controller_news extends controller_base
{


    /**
     * @var news
     */
    private $model_news;


    public function __construct($registry)
    {
        parent::__construct($registry);
        $this->model_news = singleton::getinstance('news', $registry);
    }


    private function news_view($type, $title, $cache_category = false, $cache = false, $template)
    {
        if (empty($cache_category)) {
            $cache_category = 'modules|news|error';
        }
        if (empty($cache)) {
            $cache = $type;
        }
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            if (!empty($title)) {
                $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            }
            $this->assign("include_center", "modules/news/" . $template);
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
        if (isset($_GET['cat_id']) && intval($_GET['cat_id']) != 0) {
            $cat_id = intval($_GET['cat_id']);
        } else {
            $cat_id = 'no';
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = "admin";
        } elseif ($this->registry->main_class->is_user()) {
            $cache_user = 'user';
        } else {
            $cache_user = 'guest';
        }
        $type = false;
        $title = '_MODULENEWSTITLE';
        $cache_category = 'modules|news|show|' . $cache_user . '|' . $cat_id;
        $cache = $num_page;
        $template = 'news.html';
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_news->index($num_page, $cat_id);
            $error = $this->model_news->get_property_value('error');
            $result = $this->model_news->get_property_value('result');
            if ($error === 'incorrect_page') {
                if (intval($cat_id) != 0) {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /" . SITE_DIR . $this->registry->sitelang . "/news/cat/" . $cat_id);
                    } else {
                        header("Location: /" . SITE_DIR . "index.php?path=news&cat_id=" . $cat_id . "&lang=" . $this->registry->sitelang);
                    }
                } else {
                    if (SYSTEMDK_MODREWRITE === 'yes') {
                        header("Location: /" . SITE_DIR . $this->registry->sitelang . "/news/");
                    } else {
                        header("Location: /" . SITE_DIR . "index.php?path=news&lang=" . $this->registry->sitelang);
                    }
                }
                exit();
            }
            $this->assign_array($result);
        }
        $this->news_view($type, $title, $cache_category, $cache, $template);
    }


    public function newsread()
    {
        if (isset($_GET['news_id']) && intval($_GET['news_id']) > 0) {
            $news_id = intval($_GET['news_id']);
        } else {
            $news_id = 0;
        }
        if (isset($_GET['num_page']) && intval($_GET['num_page']) > 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        if ($this->registry->main_class->is_admin()) {
            $cache_user = 'admin';
        } elseif ($this->registry->main_class->is_user()) {
            $cache_user = 'user';
        } else {
            $cache_user = '';
        }
        $this->model_news->newsread($news_id, $num_page, true);
        $type = false;
        $title = false;
        $cache_category = 'modules|news|newsread|' . $news_id . $cache_user;
        $cache = $num_page;
        $template = 'newsread.html';
        if (!$this->isCached("index.html", $this->registry->sitelang . "|" . $cache_category . "|" . $cache . "|" . $this->registry->language)) {
            $this->model_news->newsread($news_id, $num_page);
            $error = $this->model_news->get_property_value('error');
            $result = $this->model_news->get_property_value('result');
            $this->assign_array($result);
            if ($error === 'notselectnews' || $error === 'notactivnews' || $error === 'notfindnews') {
                $title = '_MODULENEWSTITLE';
                $cache_category = false;
                $cache = $error;
                $this->news_view($type, $title, $cache_category, $cache, $template);
            }
            $this->registry->main_class->set_sitemeta($result['news_all']['1']['news_title']);
        }
        $this->news_view($type, $title, $cache_category, $cache, $template);
    }
}