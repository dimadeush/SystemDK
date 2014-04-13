<?php


/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_system_pages.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2014 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.1
 */
class controller_admin_system_pages extends controller_base {


    private $model_admin_system_pages;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_system_pages = singleton::getinstance('admin_system_pages',$registry);
    }


    private function system_pages_view($type,$title,$cache_category = false,$cache = false,$template = false) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|system_pages|error';
        }
        if(empty($template)) {
            $template = 'system_pages_messages.html';
        }
        if(empty($cache)) {
            $cache = $type;
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'system_pages_messages.html') {
                $this->assign("system_pages_message",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/system_pages/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language);
        exit();
    }


    public function index() {
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $num_page = intval($_GET['num_page']);
        } else {
            $num_page = 1;
        }
        $title = '_MENUSYSTEMPAGES';
        $cache_category = 'modules|system_pages|show';
        $cache = $num_page;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_system_pages->index($num_page);
            $error = $this->model_admin_system_pages->get_property_value('error');
            $error_array = $this->model_admin_system_pages->get_property_value('error_array');
            $result = $this->model_admin_system_pages->get_property_value('result');
            if($error === 'sql_error' or $error === 'unknown_page') {
                if($error === 'unknown_page') {
                    header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $cache = 'show_'.$error;
                $this->system_pages_view($error,$title,$cache_category,$cache);
            }
            $this->assign_array($result);
        }
        $template = 'system_pages.html';
        $type = false;
        $this->system_pages_view($type,$title,$cache_category,$cache,$template);
    }


    public function systempage_add() {
        $type = false;
        $title = '_ADMINISTRATIONADDRULESPAGETITLE';
        $cache_category = 'modules|system_pages';
        $cache = 'add';
        $template = 'system_page_add.html';
        $this->model_admin_system_pages->systempage_add();
        $result = $this->model_admin_system_pages->get_property_value('result');
        $this->assign_array($result);
        $this->system_pages_view($type,$title,$cache_category,$cache,$template);
    }


    public function systempage_add_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'systempage_title',
                    'systempage_content',
                    'systempage_name',
                    'systempage_lang'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
            }
        }
        $title = '_ADMINISTRATIONADDRULESPAGETITLE';
        $this->model_admin_system_pages->systempage_add_inbase($data_array);
        $error = $this->model_admin_system_pages->get_property_value('error');
        $error_array = $this->model_admin_system_pages->get_property_value('error_array');
        $result = $this->model_admin_system_pages->get_property_value('result');
        if($error === 'not_all_data' or $error === 'find_such_system_page' or $error === 'sql_error') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'add_'.$error;
            $this->system_pages_view($error,$title,$cache_category,$cache);
        }
        $cache_category = 'modules|system_pages';
        $this->system_pages_view($result,$title,$cache_category);
    }


    public function systempage_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['action'] = trim($_GET['action']);
        }
        if(isset($_GET['systempage_id'])) {
            $data_array['systempage_id'] = intval($_GET['systempage_id']);
        }
        $title = '_MENUSYSTEMPAGES';
        $this->model_admin_system_pages->systempage_status($data_array);
        $error = $this->model_admin_system_pages->get_property_value('error');
        $error_array = $this->model_admin_system_pages->get_property_value('error_array');
        $result = $this->model_admin_system_pages->get_property_value('result');
        if($error === 'empty_data' or $error === 'unknown_action' or $error === 'sql_error' or $error === 'not_done') {
            if($error === 'empty_data' or $error === 'unknown_action') {
                header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'status_'.$error;
            $this->system_pages_view($error,$title,$cache_category,$cache);
        }
        $cache_category = 'modules|system_pages';
        $this->system_pages_view($result,$title,$cache_category);
    }


    public function systempage_edit() {
        $systempage_id = false;
        if(isset($_GET['systempage_id'])) {
            $systempage_id = intval($_GET['systempage_id']);
        }
        $title = '_ADMINISTRATIONEDITRULESPAGETITLE';
        $cache_category = 'modules|system_pages|edit';
        $cache = $systempage_id;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$cache."|".$this->registry->language)) {
            $this->model_admin_system_pages->systempage_edit($systempage_id);
            $error = $this->model_admin_system_pages->get_property_value('error');
            $error_array = $this->model_admin_system_pages->get_property_value('error_array');
            $result = $this->model_admin_system_pages->get_property_value('result');
            if($error === 'no_id' or $error === 'sql_error' or $error === 'not_found') {
                if($error === 'no_id') {
                    header("Location: index.php?path=admin_system_pages&func=index&lang=".$this->registry->sitelang);
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $cache = 'edit_'.$error;
                $this->system_pages_view($error,$title,$cache_category,$cache);
            }
            $this->assign_array($result);
        }
        $type = false;
        $template = 'system_page_edit.html';
        $this->system_pages_view($type,$title,$cache_category,$cache,$template);
    }


    public function systempage_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'systempage_name',
                    'systempage_title',
                    'systempage_content',
                    'systempage_lang'
                );
                $keys2 = array(
                    'systempage_id'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_ADMINISTRATIONEDITRULESPAGETITLE';
        $this->model_admin_system_pages->systempage_edit_inbase($data_array);
        $error = $this->model_admin_system_pages->get_property_value('error');
        $error_array = $this->model_admin_system_pages->get_property_value('error_array');
        $result = $this->model_admin_system_pages->get_property_value('result');
        if($error === 'not_all_data' or $error === 'find_such_system_page' or $error === 'sql_error' or $error === 'not_done') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $cache = 'edit_'.$error;
            $this->system_pages_view($error,$title,$cache_category,$cache);
        }
        $cache_category = 'modules|system_pages';
        $this->system_pages_view($result,$title,$cache_category);
    }
}