<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.3
 */
class controller_main_pages extends controller_base {


    private $model_main_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_main_pages = singleton::getinstance('main_pages',$registry);
    }


    private function main_pages_view($type,$title,$cache_category = false,$cache = false,$template = false) {
        if(empty($cache_category)) {
            $cache_category = 'modules|main_pages|error';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if(empty($template)) {
            $template = 'main_pages.html';
        }
        if(!$this->isCached("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language)) {
            if(!empty($title)) {
                $this->registry->main_class->set_sitemeta($title);
            }
            $this->assign("include_center","modules/main_pages/".$template);
        }
        $this->display("index.html",$this->registry->sitelang."|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        $main_page_id = 0;
        if(!empty($_GET['main_pageid'])) {
            $main_page_id = intval($_GET['main_pageid']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $type = false;
        $this->model_main_pages->index($main_page_id,$num_page);
        $error = $this->model_main_pages->get_property_value('error');
        $result = $this->model_main_pages->get_property_value('result');
        $this->assign_array($result);
        if($error === 'empty_data' or $error === 'nopage' or $error === 'notactive' or $error === 'noaccess') {
            if($error === 'empty_data') {
                if(SYSTEMDK_MODREWRITE === 'yes') {
                    header("Location: /".SITE_DIR."lang/".$this->registry->sitelang."/");
                } else {
                    header("Location: /".SITE_DIR."index.php?lang=".$this->registry->sitelang);
                }
                exit();
            }
            $title = false;
            $cache_category = false;
            $cache = $error;
            $this->main_pages_view($type,$title,$cache_category,$cache);
        }
        $title = $result['mainpage_all']['mainpage_title'];
        $cache_category = 'modules|main_pages|show|'.$main_page_id;
        $cache = $num_page;
        $this->main_pages_view($type,$title,$cache_category,$cache);
    }
}