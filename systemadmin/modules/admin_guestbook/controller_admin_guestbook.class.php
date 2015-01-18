<?php
/**
 * Project:   SystemDK: PHP Content Management System
 * File:      controller_admin_guestbook.class.php
 *
 * @link      http://www.systemsdk.com/
 * @copyright 2015 SystemDK
 * @author    Dmitriy Kravtsov <admin@systemsdk.com>
 * @package   SystemDK
 * @version   3.2
 */
class controller_admin_guestbook extends controller_base {


    private $model_admin_guestbook;


    public function __construct($registry) {
        parent::__construct($registry);
        $this->model_admin_guestbook = singleton::getinstance('admin_guestbook',$registry);
    }


    private function guestbook_view($type,$cache_category = false,$template = false,$title) {
        $this->registry->controller_theme->display_theme_adminheader();
        $this->registry->controller_theme->display_theme_adminmain();
        $this->registry->main_class->assign_admin_info();
        if(empty($cache_category)) {
            $cache_category = 'modules|guestbook|error';
        }
        if(empty($template)) {
            $template = 'guestbook_messages.html';
        }
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->configLoad($this->registry->language."/systemadmin/modules/guestbook/guestbook.conf");
            $this->registry->main_class->set_sitemeta($this->getConfigVars($title));
            if($template === 'guestbook_messages.html') {
                $this->assign("guestbook_message",$type);
            }
            $this->assign("include_center_up","systemadmin/adminup.html");
            $this->assign("include_center","systemadmin/modules/guestbook/".$template);
        }
        $this->display("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language);
        exit();
    }


    public function index() {
        if(isset($_GET['pend'])) {
            $data_array['pend'] = intval($_GET['pend']);
        }
        if(isset($_GET['num_page']) and intval($_GET['num_page']) != 0) {
            $data_array['num_page'] = intval($_GET['num_page']);
        } else {
            $data_array['num_page'] = 1;
        }
        if(isset($_GET['pend']) and intval($_GET['pend']) != 0 and intval($_GET['pend']) == 1) {
            $temp = "pending";
            $title = '_GUESTBOOKPENDLIST';
        } else {
            $temp = "current";
            $title = '_GUESTBOOKTITLE';
        }
        $cache_category = 'modules|guestbook|show'.$temp;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$data_array['num_page']."|".$this->registry->language)) {
            $this->model_admin_guestbook->index($data_array);
            $error = $this->model_admin_guestbook->get_property_value('error');
            $error_array = $this->model_admin_guestbook->get_property_value('error_array');
            $result = $this->model_admin_guestbook->get_property_value('result');
            if($error === 'unknown_page' or $error === 'show_'.$temp.'_sql_error') {
                if($error === 'unknown_page') {
                    if($temp == "current") {
                        header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->guestbook_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'guestbook_posts.html';
        $this->guestbook_view($data_array['num_page'],$cache_category,$template,$title);
    }


    public function guestbook_status() {
        $data_array = false;
        if(isset($_GET['action'])) {
            $data_array['get_action'] = trim($_GET['action']);
        }
        if(isset($_POST['action'])) {
            $data_array['post_action'] = trim($_POST['action']);
        }
        if(isset($_GET['post_id'])) {
            $data_array['get_post_id'] = intval($_GET['post_id']);
        }
        if(isset($_POST['post_id'])) {
            $data_array['post_post_id'] = $_POST['post_id'];
        }
        $title = '_GUESTBOOK';
        $template = false;
        $this->model_admin_guestbook->guestbook_status($data_array);
        $error = $this->model_admin_guestbook->get_property_value('error');
        $error_array = $this->model_admin_guestbook->get_property_value('error_array');
        $result = $this->model_admin_guestbook->get_property_value('result');
        if($error === 'empty_pend_data' or $error === 'empty_data' or $error === 'unknown_action' or $error === 'status_sql_error' or $error === 'status_ok') {
            if($error === 'empty_pend_data') {
                header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
                exit();
            } elseif($error === 'empty_data') {
                header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
                exit();
            } elseif($error === 'unknown_action') {
                header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
                exit();
            }
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->guestbook_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|guestbook|status';
        $this->guestbook_view($result,$cache_category,$template,$title);
    }


    public function guestbook_edit() {
        $data_array['post_id'] = 0;
        if(isset($_GET['pend'])) {
            $data_array['pend'] = intval($_GET['pend']);
        }
        if(isset($_GET['post_id'])) {
            $data_array['post_id'] = intval($_GET['post_id']);
        }
        if(isset($_GET['pend']) and intval($_GET['pend']) != 0 and intval($_GET['pend']) == 1) {
            $temp = "pending";
        } else {
            $temp = "current";
        }
        $title = '_GUESTBOOKEDITTITLE';
        $type = $data_array['post_id'];
        $cache_category = 'modules|guestbook|edit'.$temp;
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|modules|guestbook|edit".$temp."|".$type."|".$this->registry->language)) {
            $this->model_admin_guestbook->guestbook_edit($data_array);
            $error = $this->model_admin_guestbook->get_property_value('error');
            $error_array = $this->model_admin_guestbook->get_property_value('error_array');
            $result = $this->model_admin_guestbook->get_property_value('result');
            if($error === 'empty_data' or $error === 'edit_sql_error' or $error === 'edit_not_found') {
                if($error === 'empty_data') {
                    if($temp === "current") {
                        header("Location: index.php?path=admin_guestbook&func=index&lang=".$this->registry->sitelang);
                    } else {
                        header("Location: index.php?path=admin_guestbook&func=index&pend=1&lang=".$this->registry->sitelang);
                    }
                    exit();
                }
                if(!empty($error_array)) {
                    $this->assign("error",$error_array);
                }
                $cache_category = false;
                $template = false;
                $this->guestbook_view($error,$cache_category,$template,$title);
            }
            $this->assign_array($result);
        }
        $template = 'guestbook_edit.html';
        $this->guestbook_view($type,$cache_category,$template,$title);
    }


    public function guestbook_edit_inbase() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'post_user_name',
                    'post_user_country',
                    'post_user_city',
                    'post_user_telephone',
                    'post_user_email',
                    'post_date',
                    'post_text',
                    'post_answer',
                    'post_answer_date',
                );
                $keys2 = array(
                    'post_id',
                    'post_show_telephone',
                    'post_show_email',
                    'post_hour',
                    'post_minute',
                    'post_status',
                    'post_answer_hour',
                    'post_answer_minute',
                    'pend'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_GUESTBOOKEDITTITLE';
        $template = false;
        $this->model_admin_guestbook->guestbook_edit_inbase($data_array);
        $error = $this->model_admin_guestbook->get_property_value('error');
        $error_array = $this->model_admin_guestbook->get_property_value('error_array');
        $result = $this->model_admin_guestbook->get_property_value('result');
        if($error === 'edit_not_all_data'or $error === 'edit_incorrect_email' or $error === 'edit_sql_error' or $error === 'edit_ok') {
            if(!empty($error_array)) {
                $this->assign("error",$error_array);
            }
            $cache_category = false;
            $this->guestbook_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|guestbook';
        $this->guestbook_view($result,$cache_category,$template,$title);
    }


    public function guestbook_config() {
        $title = '_GUESTBOOKCONFIGPAGETITLE';
        $type = 'config';
        $cache_category = 'modules|guestbook';
        $template = 'guestbook_config.html';
        if(!$this->isCached("systemadmin/main.html",$this->registry->sitelang."|systemadmin|".$cache_category."|".$type."|".$this->registry->language)) {
            $this->model_admin_guestbook->guestbook_config();
            $result = $this->model_admin_guestbook->get_property_value('result');
            $this->assign_array($result);
        }
        $this->guestbook_view($type,$cache_category,$template,$title);
    }


    public function guestbook_config_save() {
        $data_array = false;
        if(!empty($_POST)) {
            foreach($_POST as $key => $value) {
                $keys = array(
                    'systemdk_guestbook_metakeywords',
                    'systemdk_guestbook_metadescription',
                    'systemdk_guestbook_antispam_ipaddr',
                    'systemdk_guestbook_confirm_need',
                    'systemdk_guestbook_sortorder'
                );
                $keys2 = array(
                    'systemdk_guestbook_storynum',
                    'systemdk_guestbook_antispam_num'
                );
                if(in_array($key,$keys)) {
                    $data_array[$key] = trim($value);
                }
                if(in_array($key,$keys2)) {
                    $data_array[$key] = intval($value);
                }
            }
        }
        $title = '_GUESTBOOKCONFIGPAGETITLE';
        $template = false;
        $this->model_admin_guestbook->guestbook_config_save($data_array);
        $error = $this->model_admin_guestbook->get_property_value('error');
        $result = $this->model_admin_guestbook->get_property_value('result');
        if($error === 'conf_not_all_data' or $error === 'conf_no_file' or $error === 'conf_no_write') {
            $cache_category = false;
            $this->guestbook_view($error,$cache_category,$template,$title);
        }
        $cache_category = 'modules|guestbook';
        $this->guestbook_view($result,$cache_category,$template,$title);
    }
}