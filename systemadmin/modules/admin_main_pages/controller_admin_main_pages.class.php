<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_main_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_main_pages extends controller_base {


    private $model_admin_main_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_main_pages = singleton::getinstance('admin_main_pages',$registry);
    }


    private function main_pages_view($type,$cache_category = false,$template = false,$title) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|main_pages|error';
        }
        if(empty($template)) {
            $template = 'main_pageupdate.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'main_pageupdate.html') {
                $this->assign("main_pageupdate",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/main_pages/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $title = '_MENUPAGES';
        $cache_category = 'modules|main_pages|show';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$num_page."|".$this->registry->language)) {
            $this->model_admin_main_pages->index($num_page);
            $error = $this->model_admin_main_pages->get_property_value('error');
            $error_array = $this->model_admin_main_pages->get_property_value('error_array');
            $result = $this->model_admin_main_pages->get_property_value('result');
            if($error === 'unknown_page' or $error === 'show_sql_error') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->main_pages_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'main_pages.html';
        $this->main_pages_view($num_page,$cache_category,$template,$title);
    }


    public function mainpage_add() {
        $this->model_admin_main_pages->mainpage_add();
        $result = $this->model_admin_main_pages->get_property_value('result');
        $this->assign_array($result);
        $title = '_ADMINISTRATIONADDPAGEPAGETITLE';
        $type = 'add';
        $cache_category = 'modules|main_pages';
        $template = 'main_pageadd.html';
        $this->main_pages_view($type,$cache_category,$template,$title);
    }


    public function mainpage_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'mainpage_author',
                    'mainpage_title',
                    'mainpage_content',
                    'mainpage_notes',
                    'mainpage_termexpireaction'
                );
                $keys2 = array(
                    'mainpage_status',
                    'mainpage_termexpire',
                    'mainpage_valueview'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONADDPAGEPAGETITLE';
        $template = false;
        $this->model_admin_main_pages->mainpage_add_inbase($data_array);
        $error = $this->model_admin_main_pages->get_property_value('error');
        $error_array = $this->model_admin_main_pages->get_property_value('error_array');
        $result = $this->model_admin_main_pages->get_property_value('result');
        if($error === 'add_not_all_data' or $error === 'add_sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->main_pages_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|main_pages|add';
        $this->main_pages_view($result,$cache_category,$template,$title);
    }


    public function mainpage_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['mainpage_id'])) {
            $data_array['get_mainpage_id'] = intval($_GET['mainpage_id']);
        }
        if(isset($_POST['mainpage_id'])) {
            $data_array['post_mainpage_id'] = $_POST['mainpage_id'];
        }
        $title = '_MENUPAGES';
        $template = false;
        $this->model_admin_main_pages->mainpage_status($data_array);
        $error = $this->model_admin_main_pages->get_property_value('error');
        $error_array = $this->model_admin_main_pages->get_property_value('error_array');
        $result = $this->model_admin_main_pages->get_property_value('result');
        if($error === 'empty_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->main_pages_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|main_pages|status';
        $this->main_pages_view($result,$cache_category,$template,$title);
    }


    public function mainpage_edit() {
        $main_page_id = 0;
        if(isset($_GET['mainpage_id'])) {
            $main_page_id = intval($_GET['mainpage_id']);
        }
        $title = '_ADMINISTRATIONEDITPAGEPAGETITLE';
        $cache_category = 'modules|main_pages|edit';
        $template = 'main_pageedit.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$main_page_id."|".$this->registry->language)) {
            $this->model_admin_main_pages->mainpage_edit($main_page_id);
            $error = $this->model_admin_main_pages->get_property_value('error');
            $error_array = $this->model_admin_main_pages->get_property_value('error_array');
            $result = $this->model_admin_main_pages->get_property_value('result');
            if($error === 'empty_data' or $error === 'edit_sql_error' or $error === 'edit_not_found') {
                if($error === 'empty_data') {
                    header("Location: index.php?path=admin_main_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->main_pages_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $this->main_pages_view($main_page_id,$cache_category,$template,$title);
    }


    public function mainpage_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'mainpage_author',
                    'mainpage_title',
                    'mainpage_content',
                    'mainpage_notes',
                    'mainpage_termexpireaction'
                );
                $keys2 = array(
                    'mainpage_id',
                    'mainpage_status',
                    'mainpage_termexpire',
                    'mainpage_valueview'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONEDITPAGEPAGETITLE';
        $cache_category = false;
        $template = false;
        $this->model_admin_main_pages->mainpage_edit_inbase($data_array);
        $error = $this->model_admin_main_pages->get_property_value('error');
        $error_array = $this->model_admin_main_pages->get_property_value('error_array');
        $result = $this->model_admin_main_pages->get_property_value('result');
        if($error === 'edit_not_all_data' or $error === 'edit_sql_error' or $error === 'edit_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $this->main_pages_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|main_pages|save';
        $this->main_pages_view($result,$cache_category,$template,$title);
    }
}